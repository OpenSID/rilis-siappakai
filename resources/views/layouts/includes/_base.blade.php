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

<!-- Font Awesome 6 (compatible with fa fa- classes) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-pFJYzZ3VZzC8ix3qap2A7JhzvN5fA6M5gZ3kA5TQH+5KjcmPUFsh6PiydrWQpL1JzUuWzTC3jF7hJxw/3W+DbA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Ionicons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Removed missing tpicker.css include (404) to prevent CSS load errors) -->

<!-- Vite Assets -->
@vite(['resources/css/app.css', 'resources/js/app.js'])

<!-- Removed Bootstrap 4-specific SweetAlert2 theme to avoid conflicts with Bootstrap 5 -->

<!-- Theme style -->
<link rel="stylesheet" href="{{asset('/themes/css/adminlte.min.css')}}">

<!-- Removed Select2 Bootstrap 4 theme to avoid conflicts with Bootstrap 5 -->
<link rel="stylesheet" href="{{ asset('/vendor/custom.css') }}">


