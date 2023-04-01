<div>
    @include('pages.wilayah.data-desa')

    <div class="item form-group d-flex">
        <label class="col-form-label col-md-3 col-sm-3 label-align" for="token_premium">Token Premium <span class="required">*</span></label>
        <div class="col-md-8 col-sm-8 ">
            <textarea wire:model.defer="token_premium" class="form-control text-primary" id="token_premium" name="token_premium" style="height: 150px">{{ $token_premium }}</textarea>
        </div>
    </div>

    @if (session()->has('message-success'))
        <div class="text-center alert alert-success">
            {{ session('message-success') }}
        </div>
    @elseif(session()->has('message-failed'))
        <div class="text-center alert alert-danger">
            {{ session('message-failed') }}
        </div>
    @endif

    <hr>

    <div class="item form-group {{ ($submit == 'Tambah' ? 'offset-md-3' : '') }}">
        <div class="col-md-8 col-sm-8">
            <button class="btn btn-primary" type="reset">{{ $reset }}</button>
            <button wire:click="Submit" type="submit" class="btn btn-success">{{ $submit }}</button>
        </div>
    </div>

</div>
