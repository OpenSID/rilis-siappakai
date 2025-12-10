{{-- Nama Sertifikat --}}
<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="nama_sertifikat">Nama Sertifikat</label>
    <div class="col-md-6 col-sm-6">
        <input type="text" id="nama_sertifikat" name="nama_sertifikat" class="form-control"
            required value="{{ old('nama_sertifikat') ?? $certificate->nama_sertifikat }}">
    </div>
    @error('nama_sertifikat')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

{{-- Domain --}}
<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="domain">Domain</label>
    <div class="col-md-6 col-sm-6">
        <input type="text" id="domain" name="domain" class="form-control"
            required value="{{ old('domain') ?? $certificate->domain ?? '' }}">
    </div>
    @error('domain')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

{{-- Tanggal Kadaluarsa --}}
<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="tgl_akhir">Tanggal Kadaluarsa</label>
    <div class="col-md-6 col-sm-6">
        <input type="date" id="tgl_akhir" name="tgl_akhir" class="form-control"
            required value="{{ old('tgl_akhir') ?? ($certificate->tgl_akhir ?? '') }}">
    </div>
    @error('tgl_akhir')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

{{-- Upload File CRT --}}
<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="path_crt">File CRT</label>
    <div class="col-md-6 col-sm-6">
        <input type="file" id="path_crt" name="path_crt" class="form-control" accept=".crt,.pem">
        @if(isset($certificate) && $certificate->path_crt)
            <small class="text-muted">File saat ini: {{ basename($certificate->path_crt) }}</small>
        @endif
    </div>
    @error('path_crt')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

{{-- Upload File KEY --}}
<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="path_key">File KEY</label>
    <div class="col-md-6 col-sm-6">
        <input type="file" id="path_key" name="path_key" class="form-control" accept=".key,.pem">
        @if(isset($certificate) && $certificate->path_key)
            <small class="text-muted">File saat ini: {{ basename($certificate->path_key) }}</small>
        @endif
    </div>
    @error('path_key')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

{{-- Upload File CA Bundle --}}
<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="path_ca">CA Bundle</label>
    <div class="col-md-6 col-sm-6">
        <input type="file" id="path_ca" name="path_ca" class="form-control" accept=".crt,.pem">
        @if(isset($certificate) && $certificate->path_ca)
            <small class="text-muted">File saat ini: {{ basename($certificate->path_ca) }}</small>
        @endif
    </div>
    @error('path_ca')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

{{-- Status --}}
<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="status">Status</label>
    <div class="col-md-6 col-sm-6">
        <select name="status" id="status" class="form-control">
            <option value="aktif" {{ old('status', $certificate->status ?? '') == 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="akan berakhir" {{ old('status', $certificate->status ?? '') == 'akan berakhir' ? 'selected' : '' }}>Akan Berakhir</option>
            <option value="tidak aktif" {{ old('status', $certificate->status ?? '') == 'tidak aktif' ? 'selected' : '' }}>Tidak Aktif</option>
        </select>
    </div>
    @error('status')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

<hr>

<div class="item form-group {{ ($submit == 'Tambah' ? 'offset-md-3' : '') }}">
    <div class="col-md-6 col-sm-6">
        <button class="btn btn-primary" type="reset">{{ $reset }}</button>
        <button type="submit" class="btn btn-success">{{ $submit }}</button>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        const elements = document.getElementsByTagName("INPUT");
        for (let i = 0; i < elements.length; i++) {
            elements[i].oninvalid = function (e) {
                e.target.setCustomValidity("");
                if (!e.target.validity.valid) {
                    e.target.setCustomValidity("Silakan isi field ini!");
                }
            };
            elements[i].oninput = function (e) {
                e.target.setCustomValidity("");
            };
        }
    });
</script>
@endpush
