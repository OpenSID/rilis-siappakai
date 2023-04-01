<!-- Modal -->
<div class="modal fade" id="detil-{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between">
                <h5 class="modal-title" id="staticBackdropLabel">Data Tema Pro</h5>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive mt-3">
                    <table id="table-detail{{ $data->id }}" class="table table-striped table-bordered" style="width: 100%">
                        <!-- Judul tabel -->
                        <thead>
                            <tr>
                                <th class="text-center"><small>No</small></th>
                                <th class="text-center"><small>Kode Desa</small></th>
                                <th class="text-center"><small>Nama Desa</small></th>
                                <th class="text-center"><small>Tema</small></th>
                            </tr>
                        </thead>

                        <!-- Isi data dalam tabel -->
                        <tbody>
                            @foreach($data->temas as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="text-center">{{ $item->kode_desa }}</td>
                                    <td class="text-center">{{ $data->nama_desa }}</td>
                                    <td>{{ ucwords($item->tema) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keluar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <!--  Datatables -->
    @include('layouts.includes._scripts-datatable')

    <script type="text/javascript">
        $(document).ready( function () {
            $('#table-detail{{ $data->id }}').DataTable();
        });
    </script>
@endpush
