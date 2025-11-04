# OpenSID Multi-Tenant with OpenLiteSpeed

Sistem multi-tenant untuk OpenSID menggunakan OpenLiteSpeed dengan isolasi keamanan yang ketat. Setiap instance OpenSID berjalan sebagai user Linux terpisah dengan konfigurasi PHP dan socket LSAPI yang terisolasi.

## Fitur Utama

- ✅ **Isolasi Keamanan**: Setiap site berjalan sebagai user Linux terpisah
- ✅ **PHP LSAPI Terpisah**: Socket unik untuk setiap site
- ✅ **Konfigurasi PHP Per-Site**: php.ini yang dapat dikustomisasi per tenant
- ✅ **OpenLiteSpeed Integration**: Virtual Host dan External App otomatis
- ✅ **Management Console**: Artisan commands untuk manajemen site
- ✅ **Template System**: Template yang dapat dikustomisasi
- ✅ **Error Handling**: Comprehensive error handling dan logging
- ✅ **Security Headers**: Default security headers dan proteksi file

## Persyaratan Sistem

- PHP 8.1+ dengan LSAPI support
- OpenLiteSpeed Web Server
- Laravel 9+
- Linux dengan sudo access untuk manajemen user
- Directory `/var/www/html/multisite` dengan write permission

## Instalasi

1. **Copy files ke project Laravel**:
   Semua file sudah terintegrasi dalam repository ini.

2. **Set permission untuk base directory**:
   ```bash
   sudo mkdir -p /var/www/html/multisite
   sudo chown www-data:www-data /var/www/html/multisite
   sudo chmod 755 /var/www/html/multisite
   ```

3. **Pastikan OpenLiteSpeed berjalan**:
   ```bash
   sudo service lsws restart
   ```

4. **Verifikasi instalasi**:
   ```bash
   php artisan multitenant:list-sites
   ```

## Penggunaan

### Menambah Site Baru

```bash
php artisan multitenant:add-site 1304012006 opensid1304012006.example.com
```

**Parameters**:
- `kodeDesa`: Kode desa 10 digit (format: 1304012006)
- `domain`: Domain untuk site (contoh: opensid1304012006.example.com)

**Options**:
- `--force`: Force create meskipun site sudah ada

### Menghapus Site

```bash
php artisan multitenant:remove-site 1304012006 opensid1304012006.example.com
```

**Parameters**:
- `kodeDesa`: Kode desa yang akan dihapus
- `domain`: Domain site (opsional)

**Options**:
- `--force`: Hapus tanpa konfirmasi

### Melihat Daftar Site

```bash
# Tampilan tabel ringkas
php artisan multitenant:list-sites

# Tampilan detail
php artisan multitenant:list-sites --detailed

# Output JSON untuk integrasi
php artisan multitenant:list-sites --json
```

## Struktur Directory

Setiap site memiliki struktur directory sebagai berikut:

```
/var/www/html/multisite/
├── 1304012006/                 # Kode Desa
│   ├── public/                 # Document Root
│   │   ├── index.php          # Landing page
│   │   └── .htaccess          # URL rewriting & security
│   ├── logs/                   # Log files
│   │   ├── access.log         # Web server access log
│   │   ├── error.log          # Web server error log
│   │   └── php_error.log      # PHP error log
│   ├── tmp/                    # Temporary files
│   │   ├── sessions/          # PHP sessions
│   │   └── uploads/           # Upload temporary
│   └── php/                    # PHP configuration
│       └── php.ini            # Per-site PHP configuration
```

## Konfigurasi

### Multi-tenant Configuration (`config/multitenant.php`)

```php
return [
    'base_path' => '/var/www/html/multisite',
    'php' => [
        'version' => '8.1',
        'socket_prefix' => 'uds://tmp/lsphp_',
    ],
    'security' => [
        'user_prefix' => 'opensid_',
        'group' => 'www-data',
        'file_permissions' => [
            'directories' => 0750,
            'files' => 0640,
        ],
    ],
];
```

### OpenLiteSpeed Integration

Sistem akan otomatis membuat:

1. **External App**: LSAPI processor dengan socket terpisah
2. **Virtual Host**: Konfigurasi domain dan document root
3. **Listener Mapping**: Mapping domain ke virtual host

### PHP Configuration per Site

Setiap site mendapat `php.ini` dengan setting:

- **Security**: `allow_url_fopen = Off`, `expose_php = Off`
- **Memory**: `memory_limit = 256M`
- **Upload**: `upload_max_filesize = 32M`
- **Session**: Custom session path per site
- **Error Logging**: Per-site error log
- **Extensions**: OpenSID required extensions

## Security Features

### User Isolation

- Setiap site berjalan sebagai user `opensid_<kodedesa>`
- User tidak memiliki shell access (`/bin/false`)
- User tidak memiliki home directory
- File ownership dan permission ketat per site

### File System Protection

- Document root terisolasi per site
- Temporary directories terpisah
- Log files terpisah
- Session storage terpisah
- Upload directories terisolasi

### PHP Security

- `disable_functions` untuk command execution
- Session security settings
- File upload restrictions
- Error display disabled in production

### Web Server Security

- Default security headers
- File access restrictions (`.env`, `.ini`, etc.)
- Directory browsing disabled
- Server signature hidden

## Monitoring dan Debugging

### Log Files

Setiap site memiliki log terpisah:

- **Access Log**: `/var/www/html/multisite/<kodedesa>/logs/access.log`
- **Error Log**: `/var/www/html/multisite/<kodedesa>/logs/error.log`
- **PHP Error Log**: `/var/www/html/multisite/<kodedesa>/logs/php_error.log`

### Monitoring Commands

```bash
# Lihat ukuran disk per site
php artisan multitenant:list-sites --detailed

# Export data untuk monitoring
php artisan multitenant:list-sites --json > sites_report.json
```

## Template System

### Customizing Templates

Template files tersedia di:

- **Virtual Host**: `resources/views/multitenant/vhost.conf.blade.php`
- **PHP Configuration**: `resources/views/multitenant/php.ini.blade.php`

### Template Variables

Template menggunakan Blade engine dengan variables:

- `$kodeDesa`: Kode desa
- `$domain`: Domain name
- `$documentRoot`: Document root path
- `$errorLog`: Error log path
- `$accessLog`: Access log path
- `$sessionSavePath`: Session directory
- `$uploadTmpDir`: Upload temp directory

## Troubleshooting

### Site Creation Failed

1. **Check permissions**:
   ```bash
   ls -la /var/www/html/multisite/
   ```

2. **Check OpenLiteSpeed status**:
   ```bash
   sudo service lsws status
   ```

3. **Check logs**:
   ```bash
   tail -f /usr/local/lsws/logs/error.log
   ```

### User Creation Issues

1. **Check if user already exists**:
   ```bash
   id opensid_1304012006
   ```

2. **Manual user cleanup**:
   ```bash
   sudo userdel opensid_1304012006
   ```

### Permission Issues

1. **Reset directory ownership**:
   ```bash
   sudo chown -R opensid_1304012006:www-data /var/www/html/multisite/1304012006
   ```

2. **Reset permissions**:
   ```bash
   sudo find /var/www/html/multisite/1304012006 -type d -exec chmod 750 {} \;
   sudo find /var/www/html/multisite/1304012006 -type f -exec chmod 640 {} \;
   ```

## API Integration

Semua fungsi tersedia melalui service classes:

```php
use App\Services\MultiTenantService;

$service = app(MultiTenantService::class);

// Create site
$result = $service->createSite('1304012006', 'example.com');

// Get site info
$info = $service->getSiteInfo('1304012006');

// Remove site
$service->removeSite('1304012006', 'example.com');

// List all sites
$sites = $service->listSites();
```

## Contributing

1. Fork repository
2. Create feature branch
3. Run tests: `./vendor/bin/phpunit`
4. Submit pull request

## Support

- GitHub Issues: [Repository Issues](https://github.com/OpenSID/dasbor-siappakai/issues)
- Documentation: [Wiki](https://github.com/OpenSID/wiki-saas/issues)

## License

MIT License - see LICENSE file for details.