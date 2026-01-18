<x-app-layout title="Sinkronisasi Domain Cloudflare">

@section('breadcrumbs')
    <x-breadcrumbs navigations="Dasbor" active="Sinkronisasi Domain Cloudflare" link="{{ route('dasbor') }}"></x-breadcrumbs>
@endsection

@section('content')
    <div class="animated fadeIn">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">DNS Valid</h6>
                                <h3 class="mb-0" id="stat-ok">-</h3>
                            </div>
                            <i class="fa fa-check-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">DNS Tidak Ditemukan</h6>
                                <h3 class="mb-0" id="stat-missing">-</h3>
                            </div>
                            <i class="fa fa-exclamation-triangle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">IP Tidak Sesuai</h6>
                                <h3 class="mb-0" id="stat-mismatch">-</h3>
                            </div>
                            <i class="fa fa-times-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Belum Disinkronkan</h6>
                                <h3 class="mb-0" id="stat-not-synced">-</h3>
                            </div>
                            <i class="fa fa-question-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">Status Sinkronisasi Domain Pelanggan</strong>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <!-- Tombol Sync All -->
                            <button type="button" class="btn btn-primary" id="syncAllBtn">
                                <i class="fa fa-sync me-2"></i>Sinkronkan Semua Domain
                            </button>

                            <!-- Filter Status -->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm filter-status active" data-status="all">Semua</button>
                                <button type="button" class="btn btn-outline-success btn-sm filter-status" data-status="Valid">Valid</button>
                                <button type="button" class="btn btn-outline-warning btn-sm filter-status" data-status="Missing">Missing</button>
                                <button type="button" class="btn btn-outline-danger btn-sm filter-status" data-status="Mismatch">Mismatch</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm filter-status" data-status="Not Synced">Not Synced</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="datatable" class="table table-striped table-bordered datatable">
                                <thead>
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Nama Desa</th>
                                        <th>Domain</th>
                                        <th class="text-center">Jenis</th>
                                        <th>Zone Induk</th>
                                        <th class="text-center">Status DNS</th>
                                        <th>IP Saat Ini</th>
                                        <th class="text-center">Proxied</th>
                                        <th class="text-center">Terakhir Sync</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($domains as $index => $domain)
                                        <tr data-status="{{ $domain->dns_status }}">
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>{{ $domain->pelanggan->nama_desa ?? '-' }}</td>
                                            <td>
                                                <strong>{{ $domain->domain }}</strong>
                                                @if($domain->last_error)
                                                    <br><small class="text-danger">{{ $domain->last_error }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($domain->domain_type === 'zone')
                                                    <span class="badge bg-primary">Zone</span>
                                                @elseif($domain->domain_type === 'subdomain')
                                                    <span class="badge bg-info">Subdomain</span>
                                                @else
                                                    <span class="badge bg-secondary">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $domain->zone_name ?? '-' }}</td>
                                            <td class="text-center">
                                                @switch($domain->dns_status)
                                                    @case('OK')
                                                        <span class="badge bg-success">
                                                            <i class="fa fa-check-circle me-1"></i>Valid
                                                        </span>
                                                        @break
                                                    @case('MISSING')
                                                        <span class="badge bg-warning">
                                                            <i class="fa fa-exclamation-triangle me-1"></i>Missing
                                                        </span>
                                                        @break
                                                    @case('IP_MISMATCH')
                                                        <span class="badge bg-danger">
                                                            <i class="fa fa-times-circle me-1"></i>IP Mismatch
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">
                                                            <i class="fa fa-question-circle me-1"></i>Not Synced
                                                        </span>
                                                @endswitch
                                            </td>
                                            <td>
                                                {{ $domain->current_ip ?? '-' }}
                                                @if($domain->expected_ip && $domain->current_ip !== $domain->expected_ip)
                                                    <br><small class="text-muted">Expected: {{ $domain->expected_ip }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($domain->is_proxied)
                                                    <span class="badge bg-info">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($domain->last_synced_at)
                                                    <small>{{ $domain->last_synced_at->diffForHumans() }}</small>
                                                @else
                                                    <small class="text-muted">-</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" 
                                                        class="btn btn-sm btn-primary sync-one-btn" 
                                                        data-pelanggan-id="{{ $domain->pelanggan_id }}"
                                                        data-domain="{{ $domain->domain }}"
                                                        title="Sync ulang domain ini">
                                                    <i class="fa fa-sync"></i>
                                                </button>
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
    <!-- Datatables -->
    @include('layouts.includes._scripts-datatable')

    <script>
        $(document).ready(function() {
            // Load statistics
            loadStatistics();

            // Sync All Button
            $('#syncAllBtn').on('click', function() {
                const btn = $(this);
                const originalHtml = btn.html();
                
                Swal.fire({
                    title: 'Konfirmasi Sinkronisasi',
                    text: 'Apakah Anda yakin ingin menyinkronkan semua domain? Proses ini mungkin memakan waktu beberapa menit.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Sinkronkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true);
                        btn.html('<i class="fa fa-spinner fa-spin me-2"></i>Syncing...');

                        $.ajax({
                            url: '{{ route("domain-sync.syncAll") }}',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: response.message,
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: response.message,
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr) {
                                const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat sinkronisasi';
                                Swal.fire({
                                    title: 'Error!',
                                    text: message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            },
                            complete: function() {
                                btn.prop('disabled', false);
                                btn.html(originalHtml);
                            }
                        });
                    }
                });
            });

            // Sync One Button
            $('.sync-one-btn').on('click', function() {
                const btn = $(this);
                const pelangganId = btn.data('pelanggan-id');
                const domain = btn.data('domain');
                const originalHtml = btn.html();

                btn.prop('disabled', true);
                btn.html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    url: '{{ route("domain-sync.syncOne") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    data: {
                        pelanggan_id: pelangganId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                // text: 'Domain ' + domain + ' berhasil disinkronkan',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Terjadi kesalahan';
                        Swal.fire({
                            title: 'Error!',
                            text: message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                        btn.html(originalHtml);
                    }
                });
            });

            // Filter by status
            $('.filter-status').on('click', function() {
                const status = $(this).data('status');
                const table = $('#datatable').DataTable();

                $('.filter-status').removeClass('active');
                $(this).addClass('active');

                if (status === 'all') {
                    table.column(5).search('').draw();
                } else {
                    table.column(5).search(status).draw();
                }
            });

            // Load statistics
            function loadStatistics() {
                $.ajax({
                    url: '{{ route("domain-sync.statistics") }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#stat-ok').text(response.data.ok);
                            $('#stat-missing').text(response.data.missing);
                            $('#stat-mismatch').text(response.data.ip_mismatch);
                            $('#stat-not-synced').text(response.data.not_synced);
                        }
                    }
                });
            }
        });
    </script>
@endpush

</x-app-layout>
