<div>
    <table id="datatable" class="table table-striped table-bordered datatable">
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
                <th rowspan="2" class="text-center" style="vertical-align : middle;">Masa Berlaku SSL</th>
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
                <th class="text-center" style="vertical-align : middle;">SiapPakai</th>
            </tr>
        </thead>

        <!-- Isi data dalam tabel -->
        <tbody>
            @foreach($pelanggans as $index => $item)
            <tr id="sid{{ $item->id }}">
                @php
                $filename = $pathDB . DIRECTORY_SEPARATOR . "db_" . str_replace('.', '', $item->kode_desa);
                $filezip = $pathDesa . DIRECTORY_SEPARATOR . "desa_" . str_replace('.', '', $item->kode_desa);
                @endphp
                <td class="text-center" style="vertical-align : middle;"><input type="checkbox" name="ids" class="checkBoxClass" value="{{ $item->kode_desa }}" {{ file_exists($filename . '.sql') ? '' : $tombolNonAktif }}></td>
                <td class="text-center" style="vertical-align : middle;">{{ $index + 1 }}</td>
                <td class="text-center" style="vertical-align : middle;">
                    <div class="d-flex mt-2">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-info dropdown-toggle me-2" data-bs-toggle="dropdown" aria-expanded="false"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Unduh Backup">
                                <i class="fa fa-download" aria-hidden="true"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><button class="dropdown-item {{ $openkab == 'true' ? 'd-none' : ''}}" wire:click="unduhDatabaseOpensid({{ $item }})" {{ file_exists($filename . '.sql') ? '' : $tombolNonAktif }}>Unduh Database OpenSID</button></li>
                                <li><button class="dropdown-item" wire:click="unduhDatabasePbb({{ $item }})" {{ file_exists($filename . '_pbb.sql') ? '' : $tombolNonAktif }}>Unduh Database PBB</button></li>
                                <li><button class="dropdown-item" wire:click="unduhFolderDesa({{ $item }})" {{ file_exists($filezip) ? '' : $tombolNonAktif }}>Unduh Folder Desa</button></li>
                            </ul>
                        </div>
                        <!-- Tombol Aktivasi Tema-->
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle me-2" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-wrench" aria-hidden="true"></i>
                            </button>
                            <ul class="dropdown-menu">
                                @if($openkab == 'true')
                                <li>
                                    <!-- Tombol Pengaturan Email API -->
                                    <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#pengaturanEmail-{{ $item->id }}"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Pengaturan Email API">
                                        Pengaturan Email API
                                    </button>
                                </li>
                                @endif
                                <li>
                                    <!-- Tombol Pengaturan Email OpenSID -->
                                    <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#pengaturanEmailOpenSID-{{ $item->id }}"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Pengaturan Email OpenSID">
                                        Pengaturan Email OpenSID
                                    </button>
                                </li>

                                @if ($item->langganan_opensid == 'umum' && $opensid != 2)
                                <li>
                                    <!-- Tombol Pengaturan Email OpenSID -->
                                    <button class="dropdown-item ubah-domain" data-id="{{ $item->id }}" title="Ubah Domain OpenSID">
                                        Ubah Domain
                                    </button>
                                </li>
                                @endif
                            </ul>
                        </div>



                        @if($openkab == 'true')
                        <!-- Tombol Hapus Desa -->
                        <button type="button" class="btn btn-sm btn-danger me-2" data-bs-toggle="modal" data-bs-target="#{{ $table }}-{{ $item->id }}"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Desa/Kelurahan">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button>

                        <!-- Modal Hapus Data -->
                        @include('layouts.modals.delete', ['table' => $table , 'data' => $item])
                        @else
                        <!-- Tombol Pengaturan Email API -->
                        <button type="button" class="btn btn-sm btn-warning me-2" data-bs-toggle="modal" data-bs-target="#pengaturanEmail-{{ $item->id }}"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Pengaturan Email API">
                            <i class="fa fa-check-circle" aria-hidden="true"></i>
                        </button>
                        @endif

                        <!-- Tombol Pembaruan Token -->
                        <button type="button" class="btn btn-sm btn-success me-2" data-bs-toggle="modal" data-bs-target="#pembaruanToken-{{ $item->id }}"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Pembaruan Token">
                            <i class="fas fa-retweet" aria-hidden="true"></i>
                        </button>
                    </div>

                    <!-- Modal Mundur Versi-->
                    <div class="modal fade" id="mundurVersi-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <livewire:pelanggan.modal-mundur-versi :data="$item" :wire:key="$item->id">
                    </div>

                    <!-- Modal Pengaturan Email-->
                    <div class="modal fade" id="pengaturanEmail-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <livewire:pelanggan.modal-pengaturan-email :data="$item" :sebutan="$sebutan" :wire:key="'modal-pengaturan-email-'.$item->id">
                    </div>

                    <!-- Modal Pengaturan Email OpenSID-->
                    <div class="modal fade" id="pengaturanEmailOpenSID-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <livewire:pelanggan.modal-pengaturan-email-opensid :data="$item" :sebutan="$sebutan" :wire:key="'modal-pengaturan-email-opensid-'.$item->id">
                    </div>

                    <!-- Modal Pembaruan Token -->
                    <div wire:ignore.self class="modal fade" id="pembaruanToken-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <livewire:pelanggan.modal-pembaruan-token :data="$item" :wire:key="'modal-pembaruan-token-'.$item->id" />
                    </div>

                    <!-- Modal Unduh Folder Desa-->
                    <div class="modal fade" id="unduhFolderDesa-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <livewire:pelanggan.modal-unduh-folder-desa :data="$item" :wire:key="'modal-unduh-folder-desa-'.$item->id">
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
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm dropdown-toggle me-2 btn-{{ ($item->getRemainingSslAttribute() > 0) ? 'success' : 'danger' }}" data-bs-toggle="dropdown" aria-expanded="false"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Generate SSL">
                                <i class="fa fa-{{ ($apacheConfDir . $item['domain_opensid'] . $cert) ? 'lock' : 'unlock' }}" aria-hidden="true"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <!-- Tombol Generate Let's Encrypt -->
                                    <button class="dropdown-item" wire:click="statusSSL({{ $item }})" type="button"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="{{ ($apacheConfDir . $item['domain_opensid'] . $cert) ? 'Non Aktifkan SSL' : 'Aktifkan SSL' }}">
                                        Generate Let’s Encrypt
                                    </button>
                                </li>
                                <li>
                                    <!-- Tombol Generate Wildcard-->
                                    <button class="dropdown-item" wire:click="statusSSLWildcard({{ $item }})" type="button"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="{{ ($apacheConfDir . $item['domain_opensid'] . $cert) ? 'Non Aktifkan SSL' : 'Aktifkan SSL' }}">
                                        Generate Wildcard
                                    </button>
                                </li>
                            </ul>
                        </div>
                    @endif
                </td>
                <td class="text-center" style="vertical-align : middle;">
                    {!! $item->jenis_ssl ? '<span class="badge bg-secondary">'.ucfirst($item->jenis_ssl).'</span><br>' : '' !!}
                    {{ !is_null($item->tgl_akhir) ? Carbon\Carbon::createFromFormat('Y-m-d', $item->tgl_akhir)->isoFormat('D MMMM Y') : '' }}

                    @php $r = $item->getRemainingSslAttribute(); @endphp

                    @if(is_null($r) || $r == 0)
                        <span class="badge bg-info">Tidak aktif</span>
                    @elseif($r < 0)
                        <span class="badge bg-danger">{{ $r }} hari lalu</span>
                    @elseif($r <= 30 && $r > 0)
                        <span class="badge bg-warning text-dark">{{ $r }} hari lagi</span>
                    @elseif($r > 30)
                        <span class="badge bg-success">{{ $r }} hari lagi</span>
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
                    @if($item->status_langganan_opensid == 3 || (strtotime($item->tgl_akhir_premium) <= strtotime('now')))
                        <span class="badge bg-info text-dark">Tidak Aktif</span>
                        @elseif($item->status_langganan_opensid == 2)
                        <span class="badge bg-danger">Suspended</span>
                        @elseif($item->status_langganan_opensid == 1)
                        <span class="badge bg-success">Aktif</span>
                        @endif
                </td>
                <td class="text-start" style="vertical-align : middle;">
                    @if($item->status_langganan_saas == 3 || (strtotime($item->tgl_akhir_saas) <= strtotime('now')))
                        <span class="badge bg-info text-dark">Tidak Aktif</span>
                        @elseif($item->status_langganan_saas == 2)
                        <span class="badge bg-danger">Suspended</span>
                        @elseif($item->status_langganan_saas == 1)
                        <span class="badge bg-success">Aktif</span>
                        @endif
                </td>
                <td class="text-center" style="vertical-align : middle;">
                    {{ !is_null($item->tgl_akhir_premium) ? Carbon\Carbon::createFromFormat('Y-m-d', $item->tgl_akhir_premium)->isoFormat('D MMMM Y') : '' }}
                </td>
                <td class="text-center" style="vertical-align : middle;">
                    {{ !is_null($item->tgl_akhir_saas) ? Carbon\Carbon::createFromFormat('Y-m-d', $item->tgl_akhir_saas)->isoFormat('D MMMM Y') : '' }}
                    @if (near_expired($item->getRemainingAttribute()))
                    <span class="badge bg-warning text-dark">{{$item->getRemainingAttribute()}} hari lagi</span>
                    @endif
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
