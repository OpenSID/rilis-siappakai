<x-app-layout title="Ubah Pengguna">

    @section('breadcrumbs')
        <x-breadcrumbs navigations="Pengaturan Pengguna" active="Ubah Pengguna" link="{{ route('pengguna.index') }}"></x-breadcrumbs>
    @endsection

    @section('content')
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <a href="{{ route('pengguna.index') }}" class="btn btn-outline-secondary btn-circle me-2">
                                <i class="fa fa-arrow-left"></i>
                            </a>

                            <strong class="card-title">Ubah Pengguna</strong>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('pengguna.update', encrypt($pengguna->id)) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                @include('pages.pengaturan.pengguna._form-control')
                            </form>

                            <span style="float: right; margin-top: -40px">
                                <!-- Tombol Hapus Data -->
                                <button type="button" class="btn btn-danger btn-circle" data-toggle="modal" data-target="#{{ $table }}-{{ $pengguna->id }}">
                                    <i class="fa fa-trash"></i> Hapus
                                </button>

                                <!-- Modal Hapus Data -->
                                @include('layouts.modals.delete', ['table' => $table , 'data' => $pengguna])
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endsection

</x-app-layout>
