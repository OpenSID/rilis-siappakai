# Cloudflare Domain Synchronization Module

## Overview

Modul Sinkronisasi Domain Cloudflare adalah sistem otomatis untuk mengidentifikasi, memvalidasi, dan memonitor status DNS domain pelanggan yang terdaftar di Cloudflare. Modul ini dirancang untuk:

- **Identifikasi Domain**: Menentukan apakah domain pelanggan adalah Cloudflare Zone atau Subdomain
- **Validasi DNS**: Memvalidasi konfigurasi DNS melalui Cloudflare API (bukan ping/nslookup)
- **Idempotent**: Aman dijalankan berkali-kali tanpa efek samping
- **Performance Optimized**: Menggunakan background jobs untuk menghindari request timeout pada sinkronisasi massal
- **Reliability**: Penanganan rate limiting Cloudflare dengan retry logic, exponential backoff, dan penanganan status 429 otomatis
- **Scalability**: Mendukung pagination penuh untuk pengambilan ribuan zone Cloudflare
- **Optimized Communication**: Menggunakan in-memory caching untuk mengurangi jumlah API call hingga 90% pada sinkronisasi massal

---

## Table of Contents

1. [Architecture](#architecture)
2. [Database Schema](#database-schema)
3. [Components](#components)
4. [Usage](#usage)
5. [API Endpoints](#api-endpoints)
6. [Command Line Interface](#command-line-interface)
7. [Web Dashboard](#web-dashboard)
8. [Configuration](#configuration)
9. [Troubleshooting](#troubleshooting)

---

## Architecture

### System Flow

```
┌─────────────────┐
│   Pelanggan     │
│ (domain_opensid)│
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────────────┐
│      DomainSyncService                  │
│  ┌───────────────────────────────────┐  │
│  │ 1. Get Active Cloudflare Accounts│  │
│  │ 2. Identify Domain Type           │  │
│  │    - Check if Zone                │  │
│  │    - Check if Subdomain (suffix)  │  │
│  │ 3. Validate DNS via CF API        │  │
│  │ 4. Save to customer_domains       │  │
│  └───────────────────────────────────┘  │
└─────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────┐
│      CloudflareService                  │
│  ┌───────────────────────────────────┐  │
│  │ - getAllZones() (Paginated & Cached)│  │
│  │ - getZoneByName() (Cached)         │  │
│  │ - getDnsRecords()                 │  │
│  │ - validateDnsRecord()             │  │
│  │ - makeApiCall() (Backoff/Retry)    │  │
│  └───────────────────────────────────┘  │
└─────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────┐
│      Cloudflare API                     │
│  - GET /zones                           │
│  - GET /zones/{id}/dns_records          │
└─────────────────────────────────────────┘
```

### Domain Type Identification Logic

```
┌─────────────────────────────────────────┐
│  Domain: example.desa.id                │
└────────────┬────────────────────────────┘
             │
             ▼
    ┌────────────────────┐
    │ Check as Zone?     │
    │ GET /zones?name=   │
    └────────┬───────────┘
             │
        ┌────┴────┐
        │  Found? │
        └────┬────┘
             │
      ┌──────┴──────┐
      │             │
     Yes           No
      │             │
      ▼             ▼
  ┌──────┐    ┌──────────────┐
  │ ZONE │    │ Get All Zones│
  └──────┘    └──────┬───────┘
                     │
                     ▼
              ┌──────────────┐
              │Suffix Match? │
              │ desa.id      │
              └──────┬───────┘
                     │
                ┌────┴────┐
                │  Match? │
                └────┬────┘
                     │
              ┌──────┴──────┐
              │             │
             Yes           No
              │             │
              ▼             ▼
        ┌───────────┐  ┌──────────┐
        │SUBDOMAIN  │  │NOT_SYNCED│
        └───────────┘  └──────────┘
```

---

## Database Schema

### Table: `customer_domains`

Tabel ini menyimpan informasi sinkronisasi domain pelanggan dengan Cloudflare.

```sql
CREATE TABLE customer_domains (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    pelanggan_id BIGINT UNSIGNED NOT NULL,
    cloudflare_account_id BIGINT UNSIGNED NULL,
    
    -- Domain Information
    domain VARCHAR(255) NOT NULL,
    domain_type ENUM('zone', 'subdomain') NULL,
    
    -- Cloudflare Zone Information
    zone_id VARCHAR(64) NULL,
    zone_name VARCHAR(255) NULL,
    
    -- DNS Validation
    dns_status ENUM('OK', 'MISSING', 'IP_MISMATCH', 'NOT_SYNCED') DEFAULT 'NOT_SYNCED',
    current_ip VARCHAR(45) NULL,
    expected_ip VARCHAR(45) NULL,
    is_proxied BOOLEAN DEFAULT FALSE,
    
    -- Sync Status
    last_synced_at TIMESTAMP NULL,
    last_error TEXT NULL,
    sync_attempt_count INT DEFAULT 0,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    -- Constraints
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE,
    FOREIGN KEY (cloudflare_account_id) REFERENCES master_cloudflare(id) ON DELETE SET NULL,
    UNIQUE KEY (pelanggan_id, domain),
    INDEX (zone_id),
    INDEX (dns_status)
);
```

### Field Descriptions

| Field | Type | Description |
|-------|------|-------------|
| `pelanggan_id` | BIGINT | Foreign key ke tabel pelanggan |
| `cloudflare_account_id` | BIGINT | Cloudflare account yang digunakan untuk sync |
| `domain` | VARCHAR(255) | Domain pelanggan (dari `domain_opensid`) |
| `domain_type` | ENUM | Jenis domain: `zone` atau `subdomain` |
| `zone_id` | VARCHAR(64) | Cloudflare Zone ID yang menaungi domain |
| `zone_name` | VARCHAR(255) | Nama zone induk (untuk subdomain) |
| `dns_status` | ENUM | Status DNS: `OK`, `MISSING`, `IP_MISMATCH`, `NOT_SYNCED` |
| `current_ip` | VARCHAR(45) | IP address saat ini dari Cloudflare |
| `expected_ip` | VARCHAR(45) | IP address yang diharapkan (dari `IP_PUBLIC_SERVER`) |
| `is_proxied` | BOOLEAN | Apakah DNS record di-proxy oleh Cloudflare |
| `last_synced_at` | TIMESTAMP | Waktu terakhir sinkronisasi berhasil |
| `last_error` | TEXT | Error message jika sinkronisasi gagal |
| `sync_attempt_count` | INT | Jumlah percobaan sync yang gagal |

### DNS Status Values

| Status | Description | Badge Color |
|--------|-------------|-------------|
| `OK` | DNS record valid dan IP sesuai | 🟢 Green (success) |
| `MISSING` | DNS record tidak ditemukan di Cloudflare | 🟡 Yellow (warning) |
| `IP_MISMATCH` | DNS record ada tapi IP tidak sesuai | 🔴 Red (danger) |
| `NOT_SYNCED` | Belum pernah disinkronkan atau gagal | ⚪ Gray (secondary) |

---

## Components

### 1. Models

#### CustomerDomain Model

**Location**: `app/Models/CustomerDomain.php`

**Relationships**:
```php
// Belongs to Pelanggan
$domain->pelanggan; // App\Models\Pelanggan

// Belongs to MasterCloudflare
$domain->cloudflareAccount; // App\Models\MasterCloudflare
```

**Scopes**:
```php
// Filter by DNS status
CustomerDomain::byDnsStatus('OK')->get();

// Filter by domain type
CustomerDomain::byDomainType('subdomain')->get();

// Only synced domains
CustomerDomain::synced()->get();

// Only not synced domains
CustomerDomain::notSynced()->get();

// Domains with errors
CustomerDomain::withErrors()->get();
```

**Helper Methods**:
```php
// Check if synced successfully
$domain->isSyncedSuccessfully(); // bool

// Check if needs resync
$domain->needsResync(); // bool
```

#### Pelanggan Model (Updated)

**New Relationships**:
```php
// Get all customer domains
$pelanggan->customerDomains; // Collection<CustomerDomain>

// Get primary domain (domain_opensid)
$pelanggan->primaryDomain; // CustomerDomain
```

### 2. Enums

#### DomainType Enum

**Location**: `app/Enums/DomainType.php`

```php
use App\Enums\DomainType;

DomainType::ZONE->value;      // 'zone'
DomainType::SUBDOMAIN->value; // 'subdomain'

DomainType::ZONE->label();      // 'Zone Utama'
DomainType::SUBDOMAIN->label(); // 'Subdomain'
```

#### DnsStatus Enum

**Location**: `app/Enums/DnsStatus.php`

```php
use App\Enums\DnsStatus;

DnsStatus::OK->value;          // 'OK'
DnsStatus::MISSING->value;     // 'MISSING'
DnsStatus::IP_MISMATCH->value; // 'IP_MISMATCH'
DnsStatus::NOT_SYNCED->value;  // 'NOT_SYNCED'

DnsStatus::OK->label();        // 'DNS Valid'
DnsStatus::OK->badgeColor();   // 'success'
```

### 3. Services

#### CloudflareService (Extended)

**Location**: `app/Services/CloudflareService.php`

**Location**: `app/Services/CloudflareService.php`

**Core Improvements**:
- **In-Memory Caching**: Menyimpan daftar zone dalam memori selama proses berjalan (static cache). Sangat efektif untuk sinkronisasi massal di mana ribuan subdomain memverifikasi zona induknya.
- **Full Pagination**: Mendukung pengambilan data zone secara bertahap (paginated loop) untuk menangani akun dengan jumlah domain yang sangat besar.
- **Unified API Wrapper**: Semua pemanggilan API melewati `makeApiCall()` yang menangani:
  - Exponential Backoff
  - Retry pada kegagalan 5xx atau koneksi
  - Penanganan otomatis `HTTP 429` (Rate Limit) dengan membaca header `Retry-After`.

**Methods**:

```php
// Get all zones for an account
$result = $cloudflareService->getAllZones($apiToken);
// Returns: ['success' => bool, 'data' => array, 'error' => string|null]

// Get zone by name
$result = $cloudflareService->getZoneByName('desa.id', $apiToken);
// Returns: ['success' => bool, 'data' => array|null, 'error' => string|null]

// Get DNS records
$result = $cloudflareService->getDnsRecords($zoneId, $apiToken, [
    'type' => 'A',
    'name' => 'example.desa.id'
]);
// Returns: ['success' => bool, 'data' => array, 'error' => string|null]

// Validate DNS record
$result = $cloudflareService->validateDnsRecord(
    $zoneId, 
    'example.desa.id', 
    '103.xxx.xxx.xxx', 
    $apiToken
);
// Returns: [
//     'success' => bool, 
//     'status' => 'OK|MISSING|IP_MISMATCH', 
//     'current_ip' => string|null, 
//     'is_proxied' => bool
// ]
```

#### DomainSyncService

**Location**: `app/Services/DomainSyncService.php`

**Main Methods**:

```php
use App\Services\DomainSyncService;

$syncService = app(DomainSyncService::class);

// Sync all customers
$result = $syncService->syncAllCustomers(function($current, $total, $customer) {
    echo "Syncing {$current}/{$total}: {$customer->domain_opensid}\n";
});
// Returns: [
//     'success' => bool,
//     'synced' => int,
//     'failed' => int,
//     'errors' => array
// ]

// Sync single customer
$pelanggan = Pelanggan::find(1);
$result = $syncService->syncCustomerDomain($pelanggan);
// Returns: [
//     'success' => bool,
//     'domain_type' => 'zone|subdomain|null',
//     'dns_status' => 'OK|MISSING|IP_MISMATCH|NOT_SYNCED',
//     'error' => string|null
// ]
```

### 4. Controllers

#### DomainSyncController

**Location**: `app/Http/Controllers/Pengaturan/DomainSyncController.php`

**Methods**:
- `index()` - Display dashboard
- `syncAll()` - Sync all customer domains (AJAX)
- `syncOne()` - Sync specific customer domain (AJAX)
- `statistics()` - Get sync statistics (AJAX)

---

## Usage

### Initial Setup

1. **Run Migration**:
```bash
php artisan migrate
```

2. **Configure Environment** (Optional):
```bash
# Add to .env
CLOUDFLARE_DEFAULT_IP=103.xxx.xxx.xxx
```

3. **Ensure Active Cloudflare Account**:
   - Navigate to `/master-cloudflare`
   - Add at least one active Cloudflare account with valid API token

### First Sync

**Via Background Job (Recommended)**:
```bash
# Dispatch job via artisan tinker
php artisan tinker
>>> \App\Jobs\SyncCustomerDomainsJob::dispatch();
```

**Via Command Line (Synchronous)**:
```bash
# Sync all customers
php artisan domains:sync

# Sync specific customer
php artisan domains:sync --customer=1
```

**Via Web Dashboard**:
1. Navigate to `/domain-sync`
2. Click "Sinkronkan Semua Domain"
3. Action akan dijadwalkan di background
4. Tunggu beberapa saat dan refresh halaman

> [!IMPORTANT]
> Karena fitur ini menggunakan **Background Jobs**, Anda harus menjalankan **Queue Worker** agar proses sinkronisasi berjalan:
> ```bash
> php artisan queue:work
> ```

### Scheduled Sync (Recommended)

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Sync domains daily at 2 AM
    $schedule->command('domains:sync')
        ->dailyAt('02:00')
        ->withoutOverlapping()
        ->runInBackground();
}
```

---

## API Endpoints

### GET `/domain-sync`

Display domain synchronization dashboard.

**Response**: HTML page

---

### POST `/domain-sync/sync-all`

Sync all customer domains.

**Headers**:
```
X-CSRF-TOKEN: {token}
```

**Response**:
```json
{
  "success": true,
  "message": "Berhasil sync 85 domain, gagal 15 domain",
  "data": {
    "success": true,
    "synced": 85,
    "failed": 15,
    "errors": [
      {
        "customer_id": 12,
        "domain": "example1.com",
        "error": "Domain tidak ditemukan di akun Cloudflare mana pun"
      }
    ]
  }
}
```

---

### POST `/domain-sync/sync-one`

Sync specific customer domain.

**Headers**:
```
X-CSRF-TOKEN: {token}
```

**Request Body**:
```json
{
  "pelanggan_id": 1
}
```

**Response**:
```json
{
  "success": true,
  "message": "Domain example.desa.id berhasil disinkronkan",
  "data": {
    "success": true,
    "domain_type": "subdomain",
    "dns_status": "OK",
    "error": null
  }
}
```

---

### GET `/domain-sync/statistics`

Get synchronization statistics.

**Response**:
```json
{
  "success": true,
  "data": {
    "total": 150,
    "ok": 120,
    "missing": 15,
    "ip_mismatch": 10,
    "not_synced": 5,
    "zones": 3,
    "subdomains": 147
  }
}
```

---

## Command Line Interface

### Command: `domains:sync`

**Signature**:
```bash
php artisan domains:sync [options]
```

**Options**:

| Option | Description |
|--------|-------------|
| `--customer=ID` | Sync specific customer by ID |
| `--force` | Force resync all domains (future) |

**Examples**:

```bash
# Sync all customers
php artisan domains:sync

# Sync customer ID 1
php artisan domains:sync --customer=1

# Sync customer ID 5
php artisan domains:sync --customer=5
```

**Output Example**:
```
═══════════════════════════════════════════════════════
   Cloudflare Domain Synchronization
═══════════════════════════════════════════════════════

Total pelanggan: 150

 45/150 [=========>          ] 30% 
 🔄 Syncing: tanjuangbungo-limapuluhkotakab.desa.id

═══════════════════════════════════════════════════════
   Summary
═══════════════════════════════════════════════════════

✓ Successfully synced: 135
✗ Failed: 15

Failed domains:

  • example1.com (Customer ID: 12)
    └─ Domain tidak ditemukan di akun Cloudflare mana pun
  • example2.com (Customer ID: 45)
    └─ Token tidak memiliki akses ke domain ini

⏱  Sync completed in 2m 34s
```

---

## Web Dashboard

### Access

**URL**: `/domain-sync`

**Route Name**: `domain-sync.index`

### Features

#### 1. Statistics Cards

Dashboard menampilkan 4 kartu statistik real-time:

- **DNS Valid (OK)** - 🟢 Green card
- **DNS Tidak Ditemukan (MISSING)** - 🟡 Yellow card
- **IP Tidak Sesuai (IP_MISMATCH)** - 🔴 Red card
- **Belum Disinkronkan (NOT_SYNCED)** - ⚪ Gray card

#### 2. DataTable

Tabel interaktif dengan kolom:

| Column | Description |
|--------|-------------|
| No | Nomor urut |
| Nama Desa | Nama pelanggan |
| Domain | Domain dengan error message (jika ada) |
| Jenis | Badge: Zone atau Subdomain |
| Zone Induk | Nama zone Cloudflare |
| Status DNS | Badge berwarna sesuai status |
| IP Saat Ini | IP address dengan expected IP |
| Proxied | Yes/No badge |
| Terakhir Sync | Waktu relatif (e.g., "2 hours ago") |
| Aksi | Tombol sync ulang |

**Features**:
- Sortable columns
- Search functionality
- Pagination
- Status filters

#### 3. Actions

**Sync All Domains**:
- Button: "Sinkronkan Semua Domain"
- Confirmation dialog (SweetAlert2)
- Progress indicator
- Success/error notification
- Auto-reload after completion

**Sync Single Domain**:
- Icon button per row
- Loading indicator
- Success/error notification
- Auto-reload after completion

#### 4. Filters

Filter by DNS status:
- Semua (All)
- Valid (OK)
- Missing
- Mismatch (IP_MISMATCH)
- Not Synced

---

## Configuration

### Environment Variables

Add to `.env`:

```bash
# Public IP of this server for DNS validation
IP_PUBLIC_SERVER=103.xxx.xxx.xxx

# API retry attempts
CLOUDFLARE_API_RETRY_ATTEMPTS=5

# Delay between API calls (milliseconds)
CLOUDFLARE_API_CALL_DELAY=1000
```

### Configuration File

Seluruh konfigurasi teknis dipusatkan di `config/cloudflare.php`. Pastikan nilai-nilai berikut sesuai dengan kebutuhan infrastruktur Anda:

```php
return [
    'base_url'           => 'https://api.cloudflare.com/client/v4',
    'api_retry_attempts' => 3,    // Jumlah percobaan retry
    'retry_delay'        => 1000, // Jeda antar retry (ms)
    'api_call_delay'     => 1000, // Jeda antar API call sukses (ms)
    'per_page'           => 50,   // Record per halaman API
];
```

### Cloudflare API Token

**Required Permissions**:
- Zone:Read
- DNS:Read

**Setup**:
1. Login to Cloudflare Dashboard
2. Go to My Profile > API Tokens
3. Create Token with permissions above
4. Add to application via `/master-cloudflare`

---

## Troubleshooting

### Issue: "No active Cloudflare accounts found"

**Cause**: Tidak ada akun Cloudflare aktif di database.

**Solution**:
```sql
-- Check active accounts
SELECT * FROM master_cloudflare WHERE status = 'aktif';

-- If none, add via UI at /master-cloudflare
```

---

### Issue: "Domain tidak ditemukan di akun Cloudflare mana pun"

**Possible Causes**:
1. Domain belum ditambahkan ke Cloudflare
2. API token tidak memiliki akses ke zone
3. Typo di `domain_opensid`

**Solution**:
1. Verify domain exists in Cloudflare dashboard
2. Check API token permissions
3. Verify `domain_opensid` spelling in database

---

### Issue: DNS status always "NOT_SYNCED"

**Possible Causes**:
1. Cloudflare API error
2. Network connectivity issue
3. Invalid API token

**Solution**:
```bash
# Check logs
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log | grep "DomainSync"

# Test API token
php artisan tinker
>>> $cf = app(\App\Services\CloudflareService::class);
>>> $cf->verifyToken('your_token_here');
```

---

### Issue: IP_MISMATCH for all domains

**Cause**: `CLOUDFLARE_DEFAULT_IP` tidak sesuai atau tidak di-set.

**Solution**:
```bash
# Set correct IP in .env
CLOUDFLARE_DEFAULT_IP=103.xxx.xxx.xxx

# Or set per-domain expected_ip in database
UPDATE customer_domains 
SET expected_ip = '103.xxx.xxx.xxx' 
WHERE pelanggan_id = 1;
```

---

## Database Queries

### Get all domains with issues

```sql
SELECT 
    p.nama_desa,
    cd.domain,
    cd.dns_status,
    cd.last_error
FROM customer_domains cd
JOIN pelanggan p ON cd.pelanggan_id = p.id
WHERE cd.dns_status IN ('MISSING', 'IP_MISMATCH', 'NOT_SYNCED')
ORDER BY cd.dns_status, p.nama_desa;
```

### Get sync statistics

```sql
SELECT 
    dns_status,
    COUNT(*) as count,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM customer_domains), 2) as percentage
FROM customer_domains
GROUP BY dns_status;
```

### Get domains not synced in last 7 days

```sql
SELECT 
    p.nama_desa,
    cd.domain,
    cd.last_synced_at,
    DATEDIFF(NOW(), cd.last_synced_at) as days_ago
FROM customer_domains cd
JOIN pelanggan p ON cd.pelanggan_id = p.id
WHERE cd.last_synced_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
   OR cd.last_synced_at IS NULL
ORDER BY cd.last_synced_at ASC;
```

---

## Best Practices

### 1. Regular Sync

Run sync command daily via cron:
```bash
# In app/Console/Kernel.php
$schedule->command('domains:sync')->dailyAt('02:00');
```

### 2. Monitor Failed Syncs

Check dashboard regularly for domains with status:
- MISSING
- IP_MISMATCH
- NOT_SYNCED

### 3. API Token Management

- Use separate API tokens per environment (dev, staging, prod)
- Rotate tokens periodically
- Monitor token usage via Cloudflare dashboard

### 4. Error Handling

- Check `last_error` field for failed syncs
- Review logs for detailed error messages
- Fix issues and resync manually

### 5. Performance & Queue

- Untuk sinkronisasi massal, sistem menggunakan `SyncCustomerDomainsJob`.
- Pastikan antrean (queue) dikonfigurasikan dengan benar di `.env` (misal: `QUEUE_CONNECTION=database`).
- Jalankan worker secara terus-menerus di server menggunakan Supervisor:
  ```bash
  php artisan queue:work --timeout=300 --tries=3
  ```

---

## Future Enhancements

### Planned Features

3. **Advanced Filtering**
   - Filter by Cloudflare account
   - Filter by zone name
   - Date range filters

4. **Bulk Actions**
   - Bulk resync selected domains
   - Export to CSV/Excel

---

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel-{date}.log`
2. Review this documentation
3. Contact development team

---

## Changelog

### Version 1.0.0 (2025-12-17)

**Initial Release**:
- ✅ Domain type identification (zone/subdomain)
- ✅ DNS validation via Cloudflare API
- ✅ Web dashboard with statistics
- ✅ CLI command for sync
- ✅ Manual resync functionality
- ✅ Comprehensive logging
- ✅ Idempotent operations

### Version 1.1.0 (2025-12-18)

**Scalability & Performance Refactor**:
- ✅ **In-Memory Zone Caching**: Optimasi API call (1 call per token per session).
- ✅ **Full Pagination**: Mendukung akun dengan jumlah zone > 50.
- ✅ **Centralized Config**: Semua setting teknis pindah ke `config/cloudflare.php`.
- ✅ **Robust API Wrapper**: Exponential backoff dan 429 handling otomatis.
- ✅ **Queue Timeout Fix**: Perbaikan bottleneck performa pada sinkronisasi massal.
- ✅ **Safe IP Rename**: Konsistensi penamaan `IP_PUBLIC_SERVER` di seluruh sistem.

---

## License

Internal use only. All rights reserved.
