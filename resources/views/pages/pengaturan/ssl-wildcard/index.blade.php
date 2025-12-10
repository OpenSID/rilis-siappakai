<x-app-layout title="Pengaturan {{ ucwords(str_replace('-', ' ', $table )) }}">

@section('breadcrumbs')
    <x-breadcrumbs navigations="Dasbor" active="Pengaturan {{ ucwords(str_replace('-', ' ', $table )) }}" link="{{ route('dasbor') }}"></x-breadcrumbs>
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
                            <a href="{{ route($table.'.create') }}" class="btn btn-success"><i class="fa fa-plus-circle me-2"></i>Tambah</a>

                            <!-- Tombol Hapus Data Yang Dipilih -->
                            <button type="button" class="btn btn-sm btn-danger btn-hapus-data-dipilih" id="deleteAllBtn" data-bs-toggle="modal" data-bs-target="#hapusDataDipilih-{{ $table }}" disabled>
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
                                        <th class="text-center">Nama Sertifikat</th>
                                        <th class="text-center">Domain</th>
                                        <th class="text-center">Tanggal Akhir</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>

                                <!-- Isi data dalam tabel -->
                                <tbody>
                                    @foreach($certificates as $index => $item)
                                        <tr id="sid{{ $item->id }}">
                                            <td class="text-center"><input type="checkbox" name="ids" class="checkBoxClass" value="{{ $item->id }}"></td>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>{{ $item->nama_sertifikat }}</td>
                                            <td>{{ $item->domain }}</td>
                                            <td class="text-center" style="vertical-align : middle;">
                                                {{ !is_null($item->tgl_akhir) ? Carbon\Carbon::createFromFormat('Y-m-d', $item->tgl_akhir)->isoFormat('D MMMM Y') : '' }}
                                                {{-- @if (near_expired($item->getRemainingAttribute())) --}}
                                                <span class="badge badge-warning">{{$item->getRemainingAttribute()}} hari lagi</span>
                                                {{-- @endif --}}
                                            </td>
                                            <td class="text-start" style="vertical-align : middle;">
                                                @if($item->status == 'tidak aktif')
                                                    <span class="badge badge-info">Tidak Aktif</span>
                                                @elseif($item->status == 'akan berakhir')
                                                    <span class="badge badge-danger">Akan Berakhir</span>
                                                @elseif($item->status == 'aktif')
                                                    <span class="badge badge-success">Aktif</span>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                <!-- Tombol Ubah Data -->
                                                <a href="{{ route($table.'.edit', encrypt($item->id)) }}" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-pencil"></i>
                                                </a>

                                                <!-- Tombol Show -->
                                                <a href="{{ route($table.'.show', encrypt($item->id)) }}" class="btn btn-info btn-sm">
                                                    <i class="fa fa-eye"></i>
                                                </a>

                                                <!-- Tombol Hapus Data -->
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#{{ $table }}-{{ $item->id }}">
                                                   <i class="fa fa-trash"></i>
                                                </button>

                                                <!-- Modal Hapus Data -->
                                                @include('layouts.modals.delete', ['table' => $table , 'data' => $item])
                                            </td>
                                        </tr>
                                    @endforeach
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
