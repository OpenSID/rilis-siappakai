// --------------------------------------------------
// ✅ Bootstrap & jQuery setup
// --------------------------------------------------
import $ from 'jquery';
window.$ = window.jQuery = $;

import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';

// --------------------------------------------------
// ✅ Font Awesome
// --------------------------------------------------
import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fortawesome/fontawesome-free/js/all.js';

// --------------------------------------------------
// ✅ DataTables (Bootstrap 5 integration)
// --------------------------------------------------
import 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';

// --------------------------------------------------
// ✅ Livewire + Alpine Focus plugin
// --------------------------------------------------
import './bootstrap';

import focus from '@alpinejs/focus';
window.addEventListener('alpine:init', () => {
    if (window.Alpine) {
        window.Alpine.plugin(focus);
    }
});

// --------------------------------------------------
// ✅ Init default DataTable (opsional untuk global use)
// --------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    if ($('#datatable').length && !$.fn.DataTable.isDataTable('#datatable')) {
        $('#datatable').DataTable({
            responsive: true,
            pageLength: 10,
            autoWidth: false,
        });
    }
});
