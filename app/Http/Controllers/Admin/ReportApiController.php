<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Services\UtilityService;
use App\Traits\ExportRequest;
use Illuminate\Http\Response;

class ReportApiController extends Controller
{
    use ExportRequest;

    private array $columns;

    public function index($type)
    {
        return $this->getData($type);
    }

    public function getProductSaleDetails($productId)
    {
        return ReportService::getProductSaleDetails($productId, request()->all());
    }

    public function getProductInwardDetails($productId)
    {
        return ReportService::getProductInwardDetails($productId, request()->all());
    }

    public function getAgentCommissionDetails($agentId)
    {
        return ReportService::getAgentCommissionDetails($agentId, request()->all());
    }

    private function getData($type)
    {
        return match ($type) {
            'summary-profit-loss' => ReportService::getProfitLoss(),
            'summary-stock' => ReportService::getSummaryStock(),
            'sales-by-product' => ReportService::getSalesByProduct(),
            'inwards-by-product' => ReportService::getInwardsByProduct(),
            'sales-commission' => ReportService::getSalesCommission(),
            'sales-by-month' => ReportService::getSalesByMonth(),
            default => [],
        };
    }

    public function getPdf($type)
    {
        $postColumns = json_decode(request('columns', ''), true);
        abort_if(!$postColumns, Response::HTTP_FORBIDDEN, 'Invalid Columns supplied');

        $columns = $postColumns;
        abort_if(!$columns, Response::HTTP_FORBIDDEN, 'Invalid Columns supplied');

        $pdfData = $this->getPdfData($type);

        $data = $pdfData['data'];
        $summaries = isset($pdfData['summary']) && $pdfData['summary'] ? $pdfData['summary'] : [];
        if ($summaries) {
            $values = [];
            foreach ($columns as $column) {
                $values[] = [
                    'v' => UtilityService::flatCall($summaries, $column['f']),
                    'a' => $column['a'],
                ];
            }
            $summaries = $values;
        }

        $headerColumns = collect($columns)->pluck('l')->toArray();

        $columnOne = isset($pdfData['title']) && $pdfData['title'] ? $pdfData['title'] : 'Report';
        $columnTwo = request('f_date_range', '');
        $columnThree = date(config('project.datetime_format'));

        $mainHeading = isset($pdfData['heading']) && $pdfData['heading'] ? $pdfData['heading'] : null;

        $fileName = 'reports-' . date(config('project.datetime_format')) . '.pdf';

        $globalReport = true;

        $compact = compact(
            'headerColumns',
            'columns',
            'globalReport',
            'data',
            'summaries',
            'mainHeading',
            'columnOne',
            'columnTwo',
            'columnThree',
        );

        $pdf_html = view('pdf-templates.general-list-v2', $compact)->render();

        $header = view('pdf-templates.header-general', $compact)->render();
        $footer = view('pdf-templates.footer-general', $compact)->render();

        return $this->generatePdf($pdf_html, $fileName, 'L', $header, $footer);
    }

    public function getCsv($type)
    {
        $postColumns = json_decode(request('columns', ''), true);
        abort_if(!$postColumns, Response::HTTP_FORBIDDEN, 'Invalid Columns supplied');

        $columns = $postColumns;
        abort_if(!$columns, Response::HTTP_FORBIDDEN, 'Invalid Columns supplied');

        $this->columns = $columns;

        $data = $this->getCsvData($type);
        $headerColumns = collect($columns)->pluck('l')->toArray();

        $fileName = 'reports-' . date(config('project.datetime_format')) . '.csv';

        return $this->generateCsvV2($data['data'], $columns, $headerColumns, $fileName);
    }

    private function getPdfData($type)
    {
        $data = $this->getData($type);

        switch ($type) {
            default:
                return $this->getProcessedData($data);
        }
    }

    private function getCsvData($type)
    {
        $data = $this->getData($type);

        return match ($type) {
            default => $this->getProcessedData($data),
        };
    }

    private function getProcessedData($collection)
    {
        $collectionData = $collection->toArray($collection);
        $data = isset($collectionData['data']) && $collectionData['data'] ? $collectionData['data'] : $collectionData;
        $data = !is_array($data) ? $data->toArray($data) : $data;

        $summary = isset($collectionData['summary']) && $collectionData['summary'] ? $collectionData['summary'] : [];
        return [
            'title'   => 'Report',
            'data'    => $data,
            'summary' => $summary,
            'heading' => 'Report',
        ];
    }
}
