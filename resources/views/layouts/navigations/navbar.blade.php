<!-- Navbar -->
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        @if($toggle == 1)
            <a class="nav-link {{ $toggle == false ? 'd-none' : '' }}" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        @endif
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Navbar user panel (optional) -->
        <li class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#">
                <img src="{{ $foto_pengguna }}" class="img-circle elevation-2" alt="Foto Pengguna" height="30px" width="30px">
                <span class="mx-3">{{ $akun_pengguna }}</span>
                <i class="right fas fa-angle-down"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <a class="nav-link" href="{{ route('dasbor') }}">
                    <i class="fa fa-desktop me-3"></i>Dasbor
                </a>

                {{-- <a class="nav-link" href="#">
                    <i class="fa fa-user me-3"></i>Profil
                </a> --}}

                @if(env('OPENKAB') == 'true')
                    <a class="nav-link" href="{{ route('aplikasi.index') }}">
                        <i class="fa fa-cog me-3"></i>Pengaturan
                    </a>
                @endif

                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="btn nav-link">
                        <i class="fa fa-power-off me-3"></i>Keluar
                    </button>
                </form>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
            <i class="fas fa-question-circle"></i>
            </a>
        </li>
    </ul>
<!-- /.navbar -->
