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
                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-info-circle"></i> Cara Menggunakan Cron Expression</h5>
                            <p>Cron expressions digunakan untuk menjadwalkan pekerjaan. Mereka terdiri dari 5 bidang yang
                                dipisahkan oleh spasi:</p>
                            <ul>
                                <li><strong>Menit</strong> (0 - 59)</li>
                                <li><strong>Jam</strong> (0 - 23)</li>
                                <li><strong>Hari dalam Bulan</strong> (1 - 31)</li>
                                <li><strong>Bulan</strong> (1 - 12)</li>
                                <li><strong>Hari dalam Minggu</strong> (0 - 6) (Minggu hingga Sabtu)</li>
                            </ul>
                            <p>Contoh:</p>
                            <ul>
                                <li><code>* * * * *</code> - Setiap menit</li>
                                <li><code>0 * * * *</code> - Setiap jam</li>
                                <li><code>0 0 * * *</code> - Setiap hari pada tengah malam</li>
                                <li><code>0 0 * * 0</code> - Setiap Minggu pada tengah malam</li>
                            </ul>
                            <p>Untuk informasi lebih lanjut, kunjungi situs <a href="https://crontab.guru/"
                                    target="_blank" rel="noopener noreferrer" style="color: blue;">Crontab Guru</a>.</p>
                        </div>
                        <div class="d-flex justify-content-between">
                            <!-- Tombol Tambah Data -->
                            <a href="{{ route($table.'.create') }}" class="btn btn-success"><i class="fa fa-plus-circle me-2"></i>Tambah</a>

                            <!-- Tombol Hapus Data Yang Dipilih -->
                            <button type="button" class="btn btn-sm btn-danger btn-hapus-data-dipilih" id="deleteAllBtn" data-toggle="modal" data-target="#hapusDataDipilih-{{ $table }}" disabled>
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
                                        <th class="text-center" style="width: 30%">Pekerjaan</th>
                                        <th class="text-center" style="width: 20%">Perintah</th>
                                        <th class="text-center">Cron Expression</th>
                                        <th class="text-center">Riwayat</th>
                                        <th class="text-center">Terakhir Dijalankan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>

                                <!-- Isi data dalam tabel -->
                                <tbody>
                                    @foreach($jadwals as $index => $item)
                                        <tr id="sid{{ $item->id }}">
                                            <td class="text-center"><input type="checkbox" name="ids" class="checkBoxClass" value="{{ $item->id }}"></td>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>{{ $item->keterangan }}</td>
                                            <td>{{ $item->command }}</td>
                                            <td>{{ $item->cron_expression }}</td>
                                            <td>{{ $item->log }}</td>
                                            <td>{{ $item->last_run_at }}</td>

                                            <td class="text-center">
                                                <!-- Tombol Ubah Data -->
                                                <a href="{{ route($table.'.edit', encrypt($item->id)) }}" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-pencil"></i>
                                                </a>

                                                <!-- Tombol Hapus Data -->
                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#{{ $table }}-{{ $item->id }}">
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
