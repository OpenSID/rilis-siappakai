<div>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between">
                <h5 class="modal-title" id="staticBackdropLabel">Pengaturan Email API Desa {{ $data->nama_desa ?? '' }}</h5>
                <button wire:click="Batal()" type="button" class="btn-close btn-sm" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table>
                    <tr>
                        <td align="left"><span>Kode Desa</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input id="kode_desa" name="kode_desa" class="form-control" value="{{ $data->kode_desa ?? '' }}" disabled>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left"><span>Nama Domain</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input id="nama_desa" name="nama_desa" class="form-control" value="{{ $data->domain_opensid ?? '' }}" disabled>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left"><span>Mail Host</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input type="text" wire:model.defer="mail_host" id="mail_host" name="mail_host" class="form-control" value="{{ $mail_host }}">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left"><span>Mail Username</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input type="text" wire:model.defer="mail_user" id="mail_user" name="mail_user" class="form-control" value="{{ $mail_user }}">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left"><span>Mail Password</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input type="text" wire:model.defer="mail_pass" id="mail_pass" name="mail_pass" class="form-control" value="{{ $mail_pass }}">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left"><span>Mail Address</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input type="text" wire:model.defer="mail_address" id="mail_address" name="mail_address" class="form-control" value="{{ $mail_address }}">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" width="45%"><span>Apakah akan menyimpan pengaturan email ?</span></td>
                        <td width="2%"><span>:</span></td>
                        <td align="left" width="53%">
                            <button wire:click="SimpanPengaturan()" type="button" class="btn btn-success mx-2"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Simpan pengaturan email">
                                <i class="fa fa-window-restore me-2" aria-hidden="true"></i> Ya
                            </button>
                            <button wire:click="Batal()" type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        </td>
                    </tr>
                </table>
                @if (session()->has('message-success'))
                    <div class="text-center alert alert-success">
                        {{ session('message-success') }}
                    </div>
                @elseif(session()->has('message-failed'))
                    <div class="text-center alert alert-danger">
                        {{ session('message-failed') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // close Modal Pengaturan Email
        window.addEventListener('closeModalPengaturanEmail', event => {
            $("#pengaturanEmail-{{ $data->id }}").modal('hide');
        })
    </script>
@endpush
