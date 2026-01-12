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
 *  *  Last modified: 09/01/25, 5:35 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace Database\Seeders\Project;

use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Database\Seeder;

class CompanyTableSeeder extends Seeder
{
    public function run()
    {
        /*
         * 1	OS-001	OM SP Construction Equipment Inc	2949 101 St T6N 1A7	Edmonton AB	745 067 082 RC0001	(780)-700-5557	1	1	18-10-2024 12:33:33	18-10-2024 16:38:00
2	MR-001	Mega Rental & Construction Signs	9126 34A Ave	NW	(null)	(null)	1	1	18-10-2024 16:37:30	18-10-2024 16:37:30
3	SS-001	SI Supplies West Ltd.	9126 34A Ave	NW	(null)	(null)	1	1	18-10-2024 16:40:29	18-10-2024 16:40:29
         */
        $data = [
            [
                'name'       => 'OM SP Construction Equipment Inc',
                'address_1'  => '3420 19 St NW, T6T0M2',
                'address_2'  => 'Edmonton AB',
                'gst_number' => '745 067 082 RC0001',
                'phone'      => '(780)-700-5557',
                'active'     => 1,
            ],
            [
                'name'      => 'Mega Rental & Construction Signs',
                'address_1' => '9126 34A Ave',
                'address_2' => 'NW',
                'active'    => 1,
            ],
            [
                'name'      => 'SI Supplies West Ltd.',
                'address_1' => '9126 34A Ave',
                'address_2' => 'NW',
                'active'    => 1,
            ],
        ];

        foreach ($data as $d) {
            $d['code'] = CompanyService::getCompanyCode($d['name']);
            Company::create($d);
        }
    }
}
