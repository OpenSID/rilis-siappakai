<x-app-layout title="{{ ucwords(str_replace('-', ' ', $table )) }}">

    @section('breadcrumbs')
        <x-breadcrumbs navigations="Dasbor" active="{{ ucwords(str_replace('-', ' ', $table )) }}"></x-breadcrumbs>
    @endsection

    @section('content')
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data {{ ucwords(str_replace('-', ' ', $table )) }}</h3>

                        <!-- Tombol Tambah Data -->
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('pelanggan.create') }}" class="btn btn-success {{env('OPENKAB') == 'true' ? 'd-inline' : 'd-none'}}"><i class="fa fa-plus-circle me-2"></i>Tambah</a>
                        </div>
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
                                            <option value="" readonly>-- Masa Aktif Layanan OpenSID Siap Pakai --</option>
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
                            <livewire:pelanggan.table-pelanggan :remain="$remain">
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
