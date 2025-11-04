# OpenLiteSpeed Listener Service Documentation

## Overview

Service ini memungkinkan Anda untuk:
1. **Membaca vhost dari file system** berdasarkan struktur folder `/usr/local/lsws/conf/vhosts-enabled/{vhostName}/`
2. **Parsing domain dari file `*-vh.conf`** dengan variable `vhDomain`
3. **Generate listener configuration** untuk HTTP (port 80) dan HTTPS (port 443)
4. **Auto-sync** dengan vhost yang sudah ada

## File System Structure

```
/usr/local/lsws/conf/vhosts-enabled/
├── siappakai.com/                     ← VHost Name
│   └── dasbor-siappakai-vh.conf      ← Config file
├── example.com/                       ← VHost Name  
│   └── example-vh.conf               ← Config file
├── test.local/                        ← VHost Name
│   └── test-site-vh.conf             ← Config file
└── multisite/                         ← VHost Name
    ├── tenant1.com/                   ← Sub VHost
    │   └── tenant1-vh.conf           ← Config file
    └── tenant2.com/                   ← Sub VHost
        └── tenant2-vh.conf           ← Config file
```

## Domain Parsing Logic

Service akan mencari domain dengan urutan prioritas:

### 1. Parse dari `vhDomain` di file `*-vh.conf`
```
# File: /usr/local/lsws/conf/vhosts-enabled/siappakai.com/dasbor-siappakai-vh.conf
docRoot $VH_ROOT
vhDomain siappakai.com              ← Ambil domain dari sini
```

### 2. Jika `vhDomain` berisi template variable
```
vhDomain {$domain}                  ← Template variable
```
Maka akan menggunakan nama folder parent sebagai domain: `siappakai.com`

### 3. Fallback ke nama folder
Jika tidak ada `vhDomain` atau parsing gagal, gunakan nama folder sebagai domain.

## Usage Examples

### 1. Discover Vhosts dari File System
```php
$service = app(OpenLiteSpeedListenerService::class);

// Discover semua vhosts
$vhosts = $service->discoverVhostsFromFileSystem();

foreach ($vhosts as $vhost) {
    echo "VHost: {$vhost['vhost_name']} → Domain: {$vhost['domain']}\n";
}
```

### 2. Generate Listeners dari Vhosts yang Ada
```php
// Auto-generate dari vhosts yang ditemukan
$config = $service->generateListenersFromVhosts();
echo $config;
```

**Output:**
```
listener Default {
address *:80
secure 0
map dasbor-siappakai siappakai.com
map example-site example.com
map test-site test.local
}

listener SSL {
address *:443
secure 1
map dasbor-siappakai siappakai.com
map example-site example.com
map test-site test.local
}
```

### 3. Sync dan Write ke File
```php
// Auto-generate dan tulis ke file
$success = $service->autoGenerateAndWriteListeners();

if ($success) {
    echo "Configuration updated successfully!";
}
```

### 4. Get Specific VHost Details
```php
$details = $service->getVhostDetails('siappakai.com');

if ($details) {
    echo "VHost: {$details['vhost_name']}\n";
    echo "Domain: {$details['domain']}\n";
    echo "Config: {$details['config_file']}\n";
}
```

## Artisan Commands

### Discover Vhosts
```bash
php artisan ols:generate-listeners --discover
```

### Generate Configuration
```bash
php artisan ols:generate-listeners
```

### Sync with Existing Vhosts
```bash
php artisan ols:generate-listeners --sync
```

### Write to File with Backup
```bash
php artisan ols:generate-listeners --sync --write --backup
```

## Configuration

### Environment Variables
```env
# OpenLiteSpeed paths
OLS_LISTENERS_CONFIG=/usr/local/lsws/conf/listeners.conf
OLS_HTTPD_CONFIG=/usr/local/lsws/conf/httpd_config.conf
OLS_VHOSTS_ENABLED=/usr/local/lsws/conf/vhosts-enabled
```

### Config File (config/siappakai.php)
```php
'openlitespeed' => [
    'listeners_config' => env('OLS_LISTENERS_CONFIG', '/usr/local/lsws/conf/listeners.conf'),
    'httpd_config' => env('OLS_HTTPD_CONFIG', '/usr/local/lsws/conf/httpd_config.conf'),
    'vhosts_enabled' => env('OLS_VHOSTS_ENABLED', '/usr/local/lsws/conf/vhosts-enabled'),
],
```

## Advanced Features

### 1. Real-time Monitoring
```php
$status = $service->getListenersStatus();
// Returns: exists, mappings_count, last_modified, file_size
```

### 2. Validation
```php
$isValid = $service->validateListenersConfig($config);
```

### 3. Backup Management
```php
$backupPath = $service->backupConfiguration();
```

### 4. Dynamic Management
```php
// Add mapping
$newConfig = $service->addMappingToListeners('new-site', 'newsite.com');

// Remove mapping  
$newConfig = $service->removeMappingFromListeners('old-site', 'oldsite.com');
```

## Error Handling

Service menggunakan Laravel logging untuk mencatat:
- Vhost discovery process
- Configuration changes
- Errors dan warnings
- Backup operations

## Security Considerations

1. **File Permissions**: Pastikan service memiliki akses read/write ke direktori OpenLiteSpeed
2. **Backup**: Selalu buat backup sebelum mengubah konfigurasi
3. **Validation**: Configuration divalidasi sebelum ditulis
4. **Logging**: Semua operasi dicatat untuk audit trail

## Integration dengan Existing System

Service ini dapat diintegrasikan dengan:
- **Site deployment** process
- **Multi-tenant** management
- **Auto-scaling** systems
- **Configuration management** tools

Dengan cara ini, Anda dapat secara otomatis mengelola listener OpenLiteSpeed berdasarkan vhost yang sudah ada di file system, tanpa perlu manual configuration.