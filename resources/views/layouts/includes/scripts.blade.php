<!-- jQuery UI 1.11.4 -->
<script src="{{ asset('/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="{{ asset('/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>



<!-- ChartJS -->
<script src="{{ asset('/plugins/chart.js/Chart.min.js') }}"></script>
<!-- Sparkline -->
<script src="{{ asset('/plugins/sparklines/sparkline.js') }}"></script>
<!-- jQuery Knob Chart -->
<script src="{{ asset('/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
<!-- daterangepicker -->
<script src="{{ asset('/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('/plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{ asset('/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<!-- Summernote -->
<script src="{{ asset('/plugins/summernote/summernote-bs4.min.js') }}"></script>
<!-- overlayScrollbars -->
<script src="{{ asset('/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>

<!-- Alert -->
<script src="{{asset('/plugins/sweetalert2/sweetalert2.all.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
@include('layouts.includes._scripts-alert')

<!-- sweetalert 2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<!-- AdminLTE App -->
<script src="{{asset('/themes/js/adminlte.js')}}"></script>

<!-- Livewire -->
<livewire:scripts />

<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>

<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js" integrity="sha256-qXBd/EfAdjOA2FGrGAG+b3YBn2tn5A6bhz+LSgYD96k=" crossorigin="anonymous"></script>


<!-- hour js -->
<script src="{{asset('/build/js/tpicker.js')}}"></script>

<!-- build -->
<script src="{{asset('/build/js/app.js')}}"></script>

<script src="{{asset('/vendor/custom.js')}}"></script>

<!-- Skrip pada halaman tertentu -->
@stack('scripts')
