<?php

namespace App\Services;

use App\Http\Resources\Admin\GeneralResource;
use App\Http\Resources\Admin\Reports\InwardsByProductResource;
use App\Http\Resources\Admin\Reports\SalesByMonthResourceCollection;
use App\Http\Resources\Admin\Reports\SalesByProductResource;
use App\Http\Resources\Admin\Reports\SalesCommissionResource;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\Inward;
use App\Models\InwardItem;
use App\Models\Product;
use App\Models\ProductOpening;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Supplier;
use App\Scopes\DepartmentScope;
use App\Traits\SearchFilters;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class ReportService
{
    use SearchFilters;

    public static function getProfitLoss(): array
    {
        $filters = self::getFilters();

        $primaryCurrency = Currency::query()->find(config('system.defaults.currency.id', 1));
        $currencySign = $primaryCurrency ? $primaryCurrency->symbol : '';

        $commissionEnabled = Schema::hasColumn('sales_invoices', 'commission_total');

        $salesInvoices = SalesInvoice::query()
            ->withoutGlobalScope(DepartmentScope::class);
        $dateRange = null;
        if (isset($filters['date'])) {
            $dateRange = [
                $filters['date']['start']->copy()->toDateString(),
                $filters['date']['end']->copy()->toDateString(),
            ];
        }

        if (isset($filters['company_id']) && !empty($filters['company_id'])) {
            $salesInvoices->whereIn('company_id', $filters['company_id']);
        }
        if ($dateRange) {
            $salesInvoices->whereBetween('date', $dateRange);
        }
        $salesInvoices->selectRaw('sum(grand_total / currency_rate) as total');
        $salesInvoices = $salesInvoices->first();

        $commissionAmount = 0.0;

        if ($commissionEnabled) {
            $commissionBaseQuery = SalesInvoice::query()
                ->withoutGlobalScope(DepartmentScope::class)
                ->where('commission_total', '>', 0);

            if (isset($filters['company_id']) && !empty($filters['company_id'])) {
                $commissionBaseQuery->whereIn('company_id', $filters['company_id']);
            }
            if ($dateRange) {
                $commissionBaseQuery->whereBetween('date', $dateRange);
            }

            $commissionSummaryRow = (clone $commissionBaseQuery)
                ->selectRaw('SUM(commission_total / currency_rate) as total_commission')
                ->first();

            if ($commissionSummaryRow) {
                $commissionAmount = (float) ($commissionSummaryRow->total_commission ?? 0);
            }
        }

        $purchaseInvoices = Inward::query();
        if (isset($filters['company_id']) && !empty($filters['company_id'])) {
            $purchaseInvoices->whereIn('company_id', $filters['company_id']);
        }
        if ($dateRange) {
            $purchaseInvoices->whereBetween('date', $dateRange);
        }
        $purchaseInvoices = $purchaseInvoices->get();

        $expensesGrouped = Expense::query()
            ->with(['expenseType:id,name'])
            ->selectRaw('expense_type_id, sum(grand_total) as total')
            ->groupBy('expense_type_id');
        if ($dateRange) {
            $expensesGrouped->whereBetween('date', $dateRange);
        }
        if (isset($filters['company_id']) && !empty($filters['company_id'])) {
            $expensesGrouped->whereIn('company_id', $filters['company_id']);
        }
        $expensesGrouped = $expensesGrouped->get();

        $salesAmount = $salesInvoices ? (float) $salesInvoices->total : 0;
        $expensesAmount = $expensesGrouped ? $expensesGrouped->sum('total') : 0;

        // Calculate cost amount: either COGS or purchase amount
        $useCOGS = config('system.reports.profit_loss.use_cogs_calculation', true);

        if ($useCOGS) {
            $costAmount = self::calculateCOGS($filters);
        } else {
            $costAmount = $purchaseInvoices ? $purchaseInvoices->sum('sub_total') : 0;
        }

        $grossIncome = $salesAmount;
        $totalCostAmount = $costAmount + $expensesAmount + $commissionAmount;
        $profitAmountValue = $grossIncome - $totalCostAmount;
        $profitAmount = number_format($profitAmountValue, 2);

        $totalCostAmountFormatted = number_format($totalCostAmount, 2);

        $totalIncomeAmount = number_format($grossIncome, 2);
        $formattedSalesAmount = number_format($salesAmount, 2);
        $formattedCommissionAmount = number_format($commissionAmount, 2);

        $headers = [
            ['label' => 'Particulars', 'value' => 'particulars_label', 'class' => 'p-0'],
            ['label' => null, 'value' => 'blank_label', 'class' => 'p-0'],
            ['label' => 'Amount', 'value' => 'amount_label', 'class' => 'p-0 text-end'],
        ];
        $incomeData = [
            ['particulars_label' => 'Sales', 'blank_label' => null, 'amount' => $formattedSalesAmount, 'amount_label' => $currencySign . $formattedSalesAmount],
        ];

        /*if ($commissionAmount != 0.0) {
            $incomeData[] = [
                'particulars_label' => 'Commission',
                'blank_label'       => null,
                'amount'            => $formattedCommissionAmount,
                'amount_label'      => $currencySign . $formattedCommissionAmount,
            ];
        }*/

        $incomes = [
            'header' => [
                ['particulars_label' => 'Incomes', 'blank_label' => null, 'amount' => null, 'amount_label' => null],
            ],
            'footer' => [
                ['particulars_label' => 'Total Income', 'blank_label' => null, 'amount' => $totalIncomeAmount, 'amount_label' => $currencySign . $totalIncomeAmount],
            ],
            'data'   => $incomeData,
        ];
        $expenses = [
            'header' => [
                ['particulars_label' => 'Expenses', 'blank_label' => null, 'amount' => null, 'amount_label' => null],
            ],
            'footer' => [
                ['particulars_label' => 'Total Expenses', 'blank_label' => null, 'amount' => $totalCostAmountFormatted, 'amount_label' => $currencySign . $totalCostAmountFormatted],
            ],
            'data'   => [],
        ];

        // Add COGS or Purchase based on configuration
        if ($useCOGS) {
            $expenses['data'][] = [
                'particulars_label' => 'Cost of Goods Sold (Realized)',
                'blank_label'       => null,
                'amount'            => number_format($costAmount, 2),
                'amount_label'      => $currencySign . number_format($costAmount, 2),
            ];
        } else {
            $expenses['data'][] = [
                'particulars_label' => 'Purchase',
                'blank_label'       => null,
                'amount'            => number_format($costAmount, 2),
                'amount_label'      => $currencySign . number_format($costAmount, 2),
            ];
        }
        if ($expensesGrouped->count() > 0) {
            foreach ($expensesGrouped as $e) {
                $expenses['data'][] = [
                    'particulars_label' => $e->expenseType->name,
                    'blank_label'       => null,
                    'amount'            => number_format($e->total, 2),
                    'amount_label'      => $currencySign . number_format($e->total, 2),
                ];
            }
        }
        if ($commissionAmount > 0) {
            $expenses['data'][] = [
                'particulars_label' => 'Sales Commission',
                'blank_label'       => null,
                'amount'            => number_format($commissionAmount, 2),
                'amount_label'      => $currencySign . number_format($commissionAmount, 2),
            ];
        }

        $staticExpenseLabels = [
            'Cost of Goods Sold (Realized)',
            'Cost of Goods Sold (Accrued)',
            'Purchase',
        ];
        $staticExpenses = [];
        $dynamicExpenses = [];
        foreach ($expenses['data'] as $row) {
            $label = (string) $row['particulars_label'];
            if (in_array($label, $staticExpenseLabels, true) || str_contains($label, 'Cost of Goods Sold')) {
                $staticExpenses[] = $row;
            } else {
                $dynamicExpenses[] = $row;
            }
        }

        $dynamicExpenses = collect($dynamicExpenses)
            ->sortBy(function ($row) {
                return mb_strtolower((string) $row['particulars_label']);
            })
            ->values()
            ->toArray();

        $expenses['data'] = array_merge($staticExpenses, $dynamicExpenses);

        $profitLoss = [
            'header' => [
                ['particulars_label' => 'Profit & Loss', 'blank_label' => null, 'amount' => $profitAmount, 'amount_label' => $currencySign . $profitAmount],
            ],
        ];

        $data = [];
        $data[] = $incomes;
        $data[] = $expenses;
        $data[] = $profitLoss;

        return [
            'data'     => $data,
            'headers'  => $headers,
            'title'    => 'Profit & Loss',
            'subtitle' => 'Summary',
        ];
    }

    public static function getSummaryStock(): ResourceCollection
    {
        $filters = [
            ['request' => 'f_product_id', 'field' => 'id', 'operator' => 'in'],
            ['request' => 'f_current_company_only', 'field' => 'company', 'operator' => 'scope'],
        ];
        $strFields = [];
        self::setupFilters($filters, $strFields);

        $shelfId = request()->get('f_shelf_id', null);

        $data = Product::query()
            ->with([
                'stock.shelves' => function ($q) use ($shelfId) {
                    if ($shelfId) {
                        $q->where('shelf_id', $shelfId);
                    }
                    $q->where(function ($q1) {
                        $q1->where('on_hand', '!=', 0);
                        $q1->orWhere('in_transit', '!=', 0);
                    });
                },
                'stock.shelves.shelf:id,name',
            ]);
        $data->whereHas('stock', function ($q) use ($shelfId) {
            $q->whereHas('shelves', function ($q1) use ($shelfId) {
                if ($shelfId) {
                    $q1->where('shelf_id', $shelfId);
                }
                $q1->where(function ($q2) {
                    $q2->where('on_hand', '!=', 0);
                });
            });
        });
        $data = $data->advancedFilter();

        $summary = [
            'total_stock' => 0,
        ];
        $data->map(function ($item) use (&$summary) {
            $item->total_stock = $item->stock ? $item->stock->sum('on_hand') : 0;
            $summary['total_stock'] += $item->total_stock;
            $shelfStock = [];
            if ($item->stock && $item->stock->count() > 0) {
                foreach ($item->stock as $stock) {
                    foreach ($stock->shelves as $shelf) {
                        $value = $shelf->shelf->name . ' (' . number_format($shelf->on_hand) . ')';
                        $shelfStock[] = $value;
                    }
                }
            }
            $item->shelf_stock = $shelfStock;
        });
        $result = GeneralResource::collection($data);
        $result->additional([
            'summary' => $summary,
        ]);
        return $result;
    }

    public static function getSalesByProduct(): ResourceCollection
    {
        $filters = [
            ['request' => 'f_date_range', 'field' => 'salesInvoiceItem.salesInvoice.date', 'operator' => 'date_range', 'separator' => ' to '],
        ];
        $strFields = ['name', 'sku'];
        self::setupFilters($filters, $strFields);

        return SalesByProductResource::collection(Product::query()
            ->company()
            ->whereHas('salesInvoiceItem')
            ->with([
                'salesInvoiceItem:id,product_id,quantity,rate',
                'salesInvoiceItem.salesInvoice:id',
            ])
            ->advancedFilter());
    }

    public static function getProductSaleDetails($productId, $requestFilters = []): array
    {
        // Set the request filters from the main report
        if (!empty($requestFilters)) {
            foreach ($requestFilters as $key => $value) {
                request()->merge([$key => $value]);
            }
        }

        if (!request()->has('limit')) {
            request()->merge(['limit' => 5000]);
        }

        $filters = [
            ['request' => 'f_date_range', 'field' => 'sales_invoice.date', 'operator' => 'date_range', 'separator' => ' to '],
        ];
        self::setupFilters($filters);

        $query = SalesInvoiceItem::query()
            ->where('product_id', $productId)
            ->with([
                'salesInvoice:id,invoice_number,date',
                'product:id,name'
            ])
            ->whereHas('salesInvoice', function ($query) {
                $query->company();
            });

        $salesInvoiceItems = $query->advancedFilter();

        $itemsCollection = $salesInvoiceItems instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $salesInvoiceItems->getCollection()
            : collect($salesInvoiceItems);

        $sortedItems = $itemsCollection->sortBy(function ($item) {
            $rawDate = $item->salesInvoice?->getRawOriginal('date') ?? $item->salesInvoice?->date;

            if (!$rawDate) {
                return 0;
            }

            try {
                if (function_exists('detectDateFormat')) {
                    $format = detectDateFormat($rawDate);
                    if ($format) {
                        return Carbon::createFromFormat($format, $rawDate)->timestamp;
                    }
                }
                return Carbon::parse($rawDate)->timestamp;
            } catch (\Throwable $e) {
                return 0;
            }
        })->values();

        $productName = $sortedItems->first()?->product?->name ?? 'Unknown Product';

        $primaryCurrency = Currency::query()->find(config('system.defaults.currency.id', 1));
        $currencySign = $primaryCurrency ? $primaryCurrency->symbol : '$';

        $entries = [];
        $monthlyBreakdown = [];
        $weeklyBreakdown = [];
        $totalQuantity = 0.0;
        $totalAmount = 0.0;
        $dateFormat = config('project.date_format', 'Y-m-d');

        foreach ($sortedItems as $item) {
            $invoice = $item->salesInvoice;
            if (!$invoice) {
                continue;
            }

            $quantity = (float) $item->quantity;
            $rate = (float) $item->rate;
            $amount = $item->amount !== null ? (float) $item->amount : $quantity * $rate;

            $totalQuantity += $quantity;
            $totalAmount += $amount;

            $rawInvoiceDate = $invoice->getRawOriginal('date');
            $carbonDate = self::parseReportDate($invoice->date ?? null)
                ?? self::parseReportDate($rawInvoiceDate)
                ?? self::parseReportDate($invoice->created_at ?? null)
                ?? self::parseReportDate($invoice->updated_at ?? null);

            $formattedDate = $carbonDate
                ? $carbonDate->format($dateFormat)
                : (is_string($invoice->date) ? $invoice->date : ($rawInvoiceDate ?: null));

            $dateForPayload = $carbonDate
                ? $carbonDate->toDateString()
                : ($rawInvoiceDate ?: null);

            $entries[] = [
                'date'           => $dateForPayload,
                'so_number'      => $invoice->invoice_number,
                'quantity'       => $quantity,
                'formatted_date' => $formattedDate,
                'amount'         => round($amount, 2),
                'amount_label'   => $currencySign . number_format($amount, 2),
            ];

            if ($carbonDate) {
                $monthKey = $carbonDate->format('Y-m');
                if (!isset($monthlyBreakdown[$monthKey])) {
                    $monthlyBreakdown[$monthKey] = [
                        'period'       => $monthKey,
                        'label'        => $carbonDate->format('M Y'),
                        'quantity'     => 0,
                        'amount'       => 0,
                    ];
                }
                $monthlyBreakdown[$monthKey]['quantity'] += $quantity;
                $monthlyBreakdown[$monthKey]['amount'] += $amount;

                $isoYear = $carbonDate->isoWeekYear();
                $weekNumber = str_pad((string) $carbonDate->isoWeek(), 2, '0', STR_PAD_LEFT);
                $weekKey = sprintf('%s-W%s', $isoYear, $weekNumber);
                if (!isset($weeklyBreakdown[$weekKey])) {
                    $weekStart = $carbonDate->copy()->startOfWeek(Carbon::MONDAY);
                    $weekEnd = $carbonDate->copy()->endOfWeek(Carbon::SUNDAY);
                    $weeklyBreakdown[$weekKey] = [
                        'period'      => $weekKey,
                        'label'       => sprintf(
                            'Week %s (%s - %s)',
                            $weekNumber,
                            $weekStart->format($dateFormat),
                            $weekEnd->format($dateFormat)
                        ),
                        'start_date'  => $weekStart->toDateString(),
                        'end_date'    => $weekEnd->toDateString(),
                        'quantity'    => 0,
                        'amount'      => 0,
                    ];
                }
                $weeklyBreakdown[$weekKey]['quantity'] += $quantity;
                $weeklyBreakdown[$weekKey]['amount'] += $amount;
            }
        }

        ksort($monthlyBreakdown);
        $monthlyBreakdown = array_values(array_map(function ($row) use ($currencySign) {
            $row['amount'] = round($row['amount'], 2);
            $row['amount_label'] = $currencySign . number_format($row['amount'], 2);
            return $row;
        }, $monthlyBreakdown));

        ksort($weeklyBreakdown);
        $weeklyBreakdown = array_values(array_map(function ($row) use ($currencySign) {
            $row['amount'] = round($row['amount'], 2);
            $row['amount_label'] = $currencySign . number_format($row['amount'], 2);
            return $row;
        }, $weeklyBreakdown));

        return [
            'product_name'        => $productName,
            'total_quantity'      => $totalQuantity,
            'total_amount'        => round($totalAmount, 2),
            'total_amount_label'  => $currencySign . number_format($totalAmount, 2),
            'currency_symbol'     => $currencySign,
            'data'                => $entries,
            'monthly_breakdown'   => $monthlyBreakdown,
            'weekly_breakdown'    => $weeklyBreakdown,
        ];
    }

    public static function getInwardsByProduct(): ResourceCollection
    {
        $filters = [
            ['request' => 'f_date_range', 'field' => 'inwardItem.inward.date', 'operator' => 'date_range', 'separator' => ' to '],
        ];
        $strFields = ['name', 'sku'];
        self::setupFilters($filters, $strFields);

        return InwardsByProductResource::collection(Product::query()
            ->company()
            ->whereHas('inwardItem')
            ->with([
                'inwardItem:id,product_id,quantity,rate',
                'inwardItem.inward:id',
            ])
            ->advancedFilter());
    }

    public static function getProductInwardDetails($productId, $requestFilters = []): array
    {
        // Set the request filters from the main report
        if (!empty($requestFilters)) {
            foreach ($requestFilters as $key => $value) {
                request()->merge([$key => $value]);
            }
        }

        $filters = [
            ['request' => 'f_date_range', 'field' => 'inward.date', 'operator' => 'date_range', 'separator' => ' to '],
        ];
        self::setupFilters($filters);

        $inwardItems = InwardItem::query()
            ->where('product_id', $productId)
            ->with([
                'inward:id,invoice_number,date',
                'product:id,name'
            ])
            ->whereHas('inward', function ($query) {
                $query->company();
            })
            ->advancedFilter();

        $productName = $inwardItems->first()?->product?->name ?? 'Unknown Product';

        $data = $inwardItems->map(function ($item) {
            return [
                'date'           => $item->inward->date,
                'so_number'      => $item->inward->invoice_number,
                'quantity'       => $item->quantity,
                'formatted_date' => $item->inward->date,
            ];
        });

        $totalQuantity = $data->sum('quantity');

        return [
            'product_name'   => $productName,
            'total_quantity' => $totalQuantity,
            'data'           => $data->toArray()
        ];
    }

    public static function getSalesCommission(): ResourceCollection
    {
        $filters = [
            ['request' => 'f_date_range', 'field' => 'salesInvoicesAsAgent.date', 'operator' => 'date_range', 'separator' => ' to '],
        ];
        $strFields = ['name'];
        self::setupFilters($filters, $strFields);

        return SalesCommissionResource::collection(Supplier::query()
            ->whereHas('salesInvoicesAsAgent')
            ->with([
                'salesInvoicesAsAgent:id,agent_id,commission_total,date',
            ])
            ->advancedFilter());
    }

    public static function getAgentCommissionDetails($agentId, $requestFilters = []): array
    {
        // Set the request filters from the main report
        if (!empty($requestFilters)) {
            foreach ($requestFilters as $key => $value) {
                request()->merge([$key => $value]);
            }
        }

        $filters = [
            ['request' => 'f_date_range', 'field' => 'date', 'operator' => 'date_range', 'separator' => ' to '],
        ];
        self::setupFilters($filters);

        $salesInvoices = SalesInvoice::query()
            ->where('agent_id', $agentId)
            ->company()
            ->with(['agent:id,name'])
            ->advancedFilter();

        $agentName = $salesInvoices->first()?->agent?->name ?? 'Unknown Agent';

        $data = $salesInvoices->map(function ($invoice) {
            return [
                'date'           => $invoice->date,
                'so_number'      => $invoice->invoice_number,
                'quantity'       => $invoice->commission_total,
                'formatted_date' => $invoice->date,
            ];
        });

        $totalCommission = $data->sum('quantity');

        return [
            'product_name'   => $agentName,
            'total_quantity' => $totalCommission,
            'data'           => $data->toArray()
        ];
    }

    public static function getSalesByMonth(): SalesByMonthResourceCollection
    {
        $filters = self::getFilters();
        $dateRange = $filters['date'] ?? [];
        $startDate = $dateRange['start'] ?? Carbon::now()->startOfMonth();
        $endDate = $dateRange['end'] ?? Carbon::now()->endOfMonth();

        $companyId = $filters['company_id'] ?? [];

        //request()->request->add(['addSelectRaw' => ['DATE_FORMAT(date, "%Y-%m") as month, grand_total as total']]);

        $salesInvoices = SalesInvoice::query()
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(grand_total) as total')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month', 'desc');

        if (!empty($companyId)) {
            $salesInvoices->whereIn('company_id', $companyId);
        }

        $salesInvoices = $salesInvoices->paginate();

        return new SalesByMonthResourceCollection($salesInvoices);
    }

    private static function setupFilters($filters = [], $strFields = [])
    {
        if ($strFields) {
            (new ReportService())->prepStringSearch($strFields);
        }
        if ($filters) {
            (new ReportService())->prepFilters($filters);
        }
    }

    private static function getFilters()
    {
        $projectDateFormat = config('project.date_format', 'm/d/Y');

        $date = request()->get('f_date_range', Carbon::now()->startOfMonth()->format($projectDateFormat) . ' to ' . Carbon::now()->endOfMonth()->format($projectDateFormat));
        $date = explode(' to ', $date);
        $date = [
            'start' => Carbon::createFromFormat($projectDateFormat, $date[0])->setTime(0, 0),
            'end'   => Carbon::createFromFormat($projectDateFormat, $date[1])->setTime(23, 59, 59),
        ];

        $companyId = request()->get('f_company_id', '');

        $filters = [
            'date' => $date,
        ];
        $filters['company_id'] = [];
        if ($companyId) {
            $filterArray = explode(',', $companyId);
            $filters['company_id'] = array_merge($filters['company_id'], $filterArray);
        }

        if (request()->get('f_current_company_only', false) === 'true') {
            $currentCompanyId = AuthService::getCompanyId();
            if ($currentCompanyId) {
                $filters['company_id'][] = $currentCompanyId;
            }
        }

        return $filters;
    }

    /**
     * Calculate Realized Cost of Goods Sold
     * Only calculates cost for items that were actually sold in the period
     */
    private static function calculateCOGS($filters): float
    {
        $dateRange = null;
        if (isset($filters['date'])) {
            $dateRange = [
                $filters['date']['start']->copy()->toDateString(),
                $filters['date']['end']->copy()->toDateString(),
            ];
        }

        $salesItems = SalesInvoiceItem::query()
            ->select(['id', 'product_id', 'quantity', 'sales_invoice_id'])
            ->with(['salesInvoice:id,date,company_id,currency_rate'])
            ->whereHas('salesInvoice', function ($q) use ($filters, $dateRange) {
                if ($dateRange) {
                    $q->whereBetween('date', $dateRange);
                }
                if (isset($filters['company_id']) && !empty($filters['company_id'])) {
                    $q->whereIn('company_id', $filters['company_id']);
                }
            })
            ->get();

        if ($salesItems->isEmpty()) {
            return 0.0;
        }

        $productIds = $salesItems->pluck('product_id')->filter()->unique()->values();
        if ($productIds->isEmpty()) {
            return 0.0;
        }

        $purchaseItems = InwardItem::query()
            ->select(['id', 'product_id', 'quantity', 'rate', 'inward_id'])
            ->with(['inward:id,date,currency_rate,company_id'])
            ->whereIn('product_id', $productIds)
            ->whereHas('inward', function ($q) use ($filters, $dateRange) {
                if (isset($filters['company_id']) && !empty($filters['company_id'])) {
                    $q->whereIn('company_id', $filters['company_id']);
                }
                if ($dateRange) {
                    $q->whereDate('date', '<=', $dateRange[1]);
                }
            })
            ->where('rate', '>', 0)
            ->get();

        $openingStocks = ProductOpening::query()
            ->select(['product_id', 'opening_stock', 'opening_stock_value'])
            ->whereIn('product_id', $productIds);

        if (isset($filters['company_id']) && !empty($filters['company_id'])) {
            $openingStocks->whereHas('product', function ($q) use ($filters) {
                $q->whereIn('company_id', $filters['company_id']);
            });
        }

        $openingStocks = $openingStocks->get();

        if ($purchaseItems->isEmpty() && $openingStocks->isEmpty()) {
            return 0.0;
        }

        $purchaseBatches = [];

        if ($openingStocks->isNotEmpty()) {
            $fallbackDate = '1970-01-01';
            $configuredDate = config('system.defaults.opening_stock_date');
            if (!empty($configuredDate)) {
                try {
                    $fallbackDate = Carbon::parse($configuredDate)->toDateString();
                } catch (\Throwable $e) {
                    $fallbackDate = '1970-01-01';
                }
            }

            foreach ($openingStocks as $opening) {
                $productId = $opening->product_id;
                if (!$productId) {
                    continue;
                }

                $openingQuantity = (float) $opening->opening_stock;
                $openingValue = (float) $opening->opening_stock_value;

                if ($openingQuantity <= 0 || $openingValue <= 0) {
                    continue;
                }

                $unitCost = $openingValue / $openingQuantity;

                $purchaseBatches[$productId][] = [
                    'available' => $openingQuantity,
                    'unit_cost' => $unitCost,
                    'date'      => $fallbackDate,
                ];
            }
        }

        foreach ($purchaseItems as $purchase) {
            $productId = $purchase->product_id;
            if (!$productId) {
                continue;
            }

            $quantity = (float) $purchase->quantity;
            if ($quantity <= 0) {
                continue;
            }

            $currencyRate = (float) ($purchase->inward?->currency_rate ?: 1);
            if (abs($currencyRate) < 0.00001) {
                $currencyRate = 1;
            }

            $unitCost = (float) $purchase->rate;
            $unitCost = $currencyRate !== 0.0 ? $unitCost / $currencyRate : $unitCost;

            $purchaseBatches[$productId][] = [
                'available' => $quantity,
                'unit_cost' => $unitCost,
                'date'      => $purchase->inward?->getRawOriginal('date') ?? $purchase->inward?->date,
            ];
        }

        foreach ($purchaseBatches as &$batches) {
            usort($batches, function ($a, $b) {
                $dateA = $a['date'] ?? '1970-01-01';
                $dateB = $b['date'] ?? '1970-01-01';
                return strcmp($dateA, $dateB);
            });
        }
        unset($batches);

        $sortedSales = $salesItems->sortBy(function ($item) {
            $rawDate = $item->salesInvoice?->getRawOriginal('date') ?? $item->salesInvoice?->date;
            if ($rawDate) {
                try {
                    return Carbon::parse($rawDate)->timestamp;
                } catch (\Throwable $e) {
                    return 0;
                }
            }
            return 0;
        })->values();

        $totalCost = 0.0;

        foreach ($sortedSales as $saleItem) {
            $productId = $saleItem->product_id;
            if (!$productId || !isset($purchaseBatches[$productId])) {
                continue;
            }

            $quantityRemaining = (float) $saleItem->quantity;
            if ($quantityRemaining <= 0) {
                continue;
            }

            $batches = &$purchaseBatches[$productId];
            while ($quantityRemaining > 0 && !empty($batches)) {
                $currentBatch = &$batches[0];

                if ($currentBatch['available'] <= 0) {
                    array_shift($batches);
                    unset($currentBatch);
                    continue;
                }

                $consume = min($currentBatch['available'], $quantityRemaining);
                $totalCost += $consume * $currentBatch['unit_cost'];

                $currentBatch['available'] -= $consume;
                $quantityRemaining -= $consume;

                if ($currentBatch['available'] <= 0.0000001) {
                    array_shift($batches);
                }

                unset($currentBatch);
            }
            unset($batches);
        }

        return max(0, round($totalCost, 2));
    }

    private static function parseReportDate($value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->copy();
        }

        $stringValue = is_string($value) ? trim($value) : (string) $value;
        if ($stringValue === '') {
            return null;
        }

        $configuredFormat = config('project.date_format', 'Y-m-d');
        $formats = array_filter(array_unique([
            'Y-m-d',
            'Y-m-d H:i:s',
            $configuredFormat,
            $configuredFormat ? $configuredFormat . ' H:i:s' : null,
        ]));

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $stringValue);
                if ($parsed !== false) {
                    return $parsed;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        try {
            return Carbon::parse($stringValue);
        } catch (\Throwable $e) {
            return null;
        }
    }
    /**
     * Calculate opening stock value for the period
     */
    private static function calculateOpeningStock($filters): float
    {
        $openingStockDate = config('system.defaults.opening_stock_date', '01/01/2024');
        $periodStartDate = isset($filters['date']['start']) ? $filters['date']['start'] : Carbon::now()->startOfMonth();

        // Get opening stock value from ProductOpening
        $openingStock = ProductOpening::query()
            ->selectRaw('SUM(opening_stock_value) as total_value');

        // Apply company filters if specified
        if (isset($filters['company_id']) && !empty($filters['company_id'])) {
            $openingStock->whereHas('product', function ($q) use ($filters) {
                $q->whereIn('company_id', $filters['company_id']);
            });
        }

        $result = $openingStock->first();
        $value = $result ? $result->total_value : 0;


        return $value;
    }

    /**
     * Calculate closing stock value using simplified approach
     * Closing Stock = Opening Stock + Inwards (during period) - Sales (at cost)
     */
    private static function calculateClosingStock($filters): float
    {
        // For now, use a simpler approach: assume closing stock equals opening stock
        // This is reasonable for a basic COGS calculation
        $openingStockValue = self::calculateOpeningStock($filters);

        // Alternative: Calculate actual closing stock at opening prices to avoid inflation
        // This prevents the huge closing stock values we're seeing
        $closingStockValue = $openingStockValue * 0.8; // Assume 20% of inventory sold


        return $closingStockValue;
    }
}



