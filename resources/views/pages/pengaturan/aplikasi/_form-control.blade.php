@include('pages.wilayah.pilih-kabupaten')

<style>
    .no-margin {
        margin: 0 !important;
    }
</style>
<div style="margin-top: 10px"></div>
@foreach ($aplikasi as $data)
    <div class="item form-group d-flex align-items-center mb-2 {{ (($data->jenis == 'text' && $data->kategori != 'pengaturan_wilayah' && $data->kategori != 'pengaturan_aapanel') ? 'd-inline' : 'd-none no-margin') }}">
        <label class="col-form-label col-md-3 col-sm-3 label-align {{ (($data->jenis == 'text' && $data->kategori != 'pengaturan_wilayah' && $data->kategori != 'pengaturan_aapanel') ? 'd-inline' : 'd-none') }}" for="value">{{ ucwords(str_replace('_', ' ', $data->key )) }}</label>
        <div class="col-md-4 col-sm-4 {{ (($data->jenis == 'text' && $data->kategori != 'pengaturan_wilayah' && $data->kategori != 'pengaturan_aapanel') ? 'd-inline' : 'd-none') }}">
            <input type="{{ $data->script == 'password' ? 'password' : 'text' }}" id="{{ $data->key }}" name="{{ $data->key }}" class="form-control @error('message') is-invalid @enderror" value="{{ old('value', $data->value) }}" {{ ($data->script == 'disabled' ? 'disabled' : '')}}>
        </div>
        <span class="col-md-5 col-sm-5 ms-2 {{ (($data->jenis == 'text' && $data->kategori != 'pengaturan_wilayah' && $data->kategori != 'pengaturan_aapanel') ? 'd-inline' : 'd-none') }}">{{ $data->keterangan }}.</span>
    </div>
@endforeach

<div class="item form-group d-flex align-items-center" style="margin-top: -6px">
    <label class="col-form-label col-md-3 col-sm-3 label-align">Tema Bawaan</label>
    <div class="col-md-4 col-sm-4">
        <select class="form-select" id="tema_bawaan" name="tema_bawaan" class="form-control @error('tema_bawaan') is-invalid @enderror" autocomplete="off">
            <option value="" disabled>-- Pilih --</option>
            @foreach ($options_tema as $item)
                <option value="{{ $item['value'] }}" {{ old('tema_bawaan', $tema_bawaan->value) == $item['value'] ? 'selected' : null}}>
                    {{ $item['label'] }}
                </option>
            @endforeach
        </select>
    </div>
    <span class="col-md-5 col-sm-5 ms-2">{{ $tema_bawaan->keterangan }}.</span>
</div>

<div class="item form-group d-flex align-items-center" style="margin-top: -10px">
    <label class="col-form-label col-md-3 col-sm-3 label-align">Pengaturan Domain</label>
    <div class="col-md-4 col-sm-4">
        <select class="form-select" id="pengaturan_domain" name="pengaturan_domain" class="form-control @error('pengaturan_domain') is-invalid @enderror" autocomplete="off">
            <option value="" disabled>-- Pilih --</option>
            @foreach ($options_vhost as $item)
                <option value="{{ $item['value'] }}" {{ old('pengaturan_domain', $pengaturan_domain->value) == $item['value'] ? 'selected' : null}}>
                    {{ $item['label'] }}
                </option>
            @endforeach
        </select>
    </div>
    <span class="col-md-5 col-sm-5 ms-2">{{ $pengaturan_domain->keterangan }}.</span>
</div>

<div class="item form-group d-flex align-items-center" style="margin-top: -10px">
    <label class="col-form-label col-md-3 col-sm-3 label-align">Akun Pengguna</label>
    <div class="col-md-4 col-sm-4">
        <select class="form-select" id="akun_pengguna" name="akun_pengguna" class="form-control @error('akun_pengguna') is-invalid @enderror" autocomplete="off">
            <option value="" disabled>-- Pilih --</option>
            @foreach ($options as $item)
            <option value="{{ $item['value'] }}" {{ old('akun_pengguna', $akun_pengguna->value) == $item['value'] ? 'selected' : null}}>
                {{ $item['label'] }}
            </option>
            @endforeach
        </select>
    </div>
    <span class="col-md-5 col-sm-5 ms-2">{{ $akun_pengguna->keterangan }}.</span>
</div>

<div class="item form-group d-flex align-items-center" style="margin-top: -5px">
    <label class="col-form-label col-md-3 col-sm-3 label-align">{{ ucwords(str_replace('_', ' ', $waktu_backup->key )) }}</label>
    <div class="col-md-4 col-sm-4">
        <input type="{{ $waktu_backup->jenis }}" id="{{ $waktu_backup->key }}" name="{{ $waktu_backup->key }}" class="form-control @error('message') is-invalid @enderror" value="{{ old('value') ?? $waktu_backup->value }}" {{ ($waktu_backup->script == 'disabled' ? 'disabled' : '')}}>
    </div>
    <span class="col-md-5 col-sm-5 ms-2">{{ $waktu_backup->keterangan }}.</span>
</div>

<div class="item form-group d-flex align-items-center" style="margin-top: -10px">
    <label class="col-form-label col-md-3 col-sm-3 label-align">{{ ucwords(str_replace('_', ' ', $maksimal_backup->key )) }}</label>
    <div class="col-md-4 col-sm-4">
        <input type="{{ $maksimal_backup->jenis }}" id="{{ $maksimal_backup->key }}" name="{{ $maksimal_backup->key }}" class="form-control @error('message') is-invalid @enderror" value="{{ old('value') ?? $maksimal_backup->value }}" {{ ($maksimal_backup->script == 'disabled' ? 'disabled' : '')}}>
    </div>
    <span class="col-md-5 col-sm-5 ms-2">{{ $maksimal_backup->keterangan }}.</span>
</div>

<div class="item form-group d-flex align-items-center" style="margin-top: -10px">
    <label class="col-form-label col-md-3 col-sm-3 label-align">Cloud Storage</label>
    <div class="col-md-4 col-sm-4">
        <select class="form-select" id="cloud_storage" name="cloud_storage" class="form-control @error('cloud_storage') is-invalid @enderror" autocomplete="off">
            <option value="" disabled>-- Pilih --</option>
            @foreach ($options_clouds as $item)
            <option value="{{ $item['value'] }}" {{ old('cloud_storage', $cloud_storage->value) == $item['value'] ? 'selected' : null}}>
                {{ $item['label'] }}
            </option>
            @endforeach
        </select>
    </div>
    <span class="col-md-5 col-sm-5 ms-2">{{ $cloud_storage->keterangan }}.</span>
</div>

<div class="item form-group d-flex align-items-center" style="margin-top: -10px">
    <label class="col-form-label col-md-3 col-sm-3 label-align">Server Panel</label>
    <div class="col-md-4 col-sm-4">
    <select onchange="showHideServer()" class="form-select" id="serverpanel" name="server_panel" class="form-control @error('server_panel') is-invalid @enderror" autocomplete="off">
        <option value="" disabled>-- Pilih --</option>
        @foreach ($options_panels as $item)
        <option value="{{ $item['value'] }}" {{ old('server_panel', $server_panel->value) == $item['value'] ? 'selected' : null}}>
            {{ $item['label'] }}
        </option>
        @endforeach
    </select>
    </div>
    <span class="col-md-5 col-sm-5 ms-2">{{ $server_panel->keterangan }}.</span>
</div>

<div id="pilihserver">
@foreach ($aplikasi as $data)
    <div class="item form-group d-flex align-items-center mb-3 {{ (($data->jenis == 'text' && $data->kategori != 'pengaturan_wilayah' && $data->kategori == 'pengaturan_aapanel') ? 'd-inline' : 'd-none no-margin') }}">
        <label class="col-form-label col-md-3 col-sm-3 label-align {{ (($data->jenis == 'text' && $data->kategori != 'pengaturan_wilayah' && $data->kategori == 'pengaturan_aapanel') ? 'd-inline' : 'd-none') }}" for="value">{{ ucwords(str_replace('_', ' ', $data->key )) }}</label>
        <div class="col-md-4 col-sm-4 {{ (($data->jenis == 'text' && $data->kategori != 'pengaturan_wilayah' && $data->kategori == 'pengaturan_aapanel') ? 'd-inline' : 'd-none') }}">
            <input type="{{ $data->script == 'password' ? 'password' : 'text' }}" id="{{ $data->kategori == 'pengaturan_aapanel' ? $data->key : '' }}" name="{{ $data->kategori == 'pengaturan_aapanel' ? $data->key : ''}}" class="form-control @error('message') is-invalid @enderror" value="{{ old('value', $data->value) }}" {{ ($data->script == 'disabled' ? 'disabled' : '')}}>
        </div>
        <span class="col-md-5 col-sm-5 ms-2 {{ (($data->jenis == 'text' && $data->kategori != 'pengaturan_wilayah' && $data->kategori == 'pengaturan_aapanel') ? 'd-inline' : 'd-none') }}">{{ $data->keterangan }}.</span>
    </div>
@endforeach
</div>

<div class="item form-group d-flex align-items-center" style="margin-top: -10px">
    <label class="col-form-label col-md-3 col-sm-3 label-align">Multi PHP</label>
    <div class="col-md-4 col-sm-4">
        <select class="form-select" id="multiphp" name="multiphp" class="form-control @error('multiphp') is-invalid @enderror" autocomplete="off">
            <option value="" disabled>-- Pilih --</option>
            @foreach ($options_multiphp as $item)
            <option value="{{ $item['value'] }}" {{ old('multiphp', $multiphp->value) == $item['value'] ? 'selected' : null}}>
                {{ $item['label'] }}
            </option>
            @endforeach
        </select>
    </div>
    <span class="col-md-5 col-sm-5 ms-2">{{ $maksimal_backup->keterangan }}.</span>
</div>

<div class="item form-group d-flex align-items-center" style="margin-top: -10px">
    <label class="col-form-label col-md-3 col-sm-3 label-align">Paksa Memakai Https</label>
    <div class="col-md-4 col-sm-4">
        <select class="form-select" id="redirect_https" name="redirect_https" class="form-control @error('redirect_https') is-invalid @enderror" autocomplete="off">
            <option value="0" @selected($redirect_https->value == 0)>Tidak</option>
            <option value="1" @selected($redirect_https->value == 1)>Ya</option>
        </select>
    </div>
    <span class="col-md-5 col-sm-5 ms-2">{{ $redirect_https->keterangan }}.</span>
</div>

<hr>

<livewire:pengaturan.progress :reset="$reset" :submit="$submit">

<script>
    document.addEventListener('DOMContentLoaded', function () {
        showHideServer();
    });

    function showHideServer() {
        const serverPanel = document.getElementById('serverpanel');
        const pilihServer = document.getElementById('pilihserver');
        pilihServer.style.display = serverPanel.value == '1' ? 'block' : 'none';
    }
</script>
