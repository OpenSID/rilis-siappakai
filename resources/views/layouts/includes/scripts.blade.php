<!-- Load Vite app FIRST (includes jQuery, Bootstrap 5, Select2, DataTables, Alpine v3 + Focus) -->
<!-- Note: Vite uses ES modules which are deferred, so we need to handle timing -->
@vite(['resources/css/app.css', 'resources/js/app.js'])

<!-- Polyfill $ and jQuery until Vite loads (prevents errors in inline scripts) -->
<script>
// Create a jQuery polyfill that queues calls until real jQuery loads
(function() {
  window.jQueryQueue = [];
  
  // Chainable polyfill object
  var chainable = {
    ready: function(fn) {
      window.jQueryQueue.push({type: 'ready', callback: fn});
      return chainable;
    },
    on: function() {
      window.jQueryQueue.push({type: 'on', args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    off: function() {
      window.jQueryQueue.push({type: 'off', args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    click: function() {
      window.jQueryQueue.push({type: 'click', args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    change: function() {
      window.jQueryQueue.push({type: 'change', args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    submit: function() {
      window.jQueryQueue.push({type: 'submit', args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    select2: function() {
      window.jQueryQueue.push({type: 'select2', args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    DataTable: function() {
      window.jQueryQueue.push({type: 'DataTable', args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    length: 0,
    each: function() { return chainable; },
    find: function() { return chainable; },
    addClass: function() { return chainable; },
    removeClass: function() { return chainable; }
  };
  
  // jQuery polyfill function
  window.$ = window.jQuery = function(selector) {
    window.jQueryQueue.push({type: 'selector', selector: selector});
    return chainable;
  };
  
  // Static methods
  window.$.fn = window.jQuery.fn = chainable;
  window.$.extend = window.jQuery.extend = function() { return {}; };
})();
</script>

<!-- Wait for Vite/jQuery to load before loading jQuery plugins -->
<script>
// Replace polyfill with real jQuery when Vite loads
(function checkViteLoaded() {
  // Check if the real jQuery from Vite is loaded (it will overwrite our polyfill)
  if (typeof window.jQuery !== 'undefined' && window.jQuery.fn && window.jQuery.fn.jquery) {
    console.log('✓ jQuery loaded from Vite:', window.jQuery.fn.jquery);
    console.log('✓ Select2 available:', typeof window.jQuery.fn.select2 !== 'undefined');
    
    // Process queued calls with real jQuery
    if (window.jQueryQueue && window.jQueryQueue.length > 0) {
      console.log('Processing', window.jQueryQueue.length, 'queued jQuery calls');
      window.jQueryQueue.forEach(function(item) {
        try {
          if (item.type === 'ready' && item.callback) {
            window.jQuery(document).ready(item.callback);
          }
          // Note: Other queued calls (on, click, etc.) can't be replayed without context
          // so they'll be re-executed when the page scripts run with real jQuery
        } catch(e) {
          console.error('Error processing queued jQuery call:', e);
        }
      });
      window.jQueryQueue = [];
    }
    
    initializePlugins();
  } else {
    // Still waiting for Vite
    setTimeout(checkViteLoaded, 50);
  }
})();

function initializePlugins() {
  // jQuery is now available, dynamically load the plugin scripts
  const scripts = [
    '{{ asset('/plugins/jquery-ui/jquery-ui.min.js') }}',
    '{{ asset('/plugins/chart.js/Chart.min.js') }}',
    '{{ asset('/plugins/sparklines/sparkline.js') }}',
    '{{ asset('/plugins/jquery-knob/jquery.knob.min.js') }}',
    '{{ asset('/plugins/moment/moment.min.js') }}',
    '{{ asset('/plugins/daterangepicker/daterangepicker.js') }}',
    '{{ asset('/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}',
    '{{ asset('/plugins/summernote/summernote-bs4.min.js') }}',
    '{{ asset('/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}',
    '{{ asset('/plugins/sweetalert2/sweetalert2.all.min.js') }}',
    '{{ asset('/themes/js/adminlte.js') }}',
    '{{ asset('/vendor/custom.js') }}'
  ];
  
  function loadScriptSequentially(index) {
    if (index >= scripts.length) {
      // All scripts loaded, initialize jQuery UI bridge
      if ($.widget && $.ui && $.ui.button) {
        $.widget.bridge('uibutton', $.ui.button);
      }
      
      // Trigger custom event to signal all plugins are loaded
      window.dispatchEvent(new Event('pluginsLoaded'));
      console.log('All jQuery plugins loaded');
      
      return;
    }
    
    const script = document.createElement('script');
    script.src = scripts[index];
    script.onload = function() {
      loadScriptSequentially(index + 1);
    };
    script.onerror = function() {
      console.error('Failed to load script:', scripts[index]);
      loadScriptSequentially(index + 1);
    };
    document.body.appendChild(script);
  }
  
  loadScriptSequentially(0);
}
</script>

<!-- External CDN scripts that don't depend on jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
@include('layouts.includes._scripts-alert')

<!-- sweetalert 2 (already included in dynamic load above, but keeping CDN as fallback) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<!-- Livewire -->
<livewire:scripts />

<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js" integrity="sha256-qXBd/EfAdjOA2FGrGAG+b3YBn2tn5A6bhz+LSgYD96k=" crossorigin="anonymous"></script>

<!-- Skrip pada halaman tertentu -->
<!-- These will execute automatically, but jQuery and plugins should be ready by now -->
@stack('scripts')
