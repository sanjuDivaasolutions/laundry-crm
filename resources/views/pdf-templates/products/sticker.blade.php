@php
    $primary_color = '#222222';
    $table_header_bg = '#fff';
    $table_border_color = '#000';

    $th_styles = 'font-size: 12px; border-right: 1px solid '.$table_border_color.'; border-bottom: 1px solid '.$table_border_color.'; background-color: '.$table_header_bg.';font-weight:bold;padding:7px; color: '.$primary_color.';';
    $td_styles = 'font-size: 12px;	border-right: 1px solid '.$table_border_color.'; border-bottom: 1px solid '.$table_border_color.';padding:3px;';
@endphp
<html>
<head>
    <style>
      @page {
        margin: 0px;
      }

      html {
        margin: 0px;
      }

      body {
        margin: 0px;
      }
    </style>
</head>
<body>
<div style="margin:0;padding:5px;width:100%;text-align:left;">
    <table style="overflow:wrap;autosize:1;font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;border-collapse: collapse; width: 95%; border-top: 1px solid {{$table_border_color}}; border-left: 1px solid {{$table_border_color}};">
        <tr>
            <th style="{{$th_styles}}">ART NO</th>
            <td style="{{$td_styles}}">{{ $product->item_code }}</td>
        </tr>
        <tr>
            <th style="{{$th_styles}}">BLEND</th>
            <td style="{{$td_styles}}">{{ $product->blend }}</td>
        </tr>
        <tr>
            <th style="{{$th_styles}}">GSM</th>
            <td style="{{$td_styles}}">{{ $product->gsm }}</td>
        </tr>
        <tr>
            <th style="{{$th_styles}}">WIDTH</th>
            <td style="{{$td_styles}}">{{ $product->width }}</td>
        </tr>
        <tr>
            <th style="{{$th_styles}}">REMARK</th>
            <td style="{{$td_styles}}">{{ $product->remark }}</td>
        </tr>
    </table>
</div>
</body>
</html>
