<div class="item form-group d-flex mb-2">
    <label class="col-form-label col-md-3 col-sm-3 label-align" for="kode_kec">Nama {{ $sebutankecamatan }}<span class="">*</span></label>
    <div class="col-md-8 col-sm-8 me-2">
        <div wire:ignore>
            <select class="form-select" name="kode_kec" id="kode_kec"></select>
        </div>
    </div>
</div>

<input type="hidden" id="sebutan_kecamatan" value="{{ ucwords(strtolower($sebutankecamatan)) }}">
<input type="hidden" id="sebutan_kab" value="{{ $sebutankab }}">

@push('scripts')
    <!-- Tom Select CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    
    <!-- Tom Select Initialization -->
    <script>
        // Function to initialize Tom Select
        function initKecamatanTomSelect() {
            console.log('✓ Initializing kecamatan Tom Select...');
            
            const selectElement = document.getElementById('kode_kec');
            
            if (!selectElement) {
                console.error('❌ Element #kode_kec not found');
                return;
            }

            console.log('✓ Element found:', selectElement);

            // Destroy existing Tom Select if any
            if (selectElement.tomselect) {
                console.log('⚠️ Destroying existing Tom Select...');
                selectElement.tomselect.destroy();
            }

            console.log('⚙️ Configuring Tom Select with URL:', '{{ $dataWilayah }}');

            // Initialize Tom Select
            try {
                const tomSelect = new TomSelect('#kode_kec', {
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',
                    placeholder: 'Pilih atau ketik nama {{ $sebutankecamatan }}',
                    load: function(query, callback) {
                        const url = '{{ $dataWilayah }}' + '&q=' + encodeURIComponent(query);
                        console.log('📤 Loading data from:', url, 'query:', query || '(empty)');
                        
                        fetch(url)
                            .then(response => response.json())
                            .then(json => {
                                console.log('📥 Data received:', json);
                                if (json && json.results) {
                                    console.log('✅ Processing', json.results.length, 'items');
                                    json.results.forEach(function(item, index) {
                                        if (index < 3) {
                                            console.log('   -', item.text);
                                        }
                                    });
                                    callback(json.results);
                                } else {
                                    console.warn('⚠️ No results in data');
                                    callback();
                                }
                            }).catch((error) => {
                                console.error('❌ Error loading data:', error);
                                callback();
                            });
                    },
                    onChange: function(value) {
                        console.log('🔔 Tom Select value changed to:', value);
                        if (typeof Livewire !== 'undefined' && value) {
                            Livewire.dispatch('getKecamatan', value);
                        }
                    },
                    render: {
                        option: function(data, escape) {
                            return '<div class="py-2 px-3">' + escape(data.text) + '</div>';
                        },
                        item: function(data, escape) {
                            return '<div>' + escape(data.text) + '</div>';
                        }
                    },
                    loadingClass: 'loading',
                    preload: true,
                    openOnFocus: true,
                    maxOptions: 50,
                    plugins: ['clear_button']
                });

                console.log('✅ Tom Select initialized successfully');
                console.log('✅ Dropdown is ready! Click to select kecamatan.');

            } catch (error) {
                console.error('❌ Error initializing Tom Select:', error);
            }
        }

        // Initialize when document is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initKecamatanTomSelect);
        } else {
            initKecamatanTomSelect();
        }
        
        // Reset button handler
        document.addEventListener('DOMContentLoaded', function() {
            const resetBtn = document.getElementById('reset-btn');
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    console.log('🔄 Reset button clicked');
                    const selectElement = document.getElementById('kode_kec');
                    if (selectElement && selectElement.tomselect) {
                        selectElement.tomselect.clear();
                    }
                });
            }
        });
    </script>
@endpush
