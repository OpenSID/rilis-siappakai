<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="command">Command</label>
    <div class="col-md-6 col-sm-6 ">
        <input type="text" id="command" name="command" class="form-control" required value="{{ old('command') ?? $jadwal->command }}">
    </div>
    @error('command')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="keterangan">Keterangan</label>
    <div class="col-md-6 col-sm-6 ">
        <input type="text" id="keterangan" name="keterangan" class="form-control" required value="{{ old('keterangan') ?? $jadwal->keterangan }}">
    </div>
    @error('keterangan')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="timezone">Timezone</label>
    <div class="col-md-6 col-sm-6">
        <select id="timezone" name="timezone" class="form-select select2 @error('timezone') is-invalid @enderror" autocomplete="off">
            <option value=''> -- Timezone -- </option>
            @foreach($timezones as $item)
                <option value="{{ $item['value'] }}" {{ old('timezone', $jadwal->timezone) == $item['value'] ? 'selected' : null}}>
                    {{ $item['value'] }}
                </option>
            @endforeach
        </select>
    </div>
    @error('timezone')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="jam">Jam</label>
    <div class="col-md-6 col-sm-6 d-flex">
        <input id="timepkr" style="width:260px;float:left;" placeholder="HH:MM" name="jam" class="form-control" required value="{{ old('jam') ?? $jadwal->jam }}">
        <button type="button" class="btn btn-primary" onclick="showpickers('timepkr',24)" style="width:40px;float:left;"><i class="fa fa-clock"></i>
    </div>
    @error('jam')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>
<div class="timepicker"></div>

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
            var elements = document.getElementsByTagName("INPUT");
            for (var i = 0; i < elements.length; i++) {
                elements[i].oninvalid = function (e) {
                    e.target.setCustomValidity("");
                    if (!e.target.validity.valid) {
                        switch (e.srcElement.id) {
                            case "command":
                                e.target.setCustomValidity("silakan isi command !!!");
                                break;
                            case "keterangan":
                                e.target.setCustomValidity("silakan isi keterangan !!!");
                                break;
                            case "timezone":
                                e.target.setCustomValidity("silakan isi timezone !!!");
                                break;
                            case "jam":
                                e.target.setCustomValidity("silakan isi jam, harus minimal 8 karakter. !!!");
                                break;
                        }
                    }
                };
                elements[i].oninput = function (e) {
                    e.target.setCustomValidity("");
                };
            }
        })
</script>
@endpush
