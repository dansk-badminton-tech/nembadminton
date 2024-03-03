<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'vapidPublicKey' => config('webpush.vapid.public_key'),
        ], JSON_THROW_ON_ERROR) !!};
    </script>
</head>
<body>
<div id="app">
    <app></app>
</div>

@vite(['js/v1/app.js', 'css/v1/app.css'])
</body>
</html>
