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
                    <div class="card-body d-flex justify-content-between">
                        <h5>{{ $certificate->nama_sertifikat }}</h5>
                        <p><strong>Domain:</strong> {{ $certificate->domain }}</p>
                        <p><strong>Status:</strong> <span class="badge bg-info text-dark">{{ $certificate->status }}</span></p>
                        <p><strong>Tanggal Akhir:</strong> {{ $certificate->tgl_akhir }}</p>
                    </div>

                    {{-- === FILE PREVIEW SECTION === --}}
                    @php
                        $files = [
                            'path_crt' => 'File CRT (Certificate)',
                            'path_key' => 'File KEY (Private Key)',
                            'path_ca'  => 'CA Bundle (Chain)',
                        ];
                    @endphp

                    @foreach($files as $key => $label)
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="fw-bold">{{ $label }}</span>
                                <div class="btn-group btn-group-sm">
                                    {{-- Tombol Copy --}}
                                    <button class="btn btn-outline-secondary btn-sm"
                                            onclick="copyToClipboard('{{ $key }}')">
                                        <i class="fa fa-copy"></i> Copy
                                    </button>

                                    {{-- Tombol Download --}}
                                    @if($certificate->{$key})
                                        <a href="{{ route('ssl.download', ['id' => $certificate->id, 'type' => $key]) }}"
                                        class="btn btn-outline-primary btn-sm">
                                            <i class="fa fa-download"></i> Download
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="card-body">
                                @if($fileContents[$key])
                                    <textarea id="{{ $key }}" class="form-control text-muted" rows="10" readonly>{{ $fileContents[$key] }}</textarea>
                                @else
                                    <span class="text-muted">Belum ada file diunggah.</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- === SCRIPT COPY FUNCTION === --}}
<script>
function copyToClipboard(elementId) {
    const textarea = document.getElementById(elementId);
    if (textarea) {
        textarea.select();
        textarea.setSelectionRange(0, 99999);
        document.execCommand("copy");

        // Tampilkan notifikasi kecil
        const btn = event.target.closest('button');
        btn.innerHTML = '<i class="fa fa-check text-success"></i> Copied!';
        setTimeout(() => {
            btn.innerHTML = '<i class="fa fa-copy"></i> Copy';
        }, 1500);
    }
}
</script>

</x-app-layout>
