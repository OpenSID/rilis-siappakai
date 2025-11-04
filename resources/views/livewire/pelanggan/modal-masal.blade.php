<div>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between">
                <h5 class="modal-title" id="staticBackdropLabel">Mundur Versi Masal</h5>
                <button type="button" class="btn-close btn-sm" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin akan melakukan mundur versi masal?</p>
                <p><span id="jmlDesa">0</span> Desa terpilih</p>
                <div>
                    <button wire:click="mundurVersi()" type="button" id="mundurButton" class="btn btn-success me-2"
                        data-toggle="tooltip" data-bs-placement="top" title="Mundur versi sebelumnya">
                        <i class="fa fa-window-restore me-2" aria-hidden="true"></i> Ya
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    $('#mundurVersi-masal').on('shown.bs.modal', function() {
        const checkBoxs = document.querySelectorAll('[name="ids"]');
        const checkedItems = [];

        checkBoxs.forEach(element => {
            if (element.checked) {
                checkedItems.push(element.value);
            }
        });

        const jml = checkedItems.length;
        const wireClickAttribute = "mundurVersi(" + JSON.stringify(checkedItems) + ")";
        const jmlDesa = document.getElementById('jmlDesa');
        jmlDesa.innerHTML = jml
        const mundurButton = document.getElementById('mundurButton');
        mundurButton.disabled = false;
        if (jml < 1) {
            mundurButton.disabled = true;
        }
        mundurButton.setAttribute('wire:click', wireClickAttribute);
    });
</script>
@endpush