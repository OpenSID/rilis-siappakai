<x-app-layout title="Pengaturan {{ ucwords(str_replace('-', ' ', $table )) }}">

    @section('breadcrumbs')
    <x-breadcrumbs navigations="Dasbor" active="Pengaturan {{ ucwords(str_replace('-', ' ', $table )) }}"
        link="{{ route('dasbor') }}"></x-breadcrumbs>
    @endsection

    @section('content')
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">Data {{ ucwords(str_replace('-', ' ', $table )) }}</strong>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="nav-tabs-custom w-100">
                                <ul class="nav nav-tabs">
                                    <li class="nav-item"><a class="nav-link {!! $act_tab == 1 ? 'active' : '' !!}" href="{{ route('modul.index') }}">Paket Tersedia</a></li>
                                    <li class="nav-item"><a class="nav-link {!! $act_tab == 2 ? 'active' : '' !!}" href="{{ route('modul.show', 1) }}">Paket Terpasang</a></li>
                                </ul>
                                <div class="tab-content p-2">
                                    @include($content)
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection

</x-app-layout>
