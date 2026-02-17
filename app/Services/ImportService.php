<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * ImportService
 *
 * Handles data imports with proper tenant isolation.
 *
 * SECURITY REQUIREMENTS:
 * - All imports MUST have tenant context
 * - All records MUST have tenant_id assigned
 * - FK references MUST be validated against tenant
 * - Quotas MUST be checked before bulk inserts
 */
class ImportService
{
    /**
     * Import items for a tenant.
     *
     * @param  Tenant  $tenant  The tenant to import for
     * @return array Import results
     */
    public static function importItems(Tenant $tenant): array
    {
        $file = request()->file('file');
        if (! $file) {
            return ['error' => 'No file uploaded'];
        }

        $data = self::parseImportFile($file);
        $results = ['imported' => 0, 'errors' => []];

        // Check quota before import
        $quotaService = app(QuotaService::class);
        if (! $quotaService->canCreate('items', count($data), $tenant)) {
            return ['error' => 'Import would exceed item quota limit'];
        }

        DB::beginTransaction();
        try {
            foreach ($data as $index => $row) {
                $validator = Validator::make($row, [
                    'name' => 'required|string|max:100',
                    'code' => 'nullable|string|max:50',
                    'price' => 'required|numeric|min:0',
                ]);

                if ($validator->fails()) {
                    $results['errors'][] = [
                        'row' => $index + 1,
                        'errors' => $validator->errors()->toArray(),
                    ];

                    continue;
                }

                // CRITICAL: Assign tenant_id
                $row['tenant_id'] = $tenant->id;

                Item::create($row);
                $results['imported']++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return ['error' => 'Import failed: '.$e->getMessage()];
        }

        // Invalidate quota cache
        $quotaService->invalidateUsageCache('items', $tenant);

        return $results;
    }

    /**
     * Import customers for a tenant.
     */
    public static function importCustomers(Tenant $tenant): array
    {
        $file = request()->file('file');
        if (! $file) {
            return ['error' => 'No file uploaded'];
        }

        $data = self::parseImportFile($file);
        $results = ['imported' => 0, 'errors' => []];

        // Check quota
        $quotaService = app(QuotaService::class);
        if (! $quotaService->canCreate('customers', count($data), $tenant)) {
            return ['error' => 'Import would exceed customer quota limit'];
        }

        DB::beginTransaction();
        try {
            foreach ($data as $index => $row) {
                $validator = Validator::make($row, [
                    'name' => 'required|string|max:100',
                    'email' => 'nullable|email',
                    'phone' => 'nullable|string|max:20',
                    'address' => 'nullable|string|max:500',
                ]);

                if ($validator->fails()) {
                    $results['errors'][] = [
                        'row' => $index + 1,
                        'errors' => $validator->errors()->toArray(),
                    ];

                    continue;
                }

                // CRITICAL: Assign tenant_id
                $row['tenant_id'] = $tenant->id;

                Customer::create($row);
                $results['imported']++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return ['error' => 'Import failed: '.$e->getMessage()];
        }

        $quotaService->invalidateUsageCache('customers', $tenant);

        return $results;
    }

    /**
     * Parse import file (CSV/Excel).
     */
    protected static function parseImportFile($file): array
    {
        $extension = $file->getClientOriginalExtension();

        if ($extension === 'csv') {
            return self::parseCsv($file);
        }

        // For Excel files, you would use a library like PhpSpreadsheet or Maatwebsite Excel
        // For now, only CSV is supported
        return [];
    }

    /**
     * Parse CSV file.
     */
    protected static function parseCsv($file): array
    {
        $data = [];
        $headers = null;

        if (($handle = fopen($file->getPathname(), 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                if ($headers === null) {
                    $headers = array_map('trim', $row);

                    continue;
                }

                $data[] = array_combine($headers, $row);
            }
            fclose($handle);
        }

        return $data;
    }

    // =========================================================================
    // Legacy import methods (to be updated with tenant context)
    // =========================================================================

    public static function importSalesInvoice(?Tenant $tenant = null): ?array
    {
        // TODO: Implement with tenant context
        return null;
    }

    public static function importPurchaseInvoice(?Tenant $tenant = null): ?array
    {
        // TODO: Implement with tenant context
        return null;
    }

    public static function importProduct(?Tenant $tenant = null): ?array
    {
        // TODO: Implement with tenant context
        return null;
    }

    public static function importPettyCash(?Tenant $tenant = null): ?array
    {
        // TODO: Implement with tenant context
        return null;
    }

    public static function importBankAccount($id = null, ?Tenant $tenant = null): ?array
    {
        // TODO: Implement with tenant context
        return null;
    }
}
