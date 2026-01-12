<?php
$primary_color = '#222222';
$table_header_bg = '#EEEEEE';
$table_border_color = '#DDDDDD';

$th_styles = 'font-size: 12px;border-right: 1px solid #DDDDDD; border-bottom: 1px solid '.$table_border_color.'; background-color: '.$table_header_bg.'; font-weight: bold;padding: 7px; color: '.$primary_color.';';
$td_styles = 'font-size: 12px;border-right: 1px solid '.$table_border_color.'; border-bottom: 1px solid '.$table_border_color.';padding:3px;';
$p_styles = 'margin-top: 0px; margin-bottom: 20px;font-size:13px;';

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
            @include('pdf-templates.shared.header-company', ['obj'=>$obj->salesOrder])
        </td>
        <td style="text-align:right;vertical-align:top;width:50%">
            <table style="width:100%;border:0;font-family:Arial, Helvetica, sans-serif;font-size: 12px; color:#000000;">
                <tbody>
                <tr><td><h4 style="font-size:18px;text-align:right;">PACKING LIST</h4></td></tr>
                <tr><td><h4 style="font-size:18px;text-align:right;"><span>PL #</span><br/>{{$obj->code}}</h4></td></tr>
                </tbody>
            </table>
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
        <th style="text-align:left;">Optus Code</th>
        <th style="text-align:right;">Qty</th>
        <th style="text-align:right;">Gross Wt.</th>
        <th style="text-align:right;">Net Wt.</th>
    </tr>
    </thead>
    <tbody>
    @foreach($obj->items as $i)
        @php $count++; @endphp
        <tr>
            <td style="text-align:left;">{{ $count }}</td>
            <td style="text-align:left;">
                {{ $i->product->name }}
            </td>
            <td style="text-align:left;">
                {{ $i->product->item_code }}
            </td>
            <td style="text-align:right;">{{ $i->quantity }}</td>
            <td style="text-align:right;">{{ $i->gross_weight }}</td>
            <td style="text-align:right;">{{ $i->net_weight }}</td>
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
@if(false)
<hr>
<div style="width:100%;margin-top:30px;">
    <p style="text-align:right;font-size:12px;margin-right:10px;">
        <strong>AUTHORIZED SIGNATURE</strong>
        <br/><br/>
        <img src="{{ public_path('media/signature/optus.png') }}" />
        <br/>
        <strong>{{ $obj->salesOrder->company->name }}</strong>
    </p>
</div>
@endif
