<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Failed</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
      .failed-message {
        text-align: center;
        margin-top: 20%;
      }

      .failed-icon {
        color: red;
        font-size: 48px;
      }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-12 failed-message">
            <i class="fas fa-times-circle failed-icon"></i>
            <h3>{{ $message }}</h3>
        </div>
    </div>
</div>
</body>
</html><?php
