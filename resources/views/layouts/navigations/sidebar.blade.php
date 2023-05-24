<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/" class="brand-link">
      <img src="{{ $logo }}" alt="OpenDesa Logo" class="brand-image img-circle">
      <span class="brand-text font-weight-light">{{ $nama_aplikasi }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
                <li class="nav-header">{{env('OPENKAB') == 'true' ? strtoupper($sebutan_kabupaten) : 'PELANGGAN'}}</li>
                <li class="nav-item">
                    <a href="{{ route('pelanggan.index') }}" class="nav-link {{ ($active == 'pelanggan' ? 'active' : '') }}">
                        <i class="fas fa-circle nav-icon"></i>
                        <p>Data {{env('OPENKAB') == 'true' ? ucwords($sebutan_desa) : 'Pelanggan Layanan'}}</p>
                    </a>
                </li>

                <li class="nav-header">MASTER APLIKASI</li>
                <li class="nav-item">
                    <a href="/pbb" class="nav-link {{ ($active == 'pbb' ? 'active' : '') }}" target="_blank">
                        <i class="nav-icon fas fa-ellipsis-h"></i>
                        <p>Aplikasi PBB</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/opensid-api" class="nav-link {{ ($active == 'opensid-api' ? 'active' : '') }}" target="_blank">
                        <i class="nav-icon fas fa-ellipsis-h"></i>
                        <p>OpenSID API</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/opensid-premium" class="nav-link {{ ($active == 'openisd-premium' ? 'active' : '') }}" target="_blank">
                        <i class="nav-icon fas fa-ellipsis-h"></i>
                        <p>OpenSID Premium</p>
                    </a>
                </li>

                <!-- Pengaturan -->
                @if(env('OPENKAB') == 'true')
                    <li class="nav-header">PENGATURAN</li>
                    <li class="nav-item">
                        <a href="{{ route('pengguna.index') }}" class="nav-link {{ ($active == 'pengguna' ? 'active' : '') }}">
                            <i class="fa fa-user me-3"></i>
                            <p>Pengguna</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('aplikasi.index') }}" class="nav-link {{ ($active == 'aplikasi' ? 'active' : '') }}">
                            <i class="fa fa-cog me-3"></i>
                            <p>Aplikasi</p>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
