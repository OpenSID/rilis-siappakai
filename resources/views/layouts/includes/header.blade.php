<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.includes._base', ['favicon' => $favicon])

        <!-- Title -->
        <title>{{ $title ? $title . ' |' : $title }} Dasbor SiapPakai</title>

        <!-- Mengatur style pada header-->
        {{ $styles }}

    </head>

    {{-- <body class="hold-transition sidebar-mini layout-fixed"> --}}
    <body>
        @yield('body')
    </body>
</html>
