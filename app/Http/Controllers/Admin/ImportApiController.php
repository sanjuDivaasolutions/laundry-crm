<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImportService;

class ImportApiController extends Controller
{
    public function import($type, $id = null)
    {
        ini_set('memory_limit', -1);
        $res = match ($type) {
            'sales-invoices'        => ImportService::importSalesInvoice(),
            'purchase-invoices'     => ImportService::importPurchaseInvoice(),
            'products'              => ImportService::importProduct(),
            'petty-cash-expenses'   => ImportService::importPettyCash(),
            'bank-accounts'         => ImportService::importBankAccount($id),
            default                 => null,
        };
        return $res != null ? response()->json(['message' => 'Imported successfully', 'data' => $res]) : response()->json(['message' => 'Import is not supported'], 400);
    }
}
