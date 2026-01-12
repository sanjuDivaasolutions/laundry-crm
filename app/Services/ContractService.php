<?php
/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 17/10/24, 5:58 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Services;

use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Models\Contract;
use App\Models\ContractItem;
use App\Models\ContractRevision;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class ContractService
{
    /**
     * @throws \Exception
     */
    public static function create(StoreContractRequest $request): Contract
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request, &$obj) {
            $obj = new Contract();
            $data = $request->validated();
            unset($data['revision'], $data['items'], $data['term']);
            $obj->fill($data);
            $obj->save();
            self::updateRelatives($request, $obj);
            return true;
        });
        return $obj;
    }

    public static function update(UpdateContractRequest $request, $obj): Contract
    {
        $obj->update($request->validated());
        self::updateRelatives($request, $obj);
        return $obj;
    }

    private static function updateRelatives($request, $obj): void
    {
        self::updateItems($request, $obj);
        self::updateRevision($obj, $request);
        self::updateTerms($request, $obj);
        self::updateTotals($obj);
        //self::updateInstallments($obj);
    }

    public static function prepareItems($items, $parentField, $parentId = null): array
    {
        $result = [];
        foreach ($items as $item) {
            $item[$parentField] = $parentId;
            if (!isset($item['product_id'])) {
                $item['product_id'] = $item['product']['id'];
            }
            unset($item['product']);
            $result[] = $item;
        }
        return $result;
    }

    public static function getItemArray($obj)
    {
        $contractId = $obj->id;
        $items = [];
        if ($contractId) {
            $existing = ContractItem::query()->where('contract_id', $contractId)->get();
            if ($existing->count() > 1) {
                ContractItem::query()->where('contract_id', $contractId)->delete();
            } else {
                $existingArray = $existing->toArray();
                $items = $existingArray[0];
                $items['amount'] = $obj->amount;
            }
        }
        if ($items) return $items;

        return [
            [
                'id'                   => null,
                'contract_id'          => $contractId,
                'contract_revision_id' => null,
                'service_id'           => config('contract.defaults.service.id', 1),
                'description'          => null,
                'remark'               => null,
                'amount'               => $obj->amount,
            ],
        ];
    }

    private static function updateTerms($request, $obj): void
    {
        $obj->term()->sync($request->input('term.*.id', []));
    }

    private static function updateItems($request, $obj)
    {
        $items = self::prepareItems($request->input('items', []), 'contract_id', $obj->id);
        $request->merge(['items' => $items]);
        ControllerService::updateChild($request, $obj, 'items', ContractItem::class, 'items', 'contract_id');
    }

    public static function updateRevision(Contract $contract, $request): void
    {
        $revision = ContractRevision::query()->where('active', 1)->findOrNew($contract->id);
        $data = $request->input('revision', []);
        $revision->fill($data);
        $revision->contract_type = $data['contract_type'];
        $revision->contract_id = $contract->id;
        $revision->active = 1;
        $revision->user_id = $revision->user_id ?: $contract->user_id;
        $revision->save();

        $contract->items()->update(['contract_revision_id' => $revision->id]);
    }

    public static function updateTotals(Contract $contract): void
    {
        $revision = $contract->revision()->first();
        $subTotal = $contract->items()->sum('amount');
        $taxTotal = $subTotal * ($revision->tax_rate / 100);
        $grandTotal = $subTotal + $taxTotal;

        $revision->sub_total = $subTotal;
        $revision->tax_total = $taxTotal;
        $revision->grand_total = $grandTotal;
        $revision->save();
    }

    public static function updateInstallments(Contract $contract): void
    {
        $contract->installments()->delete();

        $installmentCount = $contract->installment_count;
        $installmentAmount = $contract->sub_total;

        for ($i = 0; $i < $installmentCount; $i++) {
            $installment = new Installment();
            $installment->contract_id = $contract->id;
            //$installment->amount = $i < $installmentCount ? $installmentAmount : $contract->sub_total - ($installmentAmount * ($installmentCount - 1));

            $installment->sub_total = $contract->sub_total;
            $installment->tax_total = $contract->tax_total;
            $installment->grand_total = $contract->grand_total;
            $installment->tax_rate = $contract->tax_rate;

            $installment->date = Carbon::createFromFormat(config('project.date_format'), $contract->start_date)->addMonths($i)->format(config('project.date_format'));
            $installment->code = $contract->code . '-' . $i;
            $installment->serial_number = $i + 1;
            $installment->save();
        }
    }

    public static function removeContract(Contract $contract): void
    {
        $contract->items()->delete();
        $contract->term()->detach();
        $contract->revision()->delete();
        $contract->delete();
    }

    public static function sendPaymentLink(Contract $contract): void
    {
        $contract->load('client');

        $name = $contract->client->company_name;
        $email = $contract->client->email;

        $url = route('subscription-checkout', [$contract->id]);

        $values = [
            'name'  => $name,
            'email' => $email,
            'link'  => $url,
        ];

        Mail::to($contract->client->email)
            ->send(new SubscriptionPaymentActivationNotificationMail($values));
    }

    public static function hasActiveSubscription(Contract $contract): bool
    {
        $contract->load('revision.subscription');
        return $contract->revision->subscription && $contract->revision->subscription->stripe_status === 'active';
    }
}
