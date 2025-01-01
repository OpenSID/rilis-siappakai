<div class="item form-group d-flex mb-2">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="list_provinsi">Kabupaten/Kota</label>
    <div class="col-md-8 col-sm-8 me-2">
        <div wire:ignore>
            <select class="form-select" id="list_provinsi" name="list_provinsi" data-placeholder="Pilih Kabupaten/Kota" style="width: 100%">
                <option selected value="{{ $wilayah['kode_wilayah'] }}">{{ $wilayah['nama_wilayah'] }}</option>
            </select>
        </div>
    </div>
</div>

<div class="form-group row d-none">
    <label class="col-sm-3 col-form-label">Wilayah</label>
    <div class="col-sm-4">
        <div class="input-group">
            <input id="wilayah" name="wilayah" type="text" readonly class="form-control" value="{{ $wilayah['kode_wilayah'] . ' , ' . $wilayah['kode_provinsi']  . ' , ' . $wilayah['nama_provinsi'] . ' , ' . $wilayah['kode_provinsi']  . '.'. $wilayah['kode_kabupaten'] . ' , ' . $wilayah['nama_kabupaten']}}">
        </div>
    </div>
</div>

@push('scripts')
    <!-- Select 2 -->
    <script>
        $(document).ready(function () {
            $('#list_provinsi').select2({
                ajax: {
                    url: '{{ $koneksiPantau }}',
                    dataType: 'json',

                }
            });

            // $('#list_provinsi').change(function () {
            //     $("#wilayah").val($('#list_provinsi').val());
            // });
        })
    </script>
@endpush
