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

<!-- pindahkan jQuery pada header -->
<!-- jQuery and Select2 are loaded via Vite in app.js -->


<body>
    @yield('body')
</body>

</html>
