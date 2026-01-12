<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ShelfResource extends JsonResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);
        if (array_key_exists('product_stock_shelf', $data)) {
            unset($data['product_stock_shelf']);
        }

        $products = $this->prepareProducts();

        $data['products'] = $products->toArray();
        $data['product_summary'] = [
            'total_products'      => $products->count(),
            'total_quantity'      => $products->sum('on_hand'),
            'total_quantity_text' => $this->formatQuantity($products->sum('on_hand')),
            'type_breakdown'      => $this->buildTypeBreakdown($products),
        ];

        return $data;
    }

    protected function prepareProducts(): Collection
    {
        if (!$this->relationLoaded('productStockShelf')) {
            return collect();
        }

        return $this->productStockShelf
            ->map(function ($shelfStock) {
                $productStock = $shelfStock->productStock;
                $product = $productStock?->product;
                if (!$product) {
                    return null;
                }

                $type = $product->type;
                $rawType = $product->getRawOriginal('type');
                $onHand = (float) $shelfStock->on_hand;
                $inTransit = (float) $shelfStock->in_transit;
                if ($onHand <= 0) {
                    return null;
                }

                return [
                    'product_id'        => $product->id,
                    'name'              => $product->name,
                    'code'              => $product->code,
                    'sku'               => $product->sku,
                    'type_value'        => data_get($type, 'value', $rawType),
                    'type_label'        => data_get($type, 'label', ucfirst((string) $rawType)),
                    'category'          => data_get($product, 'category.name'),
                    'manufacturer'      => $product->manufacturer,
                    'unit'              => data_get($product, 'unit_01.name'),
                    'warehouse'         => data_get($productStock, 'warehouse.name'),
                    'on_hand'           => $onHand,
                    'on_hand_text'      => $this->formatQuantity($onHand),
                    'in_transit'        => $inTransit,
                    'in_transit_text'   => $this->formatQuantity($inTransit),
                ];
            })
            ->filter()
            ->values();
    }

    protected function buildTypeBreakdown(Collection $products): array
    {
        if ($products->isEmpty()) {
            return [];
        }

        return $products
            ->groupBy('type_value')
            ->map(function ($items) {
                $totalQuantity = $items->sum('on_hand');

                return [
                    'type_value'          => data_get($items->first(), 'type_value'),
                    'type_label'          => data_get($items->first(), 'type_label'),
                    'unique_products'     => $items->count(),
                    'total_quantity'      => $totalQuantity,
                    'total_quantity_text' => $this->formatQuantity($totalQuantity),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function formatQuantity($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
