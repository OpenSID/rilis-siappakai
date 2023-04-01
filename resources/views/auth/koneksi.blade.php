@extends('layouts.includes.header', ['favicon' => $favicon, 'title' => 'Koneksi', 'styles' => ''])

@section('body')

<div class="hold-transition login-page">
    <div class="login-box">
        <!-- /.login-logo -->
        <div class="card card-outline card-success">
            <div class="card-header text-center">
                <a href="/">
                    <img class="align-content my-2" src="{{ $logo }}" alt="logo-aplikasi-pbb" height="75px">
                </a>
            </div>
            <div class="card-body">
                <p class="login-box-msg fw-bold text-red">
                    <i class="nav-icon fas fa-warning me-2"></i>
                    Koneksi Database Gagal
                </p>

                @foreach ($infos as $info)
                    <div class="row">
                        <div class="col-md-1">
                            <small>{{ $info['nomor'] }}</small>
                        </div>
                        <div class="col-md-11">
                            @if($info['url'] == true)
                                <a href="{{ route('login') }}">
                                    <small>{{ $info['keterangan'] }}</small>
                                </a>
                            @else
                                <small>{{ $info['keterangan'] }}</small>
                                @if(!is_null($info['detail']))
                                    <div class="row">
                                        @foreach ($details as $detail)
                                            <div class="col-md-4">
                                                <small>{{ $detail['default'] }}</small>
                                            </div>
                                            <div class="col-md-1">
                                                <small>:</small>
                                            </div>
                                            <div class="col-md-7">
                                                <small class="text-red">{{ $detail['keterangan'] }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach


                <!-- Copyright -->
                <div class="text-center mt-3">
                    @include('layouts.includes._copyright')
                    <br/>
                    @include('layouts.includes._version')
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.login-box -->
</div>

<!-- Scripts -->
    @include('layouts.includes.scripts')
    <script>
        $('document').ready(function() {
            var pass = $("#password");
            $('#tampilkan').click(function() {
                if (pass.attr('type') === "password") {
                    pass.attr('type', 'text');
                } else {
                    pass.attr('type', 'password')
                }
            });
        });
    </script>
<!-- /.scripts -->

@endsection
