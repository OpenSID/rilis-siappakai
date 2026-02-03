<x-app-layout title="Cloudflare Rule Master">

    @section('breadcrumbs')
        <x-breadcrumbs navigations="Dasbor"
            active="Pengaturan {{ ucwords(str_replace('-', ' ', $table)) }}"
            link="{{ route('dasbor') }}"></x-breadcrumbs>
    @endsection

    @section('content')
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Data Cloudflare Rule Master</strong>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <!-- Tombol Tambah Data -->
                                <div>
                                    <a href="{{ route($table . '.create') }}"
                                        class="btn btn-success me-1"><i
                                            class="fa fa-plus-circle me-2"></i>Tambah</a>
                                    <button type="button" class="btn btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deployModal-{{ $table }}">
                                        <i class="fa fa-cloud-upload me-2"></i>Deploy Rules
                                    </button>
                                    <a href="{{ route('cloudflare-rule-master.history') }}"
                                        class="btn btn-secondary ms-1">
                                        <i class="fa fa-history me-2"></i>History
                                    </a>
                                </div>

                                <!-- Tombol Hapus Data Yang Dipilih -->
                                <button type="button"
                                    class="btn btn-sm btn-danger btn-hapus-data-dipilih"
                                    id="deleteAllBtn" data-toggle="modal"
                                    data-target="#hapusDataDipilih-{{ $table }}" disabled>
                                    Hapus data yang dipilih
                                </button>

                                <!-- Modal Hapus Data Yang Dipilih -->
                                @include('layouts.modals.delete-selected', [
                                    'table' => $table,
                                ])

                                <!-- Deploy Modal -->
                                <div class="modal fade" id="deployModal-{{ $table }}"
                                    tabindex="-1" role="dialog"
                                    aria-labelledby="deployModalLabel-{{ $table }}"
                                    aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deployModalLabel">Deploy
                                                    Rules to All Domains</h5>
                                                <button type="button" class="close"
                                                    data-bs-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('cloudflare-rule-master.deploy') }}"
                                                method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="mode">Select Deployment
                                                            Mode</label>
                                                        <select name="mode" id="mode"
                                                            class="form-control">
                                                            <option value="smart_sync">Smart Sync
                                                                (Recommended)</option>
                                                            <option value="append">Append Only</option>
                                                            <option value="full_replace">Full Replace
                                                                (Caution)</option>
                                                        </select>
                                                        <small class="form-text text-muted">
                                                            <strong>Smart Sync:</strong> Memperbarui
                                                            rule yang ada, membuat yang baru, dan
                                                            menghapus yang sudah tidak aktif di
                                                            master.<br>
                                                            <strong>Append:</strong> Hanya menambahkan
                                                            rule baru yang belum ada..<br>
                                                            <strong>Full Replace:</strong> Menghapus
                                                            rule lama yang dikelola sistem dan
                                                            menggantinya dengan rule master terbaru.
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Start
                                                        Deployment</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive mt-3">
                                <table id="datatable"
                                    class="table table-striped table-bordered datatable">
                                    <!-- Judul tabel -->
                                    <thead>
                                        <tr>
                                            <th class="text-end"><input type="checkbox" id="check-all">
                                            </th>
                                            <th class="text-center">No</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">Action</th>
                                            <th class="text-center">Expression</th>
                                            <th class="text-center">Priority</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>

                                    <!-- Isi data dalam tabel -->
                                    <tbody>
                                        @foreach ($rules as $index => $item)
                                            <tr id="sid{{ $item->id }}">
                                                <td class="text-center"><input type="checkbox"
                                                        name="ids" class="checkBoxClass"
                                                        value="{{ $item->id }}"></td>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $item->name }} <br> <small
                                                        class="text-muted">{{ $item->description }}</small>
                                                </td>
                                                <td><span
                                                        class="badge bg-info">{{ $item->action }}</span>
                                                </td>
                                                <td><code>{{ Str::limit($item->expression, 50) }}</code>
                                                </td>
                                                <td>{{ $item->priority }}</td>
                                                <td class="text-center">
                                                    @if ($item->is_enabled)
                                                        <span class="badge bg-success">Aktif</span>
                                                    @else
                                                        <span
                                                            class="badge bg-secondary">Non-Aktif</span>
                                                    @endif
                                                </td>

                                                <td class="text-center">
                                                    <!-- Tombol Ubah Data -->
                                                    <a href="{{ route($table . '.edit', encrypt($item->id)) }}"
                                                        class="btn btn-primary btn-sm">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>

                                                    <!-- Tombol Hapus Data -->
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#{{ $table }}-{{ $item->id }}">
                                                        <i class="fa fa-trash"></i>
                                                    </button>

                                                    <!-- Modal Hapus Data -->
                                                    @include('layouts.modals.delete', [
                                                        'table' => $table,
                                                        'data' => $item,
                                                    ])
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
