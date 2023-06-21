<div>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between">
                <h5 class="modal-title" id="staticBackdropLabel">Unduh Folder Desa</h5>
                <button wire:click="Batal()" type="button" class="btn-close btn-sm" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table>
                    <tr>
                        <td align="left" width="30%"><span>Apakah Anda akan mengunduh folder desa ?</span></td>
                        <td align="left" width="2%"><span>:</span></td>
                        <td align="left">
                            <div wire:loading>
                                Persiapan File Unduh .......
                            </div>
                            <div wire:loading.remove>
                                <button wire:click="unduhFolderDesa()" type="button" class="btn btn-success me-2" {{ $show == 'true' ? '' : 'disabled' }}
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Perbarui Token">
                                    <i class="fa fa-window-restore me-2" aria-hidden="true"></i> Unduh
                                </button>
                                <button wire:click="Batal()" type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            </div>
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
        // close Modal Unduh Folder Desa
        window.addEventListener('closeModalUnduhFolderDesa-{{ $data->id }}', event => {
            $("#unduhFolderDesa-{{ $data->id }}").modal('hide');
            $('.modal-backdrop').remove();
        })

        // open Modal Unduh Folder Desa
        window.addEventListener('openModalUnduhFolderDesa-{{ $data->id }}', event => {
            $("#unduhFolderDesa-{{ $data->id }}").modal('show');
        })
    </script>
@endpush
