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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#list_provinsi').select2({
                ajax: {
                    url: '{{ $koneksiPantau }}',
                    dataType: 'json',
                    delay: 400,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page || 1,
                        };
                    },
                    processResults: function(response, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(response.results, function (item) {
                                return {
                                    id: `${item.kode_desa} , ${item.kode_prov} , ${item.nama_prov} , ${item.kode_kab} , ${item.nama_kab}`,
                                    text: `${item.nama_kab}, PROVINSI ${(item.nama_prov).toUpperCase()}`,
                                }
                            }),
                            pagination: response.pagination
                        };
                    },
                    cache: true
                }
            });

            $('#list_provinsi').change(function () {
                $("#wilayah").val($('#list_provinsi').val());
            });
        })
    </script>
@endpush
