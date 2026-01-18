<x-app-layout title="Edit {{ ucwords(str_replace('-', ' ', $table)) }}">

    @section('breadcrumbs')
        <x-breadcrumbs navigations="Pengaturan {{ ucwords(str_replace('-', ' ', $table)) }}"
            active="Edit {{ ucwords(str_replace('-', ' ', $table)) }}"
            link="{{ route($table . '.index') }}"></x-breadcrumbs>
    @endsection

    @section('content')
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Alert Messages -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><i class="fa fa-exclamation-circle"></i> Validasi Gagal!</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><i class="fa fa-exclamation-circle"></i> Error!</strong>
                            <div class="mt-2">{{ session('error') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <a href="{{ route($table . '.index') }}"
                                class="btn btn-outline-secondary btn-circle me-2">
                                <i class="fa fa-arrow-left"></i>
                            </a>

                            <strong class="card-title">Edit
                                {{ ucwords(str_replace('-', ' ', $table)) }}</strong>
                        </div>

                        <div class="card-body">
                            <form action="{{ route($table . '.update', encrypt($cloudflare->id)) }}"
                                method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                @include($viewPage . '._form-control')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

</x-app-layout>
