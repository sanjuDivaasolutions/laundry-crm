<!DOCTYPE html>
<html>
<head>
    <style>
      body {
        font-family: Arial, Helvetica, sans-serif;
        color: #333;
      }

      .header {
        text-align: center;
        padding: 20px;
        border-bottom: 1px solid #ddd;
      }

      .header img {
        max-width: 150px;
      }

      .header h1 {
        margin: 0;
        font-size: 24px;
      }

      .content {
        padding: 20px;
      }
    </style>
</head>
<body>
<div class="header">
    @php
        $imageUrl = @$company->image[0]['original_url'] ?? null;
    @endphp
    @if($imageUrl)
        <img src="{{$imageUrl}}" alt="{{ $company->name }} Logo">
    @endif
    <h4>{{ $company->name }}</h4>
</div>
<div class="content">
    {!! $content !!}
</div>
</body>
</html>
