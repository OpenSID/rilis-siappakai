<x-app-layout title="Tambah {{ $sebutandesa }}">

    @section('breadcrumbs')
        <x-breadcrumbs navigations="Data Desa" active="Tambah {{ $sebutandesa }}"></x-breadcrumbs>
    @endsection

    @section('content')
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <a href="{{ route('pelanggan.index') }}" class="btn btn-outline-secondary btn-circle me-2">
                                <i class="fa fa-arrow-left"></i>
                            </a>

                            <strong class="card-title">Tambah {{ $sebutandesa }}</strong>
                        </div>

                        <div class="card-body">
                            <livewire:pelanggan.tambah-desa :pelanggan="$pelanggan" :sebutandesa="$sebutandesa">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endsection

</x-app-layout>
