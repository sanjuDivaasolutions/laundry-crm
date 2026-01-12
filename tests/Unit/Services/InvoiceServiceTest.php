<?php

namespace Tests\Unit\Services;

use App\Models\Buyer;
use App\Models\Company;
use App\Models\Payment;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private Buyer $buyer;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->buyer = Buyer::create([
            'code' => 'BUY-UNIT',
            'display_name' => 'Unit Test Buyer',
            'name' => 'Unit Test Buyer',
            'active' => true,
            'currency_id' => null,
            'payment_term_id' => null,
            'billing_address_id' => null,
            'shipping_address_id' => null,
        ]);
    }

    public function test_it_returns_only_unpaid_or_partial_invoices_for_outstanding_summary()
    {
        $currentInvoice = $this->createInvoice('SI-CURRENT', 150.00);

        $paidInvoice = $this->createInvoice('SI-PAID', 200.00, 'paid');
        $this->recordPayment($paidInvoice, 200.00);

        $partialInvoice = $this->createInvoice('SI-PARTIAL', 120.00, 'partial');
        $this->recordPayment($partialInvoice, 70.00);

        $outstanding = InvoiceService::getOutstandingInvoicesForBuyer($currentInvoice->fresh());

        $this->assertArrayHasKey('invoices', $outstanding);
        $this->assertCount(1, $outstanding['invoices'], 'Only unpaid invoices should be returned');

        $invoiceData = $outstanding['invoices']->first();
        $this->assertEquals('SI-PARTIAL', $invoiceData['invoice_number']);
        $this->assertEquals(50.00, $invoiceData['pending_amount']);
        $this->assertSame('Partial', $invoiceData['status_label']);

        $this->assertSame(50.00, $outstanding['total']);
    }

    private function createInvoice(string $number, float $grandTotal, ?string $paymentStatus = null): SalesInvoice
    {
        return SalesInvoice::create([
            'invoice_number' => $number,
            'company_id' => $this->company->id,
            'buyer_id' => $this->buyer->id,
            'user_id' => $this->user->id,
            'type' => 'p',
            'order_type' => 'product',
            'date' => now()->format(config('project.date_format')),
            'sub_total' => $grandTotal,
            'tax_total' => 0,
            'tax_rate' => 0,
            'grand_total' => $grandTotal,
            'is_taxable' => false,
            'payment_status' => $paymentStatus ?? 'pending',
        ]);
    }

    private function recordPayment(SalesInvoice $invoice, float $amount): void
    {
        Payment::create([
            'payment_type' => 'si',
            'tran_type' => 'receive',
            'sales_invoice_id' => $invoice->id,
            'payment_date' => now()->format(config('project.date_format')),
            'amount' => $amount,
            'user_id' => $this->user->id,
            'order_no' => 'PAY-' . uniqid(),
        ]);
    }
}
