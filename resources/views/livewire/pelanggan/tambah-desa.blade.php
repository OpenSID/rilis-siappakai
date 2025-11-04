<div>
    <div x-data="{ isNewsite: false, progress: 0 }" x-on:livewire-upload-start="isNewsite = true"
        x-on:livewire-upload-finish="isNewsite = false" x-on:livewire-upload-error="isNewsite = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress">

        <div class="item form-group d-flex">
            <label class="col-form-label col-md-3 col-sm-3 label-align">Metode Pendaftaran</label>
            <div class="col-md-8 col-sm-8">
                <select class="form-select @error('pilih_pendaftaran') is-invalid @enderror" wire:model.live="pilih_pendaftaran" wire:change="onPendaftaranChange" id="pilih_pendaftaran" name="pilih_pendaftaran"
                    autocomplete="off">
                    <option value="" disabled>-- Pilih Pendaftaran --</option>
                    @foreach ($options->pilihPendaftaran($sebutandesa, $sebutankab, $namakabupaten) as $item)
                        <option value="{{ $item['key'] }}">
                            {{ $item['value'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($pilih_pendaftaran == 'false' && $opensid == 3)
            <div class="item form-group d-flex">
                <label class="col-form-label col-md-3 col-sm-3 label-align">Versi OpenSID</label>
                <div class="col-md-8 col-sm-8">
                    <select class="form-select @error('pilih_opensid') is-invalid @enderror" wire:model.live="pilih_opensid" id="pilih_opensid"
                        name="pilih_opensid" autocomplete="off">
                        <option value="" disabled>-- Pilih OpenSID --</option>
                        @foreach ($options->pilihOpenSid() as $item)
                            <option value="{{ $item['value'] }}">
                                {{ $item['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        @if ($pilih_pendaftaran == 'false')
            <div>
                @include('pages.wilayah.data-desa')

                <div class="item form-group d-flex">
                    <label class="col-form-label col-md-3 col-sm-3 label-align">Sudah Terdaftar di Layanan</label>
                    <div class="col-md-8 col-sm-8">
                        <select class="form-select @error('terdaftar_layanan') is-invalid @enderror" wire:model.live="terdaftar_layanan" id="terdaftar_layanan"
                            name="terdaftar_layanan" autocomplete="off" @disabled($pilih_opensid == 1 || $opensid == 1)>
                            <option value="" disabled>-- Pilih Layanan --</option>
                            @foreach ($options->pilihNilaiKebenaran() as $item)
                                <option value="{{ $item['value'] }}">
                                    {{ $item['nilai'] == 'Tidak' ? 'Belum' : $item['nilai'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if ($show_port == 'proxy')
                    <div class="item form-group d-flex">
                        <label class="col-form-label col-md-3 col-sm-3 label-align" for="port_domain">Port Domain <span
                                class="required">*</span></label>
                        <div class="col-md-8 col-sm-8">
                            <input type="number" wire:model.live="port_domain" id="port_domain" name="port_domain"
                                oninput="maxLengthCheck(this)" maxlength="4" max="9999" class="form-control" required
                                value="{{ old('port_domain') ?? '' }}">
                        </div>
                        @error('port_domain')
                            <div class="text-danger mt-1 d-block">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                @if ($terdaftar_layanan == 'true' && $pilih_opensid == 2 )
                    <div class="item form-group d-flex">
                        <label class="col-form-label col-md-3 col-sm-3 label-align" for="token_premium">Token Premium <span
                                class="required">*</span></label>
                        <div class="col-md-8 col-sm-8 ">
                            <textarea wire:model.defer="token_premium" class="form-control text-primary" id="token_premium" name="token_premium"
                                style="height: 150px">{{ $token_premium }}</textarea>
                        </div>
                    </div>
                @endif

                @if ($terdaftar_layanan == 'false' || ($pilih_opensid == 1 && $opensid == 3))
                    <div class="item form-group d-flex">
                        <label class="col-form-label col-md-3 col-sm-3 label-align" for="domain_opensid">Nama Domain</label>
                        <div class="col-md-8 col-sm-8">
                            <input type="text" wire:model.live="domain_opensid" id="domain_opensid" name="domain_opensid"
                                class="form-control" value="{{ old('domain_opensid') ?? '-' }}"
                                placeholder="Dikosongkan jika domain belum tersedia">
                        </div>
                        @error('domain_opensid')
                            <div class="text-danger mt-1 d-block">{{ $message }}</div>
                        @enderror
                    </div>
                @endif
            </div>
        @endif

        <!-- Progress Bar -->
        <div class="text-center">
            <div wire:loading wire:target="Submit">
                <div x-show="isNewsite" class="item form-group d-flex">
                    <progress max="100" x-bind:value="progress"></progress><br />
                    <small class="text-danger ms-2">Silakan tunggu proses tambah {{ $sebutandesa }} selesai ...
                        !!!</small>
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

        <div class="item form-group {{ $submit == 'Tambah' ? 'offset-md-3' : '' }}">
            <div class="col-md-8 col-sm-8">
                <button wire:click="Kosongkan" id="reset-btn" class="btn btn-primary"
                    type="reset">{{ $reset }}</button>
                <button wire:click="Submit" type="submit" class="btn btn-success"
                    {{ $btnTambah ?? '' }}>{{ $submit }}</button>
            </div>
        </div>

    </div>

    @push('scripts')
        @include('layouts.includes._scripts-validation')
    @endpush
