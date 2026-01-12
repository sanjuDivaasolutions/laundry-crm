<?php

namespace App\Http\Resources\Admin\Reports;

use Illuminate\Http\Resources\Json\ResourceCollection;

/** @mixin \App\Models\Permission */
class SalesByMonthResourceCollection extends ResourceCollection
{
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $data = SalesByMonthResource::collection($this->collection);
        return [
            'data'    => $data,
            'summary' => $this->getSummary($data),
        ];
    }

    private function getSummary($data): array
    {
        $currencySign = '$';
        $total = 0;
        foreach ($data as $item) {
            $total += $item->total;
        }
        return [
            'total'       => $total,
            'total_label' => $currencySign . number_format($total, 2),
        ];
    }
}
