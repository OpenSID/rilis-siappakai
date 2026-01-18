<div class="item form-group d-flex mb-2">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="kode_kec">Nama {{ $sebutankecamatan }}<span class="">*</span></label>
    <div class="col-md-8 col-sm-8 me-2">
        <div wire:ignore>
            <select class="form-select" name="kode_kec" id="kode_kec" style="width: 100%"></select>
        </div>
    </div>
</div>

<input type="hidden" id="sebutan_kecamatan" value="{{ ucwords(strtolower($sebutankecamatan)) }}">
<input type="hidden" id="sebutan_kab" value="{{ $sebutankab }}">

@push('scripts')
    <!-- Select2 Bootstrap 5 Theme CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    
    <!-- Select 2 -->
    <script>
        // Function to initialize Select2
        function initKecamatanSelect2() {
            if (typeof window.$ === 'undefined' || typeof window.$.fn.select2 === 'undefined') {
                console.warn('jQuery or Select2 not ready, retrying...');
                setTimeout(initKecamatanSelect2, 200);
                return;
            }

            console.log('Initializing kecamatan Select2...');
            
            const $select = window.$('#kode_kec');
            
            if ($select.length === 0) {
                console.error('Element #kode_kec not found');
                return;
            }

            console.log('Element found:', $select[0]);

            // Destroy existing Select2 if any
            if ($select.hasClass('select2-hidden-accessible')) {
                console.log('Destroying existing Select2...');
                $select.select2('destroy');
            }
            
            // Remove any orphaned Select2 containers
            $select.siblings('.select2-container').remove();
            $('body').find('.select2-container--open').remove();
            
            console.log('✅ Cleaned up old Select2 instances');

            console.log('Configuring Select2 with URL:', '{{ $dataWilayah }}');

            // Initialize Select2 with AJAX
            try {
                $select.select2({
                    ajax: {
                        url: '{{ $dataWilayah }}',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            console.log('📤 Sending AJAX request, search term:', params.term || '(empty)');
                            return {
                                q: params.term || '',
                                page: params.page || 1
                            };
                        },
                        processResults: function (data, params) {
                            console.log('📥 Data received:', data);
                            if (data && data.results) {
                                console.log('✅ Processing', data.results.length, 'items');
                                data.results.forEach(function(item, index) {
                                    if (index < 3) {
                                        console.log('  -', item.text);
                                    }
                                });
                            } else {
                                console.warn('⚠️ No results in data');
                            }
                            return {
                                results: data.results || [],
                                pagination: {
                                    more: (data.pagination && data.pagination.more) || false
                                }
                            };
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error('❌ AJAX Error:', {
                                status: jqXHR.status,
                                statusText: textStatus,
                                error: errorThrown,
                                response: jqXHR.responseText
                            });
                        },
                        cache: true
                    },
                    placeholder: 'Pilih atau ketik nama {{ $sebutankecamatan }}',
                    allowClear: true,
                    minimumInputLength: 0,
                    width: '100%',
                    theme: 'bootstrap-5',
                    dropdownCssClass: 'select2-dropdown-kecamatan',
                    language: {
                        searching: function() {
                            return 'Mencari...';
                        },
                        noResults: function() {
                            return 'Tidak ada data';
                        },
                        loadingMore: function() {
                            return 'Memuat lebih banyak...';
                        }
                    }
                });

                console.log('✅ Select2 initialized with AJAX');
                
                // Remove any duplicate containers that might appear
                setTimeout(function() {
                    var containers = $select.siblings('.select2-container');
                    console.log('🔍 Found', containers.length, 'container(s)');
                    if (containers.length > 1) {
                        console.warn('⚠️ Multiple containers detected, removing duplicates');
                        containers.not(':first').remove();
                        $('.select2-container--open').not($select.siblings('.select2-container')).remove();
                    }
                }, 100);

                // Add CSS to ensure dropdown is visible and clickable
                if (!window.$('#select2-kecamatan-style').length) {
                    window.$('<style id="select2-kecamatan-style">')
                        .text(`
                            .select2-dropdown-kecamatan { 
                                z-index: 9999 !important; 
                            }
                            .select2-container--bootstrap-5 {
                                display: block !important;
                                width: 100% !important;
                            }
                            .select2-container--bootstrap-5 .select2-selection {
                                cursor: pointer !important;
                                pointer-events: auto !important;
                                min-height: 38px;
                                display: flex !important;
                                align-items: center;
                            }
                            .select2-container--bootstrap-5 .select2-selection__rendered {
                                padding-left: 12px;
                                color: #495057;
                            }
                            .select2-container--bootstrap-5 .select2-selection__placeholder {
                                color: #6c757d;
                            }
                            .select2-container--bootstrap-5 .select2-selection__arrow {
                                height: 100%;
                                right: 10px;
                            }
                            .select2-container--bootstrap-5 .select2-dropdown {
                                border: 1px solid #dee2e6;
                                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                            }
                            .select2-container--bootstrap-5 .select2-results__option {
                                padding: 6px 12px;
                            }
                        `)
                        .appendTo('head');
                }

                // Verify Select2 is attached
                if ($select.hasClass('select2-hidden-accessible')) {
                    console.log('✅ Select2 successfully attached to element');
                    console.log('✅ Dropdown is ready! Click to select kecamatan.');
                    
                    // Get the Select2 container
                    var $container = $select.next('.select2-container');
                    console.log('   Container found:', $container.length);
                    console.log('   Container visible:', $container.is(':visible'));
                    console.log('   Container classes:', $container.attr('class'));
                    
                    // Use event delegation on parent div instead
                    var $parentDiv = $select.closest('.col-md-8');
                    
                    // Add multiple event handlers with delegation
                    var openDropdown = function(e, eventType) {
                        console.log('👆 Event triggered:', eventType, 'on', e.target);
                        var $target = $(e.target);
                        
                        // Check if click is on Select2 elements
                        if ($target.closest('.select2-container').length || 
                            $target.hasClass('select2-container') ||
                            $target.closest('.select2-selection').length) {
                            
                            if (!$select.select2('isOpen')) {
                                console.log('   ✓ Opening dropdown...');
                                e.preventDefault();
                                e.stopPropagation();
                                $select.select2('open');
                                return false;
                            } else {
                                console.log('   ℹ Dropdown already open');
                            }
                        }
                    };
                    
                    // Attach to parent with delegation
                    $parentDiv.off('click.select2custom mousedown.select2custom');
                    $parentDiv.on('click.select2custom', function(e) { 
                        openDropdown(e, 'parent-click'); 
                    });
                    $parentDiv.on('mousedown.select2custom', function(e) { 
                        openDropdown(e, 'parent-mousedown'); 
                    });
                    
                    console.log('✅ Manual click handlers attached via delegation');
                    console.log('   Parent div:', $parentDiv[0]);
                } else {
                    console.error('❌ Select2 NOT attached to element');
                }

                // Debug: Listen to select2 events
                $select.on('select2:opening', function (e) {
                    console.log('🔔 Select2 opening event triggered - dropdown will open');
                });

                $select.on('select2:open', function (e) {
                    console.log('🔔 Select2 opened - dropdown is now visible');
                    // Check if dropdown container exists
                    var $dropdown = $('.select2-dropdown');
                    console.log('   Dropdown container found:', $dropdown.length);
                    if ($dropdown.length) {
                        console.log('   Dropdown visible:', $dropdown.is(':visible'));
                        console.log('   Dropdown z-index:', $dropdown.css('z-index'));
                    }
                });

                $select.on('select2:close', function (e) {
                    console.log('🔔 Select2 closed');
                });

                $select.on('select2:selecting', function (e) {
                    console.log('🔔 Selecting:', e.params.args.data);
                });

            } catch (error) {
                console.error('❌ Error initializing Select2:', error);
            }

            // Handle change event
            $select.on('change', function () {
                const selectedValue = window.$(this).val();
                console.log('Select2 value changed to:', selectedValue);
                if (typeof Livewire !== 'undefined' && selectedValue) {
                    // Send the value directly, not as an object
                    Livewire.dispatch('getKecamatan', selectedValue);
                }
            });

            // Reset button handler
            window.$('#reset-btn').on('click', function() {
                console.log('Reset button clicked');
                $select.val('').trigger('change');
            });
        }

        // Try to initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initKecamatanSelect2);
        } else {
            // DOM already loaded
            initKecamatanSelect2();
        }
    </script>
@endpush
