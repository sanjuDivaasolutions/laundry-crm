<?php
$primary_color = '#222222';
$table_header_bg = '#EEEEEE';
$table_border_color = '#DDDDDD';

$th_styles = 'font-size: 12px;border-right: 1px solid #DDDDDD; border-bottom: 1px solid '.$table_border_color.'; background-color: '.$table_header_bg.'; font-weight: bold;padding: 7px; color: '.$primary_color.';';
$td_styles = 'font-size: 12px;border-right: 1px solid '.$table_border_color.'; border-bottom: 1px solid '.$table_border_color.';padding:3px;';
$p_styles = 'margin-top: 0px; margin-bottom: 20px;font-size:13px;';

$count = 0;

$currency = $obj->currency;

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
        <td style="text-align:right;vertical-align:top;width:50%;margin:0;padding:0;font-size:16px;">
            <strong>YARDAGE INVOICE</strong><br/>
            <small><strong>{{$obj->code}}</strong></small>
        </td>
    </tr>
    </tbody>
</table>
@if(false)
<table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 12px; color:#000000;">
    <tbody>
    <tr>
        <td style="text-align:left;vertical-align:top;width:50%;font-size:14px;">
            @include('pdf-templates.shared.header-company')
        </td>
        <td style="text-align:right;vertical-align:top;width:50%">
            <table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 12px; color:#000000;">
                <tbody>
                <tr><td><h4 style="font-size:18px;text-align:right;">YARDAGE INVOICE</h4></td></tr>
                <tr><td><h4 style="font-size:18px;text-align:right;"><span>Reference #</span><br/>{{$obj->code}}</h4></td></tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
@endif
<hr>
<table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 14px;color:#000000;">
    <thead>
    <tr>
        <th style="text-align:center;">Date</th>
        <th style="text-align:center;">Buyer</th>
        <th style="text-align:center;">Sent via</th>
        <th style="text-align:center;">Courier Number</th>
        <th style="text-align:center;">Contact Person</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td style="text-align:center;">{{ $obj->date }}</td>
        <td style="text-align:center;">{{ $obj->buyer ? $obj->buyer->display_name : '-' }}</td>
        <td style="text-align:center;">{{ $obj->inquirySource ? $obj->inquirySource->name : '-' }}</td>
        <td style="text-align:center;">{{ $obj->courier_number ?: '-' }}</td>
        <td style="text-align:center;">{{ $obj->contact_person ?: '-' }}</td>
    </tr>
    </tbody>
</table>
<hr>
<table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 14px;color:#000000;">
    <thead>
    <tr>
        <th style="text-align:center;">Description</th>
        <th style="text-align:center;">Remark</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td style="text-align:center;">{{ $obj->description }}</td>
        <td style="text-align:center;">{{ $obj->remark }}</td>
    </tr>
    </tbody>
</table>
<hr>
<table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 12px;color:#000000;vertical-align:top;">
    <thead>
    <tr>
        <th style="text-align:left;">#</th>
        <th style="text-align:left;">Product</th>
        <th style="text-align:left;">Type</th>
        <th style="text-align:left;">Unit</th>
        <th style="text-align:right;">Rate</th>
        <th style="text-align:right;">Qty</th>
        <th style="text-align:right;">Amount</th>
    </tr>
    </thead>
    <tbody>
    @foreach($obj->items as $i)
        @php $count++; @endphp
        <tr>
            <td style="text-align:left;vertical-align:top;">{{ $count }}</td>
            <td style="text-align:left;vertical-align:top;">
                {{ $i->product }}
                @if($i->remark)
                    <p style="{{ $p_styles }}"><small>Remark: {{ $i->remark }}</small></p>
                @endif
            </td>
            <td style="text-align:left;vertical-align:top;">
                {{ $i->type }}
            </td>
            <td style="text-align:left;vertical-align:top;">
                {{ $i->unit ? $i->unit->name : '-'}}
            </td>
            <td style="text-align:right;vertical-align:top;">{{ $currency->symbol . number_format($i->rate,2) }}</td>
            <td style="text-align:right;vertical-align:top;">{{ $i->quantity }}</td>
            <td style="text-align:right;vertical-align:top;">{{ $currency->symbol . number_format($i->amount,2) }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th colspan="5" style="text-align:right;">Sub total</th>
        <th style="text-align:right;">{{ $obj->items->sum('quantity') }}</th>
        <th style="text-align:right;">{{ $currency->symbol . number_format($obj->sub_total,2) }}</th>
    </tr>
    @if($obj->discount_total)
        <tr>
            <th colspan="5" style="text-align:right;">Discount</th>
            <th style="text-align:right;">&nbsp;</th>
            <th style="text-align:right;">{{ $currency->symbol . number_format($obj->discount_total,2) }}</th>
        </tr>
    @endif
    @if($obj->sample_cost)
        <tr>
            <th colspan="5" style="text-align:right;">Other Charges</th>
            <th style="text-align:right;">&nbsp;</th>
            <th style="text-align:right;">{{ $currency->symbol . number_format($obj->sample_cost,2) }}</th>
        </tr>
    @endif
    @if($obj->courier_cost)
        <tr>
            <th colspan="5" style="text-align:right;">Courier Charges</th>
            <th style="text-align:right;">&nbsp;</th>
            <th style="text-align:right;">{{ $currency->symbol . number_format($obj->courier_cost,2) }}</th>
        </tr>
    @endif
    <tr>
        <th colspan="5" style="text-align:right;">Grand Total</th>
        <th style="text-align:right;">&nbsp;</th>
        <th style="text-align:right;">{{ $currency->symbol . number_format($obj->grand_total,2) }}</th>
    </tr>
    </tfoot>
</table>
@if($obj->remark)
<hr>
<table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 14px;color:#000000;">
    <thead>
    <tr>
        <th style="text-align:left;">Remark</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td style="text-align:left;">{{ $obj->remark }}</td>
    </tr>
    </tbody>
</table>
@endif
@include('pdf-templates.shared.authorized-signature')
