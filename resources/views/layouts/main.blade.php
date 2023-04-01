@extends('layouts.includes.header', ['favicon' => $favicon])

@section('body')

    <!-- Preloader -->
    <x-preloader></x-preloader>

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light" style="margin-left: 0px">
        <x-navbar toggle=0></x-navbar>
    </nav>
    <!-- /.navbar -->

     <!-- Content Wrapper. Contains page content -->
        <!-- Content-->
        <div class="">
            <div class="p-4">
                @yield('content')
            </div>
        </div>
        <!-- /.content-->
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-light vh-100">
        <!-- Control sidebar content goes here -->
        <x-information></x-information>
    </aside>
    <!-- /.control-sidebar -->

    <!-- Footer -->
    <footer id="main-footer-dashboard">
        @include('layouts.includes.footer')
    </footer>
    <!-- /.footer -->

    <!-- Scripts -->
        @include('layouts.includes.scripts')
    <!-- /.scripts -->

@endsection
