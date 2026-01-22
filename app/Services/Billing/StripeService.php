<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\URL;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/**
 * Comprehensive Stripe billing service.
 *
 * Handles:
 * - Customer creation and management
 * - Checkout sessions for new subscriptions
 * - Billing portal for subscription management
 * - Plan changes and upgrades/downgrades
 * - Invoice retrieval
 */
class StripeService
{
    protected ?StripeClient $stripe = null;

    /**
     * Get the Stripe client (lazy initialization).
     */
    protected function getStripeClient(): StripeClient
    {
        if ($this->stripe === null) {
            $secret = config('cashier.secret');

            if (!$secret) {
                throw new \RuntimeException('Stripe secret key not configured. Set STRIPE_SECRET in .env');
            }

            $this->stripe = new StripeClient($secret);
        }

        return $this->stripe;
    }

    /**
     * Create a Stripe customer for a tenant.
     */
    public function createCustomer(Tenant $tenant, array $metadata = []): string
    {
        if ($tenant->hasStripeId()) {
            return $tenant->stripe_id;
        }

        $customer = $this->getStripeClient()->customers->create([
            'email' => $tenant->users()->first()?->email,
            'name' => $tenant->name,
            'metadata' => array_merge([
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
            ], $metadata),
        ]);

        $tenant->update(['stripe_id' => $customer->id]);

        return $customer->id;
    }

    /**
     * Create a Stripe Checkout session for new subscription.
     *
     * @param Tenant $tenant The tenant subscribing
     * @param Plan $plan The plan to subscribe to
     * @param string $billingPeriod 'monthly' or 'yearly'
     * @param string|null $successUrl Custom success URL
     * @param string|null $cancelUrl Custom cancel URL
     * @return CheckoutSession
     * @throws ApiErrorException
     */
    public function createCheckoutSession(
        Tenant $tenant,
        Plan $plan,
        string $billingPeriod = 'monthly',
        ?string $successUrl = null,
        ?string $cancelUrl = null
    ): CheckoutSession {
        // Ensure customer exists
        $customerId = $this->createCustomer($tenant);

        // Get the correct price ID for the billing period
        $priceId = $plan->getStripePriceId($billingPeriod);

        if (!$priceId) {
            throw new \RuntimeException("No Stripe price ID configured for plan {$plan->code} ({$billingPeriod})");
        }

        // Build session parameters
        $params = [
            'customer' => $customerId,
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'success_url' => $successUrl ?? $this->getDefaultSuccessUrl($tenant),
            'cancel_url' => $cancelUrl ?? $this->getDefaultCancelUrl(),
            'subscription_data' => [
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'plan_code' => $plan->code,
                    'billing_period' => $billingPeriod,
                ],
            ],
            'metadata' => [
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
            ],
            'allow_promotion_codes' => true,
            'billing_address_collection' => 'required',
        ];

        // Add trial if plan has trial days and tenant hasn't used trial
        if ($plan->trial_days > 0 && !$tenant->hasUsedTrial()) {
            $params['subscription_data']['trial_period_days'] = $plan->trial_days;
        }

        return $this->getStripeClient()->checkout->sessions->create($params);
    }

    /**
     * Create a Stripe Billing Portal session.
     *
     * Allows customers to manage their subscription, payment methods, and invoices.
     *
     * @param Tenant $tenant
     * @param string|null $returnUrl URL to return to after portal
     * @return PortalSession
     * @throws ApiErrorException
     */
    public function createBillingPortalSession(Tenant $tenant, ?string $returnUrl = null): PortalSession
    {
        if (!$tenant->hasStripeId()) {
            throw new \RuntimeException('Tenant does not have a Stripe customer ID');
        }

        return $this->getStripeClient()->billingPortal->sessions->create([
            'customer' => $tenant->stripe_id,
            'return_url' => $returnUrl ?? $this->getDefaultReturnUrl(),
        ]);
    }

    /**
     * Change a tenant's subscription plan.
     *
     * @param Tenant $tenant
     * @param Plan $newPlan
     * @param string $billingPeriod 'monthly' or 'yearly'
     * @param bool $prorate Whether to prorate the change
     * @return bool
     * @throws IncompletePayment
     */
    public function changePlan(
        Tenant $tenant,
        Plan $newPlan,
        string $billingPeriod = 'monthly',
        bool $prorate = true
    ): bool {
        $subscription = $tenant->subscription('default');

        if (!$subscription) {
            throw new \RuntimeException('Tenant does not have an active subscription');
        }

        $priceId = $newPlan->getStripePriceId($billingPeriod);

        if (!$priceId) {
            throw new \RuntimeException("No Stripe price ID configured for plan {$newPlan->code}");
        }

        // Use Laravel Cashier's swap method for seamless plan changes
        if ($prorate) {
            $subscription->swap($priceId);
        } else {
            $subscription->noProrate()->swap($priceId);
        }

        // Provision new plan's features and quotas
        $newPlan->provisionForTenant($tenant);

        logger()->info('Tenant plan changed', [
            'tenant_id' => $tenant->id,
            'new_plan' => $newPlan->code,
            'billing_period' => $billingPeriod,
        ]);

        return true;
    }

    /**
     * Cancel a tenant's subscription.
     *
     * @param Tenant $tenant
     * @param bool $immediately Cancel immediately or at period end
     * @return bool
     */
    public function cancelSubscription(Tenant $tenant, bool $immediately = false): bool
    {
        $subscription = $tenant->subscription('default');

        if (!$subscription) {
            return false;
        }

        if ($immediately) {
            $subscription->cancelNow();
        } else {
            $subscription->cancel();
        }

        logger()->info('Tenant subscription canceled', [
            'tenant_id' => $tenant->id,
            'immediately' => $immediately,
        ]);

        return true;
    }

    /**
     * Resume a canceled subscription.
     *
     * @param Tenant $tenant
     * @return bool
     */
    public function resumeSubscription(Tenant $tenant): bool
    {
        $subscription = $tenant->subscription('default');

        if (!$subscription || !$subscription->onGracePeriod()) {
            return false;
        }

        $subscription->resume();

        logger()->info('Tenant subscription resumed', [
            'tenant_id' => $tenant->id,
        ]);

        return true;
    }

    /**
     * Get tenant's invoices.
     *
     * @param Tenant $tenant
     * @param int $limit
     * @return array
     */
    public function getInvoices(Tenant $tenant, int $limit = 10): array
    {
        if (!$tenant->hasStripeId()) {
            return [];
        }

        return $tenant->invoices($limit)
            ->map(fn ($invoice) => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'date' => $invoice->date()->toDateString(),
                'total' => $invoice->total(),
                'status' => $invoice->status,
                'pdf_url' => $invoice->invoice_pdf,
                'hosted_invoice_url' => $invoice->hosted_invoice_url,
            ])
            ->toArray();
    }

    /**
     * Get upcoming invoice preview.
     *
     * @param Tenant $tenant
     * @return array|null
     */
    public function getUpcomingInvoice(Tenant $tenant): ?array
    {
        if (!$tenant->hasStripeId()) {
            return null;
        }

        try {
            $invoice = $tenant->upcomingInvoice();

            if (!$invoice) {
                return null;
            }

            return [
                'subtotal' => $invoice->subtotal(),
                'tax' => $invoice->tax(),
                'total' => $invoice->total(),
                'next_payment_date' => $invoice->date()?->toDateString(),
                'lines' => collect($invoice->lines->data)->map(fn ($line) => [
                    'description' => $line->description,
                    'amount' => $line->amount / 100,
                ])->toArray(),
            ];
        } catch (\Exception $e) {
            logger()->warning('Failed to get upcoming invoice', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Update tenant's default payment method.
     *
     * @param Tenant $tenant
     * @param string $paymentMethodId
     * @return bool
     */
    public function updatePaymentMethod(Tenant $tenant, string $paymentMethodId): bool
    {
        try {
            $tenant->updateDefaultPaymentMethod($paymentMethodId);

            logger()->info('Tenant payment method updated', [
                'tenant_id' => $tenant->id,
            ]);

            return true;
        } catch (\Exception $e) {
            logger()->error('Failed to update payment method', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get subscription status for a tenant.
     *
     * @param Tenant $tenant
     * @return array
     */
    public function getSubscriptionStatus(Tenant $tenant): array
    {
        $subscription = $tenant->subscription('default');

        if (!$subscription) {
            return [
                'status' => 'none',
                'has_subscription' => false,
                'is_active' => false,
                'is_on_trial' => false,
                'is_canceled' => false,
                'is_on_grace_period' => false,
            ];
        }

        return [
            'status' => $subscription->stripe_status,
            'has_subscription' => true,
            'is_active' => $subscription->active(),
            'is_on_trial' => $subscription->onTrial(),
            'is_canceled' => $subscription->canceled(),
            'is_on_grace_period' => $subscription->onGracePeriod(),
            'trial_ends_at' => $subscription->trial_ends_at?->toDateString(),
            'ends_at' => $subscription->ends_at?->toDateString(),
            'current_period_end' => $this->getCurrentPeriodEnd($subscription),
        ];
    }

    /**
     * Get current period end date from Stripe.
     */
    protected function getCurrentPeriodEnd($subscription): ?string
    {
        try {
            $stripeSubscription = $this->getStripeClient()->subscriptions->retrieve($subscription->stripe_id);
            return date('Y-m-d', $stripeSubscription->current_period_end);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get default success URL for checkout.
     */
    protected function getDefaultSuccessUrl(Tenant $tenant): string
    {
        return URL::signedRoute('checkout.success', [
            'tenant' => $tenant->id,
        ]);
    }

    /**
     * Get default cancel URL for checkout.
     */
    protected function getDefaultCancelUrl(): string
    {
        return url('/pricing');
    }

    /**
     * Get default return URL for billing portal.
     */
    protected function getDefaultReturnUrl(): string
    {
        return url('/billing');
    }
}
