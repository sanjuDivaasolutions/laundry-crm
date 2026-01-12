<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\SalesInvoice;
use App\Models\Supplier;
use App\Services\QueryService;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class AgentCommissionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['project.date_format' => 'Y-m-d']);
        Carbon::setTestNow('2025-01-15 00:00:00');
        Currency::create([
            'code'   => 'USD',
            'name'   => 'US Dollar',
            'symbol' => '$',
            'rate'   => 1,
            'active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_agent_scope_limits_supplier_search_results(): void
    {
        $agent = Supplier::create([
            'code'       => 'AG-' . Str::upper(Str::random(6)),
            'display_name' => 'Agent Alpha',
            'name'       => 'Agent Alpha',
            'active'     => true,
            'email'      => 'agent@example.com',
            'phone'      => '111-111-1111',
            'remarks'    => null,
            'is_agent'   => true,
        ]);

        Supplier::create([
            'code'         => 'SUP-' . Str::upper(Str::random(6)),
            'display_name' => 'Supplier Beta',
            'name'         => 'Supplier Beta',
            'active'       => true,
            'email'        => 'supplier@example.com',
            'phone'        => '222-222-2222',
            'remarks'      => null,
            'is_agent'     => false,
        ]);

        $results = QueryService::search(
            Supplier::class,
            'display_name',
            '',
            'id',
            'display_name',
            ['agents']
        );

        $this->assertCount(1, $results);
        $this->assertEquals($agent->id, $results[0]['id']);
    }

    public function test_profit_loss_report_includes_agent_commission_breakdown(): void
    {
        $agent = Supplier::create([
            'code'         => 'AG-' . Str::upper(Str::random(6)),
            'display_name' => 'Agent Gamma',
            'name'         => 'Agent Gamma',
            'active'       => true,
            'email'        => 'gamma@example.com',
            'phone'        => '333-333-3333',
            'remarks'      => null,
            'is_agent'     => true,
        ]);

        SalesInvoice::create([
            'invoice_number'  => 'SI-TEST-001',
            'date'            => '2025-01-01',
            'due_date'        => '2025-01-02',
            'remark'          => null,
            'type'            => 'p',
            'order_type'      => 'product',
            'reference_no'    => null,
            'currency_rate'   => 1,
            'sub_total'       => 100,
            'tax_total'       => 0,
            'tax_rate'        => 0,
            'grand_total'     => 100,
            'is_taxable'      => false,
            'payment_status'  => 'pending',
            'commission'      => 10,
            'commission_total'=> 50,
            'agent_id'        => $agent->id,
        ]);

        $report = ReportService::getProfitLoss();
        $sections = collect($report['data']);

        $commissionSection = $sections->first(function ($section) {
            return isset($section['header'][0]['particulars_label']) &&
                $section['header'][0]['particulars_label'] === 'Commission Breakdown';
        });
        $this->assertNull($commissionSection, 'Commission breakdown section should not be present.');

        $expensesSection = $sections->first(function ($section) {
            return isset($section['header'][0]['particulars_label']) &&
                $section['header'][0]['particulars_label'] === 'Expenses';
        });

        $this->assertNotNull($expensesSection, 'Expected Expenses section in profit & loss report.');
        $commissionRow = collect($expensesSection['data'])->firstWhere('particulars_label', 'Sales Commission');
        $this->assertNotNull($commissionRow, 'Expected Sales Commission row in expenses.');
        $this->assertEquals('50.00', $commissionRow['amount']);
        $this->assertEquals('50.00', $expensesSection['footer'][0]['amount'], 'Total expenses should include sales commission.');
    }
}
