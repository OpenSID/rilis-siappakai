<x-main-layout title="">

    @section('breadcrumbs')
        <x-breadcrumbs></x-breadcrumbs>
    @endsection

    @section('content')
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
            <!-- Small boxes (Stat box) -->
                <div class="row">
                    @foreach ($infopelanggans as $item)
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box {{ $item['color'] }}">
                            <div class="inner">
                                <h3>{{ $item['info'] }} {{ $item['desc'] }}</h3>

                                <p>{{ $item['title'] }}</p>
                            </div>
                            <div class="icon">
                                <i class="{{ $item['icon'] }}"></i>
                            </div>
                            <a href="{{ $item['title'] == "Dasbor" ? route('pelanggan.remain', ['remain'=> 'expired']) : $item['link'] }}" class="small-box-footer">Info selengkapnya <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                    @endforeach
                </div>

                <hr>
                <div class="row">
                    <!-- Table -->
                    @include('pages.dashboard._table')

                    <!-- Basic Application -->
                    <div class="col-md-3">
                        @foreach($applications as $basic)
                            <div class="info-box mb-3 {{ $basic['color'] }}">
                                <span class="info-box-icon"><i class="fa-solid fa-clipboard-list"></i></span>
                                <div class="info-box-content">
                                    <a href="{{ $basic['title'] == "Job Monitoring" ? route('queue-monitor::index') : $basic['link'] }}" target="_blank" class="small-box-footer">
                                        <span class="info-box-text text-black">{{ $basic['title'] }}
                                        <i class="ms-2 fas fa-arrow-circle-right"></i>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endsection

</x-app-layout>
