// --------------------------------------------------
// ✅ Bootstrap & jQuery setup
// --------------------------------------------------
import $ from 'jquery';
window.$ = window.jQuery = $;

import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';

// --------------------------------------------------
// ✅ Select2
// --------------------------------------------------
import select2 from 'select2';
import 'select2/dist/css/select2.min.css';

// Initialize Select2 on jQuery explicitly
select2($);

// Verify Select2 is available
if (typeof $.fn.select2 !== 'undefined') {
    console.log('✓ Select2 initialized on jQuery');
} else {
    console.error('✗ Select2 failed to initialize on jQuery');
}

// --------------------------------------------------
// ✅ DataTables (Bootstrap 5 integration)
// --------------------------------------------------
// Import core DataTables first
import 'datatables.net';
// Then import Bootstrap 5 styling
import 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';

// Ensure DataTables is available globally on jQuery
if (typeof $.fn.DataTable === 'undefined') {
    console.error('✗ DataTables failed to initialize on jQuery');
} else {
    console.log('✓ DataTables initialized on jQuery');
    // Set default configuration to prevent errors
    $.extend(true, $.fn.DataTable.defaults, {
        language: {
            processing: "Processing...",
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            loadingRecords: "Loading...",
            zeroRecords: "No matching records found",
            emptyTable: "No data available in table",
            paginate: {
                first: "First",
                previous: "Previous",
                next: "Next",
                last: "Last"
            }
        }
    });
}

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
    // Wait a bit to ensure everything is ready
    setTimeout(function() {
        if ($('#datatable').length && typeof $.fn.DataTable !== 'undefined' && !$.fn.DataTable.isDataTable('#datatable')) {
            $('#datatable').DataTable({
                responsive: true,
                pageLength: 10,
                autoWidth: false,
            });
        }
    }, 100);
});
