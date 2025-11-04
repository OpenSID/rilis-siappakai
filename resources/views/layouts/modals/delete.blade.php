<!-- Modal untuk hapus 1 data -->
<div class="modal fade text-start" id="{{ $table .'-'. $data->id }}" tabindex="-1" role="dialog" aria-labelledby="{{ $table .'-'. $data->id }}Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $table .'-'. $data->id }}Label">Apakah Anda Yakin menghapus {{ str_replace('-', ' ', $table ) }} yang dipilih ?</h5>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    @if($table == 'pelanggan')
                        <span>Kode Desa/Kelurahan : {{ $data->kode_desa }}</span><br>
                        <span>Nama Desa/Kelurahan : {{ $data->nama_desa }}</span><br>
                        <span>Nama Domain OpenSID : https://{{ $data->domain_opensid }}</span><br>
                    @endif
                    Data yang sudah dihapus tidak bisa dikembalikan <i class="fa fa-warning"></i>
                </div>
                <div class="clear"></div>

                <div class="d-flex justify-content-between">
                    <div>
                        <br>
                        <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Tidak</button>
                    </div>
                    <div class="mx-1"></div>
                    <div>
                        <form action="{{ route($table.'.destroy', encrypt($data->id)) }}" method="post">
                            @csrf
                            @method("DELETE")
                            <br>
                            <button type="submit" class="btn btn-danger w-100"><i class="fa fa-trash"></i>  Ya</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
