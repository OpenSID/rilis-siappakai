<div>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between">
                <h5 class="modal-title" id="staticBackdropLabel">Pembaruan Token</h5>
                <button wire:click="Batal" type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table>
                    <tr>
                        <td align="left">
                            <span>Token Saat ini</span><br><br>
                            <span class="text-secondary">(Perbarui config dan env menggunakan token saat ini)</span>
                        </td>
                        <td align="left" width="2%"><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <textarea wire:model="tokenConfig" class="form-control {{ ($tokenConfig ? 'd-inline' : 'd-none') }} text-primary" id="tokenConfig" style="height: 150px" disabled>{{ $tokenConfig }}</textarea>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left">
                            <span>Token Baru</span><br><br>
                            <span class="text-secondary">(Perbarui config dan env menggunakan token baru)</span>
                        </td>
                        <td align="left" width="2%"><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <textarea wire:model.defer="tokenBaru" class="form-control text-primary" id="tokenBaru" style="height: 150px">{{ $tokenBaru }}</textarea>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" width="30%"><span>Apakah Anda yakin akan memperbarui token ?</span></td>
                        <td align="left" width="2%"><span>:</span></td>
                        <td align="left">
                            <button wire:click="pembaruanToken" type="button" class="btn btn-success me-2"
                                data-toggle="tooltip" data-bs-placement="top" title="Perbarui Token">
                                <i class="fa fa-window-restore me-2" aria-hidden="true"></i> Ya
                            </button>
                            <button wire:click="Batal" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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
        // close Modal Pengaturan Email - Bootstrap 5 API
        window.addEventListener('closeModalPembaruanToken-{{ $data->id }}', event => {
            const el = document.getElementById('pembaruanToken-{{ $data->id }}');
            if (!el) return;
            const modal = window.bootstrap?.Modal.getOrCreateInstance(el);
            modal?.hide();
            // Fallback cleanup
            document.querySelectorAll('.modal-backdrop')?.forEach(b => b.remove());
            el.classList.remove('show');
            el.setAttribute('aria-hidden', 'true');
            el.style.display = 'none';
        })
    </script>
@endpush
