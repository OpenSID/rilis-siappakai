<!--  DataTables is now loaded via Vite bundle (see resources/js/app.js) -->
<!--  No need to load from CDN anymore -->

<!--  Menampilkan Datatables -->
<script type="text/javascript">
    // Wait for Vite bundle to load DataTables
    (function initDataTable() {
        if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
            $(document).ready(function () {
                if ($('#datatable').length && !$.fn.DataTable.isDataTable('#datatable')) {
                    $('#datatable').DataTable();
                }
            });
        } else {
            // DataTables not ready yet, wait a bit
            setTimeout(initDataTable, 100);
        }
    })();
</script>
