<x-app-layout title="Ubah Rule Master">

    @section('breadcrumbs')
        <x-breadcrumbs navigations="Pengaturan {{ ucwords(str_replace('-', ' ', $table)) }}"
            active="Ubah {{ ucwords(str_replace('-', ' ', $table)) }}"
            link="{{ route($table . '.index') }}"></x-breadcrumbs>
    @endsection

    @section('content')
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <a href="{{ route($table.'.index') }}" class="btn btn-outline-secondary btn-circle me-2">
                                <i class="fa fa-arrow-left"></i>
                            </a>

                            <strong class="card-title">Ubah Rule Master</strong>
                        </div>

                        <div class="card-body">
                            <form action="{{ route($table.'.update', encrypt($rule->id)) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                @include($viewPage.'._form-control')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endsection

</x-app-layout>
