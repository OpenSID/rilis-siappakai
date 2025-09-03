<x-app-layout title="Tambah {{ $sebutankecamatan }}">

    @section('breadcrumbs')
        <x-breadcrumbs navigations="Data {{ $sebutankecamatan }}" active="Tambah {{ $sebutankecamatan }}" link="{{ route('pelanggan.index') }}"></x-breadcrumbs>
    @endsection

    @section('content')
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <a href="{{ route('opendk.index') }}" class="btn btn-outline-secondary btn-circle me-2">
                                <i class="fa fa-arrow-left"></i>
                            </a>

                            <strong class="card-title">Tambah {{ $sebutankecamatan }}</strong>
                        </div>

                        <div class="card-body">
                            <livewire:opendk.tambah-kecamatan :opendk="$opendk" :sebutankecamatan="$sebutankecamatan" :namakabupaten="$namakabupaten">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endsection

</x-app-layout>
