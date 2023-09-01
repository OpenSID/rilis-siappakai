<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="command">Username</label>
    <div class="col-md-6 col-sm-6 ">
        <input type="text" id="username" name="username" class="form-control" required value="{{ old('username') ?? $tema->username }}" placeholder="Username Github">
    </div>
    @error('username')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="keterangan">Tema</label>
    <div class="col-md-6 col-sm-6 ">
        <input type="text" id="tema" name="tema" class="form-control" required value="{{ old('tema') ?? $tema->tema }}" placeholder="Nama Tema">
    </div>
    @error('tema')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="keterangan">Repo</label>
    <div class="col-md-6 col-sm-6 ">
        <input type="text" id="repo" name="repo" class="form-control" required value="{{ old('repo') ?? $tema->repo }}" placeholder="Nama Repository">
    </div>
    @error('repo')
    <div class="text-danger mt-1 d-block">{{ $message }}</div>
    @enderror
</div>

<div class="item form-group d-flex">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="keterangan">Branch</label>
    <div class="col-md-6 col-sm-6 ">
        <input type="text" id="branch" name="branch" class="form-control" required value="{{ old('branch') ?? $tema->branch }}" placeholder="Nama Branch Default">
    </div>
    @error('branch')
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
            var elements = document.getElementsByTagName("INPUT");
            for (var i = 0; i < elements.length; i++) {
                elements[i].oninvalid = function (e) {
                    e.target.setCustomValidity("");
                    if (!e.target.validity.valid) {
                        switch (e.srcElement.id) {
                            case "username":
                                e.target.setCustomValidity("silakan isi username !!!");
                                break;
                            case "tema":
                                e.target.setCustomValidity("silakan isi tema !!!");
                                break;
                            case "repo":
                                e.target.setCustomValidity("silakan isi repo !!!");
                                break;
                            case "branch":
                                e.target.setCustomValidity("silakan isi branch !!!");
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
