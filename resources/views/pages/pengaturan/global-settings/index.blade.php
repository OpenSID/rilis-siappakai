<x-app-layout title="Pengaturan Global">

    @section('breadcrumbs')
        <x-breadcrumbs navigations="Pengaturan" active="Pengaturan Global"
            link="{{ route('dasbor') }}"></x-breadcrumbs>
    @endsection

    @section('content')
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                {{-- Pengaturan Template Default --}}
                <div class="card card-outline card-warning collapsed-card">
                    <div class="card-header">
                        <strong class="card-title">
                            <i class="fa fa-cogs me-1"></i> Pengaturan Template Default global.json
                        </strong>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body" style="display: none;">
                        <p class="text-muted mb-3">
                            Atur nilai default yang akan digunakan saat men-generate file <code>global.json</code> untuk pelanggan baru.
                            Nilai ini akan menjadi template awal untuk semua pelanggan.
                        </p>
                        <form method="POST" action="{{ route('pengaturan-global.update-template') }}">
                            @csrf
                            @php
                                $groupedTemplate = collect($templateSettings)->groupBy('kategori');
                            @endphp

                            @foreach ($groupedTemplate as $kategori => $items)
                                <h6 class="text-uppercase text-muted mt-3 mb-2">
                                    <i class="fa fa-tag me-1"></i> {{ ucfirst($kategori) }}
                                </h6>
                                <hr class="mt-0">
                                @foreach ($items as $setting)
                                    <div class="form-group row">
                                        <label class="col-sm-3 col-form-label" for="template_{{ $setting['key'] }}">
                                            {{ $setting['judul'] }}
                                        </label>
                                        <div class="col-sm-9">
                                            <input
                                                type="{{ $setting['jenis'] === 'password' ? 'password' : 'text' }}"
                                                class="form-control"
                                                id="template_{{ $setting['key'] }}"
                                                name="template[{{ $setting['key'] }}]"
                                                value="{{ $setting['value'] }}"
                                                placeholder="{{ $setting['keterangan'] }}"
                                            >
                                            <small class="form-text text-muted">{{ $setting['keterangan'] }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach

                            <div class="text-right mt-3">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fa fa-save me-1"></i> Simpan Template Default
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Generate Semua Pelanggan --}}
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <strong class="card-title">
                            <i class="fa fa-globe me-1"></i> Generate Semua Pelanggan
                        </strong>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-0">
                                    Generate file <code>global.json</code> untuk <strong>semua pelanggan aktif</strong> sekaligus.
                                    File yang sudah ada akan diperbarui dengan template terbaru (nilai yang sudah diisi tetap dipertahankan).
                                </p>
                            </div>
                            <div class="ml-3">
                                <form id="formDeployAll" method="POST" action="{{ route('pengaturan-global.deploy-all') }}">
                                    @csrf
                                    <button type="button" class="btn btn-success btn-lg" id="btnDeployAll">
                                        <i class="fa fa-sync me-1"></i> Generate Semua Pelanggan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pilih Pelanggan --}}
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <strong class="card-title">
                            <i class="fa fa-map-marker-alt me-1"></i> Pilih Pelanggan
                        </strong>
                    </div>

                    <div class="card-body">
                        <form method="GET" action="{{ route('pengaturan-global.index') }}">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <div class="form-group mb-0">
                                        <label for="pelanggan_id" class="mb-1">
                                            Cari desa berdasarkan nama, kode desa, atau domain:
                                        </label>
                                        <select name="pelanggan_id" id="pelanggan_id" class="form-control select2 w-100">
                                            <option value="">-- Pilih Desa --</option>
                                            @foreach ($pelangganList as $item)
                                                <option value="{{ $item->id }}"
                                                    {{ ($pelanggan && $pelanggan->id == $item->id) ? 'selected' : '' }}>
                                                    {{ $item->nama_desa ?: '(tanpa nama)' }} — {{ $item->kode_desa ?: '-' }} | {{ $item->domain_opensid ?: '-' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fa fa-search me-1"></i> Tampilkan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @if ($pelanggan)
                    {{-- Info Pelanggan --}}
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">
                                <i class="fa fa-info-circle me-1"></i>
                                Info Pelanggan: {{ $pelanggan->nama_desa }}
                            </strong>
                            <div class="float-right">
                                @if ($hasFile)
                                    <span class="badge badge-success"><i class="fa fa-check me-1"></i>global.json tersedia</span>
                                @else
                                    <span class="badge badge-danger"><i class="fa fa-times me-1"></i>global.json belum ada</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Kode Desa:</strong> {{ $pelanggan->kode_desa }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Domain:</strong> {{ $pelanggan->domain_opensid ?? '-' }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Path:</strong>
                                    <code>{{ $pelanggan->folder_opensid }}/desa/config/global.json</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (!$hasFile)
                        {{-- Tombol Generate --}}
                        <div class="card">
                            <div class="card-body text-center py-4">
                                <p class="text-muted mb-3">File global.json belum tersedia untuk pelanggan ini.</p>
                                <form method="POST" action="{{ route('pengaturan-global.generate') }}">
                                    @csrf
                                    <input type="hidden" name="pelanggan_id" value="{{ $pelanggan->id }}">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fa fa-plus-circle me-2"></i> Generate File global.json
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        {{-- Tabel Pengaturan --}}
                        <div class="card">
                            <div class="card-header">
                                <strong class="card-title">
                                    <i class="fa fa-sliders-h me-1"></i> Daftar Pengaturan Global
                                </strong>
                                <div class="float-right">
                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#bulkEditModal">
                                        <i class="fa fa-edit me-1"></i> Edit Semua
                                    </button>
                                    <a href="{{ route('pengaturan-global.export', $pelanggan->id) }}" class="btn btn-sm btn-info">
                                        <i class="fa fa-download me-1"></i> Export JSON
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                {{-- Tab Kategori --}}
                                <ul class="nav nav-tabs mb-3" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#tab-semua">Semua</a>
                                    </li>
                                    @foreach (array_keys($groupedSettings) as $category)
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#tab-{{ Str::slug($category) }}">
                                                {{ ucfirst($category) }}
                                                <span class="badge badge-secondary">{{ count($groupedSettings[$category]) }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="tab-content">
                                    {{-- Tab Semua --}}
                                    <div class="tab-pane fade show active" id="tab-semua">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center" width="5%">No</th>
                                                        <th>Nama Pengaturan</th>
                                                        <th>Deskripsi</th>
                                                        <th>Tipe</th>
                                                        <th>Kategori</th>
                                                        <th>Nilai</th>
                                                        <th class="text-center" width="8%">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($settings as $index => $setting)
                                                        <tr>
                                                            <td class="text-center">{{ $index + 1 }}</td>
                                                            <td><strong>{{ $setting['judul'] }}</strong></td>
                                                            <td><small class="text-muted">{{ $setting['keterangan'] }}</small></td>
                                                            <td>
                                                                <span class="badge badge-info">{{ $setting['jenis'] }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-secondary">{{ ucfirst($setting['kategori']) }}</span>
                                                            </td>
                                                            <td>
                                                                @if ($setting['jenis'] === 'password' && $setting['value'])
                                                                    <code>••••••••</code>
                                                                @elseif ($setting['value'])
                                                                    <code>{{ Str::limit($setting['value'], 30) }}</code>
                                                                @else
                                                                    <span class="text-muted">(kosong)</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                <a href="{{ route('pengaturan-global.edit', [$pelanggan->id, $setting['key']]) }}"
                                                                   class="btn btn-primary btn-sm" title="Edit">
                                                                    <i class="fa fa-pencil-alt"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted">Tidak ada data pengaturan.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Tab Per Kategori --}}
                                    @foreach ($groupedSettings as $category => $categorySettings)
                                        <div class="tab-pane fade" id="tab-{{ Str::slug($category) }}">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center" width="5%">No</th>
                                                            <th>Nama Pengaturan</th>
                                                            <th>Deskripsi</th>
                                                            <th>Tipe</th>
                                                            <th>Nilai</th>
                                                            <th class="text-center" width="8%">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($categorySettings as $idx => $setting)
                                                            <tr>
                                                                <td class="text-center">{{ $idx + 1 }}</td>
                                                                <td><strong>{{ $setting['judul'] }}</strong></td>
                                                                <td><small class="text-muted">{{ $setting['keterangan'] }}</small></td>
                                                                <td><span class="badge badge-info">{{ $setting['jenis'] }}</span></td>
                                                                <td>
                                                                    @if ($setting['jenis'] === 'password' && $setting['value'])
                                                                        <code>••••••••</code>
                                                                    @elseif ($setting['value'])
                                                                        <code>{{ Str::limit($setting['value'], 30) }}</code>
                                                                    @else
                                                                        <span class="text-muted">(kosong)</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    <a href="{{ route('pengaturan-global.edit', [$pelanggan->id, $setting['key']]) }}"
                                                                       class="btn btn-primary btn-sm" title="Edit">
                                                                        <i class="fa fa-pencil-alt"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Statistik --}}
                        <div class="row">
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3>{{ count($settings) }}</h3>
                                        <p>Total Pengaturan</p>
                                    </div>
                                    <div class="icon"><i class="fa fa-sliders-h"></i></div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-purple">
                                    <div class="inner">
                                        <h3>{{ count($groupedSettings) }}</h3>
                                        <p>Kategori</p>
                                    </div>
                                    <div class="icon"><i class="fa fa-folder"></i></div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3>{{ collect($settings)->whereNotNull('value')->where('value', '!=', '')->count() }}</h3>
                                        <p>Terisi</p>
                                    </div>
                                    <div class="icon"><i class="fa fa-check-circle"></i></div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3>{{ collect($settings)->filter(fn($s) => empty($s['value']))->count() }}</h3>
                                        <p>Kosong</p>
                                    </div>
                                    <div class="icon"><i class="fa fa-exclamation-triangle"></i></div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Bulk Edit --}}
    @if ($pelanggan && !empty($settings))
        <div class="modal fade" id="bulkEditModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa fa-edit me-1"></i> Edit Pengaturan - {{ $pelanggan->nama_desa }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('pengaturan-global.bulk-update') }}">
                        @csrf
                        <input type="hidden" name="pelanggan_id" value="{{ $pelanggan->id }}">
                        <div class="modal-body">
                            @foreach ($groupedSettings as $category => $categorySettings)
                                <h6 class="text-uppercase text-muted mt-3 mb-2">
                                    <i class="fa fa-tag me-1"></i> {{ ucfirst($category) }}
                                </h6>
                                <hr class="mt-0">
                                @foreach ($categorySettings as $setting)
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label" for="settings_{{ $setting['key'] }}">
                                            {{ $setting['judul'] }}
                                        </label>
                                        <div class="col-sm-8">
                                            <input
                                                type="{{ $setting['jenis'] === 'password' ? 'password' : 'text' }}"
                                                class="form-control"
                                                id="settings_{{ $setting['key'] }}"
                                                name="settings[{{ $setting['key'] }}]"
                                                value="{{ $setting['value'] }}"
                                                placeholder="{{ $setting['keterangan'] }}"
                                            >
                                            <small class="form-text text-muted">{{ $setting['keterangan'] }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @endsection

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Inisialisasi Select2
            if ($.fn.select2) {
                $('#pelanggan_id').select2({
                    placeholder: '-- Pilih Desa --',
                    allowClear: true
                });
            }

            // Konfirmasi Generate Semua Pelanggan
            $('#btnDeployAll').on('click', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Generate Semua Pelanggan?',
                    html: 'Proses ini akan men-generate file <code>global.json</code> untuk <strong>semua pelanggan aktif</strong>.<br><br>' +
                          '<small class="text-muted">File yang sudah ada akan diperbarui dengan template terbaru. Nilai yang sudah diisi tetap dipertahankan.</small>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fa fa-sync"></i> Ya, Generate Semua',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Tampilkan loading
                        Swal.fire({
                            title: 'Memproses...',
                            html: 'Sedang men-generate file global.json untuk semua pelanggan.<br>Mohon tunggu...',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $('#formDeployAll').submit();
                    }
                });
            });
        });
    </script>
    @endpush

</x-app-layout>
