# Database Configuration Enhancement

## Overview
Penambahan pengaturan database untuk memilih antara database tunggal dan database gabungan dalam sistem OpenSID API.

## Changes Made

### 1. ApiOpensidService.php
**File**: `app/Services/ApiOpensidService.php`

#### New Features:
- Menambahkan konfigurasi `pengaturan_database` untuk memilih tipe database
- Support untuk dua tipe database:
  - `database_tunggal`: Setiap desa memiliki database terpisah
  - `database_gabungan`: Beberapa desa dalam satu database berdasarkan langganan

#### Code Changes:
```php
// Konfigurasi pengaturan database
$pengaturanDatabase = Aplikasi::where('key', 'pengaturan_database')->first();
$tipDatabase = $pengaturanDatabase ? $pengaturanDatabase->value : 'database_gabungan';

// Tentukan nama database berdasarkan pengaturan
if ($tipDatabase === 'database_tunggal') {
    $database = 'db_' . $kodedesa;
} else {
    // Default: database_gabungan
    $database = 'db_gabungan_' . $langgananOpensid;
}
```

#### Database Creation Logic:
```php
// Buat database dan user berdasarkan tipe database
if ($tipDatabase === 'database_tunggal') {
    // Untuk database tunggal, buat database dan user per desa
    $databaseService->createDatabase($kodedesa);
    $databaseService->createUser($kodedesa, $kodedesa);
} else {
    // Untuk database gabungan, buat database berdasarkan langganan
    $databaseService->createDatabase('gabungan_' . $langgananOpensid);
    $databaseService->createUser('gabungan_' . $langgananOpensid, $kodedesa);
}
```

### 2. Database Migration
**File**: `database/migrations/2025_08_16_090824_add_pengaturan_database_to_aplikasis_table.php`

#### Purpose:
- Menambahkan pengaturan `pengaturan_database` ke tabel aplikasis
- Default value: `database_gabungan`

#### Migration Content:
```php
Aplikasi::create([
    'key' => 'pengaturan_database',
    'value' => 'database_gabungan',
    'keterangan' => 'Pilih tipe database: database_tunggal (setiap desa memiliki database terpisah) atau database_gabungan (beberapa desa dalam satu database berdasarkan langganan)',
    'jenis' => 'option',
    'kategori' => 'pengaturan_database',
    'script' => ''
]);
```

### 3. Database Seeder Updates
**Files**: 
- `database/seeders/Pengaturan/AplikasiSeeder.php`
- `database/seeders/DatabaseSeeder.php`

#### Changes:
- Menambahkan pengaturan database ke AplikasiSeeder
- Mengaktifkan AplikasiSeeder di DatabaseSeeder

## Database Schema Comparison

### Database Tunggal (database_tunggal)
```
Database Structure:
├── db_3303012001 (untuk desa 33.03.01.2001)
├── db_3303012002 (untuk desa 33.03.01.2002)
├── db_3303012003 (untuk desa 33.03.01.2003)
└── ...

User Structure:
├── user_3303012001@ip_source_code
├── user_3303012002@ip_source_code
├── user_3303012003@ip_source_code
└── ...
```

### Database Gabungan (database_gabungan)
```
Database Structure:
├── db_gabungan_premium (semua desa premium)
├── db_gabungan_basic (semua desa basic)
├── db_gabungan_trial (semua desa trial)
└── ...

User Structure (per desa):
├── user_3303012001@ip_source_code → db_gabungan_premium
├── user_3303012002@ip_source_code → db_gabungan_premium
├── user_3303012003@ip_source_code → db_gabungan_basic
└── ...
```

## Configuration Options

### pengaturan_database Values:
- **`database_tunggal`**: 
  - Setiap desa memiliki database terpisah
  - Format database: `db_{kode_desa}`
  - Format user: `user_{kode_desa}`
  
- **`database_gabungan`** (Default):
  - Beberapa desa dalam satu database berdasarkan langganan
  - Format database: `db_gabungan_{langganan}`
  - Format user: `user_{kode_desa}` (akses ke database gabungan)

## Benefits

### Database Tunggal:
- **Isolation**: Setiap desa memiliki database terpisah
- **Security**: Data desa tidak tercampur
- **Flexibility**: Mudah untuk backup/restore per desa
- **Scalability**: Dapat didistribusikan ke server berbeda

### Database Gabungan:
- **Resource Efficiency**: Menggunakan sumber daya server lebih efisien
- **Management**: Lebih mudah dalam pengelolaan database
- **Backup**: Backup bisa dilakukan per langganan
- **Cost Effective**: Mengurangi overhead database

## Usage Instructions

### Setting Configuration:
1. Akses panel admin
2. Masuk ke pengaturan aplikasi
3. Cari pengaturan "pengaturan_database"
4. Pilih salah satu:
   - `database_tunggal` - untuk database per desa
   - `database_gabungan` - untuk database berdasarkan langganan

### Database Migration:
```bash
# Run migration to add the setting
php artisan migrate

# Or seed the data
php artisan db:seed --class=AplikasiSeeder
```

## Implementation Notes

### Backward Compatibility:
- Default menggunakan `database_gabungan` untuk menjaga kompatibilitas
- Sistem akan otomatis membaca pengaturan dari database
- Jika pengaturan tidak ada, menggunakan default `database_gabungan`

### Database Naming Convention:
- **Tunggal**: `db_{kode_desa}` (contoh: `db_3303012001`)
- **Gabungan**: `db_gabungan_{langganan}` (contoh: `db_gabungan_premium`)

### User Naming Convention:
- **Format**: `user_{kode_desa}` (contoh: `user_3303012001`)
- **Password**: `pass_{kode_desa}` (contoh: `pass_3303012001`)

## Future Considerations

### Possible Enhancements:
1. **Database Sharding**: Pembagian database berdasarkan region
2. **Connection Pooling**: Optimisasi koneksi database
3. **Read Replicas**: Pemisahan read/write operations
4. **Dynamic Scaling**: Auto-scaling database berdasarkan load

### Migration Strategy:
- Jika ingin mengubah dari gabungan ke tunggal, perlu migration script
- Backup data sebelum melakukan perubahan konfigurasi
- Test di environment staging terlebih dahulu

## Related Files
- `app/Services/ApiOpensidService.php` - Main implementation
- `app/Services/DatabaseService.php` - Database operations
- `database/migrations/2025_08_16_090824_add_pengaturan_database_to_aplikasis_table.php` - Migration
- `database/seeders/Pengaturan/AplikasiSeeder.php` - Seeder data
