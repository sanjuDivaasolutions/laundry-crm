<?php

namespace App\Traits;

use App\Services\UtilityService;
use Illuminate\Http\Response;
use Mpdf\Mpdf;

trait ExportRequest
{
    public function getCsv()
    {

        $data = $this->getList();
        $data = ! is_array($data) ? json_decode($data->toJson(), true) : $data;
        if (isset($data['data'])) {
            $data = $data['data'];
        }

        return $this->generateCsv($data);
    }

    public function generateCsv($data)
    {

        $postColumns = request('columns');
        abort_if(! $postColumns, Response::HTTP_FORBIDDEN, 'Invalid Columns supplied');

        $columns = stringToArray($postColumns);
        abort_if(! $columns, Response::HTTP_FORBIDDEN, 'Invalid Columns supplied');

        $headerColumns = collect($columns)->pluck('l')->toArray();

        $filePrefix = $this->csvFilePrefix ?? 'reports-';
        $fileName = $filePrefix.date(config('project.datetime_format')).'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($data, $headerColumns, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headerColumns);

            foreach ($data as $item) {
                $row = [];
                foreach ($columns as $c) {
                    $row[] = UtilityService::flatCall($item, explode('.', $c['f']));
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function generateCsvV2($data, $columns, $headerColumns, $fileName)
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($data, $headerColumns, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headerColumns);

            foreach ($data as $item) {
                $row = [];
                foreach ($columns as $c) {
                    $row[] = UtilityService::flatCall($item, explode('.', $c['f']));
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function generatePdf($pdf_html, $fileName, $orientation = 'P', $header = null, $footer = null)
    {
        $default_mpdf_configs = getMpdfConfigs();
        $pdf_config = getMpdfOrientation($orientation);

        $mpdf = new Mpdf(array_merge($default_mpdf_configs, $pdf_config));
        if ($header) {
            $mpdf->SetHTMLHeader($header);
        }
        if ($footer) {
            $mpdf->SetHTMLFooter($footer);
        }
        $mpdf->WriteHTML($pdf_html);
        $pdf = $mpdf->Output($fileName, 'D');

        $headers = [
            'Content-type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($pdf) {
            $file = fopen('php://output', 'w');
            fwrite($file, $pdf);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
