<!-- Navbar -->
<ul class="navbar-nav">
    <li class="nav-item">
        @if($toggle == 1)
            <a class="nav-link {{ $toggle == false ? 'd-none' : '' }}" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        @endif
    </li>
</ul>

<!-- Right navbar links -->
<ul class="navbar-nav ms-auto">
    <li class="mt-2 {{ $notifbackup ? '' : 'd-none' }}">
        <i class="fa-solid fa-file-circle-xmark text-danger me-1"></i>
        <span class="text-danger fw-bold">BACKUP GAGAL</span>
    </li>

    <!-- Navbar user panel -->
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
           role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="{{ $foto_pengguna }}" class="img-circle elevation-2 me-2" alt="Foto Pengguna" height="30" width="30">
            <span>{{ $akun_pengguna }}</span>
        </a>

        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li>
                <a class="dropdown-item" href="{{ route('dasbor') }}">
                    <i class="fa fa-desktop me-2"></i> Dasbor
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('aplikasi.index') }}">
                    <i class="fa fa-cog me-2"></i> Pengaturan Aplikasi
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('jadwal-tugas.index') }}">
                    <i class="fa fa-clock me-2"></i> Pengaturan Jadwal Tugas
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form action="{{ route('logout') }}" method="post" class="m-0">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fa fa-power-off me-2"></i> Keluar
                    </button>
                </form>
            </li>
        </ul>
    </li>

    <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
            <i class="fas fa-question-circle"></i>
        </a>
    </li>
</ul>
<!-- /.navbar -->
