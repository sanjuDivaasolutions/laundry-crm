<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribe to Newsletter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
      body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background-color: #f8f9fa;
      }

      .card {
        width: 400px;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
      }
    </style>
</head>
<body>
<div class="card text-center">
    <div class="text-center mb-4">
        <img src="{{ $company->image_base64 }}" alt="Company Logo" class="img-fluid" style="max-width: 150px;">
        <h2 class="mt-3 d-none">{{ $company->name }}</h2>
    </div>
    <h4 class="mb-3">Subscribe to our Newsletter</h4>
    <form id="subscribe-form">
        <div class="mb-3">
            <input type="text" id="name" class="form-control" placeholder="Your Name (Optional)">
        </div>
        <div class="mb-3">
            <input type="email" id="email" class="form-control" placeholder="Your Email" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Subscribe</button>
    </form>
    <div id="message" class="mt-3"></div>
</div>

<script>
    $(document).ready(function () {
        $('#subscribe-form').on('submit', function (event) {
            console.log(event);
            event.preventDefault();
            subscribe();
        });
    });

    function subscribe() {
        let name = $('#name').val().trim();
        let email = $('#email').val().trim();

        if (!email) {
            $('#message').html('<div class="alert alert-danger">Please enter a valid email.</div>');
            return;
        }

        $.ajax({
            url: '{{  $subscriptionUrl }}', // Adjust the endpoint as per backend route
            method: 'POST',
            data: {name: name, email: email, company_id: '{{ $company->id }}'},
            success: function (response) {
                $('#message').html('<div class="alert alert-success">Subscribed successfully!</div>');
                $('#subscribe-form')[0].reset();
            },
            error: function () {
                $('#message').html('<div class="alert alert-danger">Something went wrong. Try again.</div>');
            }
        });
    }
</script>
</body>
</html>
