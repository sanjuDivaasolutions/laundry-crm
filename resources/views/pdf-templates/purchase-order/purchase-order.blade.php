<?php
$primary_color = '#222222';
$table_header_bg = '#EEEEEE';
$table_border_color = '#DDDDDD';

$th_styles = 'font-size: 12px;border-right: 1px solid #DDDDDD; border-bottom: 1px solid ' . $table_border_color . '; background-color: ' . $table_header_bg . '; font-weight: bold;padding: 7px; color: ' . $primary_color . ';';
$td_styles = 'font-size: 12px;border-right: 1px solid ' . $table_border_color . '; border-bottom: 1px solid ' . $table_border_color . ';padding:3px;';
$p_styles = 'margin-top: 0px; margin-bottom: 20px;font-size:13px;';

/*$obj = $invoice->toArray();
$client = $obj['client'];

$project = $obj['project'];
$notes = $obj['note'];*/

$totalColspan = 5;

$count = 0;
?>
<style>
    table.shaded tr:nth-child(even) {
        background-color: #f2f2f2;
    }
</style>
<table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 12px; color:#000000;">
    <tbody>
        <tr>
            <td style="text-align:left;vertical-align:top;width:50%;font-size:14px;">
                @include('pdf-templates.shared.header-company')
            </td>
            <td style="text-align:right;vertical-align:top;width:50%;font-size:16px;">
                <strong>PURCHASE ORDER</strong><br />
                <small><strong>{{ $obj->po_number }}</strong></small>
            </td>
        </tr>
    </tbody>
</table>
<hr>
<table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 14px; color:#000000;">
    <tbody>
        <tr>
            <td style="text-align:left;vertical-align:top;width:50%">
                <small>Bill To</small><br />
                <strong>{{ $obj->supplier->name }}</strong><br />
                {{ $obj->supplier->billingAddress->address_1 }}<br />
                {{--  @if ($obj->supplier->billingAddress->address_2)
                    {{ $obj->supplier->billingAddress->address_2 }}<br />
                @endif
                @if (false)
                    {{ $client['city'] }}, {{ $client['state'] }} {{ $client['postal_code'] }}<br />
                    Phone: {{ $client['phone'] }}
                @endif --}}
            </td>
        </tr>
    </tbody>
</table>
<hr>
<table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 14px;color:#000000;">
    <thead>
        <tr>
            <th style="text-align:center;">Date</th>
            <th style="text-align:center;">Payment Term</th>
            <th style="text-align:center;">Shipment Mode</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align:center;">{{ $obj->date }}</td>
            <td style="text-align:center;">{{ $obj->paymentTerm->name }}</td>
            <td style="text-align:center;">{{ $obj->shipmentMode->name }}</td>
        </tr>
    </tbody>
</table>
<hr>
<table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 12px;color:#000000;">
    <thead>
        <tr>
            <th style="text-align:left;">#</th>
            <th style="text-align:left;">Item</th>
            <th style="text-align:center;">Unit</th>
            <th style="text-align:right;">Qty</th>
            <th style="text-align:right;">Rate</th>
            <th style="text-align:right;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($obj->items as $i)
            @php $count++; @endphp
            <tr>
                <td style="text-align:left;">{{ $count }}</td>
                <td style="text-align:left;">
                    {{ $i->product->name }}

                </td>
                <td style="text-align:center;">{{ $i->unit ? $i->unit->name : '-' }}</td>
                <td style="text-align:right;">{{ $i->quantity }}</td>
                <td style="text-align:right;">
                    {{ \App\Services\UtilityService::dsMoneyRound($i->rate) }}</td>
                <td style="text-align:right;">
                    {{ \App\Services\UtilityService::dsMoneyRound($i->amount) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="{{ $totalColspan }}" style="text-align:right;">Sub Total</th>
            <th style="text-align:right;">
                {{ \App\Services\UtilityService::dsMoneyRound($obj->sub_total) }}</th>
        </tr>
        @if (false)
            <tr>
                <th colspan="{{ $totalColspan }}" style="text-align:right;padding-top:3px;"><strong>DISCOUNT</strong>
                </th>
                <th style="text-align:right;padding-top:3px;">
                    <strong>{{ \App\Services\UtilityService::dsMoneyRound($obj->discount_total) }}</strong>
                </th>
            </tr>
        @endif
        @if (false)
            <tr>
                <th colspan="{{ $totalColspan }}" style="text-align:right;">TAX ({{ $taxPercent }}%)</th>
                <th style="text-align:right;">
                    {{ \App\Services\UtilityService::dsMoneyRound($obj->tax_total) }}</th>
            </tr>
        @endif
        <tr>
            <th colspan="{{ $totalColspan }}" style="text-align:right;">Grand Total</th>
            <th style="text-align:right;">
                {{ \App\Services\UtilityService::dsMoneyRound($obj->grand_total) }}</th>
        </tr>
    </tfoot>
</table>
<hr>
<table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 14px;color:#000000;">
    <thead>
        <tr>
            <th style="text-align:center;">Other Terms</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align:center;">{{ $obj->remarks }}</td>
        </tr>
    </tbody>
</table>
@include('pdf-templates.shared.authorized-signature')
