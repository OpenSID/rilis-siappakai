<div>
    <table id="tabel-pelanggan" class="table table-striped table-bordered datatable">
        <!-- Judul tabel -->
        <thead>
            <tr>
                <th rowspan="2" class="text-end" style="vertical-align : middle;"><input type="checkbox" id="check-all"></th>
                <th rowspan="2" class="text-center" style="vertical-align : middle;">No</th>
                <th rowspan="2" class="text-center" style="vertical-align : middle;">Aksi</th>
                <th rowspan="2" class="text-center" style="vertical-align : middle;">Kode Desa</th>
                <th rowspan="2" class="text-center" style="vertical-align : middle;">Nama Desa</th>
                <th rowspan="2" class="text-center" style="vertical-align : middle;">Langganan Opensid</th>
                <th rowspan="2" class="text-center" style="vertical-align : middle;">Versi Opensid</th>
                <th rowspan="2" class="text-center" style="vertical-align : middle;">Tema Pro</th>
                <th rowspan="2" class="text-center" style="vertical-align : middle;">{{ $port == 'proxy' ? 'Port Domain' : 'Status SSL' }}</th>
                <th colspan="3" class="text-center" style="vertical-align : middle;">Nama Domain</th>
                <th colspan="2" class="text-center" style="vertical-align : middle;">Status Langganan</th>
                <th colspan="2" class="text-center" style="vertical-align : middle;">Tanggal Berakhir</th>
                <th rowspan="2" class="text-center" style="vertical-align : middle;">Tanggal Terakhir Backup</th>
            </tr>
            <tr>
                <th class="text-center" style="vertical-align : middle;">OpenSID</th>
                <th class="text-center" style="vertical-align : middle;">PBB</th>
                <th class="text-center" style="vertical-align : middle;">API</th>
                <th class="text-center" style="vertical-align : middle;">OpenSID</th>
                <th class="text-center" style="vertical-align : middle;">SiapPakai</th>
                <th class="text-center" style="vertical-align : middle;">Premium</th>
                <th class="text-center" style="vertical-align : middle;">Saas</th>
            </tr>
        </thead>

        <!-- Isi data dalam tabel -->
        <tbody>
            @foreach($pelanggans as $index => $item)
                <tr id="sid{{ $item->id }}">
                    <td class="text-center" style="vertical-align : middle;"><input type="checkbox" name="ids" class="checkBoxClass" value="{{ $item->id }}"></td>
                    <td class="text-center" style="vertical-align : middle;">{{ $index + 1 }}</td>
                    <td class="text-center" style="vertical-align : middle;">
                        <div class="d-flex">
                            @php
                                $filename = $pathDB . DIRECTORY_SEPARATOR . "db_" . str_replace('.', '', $item->kode_desa)  . '.sql';
                                $filezip = $pathDesa . DIRECTORY_SEPARATOR . "desa_" . str_replace('.', '', $item->kode_desa);
                            @endphp

                            <!-- Tombol Backup Database dan Folder Desa -->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-purple dropdown-toggle me-2" data-bs-toggle="dropdown" aria-expanded="false"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Unduh Backup">
                                    <i class="fa fa-download" aria-hidden="true"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><button class="dropdown-item {{ $openkab == 'true' ? 'd-none' : ''}}" wire:click="unduhDatabase({{ $item }})" {{ file_exists($filename) ? '' : $tombolNonAktif }}>Unduh Database</button></li>
                                    <li><button class="dropdown-item" wire:click="unduhFolderDesa({{ $item }})">Unduh Folder Desa</button></li>
                                </ul>
                            </div>

                            <!-- Tombol Mundur Versi -->
                            <button type="button" class="btn btn-sm btn-orange me-2" data-toggle="modal" data-target="#mundurVersi-{{ $item->id }}"
                                {{ file_exists($filename) ? '' : $tombolNonAktif }} {{ file_exists($filezip) ? '' : $tombolNonAktif }}
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Mundur versi sebelumnya">
                                <i class="fa fa-window-restore" aria-hidden="true"></i>
                            </button>

                            <!-- Tombol Transfer Hosting -->
                            <button type="button" class="btn btn-sm btn-teal" data-toggle="modal" data-target="#pindahHosting-{{ $item->id }}"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Pindah Hosting">
                                <i class="fa fa-exchange" aria-hidden="true"></i>
                            </button>
                        </div>

                        <div class="d-flex mt-2">
                            <!-- Tombol Aktivasi Tema-->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle me-2" data-bs-toggle="dropdown" aria-expanded="false"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Tambah, Aktivasi dan Konfigurasi Tema Pro serta Email API">
                                    <i class="fa fa-wrench" aria-hidden="true"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <button class="dropdown-item" data-toggle="modal" data-target="#tambahTema-{{ $item->id }}"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Tambah Tema Pro">
                                            Tambah Tema Pro
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" data-toggle="modal" data-target="#aktivasiTema-{{ $item->id }}"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Aktivasi dan Konfigurasi Tema">
                                            Aktivasi dan Konfigurasi Tema
                                        </button>
                                    </li>
                                    @if($openkab == 'true')
                                        <li>
                                            <!-- Tombol Pengaturan Email API -->
                                            <button class="dropdown-item" data-toggle="modal" data-target="#pengaturanEmail-{{ $item->id }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Pengaturan Email API">
                                                Pengaturan Email API
                                            </button>
                                        </li>
                                    @endif
                                </ul>
                            </div>

                            @if($openkab == 'true')
                                <!-- Tombol Hapus Desa -->
                                <button type="button" class="btn btn-sm btn-danger me-2" data-toggle="modal" data-target="#{{ $table }}-{{ $item->id }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Desa/Kelurahan">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </button>

                                <!-- Modal Hapus Data -->
                                @include('layouts.modals.delete', ['table' => $table , 'data' => $item])
                            @else
                                <!-- Tombol Pengaturan Email API -->
                                <button type="button" class="btn btn-sm btn-warning me-2" data-toggle="modal" data-target="#pengaturanEmail-{{ $item->id }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Pengaturan Email API">
                                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                                </button>
                            @endif

                            <!-- Tombol Pembaruan Token -->
                            <button type="button" class="btn btn-sm btn-success me-2" data-toggle="modal" data-target="#pembaruanToken-{{ $item->id }}"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Pembaruan Token">
                                <i class="fa fa-repeat" aria-hidden="true"></i>
                            </button>
                        </div>

                        <!-- Modal Mundur Versi-->
                        <div class="modal fade" id="mundurVersi-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <livewire:pelanggan.modal-mundur-versi :data="$item" :wire:key="$item->id">
                        </div>

                        <!-- Modal Pengaturan Email-->
                        <div class="modal fade" id="pengaturanEmail-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <livewire:pelanggan.modal-pengaturan-email :data="$item" :wire:key="'modal-pengaturan-email-'.$item->id">
                        </div>

                        <!-- Modal Tambah Tema Pro-->
                        <div class="modal fade" id="tambahTema-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <livewire:pelanggan.modal-tambah-tema :data="$item" :wire:key="'modal-tambah-tema-'.$item->id">
                        </div>

                        <!-- Modal Aktivasi dan Konfigurasi Tema-->
                        <div class="modal fade" id="aktivasiTema-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <livewire:pelanggan.modal-aktivasi-tema :data="$item" :wire:key="'modal-aktivasi-tema-'.$item->id">
                        </div>

                        <!-- Modal Pembaruan Token -->
                        <div class="modal fade" id="pembaruanToken-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <livewire:pelanggan.modal-pembaruan-token :data="$item" :wire:key="'modal-pembaruan-token-'.$item->id">
                        </div>

                        <!-- Modal Pindah Hosting-->
                        <div class="modal fade" id="pindahHosting-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <livewire:pelanggan.modal-pindah-hosting :data="$item" :wire:key="'modal-pindah-hosting-'.$item->id">
                        </div>
                    </td>
                    <td class="text-center" style="vertical-align : middle;">{{ $item->kode_desa }}</td>
                    <td class="text-start" style="vertical-align : middle;">{{ $item->nama_desa }}</td>
                    <td class="text-center" style="vertical-align : middle;">{{ $item->langganan_opensid}}</td>
                    <td class="text-center" style="vertical-align : middle;">{{ $item->versi_opensid}}</td>
                    <td style="vertical-align : middle;">
                        @foreach ($item->temas as $data)
                            {{ ucwords($data->tema) }}
                        @endforeach

                    </td>
                    <td class="text-center" style="vertical-align : middle;">
                        @if($port == "proxy")
                            {{ $item->port_domain }}
                        @else
                            <button wire:click="statusSSL({{ $item }})" type="button" class="btn btn-sm btn-{{ file_exists($apacheConfDir . $item['domain_opensid'] . '-le-ssl.conf') ? 'success' : 'danger' }}"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="{{ file_exists($apacheConfDir . $item['domain_opensid'] . '-le-ssl.conf') ? 'Non Aktifkan SSL' : 'Aktifkan SSL' }}">
                                <i class="fa fa-{{ file_exists($apacheConfDir . $item['domain_opensid'] . '-le-ssl.conf') ? 'lock' : 'unlock' }}" aria-hidden="true"></i>
                            </button>
                        @endif
                    </td>
                    <td class="text-start" style="vertical-align : middle;"><a href="{{ substr($item->domain_opensid, 0, 8) == "https://" ? $item->domain_opensid : "https://".$item->domain_opensid }}" target="_blank">
                        {{ substr($item->domain_opensid, 0, 8) == "https://" ? $item->domain_opensid : "https://".$item->domain_opensid }}</a>
                    </td>
                    <td class="text-start" style="vertical-align : middle;"><a href="{{ substr($item->domain_pbb, 0, 8) == "https://" ? $item->domain_pbb : "https://".$item->domain_pbb }}" target="_blank">
                        {{ substr($item->domain_pbb, 0, 8) == "https://" ? $item->domain_pbb : "https://".$item->domain_pbb }}</a>
                    </td>
                    <td class="text-start" style="vertical-align : middle;"><a href="{{ substr($item->domain_api, 0, 8) == "https://" ? $item->domain_api : "https://".$item->domain_api }}" target="_blank">
                        {{ substr($item->domain_api, 0, 8) == "https://" ? $item->domain_api : "https://".$item->domain_api }}</a>
                    </td>
                    <td class="text-start" style="vertical-align : middle;">
                        @if($item->status_langganan_opensid == 1)
                            <span class="badge badge-success">Aktif</span>
                        @elseif($item->status_langganan_opensid == 2)
                            <span class="badge badge-danger">Suspended</span>
                        @elseif($item->status_langganan_opensid == 3)
                            <span class="badge badge-info">Tidak Aktif</span>
                        @endif
                    </td>
                    <td class="text-start" style="vertical-align : middle;">
                        @if($item->status_langganan_saas == 1)
                            <span class="badge badge-success">Aktif</span>
                        @elseif($item->status_langganan_saas == 2)
                            <span class="badge badge-danger">Suspended</span>
                        @elseif($item->status_langganan_saas == 3)
                            <span class="badge badge-info">Tidak Aktif</span>
                        @endif
                    </td>
                    <td class="text-center" style="vertical-align : middle;">
                        {{ Carbon\Carbon::createFromFormat('Y-m-d', $item->tgl_akhir_premium)->isoFormat('D MMMM Y'); }}
                    </td>
                    <td class="text-center" style="vertical-align : middle;">
                        {{ Carbon\Carbon::createFromFormat('Y-m-d', $item->tgl_akhir_saas)->isoFormat('D MMMM Y'); }}
                    </td>
                    <td class="text-center" style="vertical-align : middle;">
                        @if($item->tgl_akhir_backup)
                            {{ Carbon\Carbon::createFromFormat('Y-m-d', $item->tgl_akhir_backup)->isoFormat('D MMMM Y'); }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
