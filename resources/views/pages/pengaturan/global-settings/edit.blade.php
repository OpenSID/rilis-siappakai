<x-app-layout title="Edit Pengaturan Global">

    @section('breadcrumbs')
        <x-breadcrumbs navigations="Pengaturan Global" active="Edit {{ $setting['judul'] }}"
            link="{{ route('pengaturan-global.index', ['pelanggan_id' => $pelanggan->id]) }}"></x-breadcrumbs>
    @endsection

    @section('content')
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-8 mx-auto">
                {{-- Info Pengaturan --}}
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">
                            <i class="fa fa-info-circle me-1"></i> Informasi Pengaturan
                        </strong>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td width="30%"><strong>Pelanggan</strong></td>
                                <td>{{ $pelanggan->nama_desa }} ({{ $pelanggan->kode_desa }})</td>
                            </tr>
                            <tr>
                                <td><strong>Key</strong></td>
                                <td><code>{{ $setting['key'] }}</code></td>
                            </tr>
                            <tr>
                                <td><strong>Tipe Input</strong></td>
                                <td><span class="badge badge-info">{{ $setting['jenis'] }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Kategori</strong></td>
                                <td><span class="badge badge-secondary text-capitalize">{{ $setting['kategori'] }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Keterangan</strong></td>
                                <td>{{ $setting['keterangan'] }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Form Edit --}}
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">
                            <i class="fa fa-edit me-1"></i> Edit: {{ $setting['judul'] }}
                        </strong>
                    </div>
                    <form method="POST"
                          action="{{ route('pengaturan-global.update', [$pelanggan->id, $setting['key']]) }}">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group">
                                <label for="value">
                                    {{ $setting['judul'] }}
                                    <span class="text-danger">*</span>
                                </label>

                                @if ($setting['jenis'] === 'textarea')
                                    <textarea
                                        class="form-control @error('value') is-invalid @enderror"
                                        id="value"
                                        name="value"
                                        rows="4"
                                        placeholder="{{ $setting['keterangan'] }}"
                                    >{{ old('value', $setting['value']) }}</textarea>

                                @elseif ($setting['jenis'] === 'password')
                                    <div class="input-group">
                                        <input
                                            type="password"
                                            class="form-control @error('value') is-invalid @enderror"
                                            id="value"
                                            name="value"
                                            value="{{ old('value', $setting['value']) }}"
                                            placeholder="{{ $setting['keterangan'] }}"
                                        >
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                @else
                                    <input
                                        type="text"
                                        class="form-control @error('value') is-invalid @enderror"
                                        id="value"
                                        name="value"
                                        value="{{ old('value', $setting['value']) }}"
                                        placeholder="{{ $setting['keterangan'] }}"
                                    >
                                @endif

                                <small class="form-text text-muted">{{ $setting['keterangan'] }}</small>

                                @error('value')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('pengaturan-global.index', ['pelanggan_id' => $pelanggan->id]) }}"
                               class="btn btn-secondary">
                                <i class="fa fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('#togglePassword').click(function() {
                var input = $('#value');
                var icon = $(this).find('i');

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        });
    </script>
    @endpush

</x-app-layout>
