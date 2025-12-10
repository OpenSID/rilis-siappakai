<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="description" content="OpenSID Saas">
<meta name="viewport" content="width=device-width, initial-scale=1">

@livewireStyles

<!-- Favicon -->
<link rel="apple-touch-icon" href="{{ asset($favicon) }}">
<link rel="shortcut icon" href="{{ asset($favicon) }}">

<!-- Google Font: Source Sans Pro -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

<!-- Font Awesome will be loaded via Vite -->

<!-- Ionicons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
<!-- Bootstrap 5 CSS (loaded via Vite) -->
<!-- DataTables CSS (loaded via Vite) -->
<!-- iCheck -->
<link rel="stylesheet" href="{{ asset('/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
<!-- overlayScrollbars -->
<link rel="stylesheet" href="{{ asset('/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
<!-- Daterange picker -->
<link rel="stylesheet" href="{{ asset('/plugins/daterangepicker/daterangepicker.css') }}">
<!-- summernote -->
<link rel="stylesheet" href="{{ asset('/plugins/summernote/summernote-bs4.min.css') }}">

<!-- Toastr -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

<!-- Select2 will be loaded via Vite -->

<!-- Removed missing tpicker.css include (404) to prevent CSS load errors) -->

<!-- Vite Assets -->
@vite(['resources/css/app.css', 'resources/js/app.js'])

<!-- Removed Bootstrap 4-specific SweetAlert2 theme to avoid conflicts with Bootstrap 5 -->

<!-- Theme style -->
<link rel="stylesheet" href="{{asset('/themes/css/adminlte.min.css')}}">

<!-- Removed Select2 Bootstrap 4 theme to avoid conflicts with Bootstrap 5 -->
<link rel="stylesheet" href="{{ asset('/vendor/custom.css') }}">


