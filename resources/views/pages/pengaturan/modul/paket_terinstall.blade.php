<div class="tab-pane active">
        <form action="{{ route($table.'.destroy' ,1) }}" id="mainform" method="post" enctype="multipart/form-data">
            <input type="hidden" name="name" value="">
            @csrf
            @method("DELETE")
            <div class="row" id="list-paket">
                <div class="alert alert-danger">Belum ada paket yang terpasang</div>
            </div>
        </form>
</div>
@push('scripts')
    <script>
        $(function() {
            let paketTerpasang = {!! $paket_terpasang !!}
            function loadModule() {
                let cardView = [],
                    disabledPaket, buttonInstall, versionCheck, templateTmp
                let urlModule = '{{ $url_marketplace }}'
                const templateCard = `@include('pages.pengaturan.modul.item')`

                $.ajax({
                    url : urlModule,
                    data: {
                        per_page: 10000,
                        list_module: paketTerpasang
                    },
                    type: 'GET',
                    contentType: 'application/json',
                    headers: {
                        'Authorization': 'Bearer {{$token_layanan}}'
                    },
                    beforeSend: function(){
                        $('#list-paket').empty()
                    },
                    success: function(response) {
                        const data = response.data
                        for (let i in data) {
                            templateTmp = templateCard
                            disabledPaket = ''
                            buttonInstall = `<button type="button" name="pasang" value="${data[i].name}" class="btn btn-danger">Hapus</button>`

                            templateTmp = templateTmp.replace('__name__', data[i].name)
                            templateTmp = templateTmp.replace('__description__', data[i].description)
                            templateTmp = templateTmp.replace('__button__', buttonInstall)
                            templateTmp = templateTmp.replace('__thumbnail__', data[i].thumbnail)
                            templateTmp = templateTmp.replace('__price__', data[i].price)
                            templateTmp = templateTmp.replace('__totalInstall__', data[i].totalInstall)
                            cardView.push(templateTmp)
                        }

                        $('#list-paket').append(cardView.join(''))
                        $('#list-paket button:button').click(function(e) {
                            e.preventDefault();

                            Swal.fire({
                                title: 'Apakah anda sudah melakukan backup database dan folder desa ?',
                                showDenyButton: true,
                                confirmButtonText: 'Sudah',
                                denyButtonText: `Belum`,
                            }).then((result) => {
                                /* Read more about isConfirmed, isDenied below */
                                if (result.isConfirmed) {
                                    Swal.fire({
                                        title: 'Sedang Memproses',
                                        allowOutsideClick: false,
                                        allowEscapeKey: false,
                                        showConfirmButton: false,
                                        didOpen: () => {
                                            Swal.showLoading()
                                        }
                                    });
                                    $(e.currentTarget).closest('form').find('input[name=name]').val($(e.currentTarget).val())
                                    $(e.currentTarget).closest('form').submit()
                                }
                            })
                        })
                    }
                })
            }

            if (paketTerpasang.length > 0) {
                loadModule()
            }
        })
    </script>
@endpush
