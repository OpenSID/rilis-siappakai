@push('scripts')
    <script>
        $(document).ready(function() {
            var aapanel = $('select[name="server_panel"]').val();
            if (aapanel == 2) {
                $('input[name="aapanel_key"]').parent().parent().addClass('d-none');
                $('input[name="aapanel_ip"]').parent().parent().addClass('d-none');
                $('input[name="aapanel_php"]').parent().parent().addClass('d-none');
            }

            $('select[name="server_panel"]').change(function() {
                var ganti = $(this).val();
                if (ganti == '2') {
                    $('input[name="aapanel_key"]').parent().parent().addClass('d-none');
                    $('input[name="aapanel_ip"]').parent().parent().addClass('d-none');
                    $('input[name="aapanel_php"]').parent().parent().addClass('d-none');
                } else {
                    $('input[name="aapanel_key"]').parent().parent().removeClass('d-none');
                    $('input[name="aapanel_ip"]').parent().parent().removeClass('d-none');
                    $('input[name="aapanel_php"]').parent().parent().removeClass('d-none');
                }
            })
        });
    </script>
@endpush
