@include('pages.wilayah.pilih-kabupaten')

<div style="margin-top: -85px"></div>

@foreach ($aplikasi as $data)
    <div class="item form-group d-flex align-items-center mb-2 {{ (($data->jenis == 'text' && $data->kategori != 'pengaturan_wilayah') ? 'd-inline' : 'd-none') }}">
        <label class="col-form-label col-md-3 col-sm-3 label-align {{ (($data->jenis == 'text' && $data->kategori != 'pengaturan_wilayah') ? 'd-inline' : 'd-none') }}" for="value">{{ ucwords(str_replace('_', ' ', $data->key )) }}</label>
        <div class="col-md-4 col-sm-4 {{ (($data->jenis == 'text' && $data->kategori != 'pengaturan_wilayah') ? 'd-inline' : 'd-none') }}">
            <input type="{{ $data->script == 'password' ? 'password' : 'text' }}" id="{{ $data->key }}" name="{{ $data->key }}" class="form-control @error('message') is-invalid @enderror" value="{{ old('value') ?? $data->value }}" {{ ($data->script == 'disabled' ? 'disabled' : '')}}>
        </div>
        <span class="col-md-5 col-sm-5 ms-2 {{ (($data->jenis == 'text' && $data->kategori != 'pengaturan_wilayah') ? 'd-inline' : 'd-none') }}">{{ $data->keterangan }}.</span>
    </div>
@endforeach

<div class="item form-group d-flex align-items-center" style="margin-top: -12px">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="tema_bawaan">Tema Bawaan</label>
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

<div class="item form-group d-flex align-items-center" style="margin-top: -12px">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="pengaturan_domain">Pengaturan Domain</label>
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

<div class="item form-group d-flex align-items-center" style="margin-top: -15px">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="akun_pengguna">Akun Pengguna</label>
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

<hr>

<livewire:pengaturan.progress :reset="$reset" :submit="$submit">
