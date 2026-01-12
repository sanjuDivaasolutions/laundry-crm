<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ env('APP_NAME') }}</title>
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96"/>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg"/>
    <link rel="shortcut icon" href="/favicon.ico"/>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png"/>
    <meta name="apple-mobile-web-app-title" content="MyWebSite"/>
    <link rel="manifest" href="/site.webmanifest"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700">
    <meta name="datepicker_date_format" content="{{ config('project.datepicker_date_format') }}"/>
    <meta name="moment_date_format" content="{{ config('project.moment_date_format') }}"/>
    <meta name="moment_time_format" content="{{ config('project.moment_time_format') }}"/>
    <meta name="moment_datetime_format" content="{{ config('project.moment_datetime_format') }}"/>
    <meta name="default_currency_id" content="{{ config('system.defaults.currency.id') }}"/>
    <meta name="default_currency_code" content="{{ config('system.defaults.currency.code') }}"/>
    <meta name="app-locale" content="{{ App::getLocale() }}"/>
    @vite('resources/sass/app.scss')
</head>
<body>
<div id="app"></div>
@vite('resources/js/app.js')
</body>
</html>
