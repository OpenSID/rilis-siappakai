<div>
    <div
        x-data="{ isNewsite: false, progress: 0 }"
        x-on:livewire-upload-start="isNewsite = true"
        x-on:livewire-upload-finish="isNewsite = false"
        x-on:livewire-upload-error="isNewsite = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
    >

    <!-- Progress Bar -->
    <div class="text-center">
        <div wire:loading wire:target="Submit">
        {{-- <div wire:loading wire:loading.delay> --}}
            <div x-show="isNewsite" class="item form-group d-flex">
                <progress max="100" x-bind:value="progress"></progress><br/>
                <small class="text-danger ms-2">Silakan tunggu proses pengambilan data {{$sebutandesa}} selesai ... !!!</small>
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

    <div class="item form-group">
        <div class="d-flex justify-content-between">
            <button class="btn btn-primary" type="reset">{{ $reset }}</button>
            <button wire:click="Submit" type="submit" class="btn btn-success ms-2">{{ $submit }}</button>
        </div>
    </div>
</div>
