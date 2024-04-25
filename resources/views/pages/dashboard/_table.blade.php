<div class="col-md-9">
    <div class="card">
      <div class="card-header border-transparent">
        <h3 class="card-title">Pelanggan Dasbor SiapPakai {{ ucwords(strtolower($openkab)) }}</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
          <button type="button" class="btn btn-tool" data-card-widget="remove">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table m-0">
            <thead>
              <tr>
                <th>No</th>
                <th>Kode Desa</th>
                <th>Nama Desa</th>
                <th>Nama Domain</th>
                <th>PBB</th>
                <th>API</th>
                <th>Tema</th>
                <th>Langganan</th>
                <th>Tanggal Berakhir</th>
                <th>Status</th>
              </tr>
            </thead>

            <tbody>
              @foreach ($pelanggans as $index => $item)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ $item->kode_desa }}</td>
                  <td>{{ $item->nama_desa }}</td>
                  <td><a href="{{ substr($item->domain_opensid, 0, 8) == "https://" ? $item->domain_opensid : "https://".$item->domain_opensid }}" target="_blank">
                    {{ substr($item->domain_opensid, 0, 8) == "https://" ? $item->domain_opensid : "https://".$item->domain_opensid }}</a>
                  </td>
                  <td><a href="{{ substr($item->domain_pbb, 0, 8) == "https://" ? $item->domain_pbb : "https://".$item->domain_pbb }}" target="_blank">
                        <i class="fa fa-external-link" aria-hidden="true"></i>
                      </a>
                  </td>
                  <td><a href="{{ substr($item->domain_api, 0, 8) == "https://" ? $item->domain_api : "https://".$item->domain_api }}" target="_blank">
                        <i class="fa fa-external-link" aria-hidden="true"></i>
                      </a>
                  </td>
                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detil-{{ $item->id }}"
                        data-bs-toggle="tooltip" data-bs-placement="top" title="Jumlah Tema Pro">
                        {{ $item->jumlah_tema ?? 0 }}
                    </button>

                    <!-- Modal Tabel Tema -->
                    @include('pages.dashboard._modal-info', ['data' => $item])
                  </td>
                  <td>{{ $item->langganan_opensid }}</td>
                  <td class="text-center">
                    {{ !is_null($item->tgl_akhir_premium) ? (Carbon\Carbon::createFromFormat('Y-m-d', $item->tgl_akhir_premium)->isoFormat('D-MM-Y')) : '-' }}
                  </td>
                  <td class="text-start">
                      @if($item->status_langganan_saas == 1)
                          <span class="badge badge-success">Aktif</span>
                      @elseif($item->status_langganan_saas == 2)
                          <span class="badge badge-danger">Suspended</span>
                      @elseif($item->status_langganan_saas == 3)
                          <span class="badge badge-info">Tidak Aktif</span>
                      @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="card-footer clearfix">
            <a href="{{ route('pelanggan.index') }}" class="btn btn-sm btn-secondary float-right">Lihat Selengkapnya</a>
            <a href="{{ route('pelanggan.create') }}" class="btn btn-sm btn-secondary float-right me-2 {{ (env('OPENKAB') == 'true' ? '' : 'd-none')  }}">Tambah {{ $sebutandesa }}</a>
      </div>
    </div>
</div>
