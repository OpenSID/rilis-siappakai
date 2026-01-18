@push('scripts')
    <script>
        console.log('🔵 Pengaturan Wilayah script loaded, readyState:', document.readyState);
        
        // Wrap in a function that waits for jQuery and plugins to be ready
        (function() {
            var retryCount = 0;
            var maxRetries = 50; // 5 seconds max
            var initialized = false;
            
            function initPengaturanWilayah() {
                console.log('🔍 Attempt', retryCount + 1, '- Checking jQuery and Select2...');
                console.log('   $ defined:', typeof $ !== 'undefined');
                console.log('   $.fn.select2 defined:', typeof $ !== 'undefined' && typeof $.fn !== 'undefined' && typeof $.fn.select2 !== 'undefined');
                
                // Verify jQuery and Select2 are loaded
                if (typeof $ === 'undefined' || typeof $.fn === 'undefined' || typeof $.fn.select2 === 'undefined') {
                    retryCount++;
                    if (retryCount >= maxRetries) {
                        console.error('❌ Failed to initialize Pengaturan Wilayah: jQuery/Select2 not available after', maxRetries, 'attempts');
                        return;
                    }
                    console.log('⏳ Waiting for jQuery and Select2... (retry in 100ms)');
                    setTimeout(initPengaturanWilayah, 100);
                    return;
                }
                
                if (initialized) {
                    console.log('⚠️ Already initialized, skipping...');
                    return;
                }
                
                initialized = true;
                console.log('✅ Initializing Pengaturan Wilayah with Select2');
                
                try {
                    // Check if element exists
                    var $select = $('select[name="pengaturan_wilayah"]');
                    console.log('   Found select element:', $select.length, 'element(s)');
                    
                    if ($select.length === 0) {
                        console.error('❌ Select element not found in DOM!');
                        initialized = false;
                        setTimeout(initPengaturanWilayah, 200);
                        return;
                    }
                    
                    // IMPORTANT: Destroy existing Select2 instance if already initialized
                    if ($select.hasClass('select2-hidden-accessible')) {
                        console.log('   ⚠️ Select2 already initialized, destroying old instance...');
                        $select.select2('destroy');
                    }
                    
                    // Clear existing options
                    $select.empty();
                    
                    // Add default option if value exists
                    if ('{{ $data->value }}') {
                        var defaultOption = new Option('{{ $data->value }}', '{{ $data->value }}', true, true);
                        $select.append(defaultOption);
                        console.log('   Default option added:', '{{ $data->value }}');
                    } else {
                        console.log('   No default value, adding placeholder option');
                        var placeholderOption = new Option('Pilih wilayah...', '', false, false);
                        $select.append(placeholderOption);
                    }

                    // Inisialisasi select2 dengan konfigurasi AJAX
                    // Note: Using default theme for Bootstrap 5 compatibility
                    console.log('   🔧 Initializing Select2 with AJAX...');
                    
                    var select2Config = {
                        width: '100%',
                        placeholder: 'Ketik untuk mencari wilayah...',
                        allowClear: true,
                        // minimumInputLength: 0, // Allow search without typing first
                        dropdownAutoWidth: true,
                        ajax: {
                            url: '{{ $koneksiPantau }}',
                            dataType: 'json',
                            delay: 400,
                            data: function(params) {
                                console.log('   🔍 AJAX data function called');
                                console.log('   🔍 Search term:', params.term);
                                console.log('   🔍 Page:', params.page);
                                return {
                                    q: params.term || '',
                                    page: params.page || 1,
                                };
                            },
                            processResults: function(response, params) {
                                console.log('   📥 AJAX processResults called');
                                console.log('   📥 Response:', response);
                                params.page = params.page || 1;
                                
                                if (!response || !response.results) {
                                    console.error('   ❌ Invalid response format');
                                    return { results: [] };
                                }
                                
                                var results = $.map(response.results, function(item) {
                                    return {
                                        id: `${item.nama_kab}, PROVINSI ${(item.nama_prov).toUpperCase()}`,
                                        text: `${item.nama_kab}, PROVINSI ${(item.nama_prov).toUpperCase()}`,
                                        data: item
                                    }
                                });
                                
                                console.log('   📥 Processed results:', results.length, 'items');
                                
                                return {
                                    results: results,
                                    pagination: response.pagination
                                };
                            },
                            transport: function(params, success, failure) {
                                console.log('   🚀 AJAX transport called');
                                console.log('   🚀 URL:', params.url);
                                console.log('   🚀 Data:', params.data);
                                
                                var $request = $.ajax(params);
                                
                                $request.then(function(data) {
                                    console.log('   ✅ AJAX success');
                                    success(data);
                                });
                                
                                $request.fail(function(jqXHR, textStatus, errorThrown) {
                                    console.error('   ❌ AJAX failed:', textStatus, errorThrown);
                                    console.error('   ❌ Status:', jqXHR.status);
                                    console.error('   ❌ Response:', jqXHR.responseText);
                                    failure();
                                });
                                
                                return $request;
                            },
                            cache: true
                        }
                    };
                    
                    console.log('   🔧 Select2 config:', select2Config);
                    $select.select2(select2Config);
                    console.log('   ✅ Select2 initialized with AJAX config');
                    console.log('   ✅ AJAX URL:', '{{ $koneksiPantau }}');
                    
                    // Verify Select2 is actually initialized
                    if ($select.hasClass('select2-hidden-accessible')) {
                        console.log('   ✅ Select2 class verified');
                    } else {
                        console.error('   ❌ Select2 class not found after init!');
                    }
                    
                    // Check if Select2 data exists
                    var select2Data = $select.data('select2');
                    if (select2Data) {
                        console.log('   ✅ Select2 data object exists');
                        console.log('   ℹ️ Select2 options:', select2Data.options.options);
                    } else {
                        console.error('   ❌ Select2 data object not found!');
                    }

                    // Event handlers for debugging
                    $select.on('select2:opening', function(e) {
                        console.log('   📂 Select2 opening...');
                    });
                    
                    $select.on('select2:open', function(e) {
                        console.log('   ✅ Select2 opened - dropdown should be visible');
                        
                        // Check if search box exists
                        var $search = $('.select2-search__field');
                        console.log('   🔍 Search box found:', $search.length);
                        
                        if ($search.length > 0) {
                            console.log('   🔍 Search box placeholder:', $search.attr('placeholder'));
                            console.log('   🔍 Search box disabled:', $search.prop('disabled'));
                            
                            // Add keyup listener for debugging
                            $search.off('keyup.debug').on('keyup.debug', function() {
                                console.log('   ⌨️ Keyup in search box, value:', $(this).val());
                            });
                        } else {
                            console.error('   ❌ Search box not found!');
                        }
                    });
                    
                    $select.on('select2:close', function(e) {
                        console.log('   🔒 Select2 closed');
                    });
                    
                    $select.on('select2:selecting', function(e) {
                        console.log('   🎯 Selecting item:', e.params.args.data);
                    });

                    // Event handler untuk saat opsi dipilih di select2
                    $select.on('select2:select', function(e) {
                        console.log('   Select2 option selected:', e.params.data);
                        var selected = e.params.data;
                        
                        // Memisahkan kode desa untuk mendapatkan kode provinsi dan kabupaten
                        var wilayah = selected.data.kode_desa.split(".");
         
                        // Mengisi input dengan data yang dipilih
                        $('input[name="kode_desa"]').val(selected.text);
                        $('input[name="kode_wilayah"]').val(selected.data.kode_desa);
                        $('input[name="kode_provinsi"]').val(wilayah[0]);
                        $('input[name="kode_kabupaten"]').val(wilayah[1]);
                        $('input[name="nama_provinsi"]').val(selected.data.nama_prov);
                        $('input[name="nama_kabupaten"]').val(selected.data.nama_kab);
                        $('input[name="nama_wilayah"]').val(selected.text);
                        
                        console.log('   All inputs populated');
                    });
                    
                    console.log('✅ Pengaturan Wilayah initialized successfully!');
                    console.log('   Element has class:', $select.attr('class'));
                    
                    // Add helper function for manual testing in console
                    window.testPengaturanWilayah = function() {
                        console.log('🧪 Testing Pengaturan Wilayah Select2...');
                        console.log('   Element exists:', $('select[name="pengaturan_wilayah"]').length);
                        console.log('   Has Select2 class:', $('select[name="pengaturan_wilayah"]').hasClass('select2-hidden-accessible'));
                        console.log('   Select2 data:', $('select[name="pengaturan_wilayah"]').data('select2'));
                        
                        // Try to open programmatically
                        console.log('   Attempting to open Select2...');
                        $('select[name="pengaturan_wilayah"]').select2('open');
                    };
                    
                    window.testSearch = function(searchTerm) {
                        console.log('🔍 Testing search with term:', searchTerm);
                        var $search = $('.select2-search__field');
                        if ($search.length > 0) {
                            console.log('   Setting search value...');
                            $search.val(searchTerm).trigger('input');
                            console.log('   Search value set:', $search.val());
                        } else {
                            console.error('   Search box not found! Open dropdown first.');
                        }
                    };
                    
                    console.log('ℹ️ Run testPengaturanWilayah() in console to test manually');
                    console.log('ℹ️ Run testSearch("jakarta") to test search');
                    
                } catch(e) {
                    console.error('❌ Error initializing Pengaturan Wilayah:', e);
                }
            }
            
            // Multiple fallback mechanisms to ensure initialization
            console.log('🚀 Setting up initialization triggers...');
            
            // 1. DOM Content Loaded
            if (document.readyState === 'loading') {
                console.log('📄 DOM still loading, adding DOMContentLoaded listener');
                document.addEventListener('DOMContentLoaded', function() {
                    console.log('📄 DOMContentLoaded triggered');
                    initPengaturanWilayah();
                });
            } else {
                console.log('📄 DOM already ready');
            }
            
            // 2. Plugins Loaded event
            window.addEventListener('pluginsLoaded', function() {
                console.log('🔌 pluginsLoaded event triggered');
                initPengaturanWilayah();
            });
            
            // 3. Immediate attempt (will retry if needed)
            console.log('⚡ Starting immediate initialization attempt');
            setTimeout(initPengaturanWilayah, 100);
            
            // 4. Fallback after delay
            setTimeout(function() {
                if (!initialized) {
                    console.log('⏰ Fallback initialization attempt after 2 seconds');
                    initPengaturanWilayah();
                }
            }, 2000);
        })();
    </script>
@endpush
