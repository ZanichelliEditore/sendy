<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content={{ csrf_token() }}>
        <script>
            window.laravel = { csrfToken: '{{ csrf_token() }}'}
        </script>

        <link rel="stylesheet" href="{{mix('css/app.css')}}">
        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
        <link rel="icon" type="image/png" href='/images/favicon.ico'>
        <title>Sendy</title>

        @yield('js-localization.head')
    </head>
    <body class="bg-white">

        <div id="app">
            <header-nav></header-nav>
            @yield('body')
        </div>

        <script src="{{mix('js/app.js')}}"></script>
    </body>
</html>
