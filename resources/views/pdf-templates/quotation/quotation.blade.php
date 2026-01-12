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

$company = $obj->company ?? null;
$image = $company->image_base64 ?? null;

$companyClasses = "text-align:left;vertical-align:top;font-size:12px;";
if ($image) {
    $companyClasses .= "width:25%";
} else {
    $companyClasses .= "width:50%";
}

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
        @if($image)
            <td style="text-align:left;vertical-align:top;width:25%;">
                <img src="{{ $image }}" style="max-width: 150px;"/>
            </td>
        @endif
        <td style="{{$companyClasses}}">
            @include('pdf-templates.shared.header-company')
        </td>
        <td style="text-align:right;vertical-align:top;width:50%;font-size:16px;">
            <strong>Quotation</strong><br/>
            <small><strong>{{ $obj->order_no }}</strong></small>
        </td>
    </tr>
    </tbody>
</table>
<hr>
<table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 14px; color:#000000;">
    <tbody>
    <tr>
        <td style="text-align:left;vertical-align:top;width:50%">
            <small>Bill To</small><br/>
            <strong>{{ $obj->buyer->name }}</strong><br/>
            @if($obj->buyer->billingAddress)
                {{ $obj->buyer->billingAddress->address_1 }}
                @if ($obj->buyer->billingAddress->address_2)
                    {{ $obj->buyer->billingAddress->address_2 }}
                @endif
                @if($obj->buyer->billingAddress->city || $obj->buyer->billingAddress->state || $obj->buyer->billingAddress->postal_code)
                    <br/>
                @endif
                @if($obj->buyer->billingAddress->city)
                    {{ $obj->buyer->billingAddress->city->name }}
                @endif
                @if($obj->buyer->billingAddress->state)
                    {{ $obj->buyer->billingAddress->state->name }}
                @endif
                @if($obj->buyer->billingAddress->postal_code)
                    {{ $obj->buyer->billingAddress->postal_code }}
                @endif
            @endif
        </td>
    </tr>
    </tbody>
</table>
<hr>
<table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 14px;color:#000000;">
    <thead>
    <tr>
        <th style="text-align:center;">Date</th>
        <th style="text-align:center;">Expected Delivery Date</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td style="text-align:center;">{{ $obj->date }}</td>
        <td style="text-align:center;">{{ $obj->expected_delivery_date }}</td>
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
    @if ($obj->tax_total)
        <tr>
            <th colspan="{{ $totalColspan }}" style="text-align:right;">TAX</th>
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
@if($obj->remarks)
    <hr>
    <table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 14px;color:#000000;">
        <thead>
        <tr>
            <th style="text-align:center;">Other Terms</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td style="text-align:center;">{{ $obj->remark }}</td>
        </tr>
        </tbody>
    </table>
@endif
@include('pdf-templates.shared.authorized-signature')
