<div>
    <table id="datatable" class="table table-striped table-bordered datatable">
        <!-- Judul tabel -->
        <thead>
            <tr>
                <th class="text-end" style="vertical-align : middle;"><input type="checkbox" id="check-all"></th>
                <th class="text-center" style="vertical-align : middle;">No</th>
                <th class="text-center" style="vertical-align : middle;">Aksi</th>
                <th class="text-center" style="vertical-align : middle;">Kode Kecamatan</th>
                <th class="text-center" style="vertical-align : middle;">Nama Kecamatan</th>
                <th class="text-center" style="vertical-align : middle;">{{ $port == 'proxy' ? 'Port Domain' : 'Status SSL' }}</th>
                <th class="text-center" style="vertical-align : middle;">Masa Berlaku SSL</th>
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
                            <!-- Tombol Ubah Domain -->
                            <button type="button" class="btn btn-sm btn-info me-2" data-bs-toggle="modal"
                                data-bs-target="#ubahDomain-{{ $item->id }}" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="Ubah Domain">
                                <i class="fa fa-globe" aria-hidden="true"></i>
                            </button>

                            <!-- Tombol Hapus Desa -->
                            {{-- <button type="button" class="btn btn-sm btn-danger me-2" data-bs-toggle="modal" data-bs-target="#{{ $table }}-{{ $item->id }}"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Desa/Kelurahan">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </button> --}}
                        </div>

                        <div class="modal fade" id="ubahDomain-{{ $item->id }}" data-bs-backdrop="static"
                            data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel"
                            aria-hidden="true">
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
                            <div class="btn-group" role="group">
                                <button type="button"
                                    class="btn btn-sm dropdown-toggle me-2 btn-{{ $item->getRemainingSslAttribute() > 0 ? 'success' : 'danger' }}"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Generate SSL">
                                    <i class="fa fa-{{ $apacheConfDir . $item['domain_opendk'] . $cert ? 'lock' : 'unlock' }}"
                                        aria-hidden="true"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <!-- Tombol Generate Let's Encrypt -->
                                        <button class="dropdown-item" wire:click="statusSSL({{ $item }})"
                                            type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ $apacheConfDir . $item['domain_opendk'] . $cert ? 'Non Aktifkan SSL' : 'Aktifkan SSL' }}">
                                            Generate Let’s Encrypt
                                        </button>
                                    </li>
                                    <li>
                                        <!-- Tombol Generate Wildcard-->
                                        <button class="dropdown-item"
                                            wire:click="statusSSLWildcard({{ $item }})" type="button"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ $apacheConfDir . $item['domain_opendk'] . $cert ? 'Non Aktifkan SSL' : 'Aktifkan SSL' }}">
                                            Generate Wildcard
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </td>
                    <td class="text-center" style="vertical-align : middle;">
                        {!! $item->jenis_ssl ? '<span class="badge bg-secondary">' . ucfirst($item->jenis_ssl) . '</span><br>' : '' !!}
                        {{ !is_null($item->tgl_akhir) ? Carbon\Carbon::createFromFormat('Y-m-d', $item->tgl_akhir)->isoFormat('D MMMM Y') : '' }}

                        @php $r = $item->getRemainingSslAttribute(); @endphp

                        @if (is_null($r) || $r == 0)
                            <span class="badge bg-info">Tidak aktif</span>
                        @elseif($r < 0)
                            <span class="badge bg-danger">{{ $r }} hari lalu</span>
                        @elseif($r <= 30 && $r > 0)
                            <span class="badge bg-warning text-dark">{{ $r }} hari lagi</span>
                        @elseif($r > 30)
                            <span class="badge bg-success">{{ $r }} hari lagi</span>
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
