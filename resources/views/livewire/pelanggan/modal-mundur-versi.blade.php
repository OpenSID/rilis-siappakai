<div>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between">
                <h5 class="modal-title" id="staticBackdropLabel">Mundur Versi Sebelumnya</h5>
                <button type="button" class="btn-close btn-sm" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table>
                    <tr>
                        <td align="left"><span>Kode {{ $openkab == 'true' ? $data['sebutan_kabupaten'] : 'Desa' }}</span></td>
                        <td><span>:</span></td>
                        <td align="left"><span>{{ $openkab == 'true' ? $data['kode_kabupaten'] : $data->kode_desa }}</span></td>
                    </tr>
                    <tr>
                        <td align="left"><span>Nama {{ $openkab == 'true' ? $data['sebutan_kabupaten'] : 'Desa' }}</span></td>
                        <td><span>:</span></td>
                        <td align="left"><span>{{ $openkab == 'true' ? $data['nama_kabupaten'] : $data->nama_desa }}</span></td>
                    </tr>
                    <tr>
                        <td align="left"><span>Versi Saat ini</span></td>
                        <td><span>:</span></td>
                        <td align="left"><span>{{ $versi_opensid }}</span></td>
                    </tr>
                    <tr>
                        <td align="left" width="50%"><span>Apakah Anda yakin akan mundur versi sebelumnya ?</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <button wire:click="mundurVersi({{ $data }})" type="button" class="btn btn-success me-2"
                                data-toggle="tooltip" data-bs-placement="top" title="Mundur versi sebelumnya">
                                <i class="fa fa-window-restore me-2" aria-hidden="true"></i> Ya
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
