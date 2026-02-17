<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    /**
     * Points earned per currency unit spent.
     */
    protected const POINTS_PER_UNIT = 1;

    /**
     * Tier thresholds (total points earned lifetime).
     */
    protected const TIERS = [
        'bronze' => 0,
        'silver' => 500,
        'gold' => 2000,
        'platinum' => 5000,
    ];

    /**
     * Bonus multiplier per tier.
     */
    protected const TIER_MULTIPLIER = [
        'bronze' => 1.0,
        'silver' => 1.25,
        'gold' => 1.5,
        'platinum' => 2.0,
    ];

    /**
     * Award loyalty points for a completed order.
     */
    public function awardPointsForOrder(Order $order): ?LoyaltyTransaction
    {
        $customer = $order->customer;
        if (! $customer) {
            return null;
        }

        $multiplier = self::TIER_MULTIPLIER[$customer->loyalty_tier] ?? 1.0;
        $basePoints = (int) floor((float) $order->total_amount * self::POINTS_PER_UNIT);
        $points = (int) floor($basePoints * $multiplier);

        if ($points <= 0) {
            return null;
        }

        return DB::transaction(function () use ($customer, $order, $points) {
            $newBalance = $customer->loyalty_points + $points;

            $transaction = LoyaltyTransaction::create([
                'tenant_id' => $customer->tenant_id,
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'type' => 'earned',
                'points' => $points,
                'balance_after' => $newBalance,
                'description' => "Earned from order {$order->order_number}",
            ]);

            $customer->update([
                'loyalty_points' => $newBalance,
                'total_orders_count' => $customer->total_orders_count + 1,
                'total_spent' => $customer->total_spent + $order->total_amount,
            ]);

            $this->updateTier($customer);

            return $transaction;
        });
    }

    /**
     * Redeem loyalty points for a discount.
     */
    public function redeemPoints(Customer $customer, int $points, ?Order $order = null): ?LoyaltyTransaction
    {
        if ($points <= 0 || $customer->loyalty_points < $points) {
            return null;
        }

        return DB::transaction(function () use ($customer, $points, $order) {
            $newBalance = $customer->loyalty_points - $points;

            $transaction = LoyaltyTransaction::create([
                'tenant_id' => $customer->tenant_id,
                'customer_id' => $customer->id,
                'order_id' => $order?->id,
                'type' => 'redeemed',
                'points' => -$points,
                'balance_after' => $newBalance,
                'description' => $order
                    ? "Redeemed on order {$order->order_number}"
                    : 'Points redeemed',
            ]);

            $customer->update(['loyalty_points' => $newBalance]);

            return $transaction;
        });
    }

    /**
     * Add bonus points (promotions, birthday, etc.).
     */
    public function addBonusPoints(Customer $customer, int $points, string $reason): LoyaltyTransaction
    {
        $newBalance = $customer->loyalty_points + $points;

        $transaction = LoyaltyTransaction::create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->id,
            'type' => 'bonus',
            'points' => $points,
            'balance_after' => $newBalance,
            'description' => $reason,
        ]);

        $customer->update(['loyalty_points' => $newBalance]);
        $this->updateTier($customer);

        return $transaction;
    }

    /**
     * Get loyalty summary for a customer.
     */
    public function getCustomerLoyaltySummary(Customer $customer): array
    {
        $totalEarned = (int) LoyaltyTransaction::where('customer_id', $customer->id)
            ->where('type', 'earned')
            ->sum('points');

        $totalRedeemed = (int) abs(LoyaltyTransaction::where('customer_id', $customer->id)
            ->where('type', 'redeemed')
            ->sum('points'));

        $nextTier = $this->getNextTier($customer->loyalty_tier);
        $pointsToNextTier = $nextTier ? self::TIERS[$nextTier] - $totalEarned : 0;

        return [
            'current_points' => $customer->loyalty_points,
            'tier' => $customer->loyalty_tier,
            'total_earned' => $totalEarned,
            'total_redeemed' => $totalRedeemed,
            'tier_multiplier' => self::TIER_MULTIPLIER[$customer->loyalty_tier] ?? 1.0,
            'next_tier' => $nextTier,
            'points_to_next_tier' => max(0, $pointsToNextTier),
            'total_orders' => $customer->total_orders_count,
            'total_spent' => (float) $customer->total_spent,
        ];
    }

    /**
     * Update customer tier based on total earned points.
     */
    protected function updateTier(Customer $customer): void
    {
        $totalEarned = (int) LoyaltyTransaction::where('customer_id', $customer->id)
            ->where('type', 'earned')
            ->sum('points');

        $newTier = 'bronze';
        foreach (array_reverse(self::TIERS) as $tier => $threshold) {
            if ($totalEarned >= $threshold) {
                $newTier = $tier;
                break;
            }
        }

        if ($customer->loyalty_tier !== $newTier) {
            $customer->update(['loyalty_tier' => $newTier]);
        }
    }

    /**
     * Get the next tier above the current one.
     */
    protected function getNextTier(string $currentTier): ?string
    {
        $tiers = array_keys(self::TIERS);
        $currentIndex = array_search($currentTier, $tiers);

        return $tiers[$currentIndex + 1] ?? null;
    }
}
