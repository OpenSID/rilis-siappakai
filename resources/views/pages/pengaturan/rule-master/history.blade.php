<x-app-layout title="Deployment History">

    @section('breadcrumbs')
        <x-breadcrumbs navigations="Pengaturan {{ ucwords(str_replace('-', ' ', $table)) }}"
            active="Deployment History" link="{{ route($table . '.index') }}"></x-breadcrumbs>
    @endsection

    @section('content')
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Deployment History</strong>
                            <a href="{{ route('cloudflare-rule-master.index') }}"
                                class="btn btn-secondary btn-sm float-end">Back to Rules</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="datatable"
                                    class="table table-striped table-bordered datatable">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Date</th>
                                            <th class="text-center">Batch ID</th>
                                            <th class="text-center">Mode</th>
                                            <th class="text-center">Total Zona</th>
                                            <th class="text-center">Success</th>
                                            <th class="text-center">Failed</th>
                                            <th class="text-center">Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($histories as $history)
                                            <tr>
                                                <td class="text-center">
                                                    {{ $history->created_at->format('Y-m-d H:i:s') }}
                                                </td>
                                                <td><code>{{ Str::limit($history->batch_id, 8) }}</code>
                                                </td>
                                                <td><span
                                                        class="badge bg-info">{{ $history->mode }}</span>
                                                </td>
                                                <td class="text-center">{{ $history->total_domains }}
                                                </td>
                                                <td class="text-center text-success">
                                                    {{ $history->success_count }}</td>
                                                <td class="text-center text-danger">
                                                    {{ $history->fail_count }}</td>
                                                <td class="text-center">
                                                    @if ($history->status == 'running')
                                                        <span class="badge bg-warning">Running</span>
                                                    @elseif($history->status == 'completed')
                                                        <span class="badge bg-success">Completed</span>
                                                    @else
                                                        <span
                                                            class="badge bg-secondary">{{ $history->status }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('cloudflare-rule-master.history.show', $history->batch_id) }}"
                                                        class="btn btn-sm btn-primary">
                                                        View Details
                                                    </a>
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
        <script type="text/javascript">
            // Wait for Vite bundle to load DataTables
            (function initDataTable() {
                if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
                    $(document).ready(function () {
                        if ($('#datatable').length && !$.fn.DataTable.isDataTable('#datatable')) {
                            $('#datatable').DataTable({
                                "order": [[ 0, "desc" ]] // Urutkan berdasarkan kolom Date (index 0) secara Descending (Terbaru diatas)
                            });
                        }
                    });
                } else {
                    // DataTables not ready yet, wait a bit
                    setTimeout(initDataTable, 100);
                }
            })();
        </script>
    @endpush
</x-app-layout>
