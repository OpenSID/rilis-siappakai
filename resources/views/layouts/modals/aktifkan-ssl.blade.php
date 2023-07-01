<!-- Modal untuk hapus 1 data -->
<div class="modal fade text-start" id="{{ $table}}-aktifkan-ssl" tabindex="-1" role="dialog" aria-labelledby="{{ $table}}-aktifkan-sslLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $table}}-aktifkan-sslLabel">Apakah Anda Yakin Memperbarui SSL ?</h5>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    Tekan tombol <b>Ya</b> untuk memperbarui SSL <br>
                    di semua {{ str_replace('-', ' ', $table ) }} <i class="fa fa-warning"></i>
                </div>
                <div class="clear"></div>

                <div class="d-flex justify-content-between">
                    <div>
                        <br>
                        <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal">Tidak</button>
                    </div>
                    <div class="mx-1"></div>
                    <div>
                        <form action="{{ route('pelanggan.aktifSsl') }}" method="post">
                            @csrf
                            <br>
                            <button type="submit" class="btn btn-success btn-block"><i class="fa fa-repeat me-1"></i>  Ya</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
