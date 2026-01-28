<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImportService;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;

/**
 * ImportApiController
 *
 * Handles data imports with proper tenant isolation.
 *
 * SECURITY: All imports MUST:
 * 1. Have tenant context established
 * 2. Assign tenant_id to all imported records
 * 3. Validate imported data against tenant ownership
 * 4. Check quotas before bulk imports
 */
class ImportApiController extends Controller
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    public function import($type, $id = null): JsonResponse
    {
        // 1. Verify tenant context is established
        $tenant = $this->tenantService->getTenant();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Import requires tenant context. Please log in.',
            ], 403);
        }

        // 2. Check if tenant can perform imports (not in read-only mode)
        if ($tenant->isReadOnly()) {
            return response()->json([
                'success' => false,
                'message' => 'Your trial has expired. Subscribe to import data.',
            ], 402);
        }

        ini_set('memory_limit', -1);

        // 3. Pass tenant context to import service
        $res = match ($type) {
            'sales-invoices' => ImportService::importSalesInvoice($tenant),
            'purchase-invoices' => ImportService::importPurchaseInvoice($tenant),
            'products' => ImportService::importProduct($tenant),
            'petty-cash-expenses' => ImportService::importPettyCash($tenant),
            'bank-accounts' => ImportService::importBankAccount($id, $tenant),
            // Tenant-scoped imports for laundry CRM
            'items' => ImportService::importItems($tenant),
            'customers' => ImportService::importCustomers($tenant),
            'categories' => ImportService::importCategories($tenant),
            default => null,
        };

        if ($res === null) {
            return response()->json([
                'success' => false,
                'message' => 'Import type is not supported',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Imported successfully',
            'data' => $res,
            'tenant_id' => $tenant->id,
        ]);
    }
}
