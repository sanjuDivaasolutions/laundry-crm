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
 *  *  Last modified: 22/01/25, 4:50 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Services;

use App\Models\Company;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use LaravelIdea\Helper\App\Models\_IH_Company_C;
use LaravelIdea\Helper\App\Models\_IH_Company_QB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CompanyService
{
    protected static ?bool $warehousesHaveCompanyColumn = null;
    protected static array $companyCache = [];

    public static function getCompanyCode($companyName): string
    {
        // Split the company name into words
        $words = explode(' ', $companyName);

        // Get the initials
        if (count($words) > 1) {
            $initials = strtoupper($words[0][0] . $words[1][0]);
        } else {
            $initials = strtoupper(substr($words[0], 0, 2));
        }

        // Query the database to find the highest existing sequence number for the initials
        $latestCompany = Company::where('code', 'like', $initials . '-%')
            ->orderBy('code', 'desc')
            ->first();

        // Determine the next sequence number
        if ($latestCompany) {
            $latestCode = $latestCompany->code;
            $latestSequence = (int)substr($latestCode, -3);
            $sequenceNumber = str_pad($latestSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $sequenceNumber = '001';
        }

        // Combine initials and sequence number
        return $initials . '-' . $sequenceNumber;
    }

    public static function getDefaultCompanyEntry(): Model|Collection|Company|array|Builder|_IH_Company_C|_IH_Company_QB|null
    {
        $companyId = AuthService::getCompanyId();
        $defaultCompanyId = $companyId ?: config('system.defaults.company.id', 1);
        return Company::query()
            ->orderBy('name')
            ->select('id', 'name', 'warehouse_id')
            ->with(['warehouse:id,name'])
            ->find($defaultCompanyId);
    }

    public static function getCompanyCodeValue(?int $companyId = null): ?string
    {
        return self::getCompanyById($companyId)?->code;
    }

    public static function getCompanyById(?int $companyId = null): ?Company
    {
        $companyId = $companyId ?: AuthService::getCompanyId();

        if (!$companyId) {
            return null;
        }

        if (!array_key_exists($companyId, self::$companyCache)) {
            self::$companyCache[$companyId] = Company::query()
                ->select('id', 'code', 'name', 'warehouse_id')
                ->find($companyId);
        }

        return self::$companyCache[$companyId];
    }

    public static function getAccessibleWarehouseIds(?int $companyId = null): array
    {
        $companyId = $companyId ?: AuthService::getCompanyId();

        if (!$companyId) {
            return [];
        }

        $warehouseIds = [];

        if (self::warehousesHaveCompanyColumn()) {
            $warehouseIds = Warehouse::query()
                ->where('company_id', $companyId)
                ->pluck('id')
                ->all();
        }

        if (empty($warehouseIds)) {
            $company = self::getCompanyById($companyId);
            $defaultWarehouseId = $company?->warehouse_id;

            if ($defaultWarehouseId) {
                $warehouseIds[] = $defaultWarehouseId;
            }
        }

        return array_values(array_unique(array_filter($warehouseIds)));
    }

    protected static function warehousesHaveCompanyColumn(): bool
    {
        if (self::$warehousesHaveCompanyColumn === null) {
            try {
                self::$warehousesHaveCompanyColumn = Schema::hasColumn((new Warehouse())->getTable(), 'company_id');
            } catch (Throwable $exception) {
                self::$warehousesHaveCompanyColumn = false;
            }
        }

        return self::$warehousesHaveCompanyColumn;
    }
}
