<html>
<head>
    <style>
        * { margin: 0; padding: 0; }
        body {
            font: 14px/1.4  dejavusanscondensed;
        }
        hr{
            border: 1px solid #000;
            border-bottom: 0;
        }
        #page-wrap { width: 800px; margin: 0 auto; }

        table { border-collapse: collapse; }
        table td, table th { padding: 5px; }

        #customer { overflow: hidden; }

        #logo { text-align: right; float: right; position: relative; margin-top: 25px; border: 1px solid #fff; max-width: 540px; overflow: hidden; }

        #meta { margin-top:1px;width:100%;float:right; }
        #meta td { text-align: right;  }
        #meta td.meta-head { text-align: left; background: #fff;font-weight: bold; }
        #meta td textarea { width: 100%; height: 20px; text-align: right; }

        #top-row-table-no-b { clear: both; width: 100%; margin: 0; padding:0; border: 0; font-size: 10px;}
        #top-row-table-no-b th { background:#fff;border:0;}
        #top-row-table-no-b td { background:#fff;border:0;}

        #top-row-table { clear: both; width: 100%; margin: 10px 0 0 0; border: 1px solid #000; font-size: 10px;}
        #top-row-table th { background:#fff;border:1px solid #000;border-right:0;border-bottom:0; }
        #top-row-table td { background:#fff;border:1px solid #000;border-right:0; }

        #billing-table { clear: both; width: 100%; margin: 20px 0 20px 0; border: 1px solid #000; font-size: 12px;}
        #billing-table th { background:#fff;border:1px solid #000;border-right:0;border-bottom:0; }
        #billing-table td { background:#fff;border:1px solid #000;border-right:0; }

        #comments-table { clear: both; width: 100%; margin: 10px 0 0 0; border: 1px solid #000; font-size: 12px;}
        #comments-table th { background:#fff;border:1px solid #000;border-right:0;border-bottom:0; }
        #comments-table td { background:#fff;border:1px solid #000;border-right:0; }

        #bank-details-table { clear: both; width: 100%; margin:0;padding:2px; border: 1px solid #000; font-size: 12px;}
        #bank-details-table th { background:#fff;border:0; }
        #bank-details-table td { background:#fff;border:0; }

        #items { clear: both; width: 100%; margin: 20px 0 20px 0; border: 1px solid #000;font-size: 10px;}
        #items th { background: #fff;border: 1px solid #000;border-right:0;border-top:0; }
        #items td { background: #fff;border: 1px solid #000;border-left:0;border-top:0; }
        #items td.b-left { border-left: 1px solid #000 !important; }
        #items textarea { width: 80px; height: 50px; }
        #items tr.item-row td {  vertical-align: top; }
        #items td.description { width: 300px; }
        #items td.item-name { width: 175px; }
        #items td.description textarea, #items td.item-name textarea { width: 100%; }
        #items td.total-line { padding-right:10px; text-align: right;font-weight: normal; }
        #items td.total-value { border-left: 0; padding: 5px;font-weight: normal; }
        #items td.total-value textarea { height: 20px; background: none; }
        #items td.balance { background: #fff; }
        #items td.blank { border: 0; }

        #terms { text-align: left; margin: 20px 0 0 0; }
        #terms h5 { text-transform: uppercase; font: 13px; letter-spacing: 10px; border-bottom: 1px solid #ccc; padding: 0 0 8px 0; margin: 0 0 8px 0; }
        #terms textarea { width: 100%; text-align: center;}

    </style>

</head>

<body>

<div id="page-wrap">
    <table id="top-row-table-no-b" style="width:100%;margin:0 auto;margin-bottom:1px;">
        <tbody>
        <tr>
            <td style="text-align:center;padding:0;">
                    <span style="text-align:center;font-size:18px;padding:0;">
                        <strong>{{ $obj->company->name }}</strong>
                    </span>
                <br/>
                @if($obj->company->address_1)
                    {{ $obj->company->address_1 }}
                @endif
                @if($obj->company->address_2)
                    @if($obj->company->address_1)
                        <br/>
                    @endif
                    {{ $obj->company->address_2 }}
                @endif
            </td>
        </tr>
        </tbody>
    </table>

    <h3 style="text-align:center;font-weight:bold;margin-bottom:5px;">PROFORMA INVOICE</h3>
    <table id="billing-table" style="margin-bottom:10px;margin-top:0;">
        <tr>
            <th style="text-align:left;width:50%;font-weight:normal;vertical-align:top;">
                <strong>TO</strong>
                <br/>{{ $obj->buyer->name }}
                <br/>{{ $obj->buyer->billingAddress->address_1 }}
                @if(@$obj->buyer->billingAddress->address_2)
                    <br/>{{ $obj->buyer->billingAddress->address_2 }}
                @endif
                @if(@$obj->buyer->billingAddress->location)
                    <br/>{{ $obj->buyer->billingAddress->location }}
                @endif
                @if(@$obj->buyer->billingAddress->postal_code)
                    - {{ $obj->buyer->billingAddress->postal_code }}
                @endif
                @if(@$obj->buyer->gst_number)
                    <br/><strong>GST: </strong>{{ $obj->buyer->gst_number }}
                @endif
                @if(@$obj->buyer->iec)
                    <br/><strong>IEC: </strong>{{ $obj->buyer->iec }}
                @endif
                @if(@$obj->buyer->user->email)
                    <br/><strong>EMAIL: </strong>{{ $obj->buyer->user->email }}
                @endif
                @if(@$obj->buyer->user->name)
                    <br/><strong>CONTACT PERSON: </strong>{{ $obj->buyer->user->name }}
                @endif
                @if(@$obj->buyer->user->phone)
                    <br/><strong>PHONE: </strong>{{ $obj->buyer->user->phone }}
                @endif
            </th>
            <th style="text-align:left;width:50%;font-weight:normal;vertical-align:top;">
                <p style="margin-bottom:3px;"><strong>P/I No </strong> {{ $obj->so_number }}</p>
                <p style="margin-bottom:3px;"><strong>DATE: </strong>{{$obj->order_date}}</p>
                <p style="margin-bottom:3px;"><strong>PRICE TERM: </strong>{{$obj->priceTerm->name}}</p>
                @if(false)
                <p style="margin-bottom:3px;"><strong>CURRENCY: </strong>{{$obj->customerCurrency->code}}</p>
                @endif
                <p style="margin-bottom:3px;"><strong>POL </strong> {{ $obj->pol ? $obj->pol->name : '' }}</p>
                <p style="margin-bottom:3px;"><strong>POD </strong>{{ $obj->pod ? $obj->pod->name : '' }}</p>
            </th>
        </tr>
    </table>
    <p style="margin-bottom:5px;"><strong>ITEMS</strong></p>
    <table id="items" style="margin-top: 5px;">
        <tr>
            <th style="text-align:center;text-transform:uppercase;">Item Code</th>
            <th style="text-align:center;text-transform:uppercase;">Description</th>
            <th style="text-align:center;text-transform:uppercase;">Qty</th>
            <th  style="text-align:center;text-transform:uppercase;">Unit</th>
            <th style="text-align:center;text-transform:uppercase;">Rate</th>
            <th style="text-align:center;text-transform:uppercase;">Amount</th>
        </tr>
        @foreach ($obj->items as $i)
        <tr class="item-row">
            <td style="text-align:center;">{{ $i->item_code }}</td>
            <td style="text-align:left;">{{ $i->description }}</td>
            <td style="text-align:center;">{{ $i->quantity }}</td>
            <td style="text-align:center;">{{ $i->unit->name }}</td>
            <td style="text-align:center;">{{ \App\Services\UtilityService::dsRound($i->rate) }}</td>
            <td style="text-align:center;">{{ \App\Services\UtilityService::dsRound($i->amount) }}</td>
        </tr>
        @endforeach
        <tr class="item-row">
            <td colspan="5" style="text-align:right;font-weight:bold;text-transform:uppercase;">Sub Total</td>
            <td style="text-align:center;font-weight:bold;">{{ \App\Services\UtilityService::dsRound($obj->sub_total) }}</td>
        </tr>
        @if($obj->expenses)
        @foreach($obj->expenses as $e)
        <tr class="item-row">
            <td colspan="5" style="text-align:right;font-weight:bold;text-transform:uppercase;">{{ $e->account->name }}</td>
            <td style="text-align:center;font-weight:bold;">{{ \App\Services\UtilityService::dsRound($e->amount) }}</td>
        </tr>
        @endforeach
        @endif
        <tr class="item-row">
            <td colspan="5" style="text-align:right;font-weight:bold;text-transform:uppercase;">Grand Total</td>
            <td style="text-align:center;font-weight:bold;">{{ \App\Services\UtilityService::dsRound($obj->grand_total) }}</td>
        </tr>
        <tr class="item-row">
            <th colspan="6" style="text-align:center;text-transform:uppercase">{{ Terbilang::make($obj->grand_total,'') }}</th>
        </tr>
    </table>
    @if(false)
        <p style="margin-bottom:5px;"><strong>TERMS & CONDITIONS</strong></p>
        <table id="bank-details-table">
            @foreach($obj->orderTerm as $ot)
            <tr>
                <td style="width:25px;">{{$ot->sort}}</td>
                <td style="text-align:left;">{{$ot->description}}</td>
            </tr>
            @endforeach
        </table>
    @endif
    @if($obj->companyBank)
    <p style="margin-bottom:5px;"><strong>BANK INFORMATION</strong></p>
    <table id="bank-details-table">
        <tr>
            <td style="width:100px;">BENEFICIARY NAME</td>
            <td style="text-align:left;width:50%;">
                {{@$obj->companyBank->beneficiary_name}}
            </td>
        </tr>
        <tr>
            <td style="width:100px;">BANK NAME</td>
            <td style="text-align:left;width:50%;">
                {{@$obj->companyBank->bank_name}}
            </td>
        </tr>
        <tr>
            <td style="width:100px;">BANK ADDRESS</td>
            <td style="text-align:left;width:50%;">
                {{@$obj->companyBank->address}}
            </td>
        </tr>
        <tr>
            <td style="width:100px;">A/C NO</td>
            <td style="text-align:left;width:50%;">
                {{ @$obj->companyBank->account_number }}
            </td>
        </tr>
        <tr>
            <td style="width:100px;">SWIFT CODE</td>
            <td style="text-align:left;width:50%;">
                {{@$obj->companyBank->swift_code}}
            </td>
        </tr>
    </table>
    @endif
    <div style="width:100%;margin-top:30px;">
        <div style="text-align:right;font-size:12px;margin-right:10px;">
            <p><strong>For and on behalf of<br/>{{ $obj->company->name }}</strong></p>
            <p style="border-top:1px solid #000;padding-top:10px;width:30%;float:right;text-align:right;">
                <img style="width:100%;" src="{{ public_path('media/signature/optus.png') }}" /><br/>
                Authorized Signature(s)
            </p>
        </div>
    </div>
</div>
</body>
</html>
