<div>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between">
                <h5 class="modal-title" id="staticBackdropLabel">Ubah Domain Kecamatan {{ $data->nama_kecamatan ?? '' }}</h5>
                <button wire:click="Batal()" type="button" class="btn-close btn-sm" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table>
                    <tr>
                        <td align="left"><span>Kode Kecamatan</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input id="kode_desa" name="kode_desa" class="form-control" value="{{ $data->kode_kecamatan ?? '' }}" disabled>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left"><span>Nama Domain Sekarang</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input id="nama_domain_lama" name="nama_domain_lama" class="form-control" value="{{ $data->domain_opendk ?? '' }}" disabled>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left"><span>Domain Baru</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input type="text" wire:model.defer="nama_domain_baru" id="nama_domain_baru" name="nama_domain_baru" class="form-control" value="{{ $nama_domain_baru }}">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" width="45%"><span>Apakah akan mengubah domain ?</span></td>
                        <td width="2%"><span>:</span></td>
                        <td align="left" width="53%">
                            <button wire:click="Simpan()" type="button" class="btn btn-success mx-2"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Simpan domain">
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
        // close Modal Ubah Domain
        window.addEventListener('closeModalUbahDomain-{{ $data->id }}', event => {
            $("#ubahDomain-{{ $data->id }}").modal('hide');
            $('.modal-backdrop').remove();
        })
    </script>
@endpush
