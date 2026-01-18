<!-- Load Vite app FIRST (includes jQuery, Bootstrap 5, Select2, DataTables, Alpine v3 + Focus) -->
<!-- Note: Vite uses ES modules which are deferred, so we need to handle timing -->
@vite(['resources/css/app.css', 'resources/js/app.js'])

<!-- Polyfill $ and jQuery until Vite loads (prevents errors in inline scripts) -->
<script>
// Create a jQuery polyfill that queues calls until real jQuery loads
(function() {
  window.jQueryQueue = [];
  window.jQueryReadyCallbacks = [];
  
  // Chainable polyfill object
  var chainable = {
    ready: function(fn) {
      window.jQueryReadyCallbacks.push(fn);
      return chainable;
    },
    on: function() {
      window.jQueryQueue.push({type: 'on', context: this, args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    off: function() {
      window.jQueryQueue.push({type: 'off', context: this, args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    click: function() {
      window.jQueryQueue.push({type: 'click', context: this, args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    change: function() {
      window.jQueryQueue.push({type: 'change', context: this, args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    submit: function() {
      window.jQueryQueue.push({type: 'submit', context: this, args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    select2: function() {
      window.jQueryQueue.push({type: 'select2', context: this, args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    DataTable: function() {
      window.jQueryQueue.push({type: 'DataTable', context: this, args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    append: function() {
      window.jQueryQueue.push({type: 'append', context: this, args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    trigger: function() {
      window.jQueryQueue.push({type: 'trigger', context: this, args: Array.prototype.slice.call(arguments)});
      return chainable;
    },
    val: function() {
      window.jQueryQueue.push({type: 'val', context: this, args: Array.prototype.slice.call(arguments)});
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
    if (typeof selector === 'function') {
      // $(function() {}) shorthand for $(document).ready()
      window.jQueryReadyCallbacks.push(selector);
      return chainable;
    }
    window.jQueryQueue.push({type: 'selector', selector: selector});
    return chainable;
  };
  
  // Static methods
  window.$.fn = window.jQuery.fn = chainable;
  window.$.extend = window.jQuery.extend = function() { return {}; };
  window.$.map = window.jQuery.map = function() { return []; };
})();

// Fallback preloader removal - hide after max 3 seconds
setTimeout(function() {
  var preloader = document.querySelector('.preloader');
  if (preloader && preloader.offsetHeight > 0) {
    console.warn('⚠️ Preloader still visible after 3s, forcing removal');
    preloader.style.height = '0';
    setTimeout(function() {
      if (preloader.parentNode) {
        preloader.parentNode.removeChild(preloader);
      }
    }, 200);
  }
}, 3000);
</script>

<!-- Wait for Vite/jQuery to load before loading jQuery plugins -->
<script>
// Replace polyfill with real jQuery when Vite loads
(function checkViteLoaded() {
  // Check if the real jQuery from Vite is loaded (it will overwrite our polyfill)
  if (typeof window.jQuery !== 'undefined' && window.jQuery.fn && window.jQuery.fn.jquery) {
    console.log('✓ jQuery loaded from Vite:', window.jQuery.fn.jquery);
    console.log('✓ Select2 available:', typeof window.jQuery.fn.select2 !== 'undefined');
    
    // Execute all ready callbacks immediately since DOM is likely ready
    if (window.jQueryReadyCallbacks && window.jQueryReadyCallbacks.length > 0) {
      console.log('Processing', window.jQueryReadyCallbacks.length, 'queued ready callbacks');
      window.jQueryReadyCallbacks.forEach(function(callback) {
        try {
          window.jQuery(document).ready(callback);
        } catch(e) {
          console.error('Error processing jQuery ready callback:', e);
        }
      });
      window.jQueryReadyCallbacks = [];
    }
    
    // Clear queue as it will be re-executed by inline scripts with real jQuery
    window.jQueryQueue = [];
    
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
      window.pluginsLoaded = true;
      window.dispatchEvent(new Event('pluginsLoaded'));
      console.log('✓ All jQuery plugins loaded and ready');
      
      // Hide preloader when all plugins are loaded
      setTimeout(function() {
        var $preloader = $('.preloader');
        if ($preloader.length) {
          $preloader.css('height', 0);
          setTimeout(function() {
            $preloader.children().hide();
            $preloader.remove();
          }, 200);
          console.log('✓ Preloader hidden');
        }
      }, 100);
      
      // Execute any deferred inline scripts
      if (window.deferredScripts && window.deferredScripts.length > 0) {
        console.log('Executing', window.deferredScripts.length, 'deferred inline scripts');
        window.deferredScripts.forEach(function(script) {
          try {
            script();
          } catch(e) {
            console.error('Error executing deferred script:', e);
          }
        });
        window.deferredScripts = [];
      }
      
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
