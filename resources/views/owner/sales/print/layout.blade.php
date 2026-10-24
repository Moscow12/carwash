<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <x-print.styles />
</head>
<body>
    <x-print.toolbar />

    <div class="page">
        @yield('content')

        <x-print.footer />
    </div>

    <script>
        // Open the print dialog straight away, like the receipt does
        window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });
    </script>
</body>
</html>
