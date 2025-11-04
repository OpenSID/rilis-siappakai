<div class="modal fade text-start" id="{{ $table}}-perbarui-token" tabindex="-1" role="dialog" aria-labelledby="{{ $table}}-perbarui-tokenLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $table}}-perbarui-tokenLabel">Apakah Anda Yakin Memperbarui Token ?</h5>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    Tekan tombol <b>Ya</b> untuk memperbarui Token <br>
                    di semua {{ str_replace('-', ' ', $table ) }} <i class="fa fa-warning"></i>
                </div>
                <div class="clear"></div>

                <div class="d-flex justify-content-between">
                    <div>
                        <br>
                        <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Tidak</button>
                    </div>
                    <div class="mx-1"></div>
                    <div>
                        <form action="{{ route('pelanggan.updateTokenMasal') }}" method="post">
                            @csrf
                            <br>
                            <button type="submit" class="btn btn-success w-100"><i class="fa fa-repeat me-1"></i>  Ya</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
