<div class="item form-group d-flex mb-2">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="kode_desa">Nama {{ $sebutandesa }}<span class="">*</span></label>
    <div class="col-md-8 col-sm-8 me-2">
        <div wire:ignore>
            <select class="form-select" name="kode_desa" id="kode_desa" style="width: 100%"></select>
        </div>
    </div>
</div>

<input type="hidden" id="sebutan_desa" value="{{ $sebutandesa }}">
<input type="hidden" id="sebutan_kab" value="{{ $sebutankab }}">

@push('scripts')
    <!-- Select 2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#kode_desa').select2({
                ajax: {
                    url: '{{ $koneksiPantau }}' + '&kode={{ $kode_kab }}',
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
                                const sebutandesa = $('#sebutan_desa').val();
                                const sebutankab = $('#sebutan_kab').val();
                                var kab = (item.nama_kab).toLowerCase();
                                var ucwords_kab = kab.replace(/\b[a-z]/g, function(letra) {
                                    return letra.toUpperCase();
                                });

                                return {
                                    id: item.kode_desa,
                                    text: `${sebutandesa} ${item.nama_desa}, Kecamatan ${item.nama_kec}, ${sebutankab} ${ucwords_kab}, Provinsi ${item.nama_prov}`,
                                }
                            }),
                            pagination: response.pagination
                        };
                    },
                    cache: true
                }
            });

            $('#kode_desa').change(function () {
                livewire.emit('getDesa', $('#kode_desa').val());
            });
        })

        //reset value in select2 (kosongkan)
        $( "#reset-btn" ).click(function() {
            $('#kode_desa').val('1').change();
        });
    </script>
@endpush
