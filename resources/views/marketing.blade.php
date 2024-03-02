<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
{{--    <link rel="stylesheet" type="text/css" href="{{ mix('css/marketing/app.css') }}">--}}
    <!-- Scripts -->
    <script
        src="https://kit.fontawesome.com/2828f7885a.js"
        integrity="sha384-WAsFbnLEQcpCk8lM1UTWesAf5rGTCvb2Y+8LvyjAAcxK1c3s5c0L+SYOgxvc6PWG"
        crossorigin="anonymous"
    ></script>
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

@vite(['resources/js/marketing/main.js'])
<script defer src="https://eu.umami.is/script.js" data-website-id="08e7bb76-ed23-40be-ae87-fd24446f8dc3"></script>
</body>
</html>
