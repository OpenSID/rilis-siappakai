@extends('layouts.includes.header', ['favicon' => $favicon, 'title' => 'Login', 'styles' => ''])

@section('body')

<div class="hold-transition login-page">
    <div class="login-box">
        <!-- /.login-logo -->
        <div class="card card-outline card-success">
            <div class="card-header text-center">
                <a href="/">
                    <img class="align-content my-2" src="{{ $logo }}" alt="logo-aplikasi-pbb" height="75px">
                </a>
                <br>
                <span class="text-center fw-bold">{{ $sebutan_kabupaten }}</span>
            </div>
            <div class="card-body">
                <p class="login-box-msg fw-bold">Halaman Login</p>

                <form action="{{ route('login') }}" method="post">
                    @csrf
                    <div class="input-group mb-3">
                        <input type="text" value="{{ old('username') }}" name="username" id="username" class="form-control" required="required" placeholder="Nama Pengguna / Email">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <input type="password" value="{{ old('password') }}" name="password" id="password"  class="form-control" placeholder="Kata Sandi">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>

                    <div class="">
                        @error('message')
                            <div class="text-danger mt-2">
                                {{ $message }}
                            </div>
                            <br/>
                        @enderror
                    </div>

                    <div class="row d-flex align-items-center">
                        <div class="col-7">
                            <div class="icheck-primary">
                                <input type="checkbox" id="tampilkan">
                                <label for="tampilkan" class="fw-normal">Tampilkan kata sandi</label>
                            </div>
                        </div>

                        <div class="col-5 mt-1">
                            <label class="pull-right">
                                <a href="#" class="fw-normal">Lupa Kata Sandi?</a>
                            </label>
                        </div>
                    </div>

                    <div class="row my-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-block toastrDefaultSuccess" style="letter-spacing: 0.1em">Masuk</button>
                        </div>
                    </div>
                </form>


                <!-- Copyright -->
                <div class="text-center">
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
