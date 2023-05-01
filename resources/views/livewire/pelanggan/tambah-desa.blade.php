<div>
    <div
        x-data="{ isNewsite: false, progress: 0 }"
        x-on:livewire-upload-start="isNewsite = true"
        x-on:livewire-upload-finish="isNewsite = false"
        x-on:livewire-upload-error="isNewsite = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
    >

    @include('pages.wilayah.data-desa')

    @if($show_port == "proxy")
        <div class="item form-group d-flex">
            <label class="col-form-label col-md-3 col-sm-3 label-align" for="port_domain">Port Domain <span class="required">*</span></label>
            <div class="col-md-8 col-sm-8">
                <input type="number" wire:model="port_domain" id="port_domain" name="port_domain" oninput="maxLengthCheck(this)" maxlength="4" max="9999" class="form-control" required value="{{ old('port_domain') ?? '' }}">
            </div>
            @error('port_domain')
            <div class="text-danger mt-1 d-block">{{ $message }}</div>
            @enderror
        </div>
    @endif

    <div class="item form-group d-flex">
        <label class="col-form-label col-md-3 col-sm-3 label-align" for="token_premium">Token Premium <span class="required">*</span></label>
        <div class="col-md-8 col-sm-8 ">
            <textarea wire:model.defer="token_premium" class="form-control text-primary" id="token_premium" name="token_premium" style="height: 150px">{{ $token_premium }}</textarea>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="text-center">
        <div wire:loading wire:target="Submit">
            <div x-show="isNewsite" class="item form-group d-flex">
                <progress max="100" x-bind:value="progress"></progress><br/>
                <small class="text-danger me-2">Silakan tunggu proses tambah {{$sebutandesa}} selesai ... !!!</small>
            </div>
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
            <button wire:click="Submit" type="submit" class="btn btn-success" {{$btnTambah ?? '' }}>{{ $submit }}</button>
        </div>
    </div>

</div>

@push('scripts')
    @include('layouts.includes._scripts-validation')
@endpush
