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
 *  *  Last modified: 11/02/25, 6:07 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace Database\Seeders;

use Database\Seeders\CanadaTaxation\CanadaTaxationSeeder;
use Database\Seeders\Custom\CurrencyTableSeeder;
use Database\Seeders\Custom\LanguageTableSeeder;
use Database\Seeders\Custom\LanguageTermGroupTableSeeder;
use Database\Seeders\Custom\LanguageTermTableSeeder;
use Database\Seeders\Custom\LanguageTranslationTableSeeder;
use Database\Seeders\Project\BuyerTableSeeder;
use Database\Seeders\Project\CategoryTableSeeder;
use Database\Seeders\Project\DummyDataSeeder;
use Database\Seeders\Project\FeatureTableSeeder;
use Database\Seeders\Project\PaymentModeTableSeeder;
use Database\Seeders\Project\PaymentTermsTableSeeder;
use Database\Seeders\Project\ProductTableSeeder;
use Database\Seeders\Project\SalesInvoiceTableSeeder;
use Database\Seeders\Project\ShelfTableSeeder;
use Database\Seeders\Project\ShipmentModeTableSeeder;
use Database\Seeders\Project\SupplierTableSeeder;
use Database\Seeders\Project\UnitTableSeeder;
use Database\Seeders\Project\WarehouseTableSeeder;
use Database\Seeders\Project\PurchaseInvoiceTableSeeder;
use Database\Seeders\Project\PurchaseOrderTableSeeder;
use Database\Seeders\Project\SalesOrderTableSeeder;
use Illuminate\Database\Seeder;

class CustomDatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            LanguageTableSeeder::class,
            LanguageTermGroupTableSeeder::class,
            LanguageTermTableSeeder::class,
            LanguageTranslationTableSeeder::class,
            CurrencyTableSeeder::class,
            UnitTableSeeder::class,

            CanadaTaxationSeeder::class,

            WarehouseTableSeeder::class,
            ShelfTableSeeder::class,
            PaymentTermsTableSeeder::class,
            CategoryTableSeeder::class,
            FeatureTableSeeder::class,
            ShipmentModeTableSeeder::class,
            BuyerTableSeeder::class,
            SupplierTableSeeder::class,
            ProductTableSeeder::class,
            PurchaseOrderTableSeeder::class,
            PurchaseInvoiceTableSeeder::class,
            SalesOrderTableSeeder::class,
            SalesInvoiceTableSeeder::class,
            PaymentModeTableSeeder::class,
            DummyDataSeeder::class,
        ]);
    }
}
