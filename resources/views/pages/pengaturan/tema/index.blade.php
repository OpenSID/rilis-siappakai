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
                            <!-- Tombol Tambah Data -->
                            <a href="{{ route($table.'.create') }}" class="btn btn-success"><i
                                    class="fa fa-plus-circle me-2"></i>Tambah</a>

                            <!-- Tombol Hapus Data Yang Dipilih -->
                            <button type="button" class="btn btn-sm btn-danger btn-hapus-data-dipilih" id="deleteAllBtn"
                                data-toggle="modal" data-target="#hapusDataDipilih-{{ $table }}" disabled>
                                Hapus data yang dipilih
                            </button>

                            <!-- Modal Hapus Data Yang Dipilih -->
                            @include('layouts.modals.delete-selected', ['table' => $table])
                        </div>
                        <div class="table-responsive mt-3">
                            <table id="datatable" class="table table-striped table-bordered datatable">
                                <!-- Judul tabel -->
                                <thead>
                                    <tr>
                                        <th class="text-end"><input type="checkbox" id="check-all"></th>
                                        <th class="text-center">No</th>
                                        <th class="text-center">Username</th>
                                        <th class="text-center">Tema</th>
                                        <th class="text-center">Repo</th>
                                        <th class="text-center">Branch</th>
                                        <th class="text-center">Jenis Tema</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>

                                <!-- Isi data dalam tabel -->
                                <tbody>
                                    @forelse($temas as $index => $item)
                                    <tr id="sid{{ $item->id }}">
                                        <td class="text-center"><input type="checkbox" name="ids" class="checkBoxClass"
                                                value="{{ $item->id }}"></td>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $item->username }}</td>
                                        <td>{{ $item->tema }}</td>
                                        <td>{{ $item->repo }}</td>
                                        <td>{{ $item->branch }}</td>
                                        <td>{{ $item->jenis_tema == 'tema-pro' ? 'Tema Pro' : 'Tema Gratis'}}</td>

                                        <td class="text-center">
                                            <!-- Tombol Ubah Data -->
                                            <a href="{{ route($table.'.edit', encrypt($item->id)) }}"
                                                class="btn btn-primary btn-sm">
                                                <i class="fa fa-pencil"></i>
                                            </a>

                                            <!-- Tombol Hapus Data -->
                                            <button type="button" class="btn btn-sm btn-danger" data-toggle="modal"
                                                data-target="#{{ $table }}-{{ $item->id }}">
                                                <i class="fa fa-trash"></i>
                                            </button>

                                            <!-- Modal Hapus Data -->
                                            @include('layouts.modals.delete', ['table' => $table , 'data' => $item])
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7">Tidak ada tema</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @push('scripts')
    <!--  Datatables -->
    @include('layouts.includes._scripts-datatable')

    <!-- Hapus Beberapa Data -->
    @include('layouts.includes._scripts-bulk', ['table' => $table])
    @endpush

</x-app-layout>
