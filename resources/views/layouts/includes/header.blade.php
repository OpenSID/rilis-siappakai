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
<script src="{{ asset('/plugins/jquery/jquery.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<body>
    @yield('body')
</body>

</html>
