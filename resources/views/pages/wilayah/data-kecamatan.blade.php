<div class="item form-group d-flex mb-2">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="kode_kec">Nama {{ $sebutankecamatan }}<span class="">*</span></label>
    <div class="col-md-8 col-sm-8 me-2">
        <div wire:ignore>
            <select class="form-select" name="kode_kec" id="kode_kec" style="width: 100%"></select>
        </div>
    </div>
</div>

<input type="hidden" id="sebutan_kecamatan" value="{{ ucwords(strtolower($sebutankecamatan)) }}">
<input type="hidden" id="sebutan_kab" value="{{ $sebutankab }}">

@push('scripts')
    <!-- Select 2 -->
    <script>
        $(document).ready(function () {
            $('#kode_kec').select2({
                ajax: {
                    url: '{{ $dataWilayah }}',
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
                                const sebutankecamatan = $('#sebutan_kecamatan').val();
                                const sebutankab = $('#sebutan_kab').val();

                                return {
                                    id: item.kode_kec,
                                    text: `${sebutankecamatan} ${item.nama_kec}, Kabupaten ${item.nama_kab}`,
                                }
                            }),
                            pagination: response.pagination
                        };
                    },
                    cache: true
                }
            });

            // Wait for Livewire to be loaded before setting up event handlers
            document.addEventListener('livewire:load', function () {
                $('#kode_kec').change(function () {
                    Livewire.dispatch('getKecamatan', { value: $('#kode_kec').val() });
                });
            });
        })

        //reset value in select2 (kosongkan)
        $( "#reset-btn" ).click(function() {
            $('#kode_kec').val('1').change();
        });
    </script>
@endpush
