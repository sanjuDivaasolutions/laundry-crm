<?php

namespace App\Http\Resources\Admin;

use App\Models\Shipment;
use App\Models\ShipmentItem;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentEditResource extends JsonResource
{
    public function toArray($request)
    {
        $result = parent::toArray($request);

        if ($this->payment_type) {
            $payment_type = collect(Shipment::PAYMENT_TYPE_SELECT)->firstWhere('value', $this->payment_type);
            $result['payment_type'] = [
                'value' => $payment_type['value'],
                'label' => $payment_type['label'],
            ];
        }

        if (isset($result['items']) && is_array($result['items'])) {
            foreach ($result['items'] as &$i) {
                if ($i['location']) {
                    $obj = collect(ShipmentItem::LOCATION_SELECT)->firstWhere('value', $i['location']);
                    $i['location'] = [
                        'value' => $obj['value'],
                        'label' => $obj['label'],
                    ];
                }
            }
        }

        return $result;
        /*return [
            'id' => $this->id,
            'date' => $this->date,
            'code' => $this->code,
            'container_no' => $this->container_no,
            'bl_no' => $this->bl_no,
            'amount' => $this->amount,
            'container_type_id' => $this->container_type_id,
            'price_term_id' => $this->price_term_id,
            'port_id' => $this->port_id,
        ];*/
    }
}
