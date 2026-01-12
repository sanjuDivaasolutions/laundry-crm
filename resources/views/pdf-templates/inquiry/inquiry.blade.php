<?php
$primary_color = '#222222';
$table_header_bg = '#EEEEEE';
$table_border_color = '#DDDDDD';

$th_styles = 'font-size: 12px;border-right: 1px solid #DDDDDD; border-bottom: 1px solid ' . $table_border_color . '; background-color: ' . $table_header_bg . '; font-weight: bold;padding: 7px; color: ' . $primary_color . ';';
$td_styles = 'font-size: 12px;border-right: 1px solid ' . $table_border_color . '; border-bottom: 1px solid ' . $table_border_color . ';padding:3px;';
$p_styles = 'margin-top: 0px; margin-bottom: 20px;font-size:13px;';

$count = 0;

$items = $obj['items'];

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
            <strong>INQUIRY</strong><br/>
            <small><strong>{{$obj->code}}</strong></small>
        </td>
    </tr>
    </tbody>
</table>
<hr>
<table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 14px;color:#000000;">
    <thead>
    <tr>
        <th style="text-align:center;">Date</th>
        <th style="text-align:center;">Buyer</th>
        <th style="text-align:center;">Contact Person</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td style="text-align:center;">{{ $obj->date }}</td>
        <td style="text-align:center;">{{ $obj->buyer ? $obj->buyer->display_name : '-' }}</td>
        <td style="text-align:center;">{{ $obj->contact_person ?: '-' }}</td>
    </tr>
    </tbody>
</table>
<hr>
<table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 12px;color:#000000;">
    <thead>
    <tr>
        <th style="text-align:left;">#</th>
        <th style="text-align:left;">Product</th>
        <th style="text-align:left;">Unit</th>
        <th style="text-align:right;">Est. Rate</th>
        <th style="text-align:right;">Min Order Qty</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $i)
        @php $count++; @endphp
        <tr>
            <td style="text-align:left;">{{ $count }}</td>
            <td style="text-align:left;">
                {{ isset($i['product']) ? $i['product']->name : '' }}
                <br/><small>{{ isset($i['product']) ? $i['product']->item_code : '' }}</small>
            </td>
            <td style="text-align:left;">
                {{ isset($i['unit']) ? $i['unit']->name : '' }}
            </td>
            <td style="text-align:right;">{{ $i['buyer_estimated_rate_label'] }}</td>
            <td style="text-align:right;">{{ $i['quantity'] }}</td>
        </tr>
    @endforeach
    </tbody>
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
