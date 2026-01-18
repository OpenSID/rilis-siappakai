<x-app-layout title="Deployment Details">

@section('breadcrumbs')
    {{-- <x-breadcrumbs navigations="Cloudflare" active="Deployment Details" link="{{ route('cloudflare-rule-master.history.show', $history->batch_id) }}"></x-breadcrumbs> --}}
    <x-breadcrumbs navigations="Deployment History"
            active="Deployment Details" link="{{ route('cloudflare-rule-master.history') }}"></x-breadcrumbs>
@endsection

@section('content')
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">Deployment Batch: {{ $history->batch_id }}</strong>
                        <div class="float-end">
                            <span id="batch-status" class="badge {{ $history->status == 'running' ? 'bg-warning' : 'bg-secondary' }}">{{ strtoupper($history->status) }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        
                        <!-- Progress Section -->
                        <div class="mb-4">
                            <h5>Progress</h5>
                            <div class="progress mb-2" style="height: 25px;">
                                <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" 
                                    style="width: {{ $history->total_domains > 0 ? ($history->success_count + $history->fail_count) / $history->total_domains * 100 : 0 }}%" 
                                    aria-valuenow="{{ $history->success_count + $history->fail_count }}" aria-valuemin="0" aria-valuemax="{{ $history->total_domains }}">
                                    <span id="progress-text">{{ $history->total_domains > 0 ? round(($history->success_count + $history->fail_count) / $history->total_domains * 100) : 0 }}%</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between text-center">
                                <div class="p-2 border rounded flex-fill me-1">
                                    <small class="text-muted d-block">Total Zona</small>
                                    <strong id="stat-total">{{ $history->total_domains }}</strong>
                                </div>
                                <div class="p-2 border rounded flex-fill me-1 bg-light-success">
                                    <small class="text-success d-block">Success</small>
                                    <strong id="stat-success" class="text-success">{{ $history->success_count }}</strong>
                                </div>
                                <div class="p-2 border rounded flex-fill bg-light-danger">
                                    <small class="text-danger d-block">Failed</small>
                                    <strong id="stat-failed" class="text-danger">{{ $history->fail_count }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Retry Action -->
                        @if($history->fail_count > 0)
                        <div class="alert alert-warning d-flex justify-content-between align-items-center">
                            <span>There are <strong>{{ $history->fail_count }}</strong> failed deployments.</span>
                            <form action="{{ route('cloudflare-rule-master.retry', $history->batch_id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning"><i class="fa fa-refresh me-1"></i> Retry Failed</button>
                            </form>
                        </div>
                        @endif

                        <hr>

                        <!-- Logs Table -->
                        <h5>Deployment Logs</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Zona Name</th>
                                        <th>Status</th>
                                        <th>Message</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $log)
                                        <tr>
                                            <td>{{ $log->customerDomain->zone_name ?? 'Unknown' }}</td>
                                            <td>
                                                @if($log->status == 'success')
                                                    <span class="badge bg-success">Success</span>
                                                @elseif($log->status == 'failed')
                                                    <span class="badge bg-danger">Failed</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $log->status }}</span>
                                                @endif
                                            </td>
                                            <td class="text-{{ $log->status == 'failed' ? 'danger' : 'muted' }}">
                                                {{ $log->error_message ?? 'OK' }}
                                            </td>
                                            <td>{{ $log->updated_at->format('H:i:s') }}</td>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const batchId = "{{ $history->batch_id }}";
        const isRunning = "{{ $history->status }}" === 'running';
        
        if (isRunning) {
            const interval = setInterval(function() {
                fetch(`{{ url('/cloudflare-rule-master/status') }}/${batchId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Update Progress Bar
                        const progressBar = document.getElementById('progress-bar');
                        const progressText = document.getElementById('progress-text');
                        progressBar.style.width = data.percentage + '%';
                        progressText.innerText = data.percentage + '%';
                        
                        // Update Stats
                        document.getElementById('stat-total').innerText = data.total;
                        document.getElementById('stat-success').innerText = data.success;
                        document.getElementById('stat-failed').innerText = data.failed;
                        
                        // Update Status Badge
                        document.getElementById('batch-status').innerText = data.status.toUpperCase();

                        // Update Logs Table
                        const tbody = document.querySelector('table tbody');
                        tbody.innerHTML = '';
                        
                        data.logs.forEach(log => {
                            const domain = log.customer_domain ? log.customer_domain.zone_name : 'Unknown';
                            // Simple time formatting needed as JS Date might be timezone dependent
                            const date = new Date(log.updated_at);
                            const time = date.toLocaleTimeString('en-GB'); // HH:MM:SS format

                            let statusBadge = '';
                            let msgClass = 'text-muted';
                            
                            if (log.status === 'success') {
                                statusBadge = '<span class="badge bg-success">Success</span>';
                            } else if (log.status === 'failed') {
                                statusBadge = '<span class="badge bg-danger">Failed</span>';
                                msgClass = 'text-danger';
                            } else {
                                statusBadge = '<span class="badge bg-secondary">' + log.status + '</span>';
                            }
                            
                            const row = `
                                <tr>
                                    <td>${domain}</td>
                                    <td>${statusBadge}</td>
                                    <td class="${msgClass}">${log.error_message || 'OK'}</td>
                                    <td>${time}</td>
                                </tr>
                            `;
                            tbody.innerHTML += row;
                        });

                        // Reload if completed (optional, maybe just stop polling if table updates live?)
                        // User asked for table update WITHOUT refresh.
                        if (data.status !== 'running') {
                            clearInterval(interval);
                            // window.location.reload(); // Disable reload to keep state
                        }
                    })
                    .catch(error => console.error('Error fetching status:', error));
            }, 2000); // Poll every 2 seconds
        }
    });
</script>
@endpush
</x-app-layout>
