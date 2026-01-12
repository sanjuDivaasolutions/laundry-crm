<?php

use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\OrderTaxDetail;
use App\Models\TaxRate;
use App\Services\OrderService;
use App\Models\Buyer;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('calculates sales order totals correctly', function () {
    // Create a sales invoice with items
    $invoice = SalesInvoice::factory()->create([
        'sub_total' => 0,
        'tax_total' => 0,
        'grand_total' => 0,
    ]);

    // Create invoice items
    SalesInvoiceItem::factory()->create([
        'sales_invoice_id' => $invoice->id,
        'amount' => 100.00,
    ]);
    
    SalesInvoiceItem::factory()->create([
        'sales_invoice_id' => $invoice->id,
        'amount' => 200.00,
    ]);

    // Create tax detail
    $taxRate = TaxRate::factory()->create(['rate' => 10.00]);
    OrderTaxDetail::create([
        'taxable_type' => SalesInvoice::class,
        'taxable_id' => $invoice->id,
        'tax_rate_id' => $taxRate->id,
        'amount' => 30.00,
    ]);

    // Execute the service method
    OrderService::updateSalesOrderTotals($invoice);

    // Refresh the model to get updated values
    $invoice->refresh();

    // Assert the calculations
    expect($invoice->sub_total)->toBe(300.00)
        ->and($invoice->tax_total)->toBe(30.00)
        ->and($invoice->grand_total)->toBe(330.00);
});

test('calculates commission correctly', function () {
    // Create a sales invoice with commission
    $invoice = SalesInvoice::factory()->create([
        'commission' => 10, // 10% commission
        'sub_total' => 0,
        'tax_total' => 0,
        'grand_total' => 0,
        'commission_total' => 0,
    ]);

    // Create invoice item with amount that includes commission
    SalesInvoiceItem::factory()->create([
        'sales_invoice_id' => $invoice->id,
        'amount' => 110.00, // This includes 10% commission
    ]);

    // Execute the service method
    OrderService::updateSalesOrderTotals($invoice, 'sales');

    // Refresh the model
    $invoice->refresh();

    // Calculate expected values
    $expectedBaseAmount = 110.00 / (1 + (10 / 100));
    $expectedCommissionTotal = round(110.00 - $expectedBaseAmount, 2);

    expect($invoice->sub_total)->toBe(110.00)
        ->and($invoice->commission_total)->toBe(round($expectedCommissionTotal, 2));
});

test('handles zero commission', function () {
    // Create a sales invoice without commission
    $invoice = SalesInvoice::factory()->create([
        'commission' => 0,
        'sub_total' => 0,
        'tax_total' => 0,
        'grand_total' => 0,
        'commission_total' => 0,
    ]);

    // Create invoice item
    SalesInvoiceItem::factory()->create([
        'sales_invoice_id' => $invoice->id,
        'amount' => 100.00,
    ]);

    // Execute the service method
    OrderService::updateSalesOrderTotals($invoice, 'sales');

    // Refresh the model
    $invoice->refresh();

    expect($invoice->sub_total)->toBe(100.00)
        ->and($invoice->commission_total)->toBe(0.00);
});

test('calculates purchase order totals', function () {
    // Create a mock purchase order (using SalesInvoice model for simplicity)
    $order = SalesInvoice::factory()->create([
        'sub_total' => 0,
        'tax_total' => 0,
        'grand_total' => 0,
    ]);

    // Create order items
    SalesInvoiceItem::factory()->create([
        'sales_invoice_id' => $order->id,
        'amount' => 150.00,
    ]);
    
    SalesInvoiceItem::factory()->create([
        'sales_invoice_id' => $order->id,
        'amount' => 250.00,
    ]);

    // Execute the service method
    OrderService::updatePurchaseOrderTotals($order);

    // Refresh the model
    $order->refresh();

    // Assert the calculations
    expect($order->sub_total)->toBe(400.00)
        ->and($order->tax_total)->toBe(0.00)
        ->and($order->grand_total)->toBe(400.00);
});

test('handles empty items', function () {
    // Create an invoice with no items
    $invoice = SalesInvoice::factory()->create([
        'sub_total' => 0,
        'tax_total' => 0,
        'grand_total' => 0,
    ]);

    // Execute the service method
    OrderService::updateSalesOrderTotals($invoice);

    // Refresh the model
    $invoice->refresh();

    // Assert zero values
    expect($invoice->sub_total)->toBe(0.00)
        ->and($invoice->tax_total)->toBe(0.00)
        ->and($invoice->grand_total)->toBe(0.00);
});

test('rounds commission total correctly', function () {
    // Create a sales invoice with commission that results in decimal places
    $invoice = SalesInvoice::factory()->create([
        'commission' => 7.5, // 7.5% commission
        'sub_total' => 0,
        'tax_total' => 0,
        'grand_total' => 0,
        'commission_total' => 0,
    ]);

    // Create invoice item
    SalesInvoiceItem::factory()->create([
        'sales_invoice_id' => $invoice->id,
        'amount' => 100.00,
    ]);

    // Execute the service method
    OrderService::updateSalesOrderTotals($invoice, 'sales');

    // Refresh the model
    $invoice->refresh();

    // Calculate expected values
    $baseAmount = 100.00 / (1 + (7.5 / 100));
    $expectedCommissionTotal = round(100.00 - $baseAmount, 2);

    expect($invoice->commission_total)->toBe($expectedCommissionTotal)
        ->and($invoice->commission_total)->toBeFloat();
    
    // Ensure the value is properly rounded to 2 decimal places
    $decimalPart = substr(strrchr((string)$invoice->commission_total, "."), 1);
    expect(strlen($decimalPart))->toBeLessThanOrEqual(2);
});

test('get outstanding invoices for buyer', function () {
    // Create a buyer with multiple invoices
    $buyer = Buyer::factory()->create();
    
    // Create current invoice (unpaid)
    $currentInvoice = SalesInvoice::factory()->create([
        'buyer_id' => $buyer->id,
        'invoice_number' => 'SI-CURRENT',
        'grand_total' => 150.00,
        'payment_status' => 'pending',
    ]);

    // Create paid invoice
    $paidInvoice = SalesInvoice::factory()->create([
        'buyer_id' => $buyer->id,
        'invoice_number' => 'SI-PAID',
        'grand_total' => 200.00,
        'payment_status' => 'paid',
    ]);

    // Create payment for paid invoice
    Payment::factory()->create([
        'payment_type' => 'si',
        'sales_invoice_id' => $paidInvoice->id,
        'amount' => 200.00,
    ]);

    // Create partial invoice
    $partialInvoice = SalesInvoice::factory()->create([
        'buyer_id' => $buyer->id,
        'invoice_number' => 'SI-PARTIAL',
        'grand_total' => 120.00,
        'payment_status' => 'partial',
    ]);

    // Create payment for partial invoice
    Payment::factory()->create([
        'payment_type' => 'si',
        'sales_invoice_id' => $partialInvoice->id,
        'amount' => 70.00,
    ]);

    $orderService = new OrderService();
    $outstanding = $orderService->getOutstandingInvoicesForBuyer($currentInvoice);

    expect($outstanding)->toHaveKeys(['invoices', 'total'])
        ->and($outstanding['invoices'])->toHaveCount(1);

    $invoiceData = $outstanding['invoices']->first();
    expect($invoiceData['invoice_number'])->toBe('SI-PARTIAL')
        ->and($invoiceData['pending_amount'])->toBe(50.00)
        ->and($invoiceData['status_label'])->toBe('Partial');

    expect($outstanding['total'])->toBe(50.00);
});