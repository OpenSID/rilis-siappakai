<x-app-layout title="{{ ucwords(str_replace('-', ' ', $table )) }}">

    @section('breadcrumbs')
        <x-breadcrumbs navigations="Dasbor" active="{{ ucwords(str_replace('-', ' ', $table )) }}" link="{{ route('dasbor') }}"></x-breadcrumbs>
    @endsection

    @section('content')
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data {{ ucwords(str_replace('-', ' ', $table )) }}</h3>

                        <!-- Tombol Tambah Data -->
                        <div class="d-flex justify-content-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-success dropdown-toggle me-2" data-bs-toggle="dropdown" aria-expanded="false"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Tombol Aksi Secara Keseluruhan">
                                    <i class="fa fa-wrench" aria-hidden="true"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><button class="dropdown-item" data-toggle="modal" data-target="#{{ $table }}-konfigurasi-ftp"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Pembaruan FTP">Pembaruan FTP</button>
                                    </li>
                                    <li><button class="dropdown-item {{ $pengaturan_domain == 'apache' ? '' : 'd-none'}}" data-toggle="modal" data-target="#{{ $table }}-aktifkan-ssl"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Pembaruan SSL">Pembaruan SSL</button>
                                    </li>
                                    <li><button type="button" class="dropdown-item {{ $openkab == 'true' ? '' : 'd-none'}}"
                                        data-toggle="modal" data-target="#mundurVersi-global"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Mundur versi sebelumnya">Mundur Versi</button>
                                    </li>
                                    <li><button type="button" class="dropdown-item {{ $openkab == 'true' ? 'd-none' : ''}}""
                                        data-toggle="modal" data-target="#mundurVersi-masal"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Mundur versi masal">Mundur Versi Masal</button>
                                    </li>
                                    <li>
                                        <form action="{{ route('pelanggan.unduhDatabaseGabungan') }}" method="post">
                                            @csrf
                                            <button type="submit" class="dropdown-item {{ $openkab == 'true' ? '' : 'd-none'}}" {{ file_exists($filename) ? '' : $tombolNonAktif }}>Unduh Database Gabungan</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                            <a href="{{ route('pelanggan.create') }}" class="btn btn-success {{env('OPENKAB') == 'true' ? 'd-inline' : 'd-none'}}"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Tambah {{ ucwords(str_replace('-', ' ', $table )) }}">
                                <i class="fa fa-plus-circle me-2"></i>Tambah
                            </a>
                        </div>

                        <!-- Modal Konfigurasi FTP -->
                        @include('layouts.modals.konfigurasi-ftp', ['table' => $table])

                        <!-- Modal Aktifkan SSL -->
                        @include('layouts.modals.aktifkan-ssl', ['table' => $table])

                        <!-- Modal Mundur Versi-->
                        @if($openkab == 'true')
                            <div class="modal fade" id="mundurVersi-global" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <livewire:pelanggan.modal-mundur-versi :data="$data">
                            </div>
                        @else
                            <div class="modal fade" id="mundurVersi-masal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <livewire:pelanggan.modal-masal>
                            </div>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="col-md-1">
                                <span class="fw-bold">Filter :</span>
                            </div>
                            <div class="col-md-11">
                                <div class="row">
                                    <div class="col-md-4">
                                        <select id="filter_langganan" name="filter_langganan" class="form-select filter">
                                            <option value="" readonly>-- Langganan OpenSID --</option>
                                            @foreach ($pilihLangganan as $item)
                                                <option value="{{ $item['value'] }}">
                                                    {{ $item['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select id="filter_status_opensid" name="filter_status_opensid" class="form-select filter">
                                            <option value="" readonly>-- Status Langganan OpenSID --</option>
                                            @foreach ($pilihStatus as $item)
                                                <option value="{{ $item['value'] }}">
                                                    {{ $item['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select id="filter_status_saas" name="filter_status_saas" class="form-select filter">
                                            <option value="" readonly>-- Status Langganan Layanan --</option>
                                            @foreach ($pilihStatus as $item)
                                                <option value="{{ $item['value'] }}">
                                                    {{ $item['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mt-3">
                                        <select id="filter_masa_aktif" name="filter_masa_aktif" class="form-select filter">
                                            <option value="" readonly>-- Masa Aktif Dasbor SiapPakai --</option>
                                            @foreach ($pilihMasaAktif as $item)
                                                <option value="{{ $item['value'] }}" {{ ($remain && $item['value'] == 2)? 'selected': ''; }}>
                                                    {{ $item['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="table-responsive">
                            <livewire:pelanggan.table-pelanggan :remain="$remain" :sebutan="$table">
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
            $('#check-all').click(function(){
                $('.checkBoxClass:not(:disabled)').prop('checked', $(this).prop('checked'));
            });

            Livewire.onLoad((e) => {
                $('#tabel-pelanggan').DataTable();
            })

            document.addEventListener("DOMContentLoaded", () => {
                Livewire.hook('message.processed', (message, component) => {
                    $('#tabel-pelanggan').DataTable().draw();
                })
                Livewire.hook('message.sent', (message, component) => {
                    $('#tabel-pelanggan').DataTable().destroy();
                })
            });

            $(".filter").on('change', function(){

                let langganan = $('#filter_langganan').val()
                let status_opensid = $('#filter_status_opensid').val()
                let status_saas = $('#filter_status_saas').val()
                let masa_aktif = $('#filter_masa_aktif').val()
                livewire.emit('setPilihLangganan', langganan);
                livewire.emit('setPilihStatusLanggananOpenSID', status_opensid);
                livewire.emit('setPilihStatusLanggananSaas', status_saas);
                livewire.emit('setPilihMasaAktif', masa_aktif);
            })
        </script>
    @endpush

</x-app-layout>
