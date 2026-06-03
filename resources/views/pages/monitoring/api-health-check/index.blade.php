<x-app-layout title="API Health Check">

    @section('breadcrumbs')
        <x-breadcrumbs navigations="Monitoring" active="API Health Check"
            link="{{ route('monitoring.api-health-check.index') }}"></x-breadcrumbs>
    @endsection

    @section('content')
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">

                {{-- Header + Button --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-heartbeat text-danger"></i> API Health Check</h5>
                    <button class="btn btn-primary" id="btn-check-all">
                        <i class="fas fa-sync-alt"></i> Check Semua
                    </button>
                </div>

                {{-- Progress Bar (hidden by default) --}}
                <div class="card card-outline card-info d-none" id="progress-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-tasks"></i> Proses Pengecekan</h3>
                    </div>
                    <div class="card-body">
                        <div class="progress progress-sm active">
                            <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated"
                                 id="progress-bar" role="progressbar" style="width: 0%">
                                <span id="progress-text">0%</span>
                            </div>
                        </div>
                        <p class="text-muted mt-2 mb-0">
                            <small>
                                Sedang memeriksa: <span id="progress-current">-</span>
                                (<span id="progress-count">0</span> / <span id="progress-total">0</span>)
                            </small>
                        </p>
                    </div>
                </div>

                {{-- Summary Cards --}}
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3 id="summary-total">{{ $summary['total'] }}</h3>
                                <p>Total Pelanggan</p>
                            </div>
                            <div class="icon"><i class="fas fa-server"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3 id="summary-success">{{ $summary['success'] }}</h3>
                                <p>API Sukses</p>
                            </div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3 id="summary-error">{{ $summary['error'] }}</h3>
                                <p>API Error</p>
                            </div>
                            <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3 id="summary-rate">{{ $summary['success_percentage'] }}%</h3>
                                <p>Success Rate</p>
                            </div>
                            <div class="icon"><i class="fas fa-chart-pie"></i></div>
                        </div>
                    </div>
                </div>

                @if ($summary['last_checked'])
                    <div class="alert alert-light border text-muted py-2 px-3">
                        <i class="fas fa-clock"></i>
                        Terakhir dicek: <strong>{{ \Carbon\Carbon::parse($summary['last_checked'])->format('d M Y H:i:s') }}</strong>
                        (<em>{{ \Carbon\Carbon::parse($summary['last_checked'])->diffForHumans() }}</em>)
                    </div>
                @endif

                {{-- Filter Tabs + Table --}}
                <div class="card">
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs" id="filter-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" href="javascript:void(0)" data-filter="all">
                                    <i class="fas fa-list"></i> Semua
                                    <span class="badge badge-secondary ml-1" id="badge-all">{{ $summary['total'] }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="javascript:void(0)" data-filter="error">
                                    <i class="fas fa-times-circle text-danger"></i> Error
                                    <span class="badge badge-danger ml-1" id="badge-error">{{ $summary['error'] }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="javascript:void(0)" data-filter="success">
                                    <i class="fas fa-check-circle text-success"></i> Sukses
                                    <span class="badge badge-success ml-1" id="badge-success">{{ $summary['success'] }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="javascript:void(0)" data-filter="belum">
                                    <i class="fas fa-question-circle text-secondary"></i> Belum Dicek
                                    <span class="badge badge-secondary ml-1" id="badge-belum">{{ $summary['belum_dicek'] }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-striped mb-0" id="health-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px">No</th>
                                    <th>Kode Desa</th>
                                    <th>Nama Desa</th>
                                    <th>Domain</th>
                                    <th>Status</th>
                                    <th>HTTP</th>
                                    <th>Keterangan</th>
                                    <th>Terakhir Dicek</th>
                                    <th style="width: 80px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($results as $index => $row)
                                    @php
                                        $status = $row->health_status ?? 'belum';
                                        // Jika nama_desa berisi kode_desa (angka + titik), gunakan nama dari domain
                                        $namaDesa = $row->nama_desa;
                                        if (preg_match('/^\d{2}\.\d{2}\.\d{2}\.\d{4}$/', $namaDesa)) {
                                            // Ambil nama dari domain: "tanjungmasmulya-mesuji.berdesa.id" → "Tanjungmasmulya Mesuji"
                                            $namaDesa = ucwords(str_replace(['-', '.berdesa.id', '.desa.id'], [' ', '', ''], $row->domain_opensid));
                                        }
                                    @endphp
                                    <tr data-id="{{ $row->id }}" data-status="{{ $status }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td><code>{{ $row->kode_desa }}</code></td>
                                        <td>{{ $namaDesa }}</td>
                                        <td>
                                            <a href="https://{{ $row->domain_opensid }}/api/healthcheck"
                                               target="_blank" class="text-primary">
                                                {{ $row->domain_opensid }}
                                                <i class="fas fa-external-link-alt fa-xs"></i>
                                            </a>
                                        </td>
                                        <td class="cell-status">
                                            @if ($status === 'success')
                                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Sukses</span>
                                            @elseif ($status === 'error')
                                                <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Error</span>
                                            @else
                                                <span class="badge badge-secondary"><i class="fas fa-minus-circle"></i> Belum Dicek</span>
                                            @endif
                                        </td>
                                        <td class="cell-http">
                                            @if ($row->http_status)
                                                <span class="badge badge-info">{{ $row->http_status }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="cell-message">
                                            <small>{{ $row->message ?? '-' }}</small>
                                        </td>
                                        <td class="cell-time">
                                            @if ($row->checked_at)
                                                <small class="text-muted" title="{{ \Carbon\Carbon::parse($row->checked_at)->format('d M Y H:i:s') }}">
                                                    {{ \Carbon\Carbon::parse($row->checked_at)->format('d/m/Y H:i') }}
                                                </small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-xs btn-info btn-check-single"
                                                    data-id="{{ $row->id }}"
                                                    title="Check ulang">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Tidak ada data pelanggan dengan domain aktif.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endsection

    @push('scripts')
    <script>
    $(document).ready(function () {
        var csrfToken = "{{ csrf_token() }}";
        var isChecking = false;

        // =============================================
        // Filter tabs — click handler (no BS tab dependency)
        // =============================================
        $('#filter-tabs').on('click', 'a.nav-link', function (e) {
            e.preventDefault();

            // Toggle active state
            $('#filter-tabs a.nav-link').removeClass('active');
            $(this).addClass('active');

            var filter = $(this).data('filter');
            applyFilter(filter);
        });

        function applyFilter(filter) {
            $('#health-table tbody tr[data-id]').each(function () {
                var status = $(this).attr('data-status');
                if (filter === 'all') {
                    $(this).show();
                } else {
                    $(this).toggle(status === filter);
                }
            });
        }

        // =============================================
        // Check single pelanggan (AJAX)
        // =============================================
        $(document).on('click', '.btn-check-single', function () {
            var btn = $(this);
            var id  = btn.data('id');
            var row = btn.closest('tr');

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: '{{ route("monitoring.api-health-check.check-single", ":id") }}'.replace(':id', id),
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                timeout: 15000,
                success: function (res) {
                    updateRow(row, res);
                    btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i>');
                    refreshSummary();
                },
                error: function (xhr) {
                    var msg = 'Request gagal';
                    if (xhr.status === 0) msg = 'Connection timeout';
                    else if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    updateRow(row, { status: 'error', http_status: null, message: msg, checked_at: new Date().toISOString() });
                    btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i>');
                }
            });
        });

        // =============================================
        // Check ALL — sequential AJAX (satu per satu)
        // =============================================
        $('#btn-check-all').on('click', function () {
            if (isChecking) return;

            Swal.fire({
                title: 'Check Semua API?',
                html: 'Proses ini akan memeriksa API health check untuk <strong>semua pelanggan</strong> satu per satu.<br><small class="text-muted">Proses berjalan di halaman ini, jangan tutup tab.</small>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-play"></i> Mulai Check',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then(function (result) {
                if (result.isConfirmed) {
                    startCheckAll();
                }
            });
        });

        function startCheckAll() {
            isChecking = true;
            $('#btn-check-all').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sedang Berjalan...');
            $('#progress-card').removeClass('d-none');

            // Ambil daftar pelanggan ID dari server
            $.get('{{ route("monitoring.api-health-check.pelanggan-ids") }}', function (data) {
                var ids = data.ids || [];
                var total = ids.length;
                var done = 0;
                var successCount = 0;
                var errorCount = 0;

                $('#progress-total').text(total);

                function checkNext(index) {
                    if (index >= total) {
                        finishCheckAll(successCount, errorCount, total);
                        return;
                    }

                    var id  = ids[index];
                    var row = $('tr[data-id="' + id + '"]');
                    var domain = row.find('td:eq(3) a').text().trim() || 'ID: ' + id;

                    $('#progress-current').text(domain);
                    $('#progress-count').text(index + 1);

                    // Set row loading state
                    row.find('.cell-status').html('<span class="badge badge-warning"><i class="fas fa-spinner fa-spin"></i> Checking...</span>');

                    $.ajax({
                        url: '/monitoring/api-health-check/' + id + '/check',
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        timeout: 15000,
                        success: function (res) {
                            updateRow(row, res);
                            if (res.status === 'success') successCount++;
                            else errorCount++;
                        },
                        error: function (xhr) {
                            var msg = 'Timeout / gagal';
                            if (xhr.status === 0) msg = 'Connection timeout';
                            else if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                            updateRow(row, { status: 'error', http_status: null, message: msg, checked_at: new Date().toISOString() });
                            errorCount++;
                        },
                        complete: function () {
                            done++;
                            var pct = Math.round((done / total) * 100);
                            $('#progress-bar').css('width', pct + '%');
                            $('#progress-text').text(pct + '%');

                            checkNext(index + 1);
                        }
                    });
                }

                checkNext(0);
            });
        }

        function finishCheckAll(successCount, errorCount, total) {
            isChecking = false;
            $('#btn-check-all').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Check Semua');
            $('#progress-current').text('Selesai!');

            refreshSummary();

            Swal.fire({
                title: 'Selesai!',
                html: '<div class="text-left">' +
                      '<p>Total: <strong>' + total + '</strong></p>' +
                      '<p><span class="badge badge-success">Sukses: ' + successCount + '</span> ' +
                      '<span class="badge badge-danger">Error: ' + errorCount + '</span></p>' +
                      '</div>',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        }

        // =============================================
        // Helper: update row cells
        // =============================================
        function updateRow(row, data) {
            // Update status badge — gunakan attr() saja agar konsisten dengan filter
            if (data.status === 'success') {
                row.find('.cell-status').html('<span class="badge badge-success"><i class="fas fa-check-circle"></i> Sukses</span>');
                row.attr('data-status', 'success');
            } else {
                row.find('.cell-status').html('<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Error</span>');
                row.attr('data-status', 'error');
            }

            // Update HTTP status
            if (data.http_status) {
                row.find('.cell-http').html('<span class="badge badge-info">' + data.http_status + '</span>');
            } else {
                row.find('.cell-http').html('<span class="text-muted">-</span>');
            }

            // Update keterangan/message (use text() to prevent XSS)
            row.find('.cell-message').html($('<small>').text(data.message || '-'));

            // Update terakhir dicek
            if (data.checked_at) {
                var d = new Date(data.checked_at);
                var pad = function(n) { return n < 10 ? '0' + n : n; };
                var formatted = pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + '/' + d.getFullYear() + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
                var fullDate = pad(d.getDate()) + ' ' + ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'][d.getMonth()] + ' ' + d.getFullYear() + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
                row.find('.cell-time').html('<small class="text-muted" title="' + fullDate + '">' + formatted + '</small>');
            }

            // Update tab badges
            updateTabBadges();
        }

        // =============================================
        // Helper: update tab badge counts + re-apply active filter
        // =============================================
        function updateTabBadges() {
            var totalRows = $('#health-table tbody tr[data-id]').length;
            var successCount = $('#health-table tbody tr[data-id][data-status="success"]').length;
            var errorCount = $('#health-table tbody tr[data-id][data-status="error"]').length;
            var belumCount = totalRows - successCount - errorCount;

            $('#badge-all').text(totalRows);
            $('#badge-error').text(errorCount);
            $('#badge-success').text(successCount);
            $('#badge-belum').text(belumCount);

            // Re-apply active filter
            var activeFilter = $('#filter-tabs a.nav-link.active').data('filter');
            if (activeFilter) {
                applyFilter(activeFilter);
            }
        }

        // =============================================
        // Helper: refresh summary cards
        // =============================================
        function refreshSummary() {
            $.get('{{ route("monitoring.api-health-check.summary") }}', function (s) {
                $('#summary-total').text(s.total);
                $('#summary-success').text(s.success);
                $('#summary-error').text(s.error);
                $('#summary-rate').text(s.success_percentage + '%');

                // Update tab badges from server data too
                updateTabBadges();
            });
        }
    });
    </script>
    @endpush

</x-app-layout>
