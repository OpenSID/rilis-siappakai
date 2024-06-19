<div class="p-4">
    <table>
        <tr>
            <td align="left"><span>Kode Desa</span></td>
            <td><span>:</span></td>
            <td align="left">
                <div class="col-md-12 col-sm-12">
                    <input id="kode_desa" name="kode_desa" class="form-control" value="{{ $data->kode_desa ?? '' }}"
                        disabled>
                </div>
            </td>
        </tr>
        <tr>
            <td align="left"><span>Nama Domain</span></td>
            <td><span>:</span></td>
            <td align="left">
                <div class="col-md-12 col-sm-12">
                    <input id="nama_desa" name="nama_desa" class="form-control"
                        value="{{ $data->domain_opensid ?? '' }}" disabled>
                </div>
            </td>
        </tr>

        <!-- Pilih Tema Pro -->
        <tr>
            <td align="left"><span>Tema</span></td>
            <td><span>:</span></td>
            <td align="left">
                <div class="col-md-12 col-sm-12 d-flex">
                    <select wire:model="selectedTema" id="tema_id" name="tema_id"
                        class="form-select @error('tema_id') is-invalid @enderror" autocomplete="off">
                        <option value="" readonly>-- {{ $temas != '[]' ? 'Pilih Tema' : 'Belum Berlangganan Tema' }} --
                        </option>
                        @foreach ($temas as $index => $item)
                        <option value="{{ $item["nama"] }}">
                            {{ $item["nama"] }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </td>
        </tr>

        <tr>
            <td align="left" width="45%"><span>Apakah akan menyimpan pengaturan konfigurasi tema ?</span></td>
            <td width="2%"><span>:</span></td>
            <td align="left" width="53%">
                <button wire:click="SimpanTema()" type="button"
                    class="btn btn-success mx-2 {{ $aktif == 'true' ? '' : 'disabled' }}" data-bs-toggle="tooltip"
                    data-bs-placement="top" title="Simpan pengaturan email">
                    <i class="fa fa-window-restore me-2" aria-hidden="true"></i> Ya
                </button>
                <button wire:click="$emit('closeModal')" type="button" class="btn btn-secondary"
                    data-dismiss="modal">Batal</button>
            </td>
        </tr>
    </table>
    @if (session()->has('message-success'))
    <div class="text-center alert alert-success mt-3">
        {{ session('message-success') }}
    </div>
    @elseif(session()->has('message-failed'))
    <div class="text-center alert alert-danger mt-3">
        {{ session('message-failed') }}
    </div>
    @endif
</div>