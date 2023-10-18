<div>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between">
                <h5 class="modal-title" id="staticBackdropLabel">Aktivasi dan Konfigurasi Tema {{ $data->nama_desa ?? '' }}</h5>
                <button wire:click="Batal()" type="button" class="btn-close btn-sm" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table>
                    <tr>
                        <td align="left"><span>Kode Desa</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input id="kode_desa" name="kode_desa" class="form-control" value="{{ $data->kode_desa ?? '' }}" disabled>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left"><span>Nama Domain</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input id="nama_desa" name="nama_desa" class="form-control" value="{{ $data->domain_opensid ?? '' }}" disabled>
                            </div>
                        </td>
                    </tr>

                    <!-- Pilih Tema Pro -->
                    <tr>
                        <td align="left"><span>Tema</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <select wire:model="selectedTema" id="tema_id" name="tema_id" class="form-select @error('tema_id') is-invalid @enderror" autocomplete="off">
                                    <option value="" readonly>-- {{ $tema != '[]' ? 'Pilih Tema' : 'Belum Berlangganan Tema' }} --</option>
                                    @foreach ($tema as $item)
                                        <option value="{{ $item->id . '-' . $item->tema }}" {{ old('tema_id', $data->tema_id) == $item->id ? 'selected' : null}}>
                                            Tema {{ ucwords($item->tema) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                    </tr>

                    <!-- Aktivasi Tema -->
                    <tr class="{{ $showAktivasiTema == true ? 'd-inline-table' : 'd-none'}}">
                        <td align="left"><span>Aktivasi Tema</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input type="text" wire:model.defer="aktivasi_tema" id="aktivasi_tema" name="aktivasi_tema" class="form-control" value="{{ $aktivasi_tema }}" placeholder="Masukan Kode Aktivasi Tema">
                            </div>
                        </td>
                    </tr>
                    <!-- Kode Kota -->
                    <tr class="{{ $showKodeKota == true ? 'd-inline-table' : 'd-none'}}">
                        <td align="left"><span>Pengaturan Jadwal Sholat</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input type="text" wire:model.defer="kode_kota" id="kode_kota" name="kode_kota" class="form-control" value="{{ $kode_kota }}" placeholder="Masukan Kode Kota">
                            </div>
                        </td>
                    </tr>

                    <!-- Tampilan Halaman Anjungan -->
                    <tr class="{{ $showIpaddress == true ? 'd-inline-table' : 'd-none'}}">
                        <td align="left"><span>Tampilan Halaman Anjungan</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input type="text" wire:model.defer="ip_address" id="ip_address" name="ip_address" class="form-control" value="{{ $ip_address }}" placeholder="Masukan IP Address">
                            </div>
                        </td>
                    </tr>

                    <!-- Pilig Logo : Bawaan Tema / Bawaan OpenSID-->
                    <tr class="{{ $showLogo == true ? 'd-inline-table' : 'd-none'}}">
                        <td align="left"><span>Logo</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <select wire:model="selectedLogo" id="logo" name="logo" class="form-select @error('logo') is-invalid @enderror" autocomplete="off">
                                    <option value="" readonly>-- Pilih Logo --</option>
                                    @foreach ($logos as $item)
                                        <option value="{{ $item['value'] }}" {{ old('logo', $logo) == $item['logo'] ? 'selected' : null}}>
                                            {{ $item['logo'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                    </tr>

                    <!-- Tampilkan Halaman Penuh : Pilih Fluid - Ya / Tidak -->
                    <tr class="{{ $showFluid == true ? 'd-inline-table' : 'd-none'}}">
                        <td align="left"><span>Tampilkan Halaman Penuh</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <select wire:model="selectedFluid" id="fluid" name="fluid" class="form-select @error('fluid') is-invalid @enderror" autocomplete="off">
                                    <option value="" readonly>-- Pilih Halaman Penuh --</option>
                                    @foreach ($fluids as $item)
                                        <option value="{{ $item['value'] }}" {{ old('fluid', $fluid) == $item['nilai'] ? 'selected' : null}}>
                                            {{ $item['nilai'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                    </tr>

                    <!-- Tampilkan Menu di Header : Pilih Menu Ya / Tidak -->
                    <tr class="{{ $showMenu == true ? 'd-inline-table' : 'd-none'}}">
                        <td align="left"><span>Tampilkan Menu di Header</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <select wire:model="selectedMenu" id="menu" name="menu" class="form-select @error('menu') is-invalid @enderror" autocomplete="off">
                                    <option value="" readonly>-- Pilih Menu di Header --</option>
                                    @foreach ($menus as $item)
                                        <option value="{{ $item['value'] }}" {{ old('menu', $menu) == $item['nilai'] ? 'selected' : null}}>
                                            {{ $item['nilai'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                    </tr>

                    <!-- Tampilan Color : Pilih Color - Primary, Success, Warning, Danger, Secondary -->
                    <tr class="{{ $showColor == true ? 'd-inline-table' : 'd-none'}}">
                        <td align="left"><span>Warna</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <select wire:model="selectedColor" id="color" name="color" class="form-select @error('color') is-invalid @enderror" autocomplete="off">
                                    <option value="" readonly>-- Pilih Warna --</option>
                                    @foreach ($colors as $item)
                                        <option value="{{ $item['value'] }}" {{ old('color', $color) == $item['color'] ? 'selected' : null}}>
                                            {{ $item['color'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                    </tr>

                    <!-- Pengaturan Komentar Facebook -->
                    <tr class="{{ $showFbadmin == true ? 'd-inline-table' : 'd-none'}}">
                        <td align="left" colspan="3"><span>Pengaturan Komentar Facebook</span></td>
                    </tr>
                    <tr class="{{ $showFbadmin == true ? 'd-inline-table' : 'd-none'}}">
                        <td align="left"><span>Fbadmin</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input type="text" wire:model.defer="fbadmin" id="fbadmin" name="fbadmin" class="form-control" value="{{ $fbadmin }}" placeholder="Masukan Fbadmin">
                            </div>
                        </td>
                    </tr>
                    <tr class="{{ $showFbappid == true ? 'd-inline-table' : 'd-none'}}">
                        <td align="left"><span>Fbappid</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <div class="col-md-12 col-sm-12">
                                <input type="text" wire:model.defer="fbappid" id="fbappid" name="fbappid" class="form-control" value="{{ $fbappid }}" placeholder="Masukan Fbappid">
                            </div>
                        </td>
                    </tr>

                    <tr class="{{ $showChats == true ? 'd-inline-table' : 'd-none'}}">
                        <td align="left"><span>Chats</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <select wire:model="selectedChat" id="chats" name="chats" class="form-select @error('chats') is-invalid @enderror" autocomplete="off">
                                <option value="" readonly>-- Pilih Chats --</option>
                                @foreach ($chats as $item)
                                    <option value="{{ $item['value'] }}" {{ old('chats', $chat) == $item['nilai'] ? 'selected' : null}}>
                                        {{ $item['nilai'] }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>

                    <tr class="{{ $showWidget == true ? 'd-inline-table' : 'd-none'}}">
                        <td align="left"><span>Widgets</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <select wire:model="selectedWidget" id="widgets" name="widgets" class="form-select @error('widgets') is-invalid @enderror" autocomplete="off">
                                <option value="" readonly>-- Pilih Widgets --</option>
                                @foreach ($widgets as $item)
                                    <option value="{{ $item['value'] }}" {{ old('widgets', $widget) == $item['widget'] ? 'selected' : null}}>
                                        {{ $item['widget'] }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>

                    <tr class="{{ $showStyle == true ? 'd-inline-table' : 'd-none'}}">
                        <td align="left"><span>Warna</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <select wire:model="selectedStyle" id="style" name="style" class="form-select @error('style') is-invalid @enderror" autocomplete="off">
                                <option value="" readonly>-- Pilih Warna --</option>
                                @foreach ($styles as $item)
                                    <option value="{{ $item['value'] }}" {{ old('styles', $style) == $item['style'] ? 'selected' : null}}>
                                        {{ $item['style'] }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>

                    <tr class="{{ $showHideBannerLayanan == true ? 'd-inline-table' : 'd-none'}}">
                        <td align="left"><span>Sembunyikan Layanan</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <select wire:model="selectedHideBannerLayanan" id="hide_banner_layanan" name="hide_banner_layanan" class="form-select @error('hide_banner_layanan') is-invalid @enderror" autocomplete="off">
                                <option value="" readonly>-- Pilih Sembunyikan Layanan --</option>
                                @foreach ($hide_banner_layanans as $item)
                                    <option value="{{ $item['value'] }}" {{ old('style', $hide_banner_layanan) == $item['nilai'] ? 'selected' : null}}>
                                        {{ $item['nilai'] }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>

                    <tr class="{{ $showHideBannerLaporan == true ? 'd-inline-table' : 'd-none'}}">
                        <td align="left"><span>Sembunyikan Banner Laporan</span></td>
                        <td><span>:</span></td>
                        <td align="left">
                            <select wire:model="selectedHideBannerLaporan" id="hide_banner_laporan" name="hide_banner_laporan" class="form-select @error('hide_banner_laporan') is-invalid @enderror" autocomplete="off">
                                <option value="" readonly>-- Pilih Hide Banner Laporan --</option>
                                @foreach ($hide_banner_laporans as $item)
                                    <option value="{{ $item['value'] }}" {{ old('style', $hide_banner_laporan) == $item['nilai'] ? 'selected' : null}}>
                                        {{ $item['nilai'] }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td align="left" width="45%"><span>Apakah akan menyimpan pengaturan konfigurasi tema ?</span></td>
                        <td width="2%"><span>:</span></td>
                        <td align="left" width="53%">
                            <button wire:click="SimpanPengaturan()" type="button" class="btn btn-success mx-2"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Simpan pengaturan email">
                                <i class="fa fa-window-restore me-2" aria-hidden="true"></i> Ya
                            </button>
                            <button wire:click="Batal()" type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        </td>
                    </tr>
                </table>
                @if (session()->has('message-success'))
                    <div class="text-center alert alert-success mt-3">
                        {{ session('message-success') }}
                    </div>
                @elseif(session()->has('message-failed'))
                    <div class="text-center alert alert-danger mt-3">
                        {{ session('message-failed') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // close Modal Aktivasi Tema
        window.addEventListener('closeModalAktivasiTema', event => {
            $("#aktivasiTema-{{ $data->id }}").modal('hide');
        })
    </script>
@endpush
