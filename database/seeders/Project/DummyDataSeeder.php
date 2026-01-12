<?php
/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 05/02/25, 10:50 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace Database\Seeders\Project;

use App\Http\Requests\ExpenseTypeRequest;
use App\Http\Requests\PaymentModeRequest;
use App\Http\Requests\StoreContractTermRequest;
use App\Http\Requests\StoreServiceRequest;
use App\Models\Company;
use App\Models\ContractTerm;
use App\Models\ExpenseType;
use App\Models\PaymentMode;
use App\Models\Product;
use App\Services\FormRequestService;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->services();
        $this->contractTerms();
        $this->contracts();

        $this->expenseTypes();
      /*   $this->paymentModes(); */
    }

    private function services(): void
    {
        $company = Company::query()->find(config('system.defaults.service_company.id', 2));
        $services = [
            [
                'name'        => 'Hoarding Rent',
                'description' => 'Basic Hoarding Rent',
                'sku'         => 'HR-001',
                'type'        => 'service',
                'price'       => 100,
                'active'      => 1,
                'user_id'     => 1,
                'company'     => $company,
                'category'    => null,
            ],
        ];
        foreach ($services as $serviceData) {
            $request = FormRequestService::getManualRequestObject($serviceData, StoreServiceRequest::class);
            $p = Product::query()->create($request->validated());

            $p->price()->create([
                'sale_price'        => $serviceData['price'],
                'purchase_price'    => 0,
                'lowest_sale_price' => $serviceData['price'],
                'unit_id'           => null,
            ]);
        }
    }

    private function contractTerms(): void
    {
        $terms = [
            [
                'name'        => 'Rent Payment',
                'description' => 'Rent is due on the same date each month, with a late fee for overdue payments.',
                'sequence'    => 1,
                'active'      => 1
            ],
            [
                'name'        => 'Security Deposit',
                'description' => 'A security deposit is required and will be refunded after the lease ends, subject to deductions.',
                'sequence'    => 2,
                'active'      => 1
            ],
            [
                'name'        => 'Maintenance and Repairs',
                'description' => 'Tenant is responsible for general maintenance; Landlord handles major repairs like plumbing and electrical issues.',
                'sequence'    => 3,
                'active'      => 1
            ],
            [
                'name'        => 'Occupancy',
                'description' => 'Only the listed tenants may live in the property; subletting is not allowed without permission.',
                'sequence'    => 4,
                'active'      => 1
            ],
            [
                'name'        => 'Termination and Renewal',
                'description' => 'The lease can be terminated with prior notice, and renewal is subject to mutual agreement.',
                'sequence'    => 5,
                'active'      => 1
            ]
        ];

        foreach ($terms as $contractTermData) {
            $request = FormRequestService::getManualRequestObject($contractTermData, StoreContractTermRequest::class);
            ContractTerm::query()->create($request->validated());
        }
    }

    private function contracts()
    {
        /*$contracts = [
            [
                'client_id' => 1,
                'date'      => Carbon::now()->format(config('project.date_format')),
                'term'      => [
                    ['id' => 1],
                    ['id' => 2],
                ],
                'amount'    => 100,
                'user_id'   => 1,
                'revision'  => [
                    'id'                  => null,
                    'start_date'          => Carbon::now()->format(config('project.date_format')),
                    'contract_type'       => 'stripe',
                    'limited_installment' => 0,
                    'active'              => 1,
                    'tax_rate'            => 5,
                    'sub_total'           => 100,
                    'tax_total'           => 5,
                    'grand_total'         => 105,
                    'user_id'             => 1,
                ],
            ],
        ];
        foreach ($contracts as $contractData) {
            $request = FormRequestService::getManualRequestObject($contractData, StoreContractRequest::class);
            ContractService::create($request);
        }*/
    }

    private function expenseTypes(): void
    {
        $expenseTypes = [
            [
                'name'   => 'Labour',
                'active' => 1,
            ],
            [
                'name'   => 'Dinner/Lunch With Client',
                'active' => 1,
            ],
            [
                'name'   => 'Gift for Client',
                'active' => 1,
            ],
            [
                'name'   => 'Gas Expense',
                'active' => 1,
            ],
            [
                'name'   => 'Business Warehouse Expense',
                'active' => 1,
            ],
            [
                'name'   => 'Order Shipment to Client',
                'active' => 1,
            ],
            [
                'name'   => 'Exhibitions',
                'active' => 1,
            ],
            [
                'name'   => 'Company Vehicle',
                'active' => 1,
            ],
            [
                'name'   => 'Pranshu Personal Expense',
                'active' => 1,
            ],
            [
                'name'   => 'Shipping Boxes for Orders',
                'active' => 1,
            ],
            [
                'name'   => 'LMIA Work Permit - Dhruv Patel',
                'active' => 1,
            ],
            [
                'name'   => 'Lawyer Fees Patent',
                'active' => 1,
            ],
            [
                'name'   => 'Business Associations',
                'active' => 1,
            ],
            [
                'name'   => 'Business Trip',
                'active' => 1,
            ],
            [
                'name'   => 'Marketing Expense',
                'active' => 1,
            ],
            [
                'name'   => 'Accounting',
                'active' => 1,
            ],
            [
                'name'   => 'Vehicle Registration',
                'active' => 1,
            ],
            [
                'name'   => 'Shipment from Factory Import Fees/GST',
                'active' => 1,
            ],
        ];
        foreach ($expenseTypes as $expenseTypeData) {
            $request = FormRequestService::getManualRequestObject($expenseTypeData, ExpenseTypeRequest::class);
            ExpenseType::query()->create($request->validated());
        }
    }

    /*  private function paymentModes(): void
    {
        $paymentMethods = [
            [
                'name'   => 'Cash',
                'active' => 1,
            ],
            [
                'name'   => 'Cheque',
                'active' => 1,
            ],
            [
                'name'   => 'Card',
                'active' => 1,
            ],
            [
                'name'   => 'ETF',
                'active' => 1,
            ],
        ];
        foreach ($paymentMethods as $paymentMethodData) {
            $request = FormRequestService::getManualRequestObject($paymentMethodData, PaymentModeRequest::class);
            PaymentMode::query()->create($request->validated());
        }
    } */
}
