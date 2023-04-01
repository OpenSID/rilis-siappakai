@extends('layouts.includes.header', ['favicon' => $favicon])

@section('body')
<div class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Preloader -->
        <x-preloader></x-preloader>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <x-navbar></x-navbar>
        </nav>
        <!-- /.navbar -->

        <!-- Sidebar-->
        <x-sidebar></x-sidebar>
        <!-- /#sidebar -->

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Breadcrumbs-->
            @yield('breadcrumbs')
            <!-- /.breadcrumbs-->

            <!-- Content-->
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
            <!-- /.content-->
        </div>
        <!-- /.content-wrapper -->

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-light vh-100">
            <!-- Control sidebar content goes here -->
            <x-information></x-information>
        </aside>
        <!-- /.control-sidebar -->

        <!-- Footer -->
        <footer class="main-footer">
            @include('layouts.includes.footer')
        </footer>
        <!-- /.footer -->

        <!-- Scripts -->
            @include('layouts.includes.scripts')
        <!-- /.scripts -->
    </div>
</div>
@endsection
