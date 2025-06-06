<!DOCTYPE html>
<html lang="en" class="has-aside-left has-aside-mobile-transition has-navbar-fixed-top">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
{{--    <link rel="stylesheet" type="text/css" href="{{ mix('css/app.css') }}">--}}
    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'vapidPublicKey' => config('webpush.vapid.public_key'),
        ], JSON_THROW_ON_ERROR) !!};
    </script>
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito&display=swap" rel="stylesheet">
    <link href="https://cdn.materialdesignicons.com/7.2.96/css/materialdesignicons.min.css" rel="stylesheet" type="text/css">
</head>
<body>
<noscript>
    <strong>We're sorry but admin-one-vue-bulma-dashboard doesn't work properly without JavaScript enabled. Please enable it to continue.</strong>
</noscript>
<div id="app"></div>
@vite(['resources/js/admin-v2/main.js'])
@production
<script defer src="https://eu.umami.is/script.js" data-website-id="08e7bb76-ed23-40be-ae87-fd24446f8dc3"></script>
@endproduction
</body>
</html>
