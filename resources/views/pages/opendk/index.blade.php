<x-app-layout title="{{ ucwords(str_replace('-', ' ', $table)) }}">

    @section('breadcrumbs')
        <x-breadcrumbs navigations="Dasbor" active="{{ ucwords(str_replace('-', ' ', $table)) }}"
            link="{{ route('dasbor') }}"></x-breadcrumbs>
    @endsection

    @section('content')
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data {{ ucwords(str_replace('-', ' ', $table)) }}</h3>

                        <!-- Tombol Tambah Data -->
                        <div class="d-flex justify-content-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-success dropdown-toggle me-2"
                                    data-toggle="dropdown" aria-expanded="false" data-toggle="tooltip"
                                    data-bs-placement="top" title="Tombol Aksi Secara Keseluruhan">
                                    <i class="fa fa-wrench" aria-hidden="true"></i>
                                </button>
                                {{-- <ul class="dropdown-menu">
                                    <li>
                                        <button type="button"
                                            class="dropdown-item {{ $openkab == 'true' ? '' : 'd-none' }}"
                                            data-toggle="modal" data-target="#mundurVersi-global" data-toggle="tooltip"
                                            data-bs-placement="top" title="Mundur versi sebelumnya">Mundur Versi</button>
                                    </li>
                                </ul> --}}
                            </div>
                            <a href="{{ route('opendk.create') }}"
                                class="btn btn-success {{ env('OPENKAB') == 'true' ? 'd-inline' : 'd-none' }}"
                                data-toggle="tooltip" data-bs-placement="top"
                                title="Tambah {{ ucwords(str_replace('-', ' ', $table)) }}">
                                <i class="fa fa-plus-circle me-2"></i>Tambah
                            </a>
                        </div>

                        <!-- Modal Aktifkan SSL -->
                        @include('layouts.modals.aktifkan-ssl', ['table' => $table])

                        <!-- Modal Mundur Versi-->
                        {{-- @if ($openkab == 'true')
                            <div class="modal fade" id="mundurVersi-global" data-bs-backdrop="static"
                                data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel"
                                aria-hidden="true">
                                <livewire:pelanggan.modal-mundur-versi :data="$data">
                            </div>
                        @else
                            <div class="modal fade" id="modalMasal" data-bs-backdrop="static" data-bs-keyboard="false"
                                tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <livewire:pelanggan.modal-masal>
                            </div>
                        @endif --}}
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <livewire:opendk.table-opendk :sebutan="$table">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('scripts')
        <!--  Datatables -->
        @include('layouts.includes._scripts-datatable')

        <script>
            $('#check-all').click(function() {
                $('.checkBoxClass:not(:disabled)').prop('checked', $(this).prop('checked'));
            });

            // Wait for Livewire to be fully loaded
            document.addEventListener('livewire:load', function () {
                initDataTable();

                Livewire.hook('message.sent', () => {
                    if ($.fn.DataTable.isDataTable('#datatable')) {
                        $('#datatable').DataTable().destroy();
                    }
                });

                Livewire.hook('message.processed', () => {
                    initDataTable();
                });
            });

            function initDataTable() {
                if (!$.fn.DataTable.isDataTable('#datatable')) {
                    $('#datatable').DataTable({
                        responsive: true,
                        pageLength: 10,
                        autoWidth: false
                    });
                }
            }

            // Wait for Livewire to be loaded before setting up event handlers
            document.addEventListener('livewire:load', function () {
                $(".filter").on('change', function() {
                    let provinsi = $('#filter_provinsi').val()
                    let kabupaten = $('#filter_kabupaten').val()

                    Livewire.dispatch('setPilihKabupaten', { kabupaten: kabupaten });
                    Livewire.dispatch('setPilihProvinsi', { provinsi: provinsi });
                })
            })

            var wilayah = @json($daftar_wilayah);

            $(document).ready(function() {
                $('#filter_provinsi').select2({
                    placeholder: 'Pilih Provinsi...',
                    ajax: {
                        transport: function(params, success, failure) {
                            // Memproses data JSON tanpa request AJAX
                            success(wilayah);
                        },
                        processResults: function(data) {
                            // Memfilter dan memetakan data menggunakan Lodash
                            var filteredData = _.chain(data)
                                .uniqBy('kode_provinsi')
                                .map(function(item) {
                                    return {
                                        id: item.kode_provinsi,
                                        text: item.nama_provinsi
                                    }; // Hanya ambil id dan text
                                })
                                .value();

                            // Mengembalikan data dalam format Select2
                            return {
                                results: filteredData
                            };
                        },
                        delay: 250 // Tambahkan delay jika perlu
                    }
                });

                $('#filter_provinsi').on('select2:select', function(e) {
                    $('#filter_kabupaten').prop('disabled', false);
                    if ($('#filter_kabupaten').hasClass('select2-hidden-accessible')) {
                        $('#filter_kabupaten').select2('destroy'); // Hapus instance Select2 sebelumnya
                    }

                    // Bersihkan pilihan dengan mengosongkan nilai elemen
                    $('#filter_kabupaten').val(null); // Set nilai ke null untuk membersihkan pilihan

                    // Jika perlu, kosongkan juga opsi di elemen select
                    $('#filter_kabupaten').empty();

                    initializeFilterKab();
                });

                function initializeFilterKab() {
                    $('#filter_kabupaten').select2({
                        placeholder: 'Pilih Kabupaten...',
                        ajax: {
                            transport: function(params, success, failure) {
                                // Memproses data JSON tanpa request AJAX
                                success(wilayah);
                            },
                            processResults: function(data) {
                                // Memfilter dan memetakan data menggunakan Lodash
                                var filteredData = _.chain(data)
                                    .filter(function(item) {
                                        return item.kode_provinsi === $('#filter_provinsi').val();
                                    })
                                    .uniqBy('kode_kabupaten')
                                    .map(function(item) {
                                        return {
                                            id: item.kode_kabupaten,
                                            text: item.nama_kabupaten
                                        }; // Hanya ambil id dan text
                                    })
                                    .value();

                                // Mengembalikan data dalam format Select2
                                return {
                                    results: filteredData
                                };
                            }
                        }
                    });
                }

                $(document).on('click', '.ubah-domain', function() {
                    var id = $(this).data('id')
                    Swal.fire({
                        title: "Masukan Domain Baru",
                        input: "text",
                        placeholder:"Nama Domain",
                        inputAttributes: {
                            autocapitalize: "off"
                        },
                        showCancelButton: true,
                        confirmButtonText: "Simpan",
                        showLoaderOnConfirm: true,
                        preConfirm: async (domainBaru) => {
                            try {
                                const url = `{{ route('opendk.updateDomain') }}`;

                                const response = await fetch(url, {
                                    method: "POST", // Menggunakan POST
                                    headers: {
                                        "Content-Type": "application/json",
                                        "Accept": "application/json",
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}" // Token CSRF
                                    },
                                    body: JSON.stringify({
                                        domain: domainBaru,
                                        id : id
                                    }) // Data yang dikirim
                                });

                                if (!response.ok) {
                                    const error = await response.json();
                                    console.error(error);
                                    return Swal.showValidationMessage(
                                        `Error: ` + error.message);
                                }

                                return response.json(); // Mengembalikan hasil respons
                            } catch (error) {
                                console.log(error)
                                Swal.showValidationMessage(`Gagal: ${error}`);
                            }
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (typeof Livewire !== 'undefined') {
                                Livewire.dispatch('$refresh');
                            }
                            Swal.fire({
                                title: `Berhasil`,
                                text: result.value.message
                            });

                        }
                    });
                })
            });
        </script>
    @endpush

</x-app-layout>
