<div>
    <div
        x-data="{ isUploading: false, progress: 0 }"
        x-on:livewire-upload-start="isUploading = true"
        x-on:livewire-upload-finish="isUploading = false"
        x-on:livewire-upload-error="isUploading = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
    >
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between">
                <h5 class="modal-title" id="staticBackdropLabel">Pindah Hosting</h5>
                <button wire:click="Batal()" type="button" class="btn-close btn-sm" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="pindahHosting({{ $data }})" enctype="multipart/form-data">
                    <table>
                        <tr>
                            <td align="left"><span>Kode Desa</span></td>
                            <td><span>:</span></td>
                            <td align="left"><span>{{ $data->kode_desa }}</span></td>
                        </tr>
                        <tr>
                            <td align="left"><span>Nama Desa</span></td>
                            <td><span>:</span></td>
                            <td align="left"><span>{{ $data->nama_desa }}</span></td>
                        </tr>
                        <tr>
                            <td align="left"><span>Versi Saat ini</span></td>
                            <td><span>:</span></td>
                            <td align="left"><span>{{ $data->versi_opensid }}</span></td>
                        </tr>
                        <tr>
                            <td align="left"><span>Nama Domain</span></td>
                            <td><span>:</span></td>
                            <td align="left"><span>{{ $data->domain_opensid }}</span></td>
                        </tr>
                        <tr>
                            <td align="left">
                                <span>Silakan Unggah Folder Desa </span> <br/>
                                <span class="text-danger">( desa.zip atau desa_{{str_replace('.', '', $data->kode_desa)}}.zip )</span>
                            </td>
                            <td><span>:</span></td>
                            <td align="left">
                                <div wire:loading.remove>
                                    <input wire:loading.attr="disabled" wire:model.defer="folderdesa" type="file" id="folderdesa" name="folderdesa" class="form-control">
                                </div>

                                @error('folderdesa')
                                    <label class="text-danger">{{ $message }}</label>
                                @enderror
                                <!-- Progress Bar -->
                                <div wire:loading wire:target="folderdesa">
                                    <div x-show="isUploading">
                                        <small class="me-2">Proses unggah data ............... </small>
                                        <progress max="100" x-bind:value="progress"></progress> <br/>
                                        <small class="text-danger">Silakan tunggu proses selesai, jangan unggah yang lain ...!!!</small>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="{{ $openkab == 'true' ? 'd-none' : '' }}">
                            <td align="left">
                                <span>Silakan Unggah Database OpenSID </span>
                                <span class="text-danger">(*.sql)</span>
                            </td>
                            <td><span>:</span></td>
                            <td align="left">
                                <div wire:loading.remove>
                                    <input wire:loading.attr="disabled" wire:model.defer="database_opensid" type="file" id="database_opensid" name="database_opensid" class="form-control">
                                </div>

                                @error('database_opensid')
                                    <label class="text-danger">{{ $message }}</label>
                                @enderror
                                <!-- Progress Bar -->
                                <div wire:loading wire:target="database_opensid">
                                    <div x-show="isUploading">
                                        <small class="me-2">Proses unggah data ............... </small>
                                        <progress max="100" x-bind:value="progress"></progress><br/>
                                        <small class="text-danger">Silakan tunggu proses selesai, jangan unggah yang lain ...!!!</small>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td align="left">
                                <span>Silakan Unggah Database PBB </span>
                                <span class="text-danger">(*.sql)</span>
                            </td>
                            <td><span>:</span></td>
                            <td align="left">
                                <div wire:loading.remove>
                                    <input wire:loading.attr="disabled" wire:model.defer="database_pbb" type="file" id="database_pbb" name="database_pbb" class="form-control">
                                </div>

                                @error('database_pbb')
                                    <label class="text-danger">{{ $message }}</label>
                                @enderror
                                <!-- Progress Bar -->
                                <div wire:loading wire:target="database_pbb">
                                    <div x-show="isUploading">
                                        <small class="me-2">Proses unggah data ............... </small>
                                        <progress max="100" x-bind:value="progress"></progress><br/>
                                        <small class="text-danger">Silakan tunggu proses selesai, jangan unggah yang lain ...!!!</small>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td align="left" width="50%"><span>Apakah Anda yakin akan pindah hosting ke Dasbor SiapPakai?</span></td>
                            <td><span>:</span></td>
                            <td align="{{$proses == true ? 'center' : 'left'}}">
                                <button wire:loading.attr="disabled" type="submit" class="btn btn-success me-2 {{$hide == true ? 'd-none' : 'd-inline'}}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Pindah Hosting">
                                    <i class="fa fa-window-restore me-2" aria-hidden="true"></i> Ya
                                </button>
                                <button wire:click="Batal()" type="button" class="btn btn-secondary {{$hide == true ? 'd-none' : 'd-inline'}}" data-dismiss="modal">Batal</button>
                                <label class="text-primary ms-2">{{ $sukses }}</label>
                                <a class="btn btn-primary {{$proses == true ? 'd-inline' : 'd-none'}}" href="{{ $link }}">Lihat proses</a>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // close Modal Pindah Hosting
        window.addEventListener('closeModalPindahHosting', event => {
            $("#pindahHosting-{{ $data->id }}").modal('hide');
        })
    </script>
@endpush
