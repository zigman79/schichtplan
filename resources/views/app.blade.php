<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    <!-- Scripts -->
    @routes
    <script src="{{ mix('js/app.js') }}" defer></script>


    <style>
        @if (config('tenant.name') == 'credimaxx')
            :root {
            --tenant-color-50: #f0f9ff;
            --tenant-color-100: #e0f2fe;
            --tenant-color-200: #bae6fd;
            --tenant-color-300: #7dd3fc;
            --tenant-color-400: #38bdf8;
            --tenant-color-500: #0ea5e9;
            --tenant-color-600: #0284c7;
            --tenant-color-700: #0369a1;
            --tenant-color-800: #075985;
            --tenant-color-900: #0c4a6e;
        }
        @endif
    </style>


</head>
<body class="font-sans antialiased">
@inertia


</body>
</html>
