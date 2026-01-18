<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="name">Name <span class="required">*</span> </label>
    <div class="col-md-6 col-sm-6 ">
        <input type="text" id="name" name="name" class="form-control" required value="{{ old('name') ?? $rule->name }}">
        <small class="text-muted">Nama identifikasi rule untuk internal sistem (misal: "Blokir Login Admin").</small>
    </div>
    @error('name')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="description">Description</label>
    <div class="col-md-6 col-sm-6 ">
        <input type="text" id="description" name="description" class="form-control" value="{{ old('description') ?? $rule->description }}">
        <small class="text-muted">Catatan tambahan mengenai fungsi rule ini.</small>
    </div>
    @error('description')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="action">Action <span class="required">*</span></label>
    <div class="col-md-6 col-sm-6">
        <select id="action" name="action" class="form-select select2 @error('action') is-invalid @enderror" autocomplete="off" required>
            <option value=''> -- Select Action -- </option>
            @foreach(['block', 'managed_challenge', 'js_challenge', 'challenge', 'log', 'skip'] as $act)
                <option value="{{ $act }}" {{ old('action', $rule->action) == $act ? 'selected' : null}}>
                    {{ ucfirst(str_replace('_', ' ', $act)) }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Tindakan jika rule terpenuhi (Block = Blokir, Allow = Izinkan, Challenge = Captcha).</small>
    </div>
    @error('action')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="expression">Expression <span class="required">*</span></label>
    <div class="col-md-6 col-sm-6 ">
        <textarea id="expression" name="expression" class="form-control" rows="4" required>{{ old('expression') ?? $rule->expression }}</textarea>
        <small class="text-muted">Logika filter (Syntax Wirefilter). Contoh: <code>(http.request.uri.path eq "/login")</code></small>
    </div>
    @error('expression')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="priority">Priority</label>
    <div class="col-md-6 col-sm-6 ">
        <input type="number" id="priority" name="priority" class="form-control" value="{{ old('priority') ?? $rule->priority ?? 0 }}">
        <small class="text-muted">Urutan eksekusi. Semakin kecil = semakin awal. Gunakan jarak, misal: 10, 20, 30.</small>
    </div>
    @error('priority')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="is_enabled">Enabled</label>
    <div class="col-md-6 col-sm-6 ">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_enabled" name="is_enabled" 
            {{ (old('is_enabled') || $rule->is_enabled) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_enabled">Aktifkan Rule ini</label>
        </div>
    </div>
</div>

<hr>

<div class="item form-group offset-md-3">
    <div class="col-md-6 col-sm-6">
        <button class="btn btn-primary" type="reset">{{ $reset }}</button>
        <button type="submit" class="btn btn-success">{{ $submit }}</button>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
            var elements = document.getElementsByTagName("INPUT");
            for (var i = 0; i < elements.length; i++) {
                elements[i].oninvalid = function (e) {
                    e.target.setCustomValidity("");
                    if (!e.target.validity.valid) {
                         // Simple validation messages
                         e.target.setCustomValidity("Silakan isi field ini");
                    }
                };
                elements[i].oninput = function (e) {
                    e.target.setCustomValidity("");
                };
            }
        })
</script>
@endpush
