@php
    $company = $obj->company ?: null;
@endphp
<strong>{{$company ? $company->name : '' }}</strong><br/>
{{$company ? $company->address_1 : '' }}
@if($company->address_2)
    <br/>{{$company ? $company->address_2 : '' }}<br/>
@endif
@if($company)
    @if($company->city || $company->state || $company->postal_code)
        {{ !empty($company->city) ? $company->city->name . ', ' : ''  }}{{!! !empty($company->state) ? $company->state->name : '' }} {{!! $company ? $company->postal_code : '' }}
        <br/>
    @endif
@endif
@if($company)
    @if($company->phone)
        Phone: {{$company->phone}}<br/>
    @endif
    @if($company->fax)
        Fax: {{$company->fax}}<br/>
    @endif
    @if($company->email)
        Email: {{$company->email}}<br/>
    @endif
    @if($company->gst_number)
        GST # {{$company->gst_number}}
    @endif
@endif
