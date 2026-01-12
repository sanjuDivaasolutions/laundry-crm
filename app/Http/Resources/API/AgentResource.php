<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class AgentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'commission_rate' => (float) $this->commission_rate,
            'commission_type' => $this->commission_type,
            'fixed_commission' => (float) $this->fixed_commission,
            'active' => $this->active,
            'user_id' => $this->user_id,
            'user' => $this->when($this->relationLoaded('user'), function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'notes' => $this->notes,
            'total_pending_commissions' => $this->when(isset($this->total_pending_commissions), (float) $this->total_pending_commissions),
            'total_approved_commissions' => $this->when(isset($this->total_approved_commissions), (float) $this->total_approved_commissions),
            'total_paid_commissions' => $this->when(isset($this->total_paid_commissions), (float) $this->total_paid_commissions),
            'commissions' => $this->when($this->relationLoaded('commissions'), function () {
                return $this->commissions->map(function ($commission) {
                    return [
                        'id' => $commission->id,
                        'commission_amount' => (float) $commission->commission_amount,
                        'commission_rate' => (float) $commission->commission_rate,
                        'commission_type' => $commission->commission_type,
                        'status' => $commission->status,
                        'commission_date' => $commission->commission_date,
                        'paid_date' => $commission->paid_date,
                        'notes' => $commission->notes,
                        'commissionable_type' => $commission->commissionable_type,
                        'commissionable_id' => $commission->commissionable_id,
                        'commissionable' => $this->when($commission->relationLoaded('commissionable'), function () use ($commission) {
                            if ($commission->commissionable) {
                                return [
                                    'type' => class_basename($commission->commissionable),
                                    'id' => $commission->commissionable->id,
                                    'number' => $commission->commissionable->order_number ?? $commission->commissionable->invoice_number ?? null,
                                ];
                            }
                            return null;
                        }),
                        'approved_by' => $this->when($commission->relationLoaded('approvedBy'), function () use ($commission) {
                            return $commission->approvedBy ? [
                                'id' => $commission->approvedBy->id,
                                'name' => $commission->approvedBy->name,
                            ] : null;
                        }),
                        'paid_by' => $this->when($commission->relationLoaded('paidBy'), function () use ($commission) {
                            return $commission->paidBy ? [
                                'id' => $commission->paidBy->id,
                                'name' => $commission->paidBy->name,
                            ] : null;
                        }),
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}