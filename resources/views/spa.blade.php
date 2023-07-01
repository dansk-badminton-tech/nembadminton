<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <link rel="stylesheet" type="text/css" href="{{ mix('css/v1/app.css') }}">
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

<script src="{{ mix('js/v1/app.js') }}"></script>
</body>
</html>
