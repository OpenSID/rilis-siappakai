@push('scripts')
    <script>
        $(document).ready(function() {
            // Menambahkan opsi default ke select2 dan memicu perubahan
            var defaultOption = new Option('{{ $data->value }}', '{{ $data->value }}', true, true);
            $('select[name="pengaturan_wilayah"]').append(defaultOption).trigger('change');

            // Inisialisasi select2 dengan tema bootstrap4 dan konfigurasi AJAX
            $('select[name="pengaturan_wilayah"]').select2({
                theme: 'bootstrap4',
                ajax: {
                    url: '{{ $koneksiPantau }}',
                    delay: 400, // Menunda permintaan AJAX selama 400ms
                    data: function(params) {
                        // Mengirimkan parameter pencarian dan halaman
                        return {
                            q: params.term,
                            page: params.page || 1,
                        };
                    },
                    processResults: function(response, params) {
                        params.page = params.page || 1;
                        // Memproses hasil dari respons AJAX
                        return {
                            results: $.map(response.results, function(item) {
                                return {
                                    id: `${item.nama_kab}, PROVINSI ${(item.nama_prov).toUpperCase()}`,
                                    text: `${item.nama_kab}, PROVINSI ${(item.nama_prov).toUpperCase()}`,
                                    data: item
                                }
                            }),
                            pagination: response.pagination
                        };
                    },
                    cache: true // Mengaktifkan cache untuk permintaan AJAX
                }
            });

            // Event handler untuk saat opsi dipilih di select2
            $('select[name="pengaturan_wilayah"]').on('select2:select', function(e) {
                var selected = e.params.data;
                
                // Memisahkan kode desa untuk mendapatkan kode provinsi dan kabupaten
                var wilayah = selected.data.kode_desa.split(".");
 
                // Mengisi input dengan data yang dipilih
                $('input[name="kode_desa"]').val(selected.text);
                $('input[name="kode_wilayah"]').val(selected.data.kode_desa);
                $('input[name="kode_provinsi"]').val(wilayah[0]);
                $('input[name="kode_kabupaten"]').val(wilayah[1]);
                $('input[name="nama_provinsi"]').val(selected.data.nama_prov);
                $('input[name="nama_kabupaten"]').val(selected.data.nama_kab);
                $('input[name="nama_wilayah"]').val(selected.text);
            });
        });
    </script>
@endpush
