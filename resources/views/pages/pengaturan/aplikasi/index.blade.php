<x-app-layout title="Pengaturan {{ ucwords(str_replace('-', ' ', $table)) }}">

    @section('breadcrumbs')
        <!-- Breadcrumbs untuk navigasi -->
        <x-breadcrumbs 
            navigations="Dasbor" 
            active="Pengaturan {{ ucwords(str_replace('-', ' ', $table)) }}"
            link="{{ route('dasbor') }}">
        </x-breadcrumbs>
    @endsection

    @section('content')
        <div class="animated fadeIn">
            <div class="row">
                <!-- Kolom untuk pengaturan gambar -->
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Pengaturan Gambar</strong>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('aplikasi.updateImage', 1) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                <!-- Menyertakan form untuk pengaturan gambar -->
                                @include('pages.pengaturan.aplikasi._image')
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Kolom untuk pengaturan dasar -->
                <div class="col-lg-9">
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Pengaturan Dasar</strong>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('aplikasi.update', 1) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                <!-- Looping untuk setiap setting aplikasi -->
                                @foreach ($setting_aplikasi as $data)
                                    @include('components.form_dinamis', $data->toArray())
                                    <!-- Menyertakan script tambahan jika ada -->
                                    @if ($data->script)
                                        @include($data->script)
                                    @endif
                                @endforeach
                                <hr>
                                <div class="item form-group">
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-primary" type="reset">Batal</button>
                                        <button type="submit" class="btn btn-success ms-2">Simpan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Event listener untuk memanggil fungsi showHideServer saat DOM siap
                document.addEventListener('DOMContentLoaded', function() {
                    showHideServer();
                });

                // Fungsi untuk menampilkan atau menyembunyikan panel server
                function showHideServer() {
                    const serverPanel = document.getElementById('serverpanel');
                    const pilihServer = document.getElementById('pilihserver');
                    pilihServer.style.display = serverPanel.value == '1' ? 'block' : 'none';
                }

                // Inisialisasi plugin select2 dengan tema bootstrap4
                // Exclude pengaturan_wilayah as it has custom initialization
                $('.multiSelect').not('[name="pengaturan_wilayah"]').select2({
                    placeholder: "",
                    allowClear: true,
                    theme: 'bootstrap4',
                });
            });
        </script>
    @endpush
</x-app-layout>
