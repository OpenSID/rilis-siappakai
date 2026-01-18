<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="account_name">Nama Akun</label>
    <div class="col-md-6 col-sm-6">
        <input type="text" id="account_name" name="account_name"
            class="form-control @error('account_name') is-invalid @enderror" required
            placeholder="Masukkan nama akun Cloudflare" value="{{ old('account_name', $cloudflare->account_name ?? '') }}">
    </div>
    @error('account_name')
        <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="api_token">API Token</label>
    <div class="col-md-6 col-sm-6">
        @if (isset($cloudflare->id) && $cloudflare->id)
            <input type="password" id="api_token" name="api_token"
                class="form-control @error('api_token') is-invalid @enderror"
                placeholder="Kosongkan jika tidak ingin mengubah token">
            <small class="text-muted d-block mt-2">
                <i class="fa fa-info-circle"></i> Biarkan kosong jika tidak ingin mengubah token
                yang sudah tersimpan
            </small>
        @else
            <input type="password" id="api_token" name="api_token"
                class="form-control @error('api_token') is-invalid @enderror" required
                placeholder="Masukkan token API dari Cloudflare">
        @endif
    </div>
    @error('api_token')
        <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

@if (isset($cloudflare->id) && $cloudflare->id)
    <div class="item form-group d-flex">
        <label class="col-form-label col-md-3 col-sm-3 label-align" for="status">Status</label>
        <div class="col-md-6 col-sm-6">
            <select id="status" name="status"
                class="form-select @error('status') is-invalid @enderror" required>
                <option value="">-- Pilih Status --</option>
                <option value="aktif"
                    {{ old('status', $cloudflare->status) === 'aktif' ? 'selected' : '' }}>Aktif
                </option>
                <option value="nonaktif"
                    {{ old('status', $cloudflare->status) === 'nonaktif' ? 'selected' : '' }}>
                    Nonaktif</option>
            </select>
        </div>
        @error('status')
            <div class="text-danger mt-1 d-block">{{ $message }}</div>
        @enderror
    </div>
@endif

<hr>

<div class="item form-group offset-md-3">
    <div class="col-md-6 col-sm-6">
        <button class="btn btn-primary" type="reset">{{ $reset }}</button>
        <button type="submit" class="btn btn-success">{{ $submit }}</button>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            var elements = document.getElementsByTagName("INPUT");
            for (var i = 0; i < elements.length; i++) {
                elements[i].oninvalid = function(e) {
                    e.target.setCustomValidity("");
                    if (!e.target.validity.valid) {
                        switch (e.srcElement.id) {
                            case "account_name":
                                e.target.setCustomValidity(
                                    "Silakan isi nama akun Cloudflare"
                                );
                                break;
                            case "api_token":
                                e.target.setCustomValidity(
                                    "Silakan isi API Token dari Cloudflare");
                                break;
                        }
                    }
                };
                elements[i].oninput = function(e) {
                    e.target.setCustomValidity("");
                };
            }
        });
    </script>
@endpush
