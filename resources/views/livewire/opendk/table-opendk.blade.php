<div>
    <table id="tabel-opendk" class="table table-striped table-bordered datatable">
        <!-- Judul tabel -->
        <thead>
            <tr>
                <th class="text-end" style="vertical-align : middle;"><input type="checkbox" id="check-all"></th>
                <th class="text-center" style="vertical-align : middle;">No</th>
                <th class="text-center" style="vertical-align : middle;">Aksi</th>
                <th class="text-center" style="vertical-align : middle;">Kode Kecamatan</th>
                <th class="text-center" style="vertical-align : middle;">Nama Kecamatan</th>
                <th class="text-center" style="vertical-align : middle;">{{ $port == 'proxy' ? 'Port Domain' : 'Status SSL' }}</th>
                <th class="text-center" style="vertical-align : middle;">Nama Domain</th>
                <th class="text-center" style="vertical-align : middle;">Tanggal Terakhir Backup</th>
            </tr>
        </thead>

        <!-- Isi data dalam tabel -->
        <tbody>
            @foreach($opendks as $index => $item)
                <tr id="sid{{ $item->id }}">
                    <td class="text-center" style="vertical-align : middle;"><input type="checkbox" name="ids" class="checkBoxClass" value="{{ $item->kode_desa }}"></td>
                    <td class="text-center" style="vertical-align : middle;">{{ $index + 1 }}</td>
                    <td class="text-center" style="vertical-align : middle;">
                        <div class="d-flex">
                            <!-- Tombol Mundur Versi -->
                            {{-- <button type="button" class="btn btn-sm btn-orange me-2" data-toggle="modal" data-target="#mundurVersi-{{ $item->id }}"
                                {{ $openkab == 'true' ? $tombolNonAktif : ''  }}
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Mundur versi sebelumnya">
                                <i class="fa fa-window-restore" aria-hidden="true"></i>
                            </button> --}}

                            <button type="button" class="btn btn-sm btn-info me-2" data-toggle="modal" data-target="#ubahDomain-{{ $item->id }}"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Ubah Domain">
                                <i class="fa fa-globe" aria-hidden="true"></i>
                            </button>

                            <!-- Tombol Hapus Desa -->
                            <button type="button" class="btn btn-sm btn-danger me-2" data-toggle="modal" data-target="#{{ $table }}-{{ $item->id }}"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Desa/Kelurahan">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </button>
                        </div>

                        <!-- Modal Mundur Versi-->
                        {{-- <div class="modal fade" id="mundurVersi-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <livewire:opendk.modal-mundur-versi :data="$item" :wire:key="$item->id">
                        </div> --}}

                        <div class="modal fade" id="ubahDomain-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <livewire:opendk.modal-ubah-domain :data="$item" :wire:key="$item->id">
                        </div>

                        <!-- Modal Hapus Data -->
                        {{-- @include('layouts.modals.delete', ['table' => $table , 'data' => $item]) --}}
                    </td>
                    <td class="text-center" style="vertical-align : middle;">{{ $item->kode_kecamatan }}</td>
                    <td class="text-start" style="vertical-align : middle;">{{ $item->nama_kecamatan }}</td>
                    <td class="text-center" style="vertical-align : middle;">
                        @if($port == "proxy")
                            {{ $item->port_domain }}
                        @else
                            <button wire:click="statusSSL({{ $item }})" type="button" class="btn btn-sm btn-{{ ($apacheConfDir . $item['domain_opendk'] . $cert) ? 'success' : 'danger' }}"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="{{ ($apacheConfDir . $item['domain_opendk'] . $cert) ? 'Non Aktifkan SSL' : 'Aktifkan SSL' }}">
                                <i class="fa fa-{{ ($apacheConfDir . $item['domain_opendk'] . $cert) ? 'lock' : 'unlock' }}" aria-hidden="true"></i>
                            </button>
                        @endif
                    </td>
                    <td class="text-start" style="vertical-align : middle;"><a href="{{ substr($item->domain_opendk, 0, 8) == "https://" ? $item->domain_opendk : "https://".$item->domain_opendk }}" target="_blank">
                        {{ substr($item->domain_opendk, 0, 8) == "https://" ? $item->domain_opendk : "https://".$item->domain_opendk }}</a>
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
