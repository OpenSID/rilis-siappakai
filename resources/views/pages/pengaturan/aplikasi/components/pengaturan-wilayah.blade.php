{{--
    Script Pengaturan Wilayah — Custom searchable dropdown (vanilla JS, tanpa Select2/jQuery)
    Dynamic berdasarkan Level:
    - Level 1 Provinsi → cari & pilih provinsi
    - Level 2 Kabupaten → cari & pilih kabupaten
    - Level 3 Kecamatan → cari & pilih kecamatan
--}}

@push('scripts')
<style>
    .wilayah-autocomplete {
        position: relative;
    }
    .wilayah-autocomplete .wilayah-search-input {
        width: 100%;
        padding: 0.375rem 2rem 0.375rem 0.75rem;
        font-size: 0.875rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        background-color: #fff;
        transition: border-color 0.15s ease-in-out;
    }
    .wilayah-autocomplete .wilayah-search-input:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    .wilayah-autocomplete .wilayah-clear-btn {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        font-size: 1.1rem;
        color: #999;
        cursor: pointer;
        padding: 0 4px;
        line-height: 1;
        display: none;
    }
    .wilayah-autocomplete .wilayah-clear-btn:hover {
        color: #333;
    }
    .wilayah-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        background: #fff;
        border: 1px solid #ced4da;
        border-top: none;
        border-radius: 0 0 0.25rem 0.25rem;
        max-height: 250px;
        overflow-y: auto;
        display: none;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .wilayah-dropdown.show {
        display: block;
    }
    .wilayah-dropdown .wilayah-item {
        padding: 8px 12px;
        cursor: pointer;
        font-size: 0.875rem;
        border-bottom: 1px solid #f0f0f0;
    }
    .wilayah-dropdown .wilayah-item:last-child {
        border-bottom: none;
    }
    .wilayah-dropdown .wilayah-item:hover,
    .wilayah-dropdown .wilayah-item.active {
        background-color: #e9ecef;
    }
    .wilayah-dropdown .wilayah-msg {
        padding: 10px 12px;
        color: #6c757d;
        font-size: 0.85rem;
        text-align: center;
    }

    /* Status sinkronisasi wilayah */
    .wilayah-sync-status {
        display: none;
        padding: 10px 15px;
        border-radius: 0.25rem;
        margin-bottom: 10px;
        font-size: 0.875rem;
        align-items: center;
        gap: 8px;
    }
    .wilayah-sync-status.show {
        display: flex;
    }
    .wilayah-sync-status.status-running {
        background-color: #fff3cd;
        border: 1px solid #ffc107;
        color: #856404;
    }
    .wilayah-sync-status.status-succeeded {
        background-color: #d4edda;
        border: 1px solid #28a745;
        color: #155724;
    }
    .wilayah-sync-status.status-failed {
        background-color: #f8d7da;
        border: 1px solid #dc3545;
        color: #721c24;
    }
    .wilayah-sync-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: wilayah-spin 0.75s linear infinite;
        flex-shrink: 0;
    }
    @keyframes wilayah-spin {
        to { transform: rotate(360deg); }
    }
    .wilayah-sync-icon {
        flex-shrink: 0;
        font-size: 1rem;
    }
    .wilayah-sync-dismiss {
        margin-left: auto;
        background: none;
        border: none;
        font-size: 1.1rem;
        cursor: pointer;
        color: inherit;
        opacity: 0.7;
        padding: 0 4px;
        line-height: 1;
    }
    .wilayah-sync-dismiss:hover {
        opacity: 1;
    }
</style>
<script>
(function() {
    // Konfigurasi
    var WILAYAH_URL = @json($koneksiPantau);
    var CURRENT_VALUE = @json($data->value ?? '');
    var SYNC_STATUS_URL = @json(route('aplikasi.statusSinkronWilayah'));

    // Tunggu DOM ready tanpa jQuery
    function onReady(fn) {
        if (document.readyState !== 'loading') { fn(); }
        else { document.addEventListener('DOMContentLoaded', fn); }
    }

    onReady(function() {
        // Elemen asli <select> — sembunyikan dan ganti dengan custom input
        var origSelect = document.querySelector('select[name="pengaturan_wilayah"]');
        if (!origSelect) return;

        var parentDiv = origSelect.parentElement;
        origSelect.style.display = 'none';

        // Buat wrapper autocomplete
        var wrapper = document.createElement('div');
        wrapper.className = 'wilayah-autocomplete';

        // Input pencarian
        var searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.className = 'wilayah-search-input';
        searchInput.autocomplete = 'off';
        searchInput.value = CURRENT_VALUE || '';

        // Tombol clear (×)
        var clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'wilayah-clear-btn';
        clearBtn.innerHTML = '&times;';
        if (CURRENT_VALUE) clearBtn.style.display = 'block';

        // Dropdown hasil
        var dropdown = document.createElement('div');
        dropdown.className = 'wilayah-dropdown';

        wrapper.appendChild(searchInput);
        wrapper.appendChild(clearBtn);
        wrapper.appendChild(dropdown);
        parentDiv.insertBefore(wrapper, origSelect);

        // === Banner Status Sinkronisasi Wilayah ===
        var syncBanner = document.createElement('div');
        syncBanner.className = 'wilayah-sync-status';
        syncBanner.id = 'wilayah-sync-status';
        parentDiv.insertBefore(syncBanner, wrapper.nextSibling);

        var syncPollingTimer = null;

        function updateSyncBanner(status, message, extra) {
            syncBanner.className = 'wilayah-sync-status show status-' + status;
            var icon = '';
            if (status === 'running') {
                icon = '<span class="wilayah-sync-spinner"></span>';
            } else if (status === 'succeeded') {
                icon = '<span class="wilayah-sync-icon">✅</span>';
            } else if (status === 'failed') {
                icon = '<span class="wilayah-sync-icon">❌</span>';
            }

            var progressText = '';
            if (status === 'running' && extra && extra.progress !== null && extra.progress !== undefined) {
                progressText = ' (' + extra.progress + '%)';
            }

            var dismissBtn = (status !== 'running')
                ? '<button type="button" class="wilayah-sync-dismiss" title="Tutup">&times;</button>'
                : '';

            syncBanner.innerHTML = icon + '<span>' + message + progressText + '</span>' + dismissBtn;

            // Tambahkan event dismiss
            var btn = syncBanner.querySelector('.wilayah-sync-dismiss');
            if (btn) {
                btn.addEventListener('click', function() {
                    syncBanner.classList.remove('show');
                    stopSyncPolling();
                });
            }
        }

        function hideSyncBanner() {
            syncBanner.classList.remove('show');
            syncBanner.className = 'wilayah-sync-status';
        }

        function checkSyncStatus() {
            fetch(SYNC_STATUS_URL, { credentials: 'same-origin' })
                .then(function(res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(function(data) {
                    if (data.status === 'running') {
                        updateSyncBanner('running', data.message, { progress: data.progress });
                    } else if (data.status === 'succeeded') {
                        // Hanya tampilkan jika selesai dalam 5 menit terakhir
                        var finishedAt = data.finished_at ? new Date(data.finished_at) : null;
                        var now = new Date();
                        var diffMinutes = finishedAt ? (now - finishedAt) / 60000 : 999;
                        if (diffMinutes <= 5) {
                            updateSyncBanner('succeeded', data.message);
                        } else {
                            hideSyncBanner();
                        }
                        stopSyncPolling();
                    } else if (data.status === 'failed') {
                        // Hanya tampilkan jika gagal dalam 5 menit terakhir
                        var failedAt = data.finished_at ? new Date(data.finished_at) : null;
                        var nowFail = new Date();
                        var diffMinFail = failedAt ? (nowFail - failedAt) / 60000 : 999;
                        if (diffMinFail <= 5) {
                            updateSyncBanner('failed', data.message);
                        } else {
                            hideSyncBanner();
                        }
                        stopSyncPolling();
                    } else {
                        // idle — tidak ada proses
                        hideSyncBanner();
                        stopSyncPolling();
                    }
                })
                .catch(function(err) {
                    console.error('Gagal cek status sinkronisasi:', err);
                });
        }

        function startSyncPolling() {
            if (syncPollingTimer) return; // sudah berjalan
            checkSyncStatus(); // cek langsung
            syncPollingTimer = setInterval(checkSyncStatus, 5000); // polling tiap 5 detik
        }

        function stopSyncPolling() {
            if (syncPollingTimer) {
                clearInterval(syncPollingTimer);
                syncPollingTimer = null;
            }
        }

        // Mulai polling saat halaman dimuat (untuk cek apakah ada sinkronisasi yang sedang berjalan)
        startSyncPolling();

        // State
        var debounceTimer = null;
        var activeIndex = -1;
        var currentResults = [];
        var isSelected = !!CURRENT_VALUE;

        // Ambil level saat ini
        function getLevel() {
            var levelSelect = document.querySelector('select[name="level"]');
            return levelSelect ? parseInt(levelSelect.value) || 2 : 2;
        }

        // Placeholder berdasarkan level
        function updatePlaceholder() {
            var level = getLevel();
            switch (level) {
                case 1: searchInput.placeholder = 'Ketik nama provinsi (min 2 huruf)...'; break;
                case 3: searchInput.placeholder = 'Ketik nama kecamatan/kabupaten (min 2 huruf)...'; break;
                default: searchInput.placeholder = 'Ketik nama kabupaten (min 2 huruf)...'; break;
            }
        }
        updatePlaceholder();

        // Set hidden input values
        function setHiddenInputs(item) {
            var level = getLevel();
            setVal('kode_wilayah', item.id || '');
            setVal('kode_provinsi', item.kode_prov || '');
            setVal('nama_provinsi', item.nama_prov || '');
            setVal('nama_wilayah', item.text || '');
            if (level === 1) {
                setVal('kode_kabupaten', '');
                setVal('nama_kabupaten', '');
            } else {
                setVal('kode_kabupaten', item.kode_kab || '');
                setVal('nama_kabupaten', item.nama_kab || '');
            }
        }

        function clearHiddenInputs() {
            setVal('kode_wilayah', '');
            setVal('kode_provinsi', '');
            setVal('nama_provinsi', '');
            setVal('kode_kabupaten', '');
            setVal('nama_kabupaten', '');
            setVal('nama_wilayah', '');
        }

        function setVal(name, value) {
            var elements = document.querySelectorAll('input[name="' + name + '"]');
            elements.forEach(function(el) {
                el.value = value;
            });
        }

        // Set value pada <select> asli agar ikut tersubmit
        function setOrigSelectValue(text) {
            origSelect.innerHTML = '';
            if (text) {
                var opt = document.createElement('option');
                opt.value = text;
                opt.textContent = text;
                opt.selected = true;
                origSelect.appendChild(opt);
            }
        }

        // Render dropdown
        function renderDropdown(items, msg) {
            dropdown.innerHTML = '';
            currentResults = items || [];
            activeIndex = -1;

            if (msg) {
                var msgEl = document.createElement('div');
                msgEl.className = 'wilayah-msg';
                msgEl.textContent = msg;
                dropdown.appendChild(msgEl);
                dropdown.classList.add('show');
                return;
            }

            if (currentResults.length === 0) {
                var noResult = document.createElement('div');
                noResult.className = 'wilayah-msg';
                noResult.textContent = 'Tidak ditemukan';
                dropdown.appendChild(noResult);
                dropdown.classList.add('show');
                return;
            }

            currentResults.forEach(function(item, index) {
                var div = document.createElement('div');
                div.className = 'wilayah-item';
                div.textContent = item.text;
                div.setAttribute('data-index', index);
                div.addEventListener('mousedown', function(e) {
                    e.preventDefault(); // Cegah blur
                    selectItem(index);
                });
                dropdown.appendChild(div);
            });
            dropdown.classList.add('show');
        }

        function hideDropdown() {
            dropdown.classList.remove('show');
            dropdown.innerHTML = '';
            currentResults = [];
            activeIndex = -1;
        }

        // Pilih item
        function selectItem(index) {
            var item = currentResults[index];
            if (!item) return;

            searchInput.value = item.text;
            isSelected = true;
            setOrigSelectValue(item.text);
            setHiddenInputs(item);
            clearBtn.style.display = 'block';
            hideDropdown();
        }

        // Ambil kode kabupaten tersimpan (dari hidden input atau initial value) untuk pencarian kecamatan
        var INITIAL_KODE_PROV = (document.querySelector('input[name="kode_provinsi"]') || {}).value || '';
        var INITIAL_KODE_KAB = (document.querySelector('input[name="kode_kabupaten"]') || {}).value || '';

        function getKodeKab() {
            // Coba ambil dari hidden input aktif dulu
            var kodeProv = (document.querySelector('input[name="kode_provinsi"]') || {}).value || INITIAL_KODE_PROV;
            var kodeKab = (document.querySelector('input[name="kode_kabupaten"]') || {}).value || INITIAL_KODE_KAB;
            // kode_kabupaten bisa berupa "03" (2 digit) atau "33.03" (5 digit)
            if (kodeKab && kodeKab.indexOf('.') === -1 && kodeProv) {
                return kodeProv + '.' + kodeKab;
            }
            return kodeKab;
        }

        // Fetch data dari API
        function fetchWilayah(query, page) {
            var level = getLevel();
            var url = WILAYAH_URL + '&q=' + encodeURIComponent(query) + '&level=' + level + '&page=' + (page || 1);
            // Untuk level 3, kirim kode_kab agar backend bisa ambil kecamatan langsung dari API
            if (level === 3) {
                var kodeKab = getKodeKab();
                if (kodeKab) {
                    url += '&kode_kab=' + encodeURIComponent(kodeKab);
                }
            }

            renderDropdown([], 'Mencari...');

            fetch(url)
                .then(function(res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(function(data) {
                    var results = (data.results || []).map(function(item) {
                        return {
                            id: item.id || '',
                            text: item.text || '',
                            kode_prov: item.kode_prov || '',
                            nama_prov: item.nama_prov || '',
                            kode_kab: item.kode_kab || '',
                            nama_kab: item.nama_kab || '',
                            kode_kec: item.kode_kec || '',
                            nama_kec: item.nama_kec || ''
                        };
                    });
                    renderDropdown(results, null);
                })
                .catch(function(err) {
                    console.error('Wilayah fetch error:', err);
                    renderDropdown([], 'Gagal memuat data');
                });
        }

        // Event: ketik di input
        searchInput.addEventListener('input', function() {
            var val = this.value.trim();

            if (isSelected) {
                // User mulai edit setelah memilih → reset pilihan
                isSelected = false;
                setOrigSelectValue('');
                clearHiddenInputs();
            }

            clearBtn.style.display = val ? 'block' : 'none';

            if (val.length < 2) {
                hideDropdown();
                return;
            }

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                fetchWilayah(val, 1);
            }, 400);
        });

        // Event: navigasi keyboard (panah atas/bawah, Enter, Escape)
        searchInput.addEventListener('keydown', function(e) {
            var items = dropdown.querySelectorAll('.wilayah-item');
            if (!items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                updateActiveItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                updateActiveItem(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIndex >= 0) selectItem(activeIndex);
            } else if (e.key === 'Escape') {
                hideDropdown();
            }
        });

        function updateActiveItem(items) {
            items.forEach(function(el, i) {
                el.classList.toggle('active', i === activeIndex);
            });
            if (items[activeIndex]) {
                items[activeIndex].scrollIntoView({ block: 'nearest' });
            }
        }

        // Event: blur → tutup dropdown
        searchInput.addEventListener('blur', function() {
            setTimeout(hideDropdown, 200);
        });

        // Event: focus → buka lagi jika sudah ada text
        searchInput.addEventListener('focus', function() {
            var val = this.value.trim();
            if (val.length >= 2 && !isSelected) {
                fetchWilayah(val, 1);
            }
        });

        // Event: tombol clear
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            isSelected = false;
            clearBtn.style.display = 'none';
            setOrigSelectValue('');
            clearHiddenInputs();
            hideDropdown();
            searchInput.focus();
        });

        // Event: level berubah → reset semua
        var levelSelect = document.querySelector('select[name="level"]');
        if (levelSelect) {
            levelSelect.addEventListener('change', function() {
                searchInput.value = '';
                isSelected = false;
                clearBtn.style.display = 'none';
                setOrigSelectValue('');
                clearHiddenInputs();
                hideDropdown();
                updatePlaceholder();
                // Sembunyikan banner sinkronisasi saat level berubah
                hideSyncBanner();
                stopSyncPolling();
            });
        }

        // Set initial select value jika ada
        if (CURRENT_VALUE) {
            setOrigSelectValue(CURRENT_VALUE);
        }
    });
})();
</script>
@endpush
