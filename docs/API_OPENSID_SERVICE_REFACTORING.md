# ApiOpensidService Code Refactoring

## Overview
Refactoring ApiOpensidService untuk membuat kode lebih bersih, mudah dibaca, dan maintainable dengan menerapkan prinsip Single Responsibility Principle (SRP) dan membagi method besar menjadi method-method kecil yang fokus.

## Changes Made

### 1. **Struktur Class**

#### Added Constants:
```php
const DATABASE_TYPE_SINGLE = 'database_tunggal';
const DATABASE_TYPE_COMBINED = 'database_gabungan';
```

**Benefits:**
- Eliminasi magic strings
- Centralized database type definitions
- Easier maintenance and updates

### 2. **Method `konfigurasiEnv()` Refactoring**

#### Before:
- Satu method besar dengan 80+ baris kode
- Semua logic dalam satu tempat
- Sulit dibaca dan di-maintain

#### After:
Method dipecah menjadi:

```php
public function konfigurasiEnv(string $kodedesa): void
private function formatKodeDesa(string $kodedesa): string
private function getPelanggan(string $kodedesaFormatted): ?Pelanggan
private function getPathConfiguration(string $kodedesa): array
private function ensureEnvFileExists(string $envPath, string $envExamplePath): void
private function getDatabaseConfiguration(string $kodedesa, Pelanggan $pelanggan): array
private function getDatabaseType(): string
private function createDatabaseAndUser(array $databaseConfig): void
private function updateEnvFile(array $pathConfig, Pelanggan $pelanggan, array $databaseConfig): void
private function prepareEnvConfiguration(Pelanggan $pelanggan, array $databaseConfig): array
private function writeEnvFile(string $envPath, array $envConfig): void
private function runArtisanCommands(array $pathConfig): void
```

**Benefits:**
- **Single Responsibility**: Setiap method memiliki tanggung jawab yang jelas
- **Readability**: Kode lebih mudah dibaca dan dipahami
- **Testability**: Method kecil lebih mudah di-unit test
- **Reusability**: Method dapat digunakan kembali di tempat lain
- **Maintainability**: Perubahan lebih mudah dilakukan

### 3. **Method `konfigurasiIndex()` Refactoring**

#### Before:
- Method besar dengan 40+ baris kode
- Logic tercampur dalam satu method

#### After:
Method dipecah menjadi:

```php
public function konfigurasiIndex(string $kodedesa): void
private function getIndexPathConfiguration(string $kodedesa): array
private function validateIndexFile(string $apiIndex): bool
private function updateIndexTemplate(array $pathConfig): void
private function fixIndexConfiguration(string $apiIndex): void
private function cleanupSymlinks(array $pathConfig): void
private function executeIndexScript(array $pathConfig): void
```

**Benefits:**
- **Clear Flow**: Alur eksekusi lebih jelas
- **Error Handling**: Validasi lebih terstruktur
- **Modularity**: Setiap step dapat di-customize secara terpisah

### 4. **Method `installTemplateDesa()` Refactoring**

#### Before:
- Logic percabangan dalam satu method
- Duplikasi kode

#### After:
Method dipecah menjadi:

```php
public function installTemplateDesa(string $kodedesa): void
private function getInstallationPaths(string $kodedesa): array
private function isApiAlreadyInstalled(string $pathApi): bool
private function configureExistingInstallation(string $kodedesa): void
private function performFreshInstallation(array $pathConfig, string $kodedesa): void
private function prepareTempDirectory(string $temp): void
private function copyTemplateToTemp(array $pathConfig): void
```

**Benefits:**
- **Clear Decision Flow**: Logic percabangan lebih jelas
- **Reusable Components**: Method dapat digunakan di berbagai scenario
- **Better Error Handling**: Setiap step dapat menangani error secara spesifik

## 5. **Database Configuration Enhancement**

### Database Type Configuration:
```php
// Menggunakan konstanta untuk tipe database
if ($tipDatabase === self::DATABASE_TYPE_SINGLE) {
    // Database tunggal per desa
} else {
    // Database gabungan berdasarkan langganan
}
```

### Configuration Structure:
```php
return [
    'type' => self::DATABASE_TYPE_SINGLE,
    'database_name' => 'db_' . $kodedesa,
    'database_key' => $kodedesa,
    'user_key' => $kodedesa
];
```

**Benefits:**
- **Consistent Structure**: Format konfigurasi yang konsisten
- **Type Safety**: Menggunakan konstanta mengurangi typo
- **Flexibility**: Mudah menambah tipe database baru

## 6. **Environment Configuration**

### Centralized Environment Preparation:
```php
private function prepareEnvConfiguration(Pelanggan $pelanggan, array $databaseConfig): array
{
    return [
        'APP_KEY' => $appkey,
        'DB_HOST' => $DB_HOST,
        'DB_DATABASE' => $databaseConfig['database_name'],
        'DB_USERNAME' => 'user_' . $databaseConfig['user_key'],
        'DB_PASSWORD' => 'pass_' . $databaseConfig['user_key'],
        // ... other configurations
    ];
}
```

**Benefits:**
- **Centralized Configuration**: Semua environment variables dalam satu tempat
- **Dynamic Values**: Menggunakan data dari database configuration
- **Easy Modification**: Mudah menambah atau mengubah environment variables

## Code Quality Improvements

### 1. **Type Hints**
- Semua method parameter dan return types menggunakan type hints
- Meningkatkan type safety dan IDE support

### 2. **Method Visibility**
- Public methods untuk API yang dapat diakses dari luar
- Private methods untuk internal logic
- Encapsulation yang lebih baik

### 3. **Documentation**
- Semua method memiliki PHPDoc yang lengkap
- Parameter dan return types dijelaskan dengan jelas
- Purpose setiap method dijelaskan

### 4. **Error Handling**
- Early returns untuk mengurangi nesting
- Specific error logging untuk debugging
- Graceful handling untuk missing files/data

## Performance Benefits

### 1. **Reduced Complexity**
- Method kecil lebih cepat di-execute
- Easier untuk PHP opcache optimization

### 2. **Better Memory Usage**
- Variables scoped per method
- Reduced memory footprint per operation

### 3. **Debugging**
- Stack traces lebih informatif
- Easier to pinpoint issues

## Testing Benefits

### 1. **Unit Testing**
- Setiap method dapat di-test secara terpisah
- Mock dependencies lebih mudah

### 2. **Integration Testing**
- Test scenarios lebih specific
- Better test coverage

## Maintenance Benefits

### 1. **Code Changes**
- Perubahan logic tidak mempengaruhi method lain
- Easier to add new features

### 2. **Bug Fixes**
- Issues dapat diisolasi ke method specific
- Reduced risk of regression

### 3. **Code Review**
- Smaller methods easier to review
- Clear intent and purpose

## Migration Path

Jika ada kode lain yang menggunakan ApiOpensidService:

### 1. **Public Interface Unchanged**
- Method public (`installTemplateDesa`, `konfigurasiEnv`, `konfigurasiIndex`) tetap sama
- Backward compatibility terjaga

### 2. **Internal Changes Only**
- Refactoring hanya pada internal methods
- No breaking changes untuk consumer

## Future Enhancements

### 1. **Service Extraction**
- DatabaseConfigurationService
- EnvironmentConfigurationService
- IndexConfigurationService

### 2. **Interface Implementation**
- ApiOpensidServiceInterface
- Better dependency injection

### 3. **Event Dispatching**
- Configuration events
- Progress tracking

### 4. **Caching**
- Configuration caching
- Template caching

## Conclusion

Refactoring ini memberikan foundation yang solid untuk:
- **Maintainability**: Kode lebih mudah di-maintain
- **Scalability**: Mudah menambah features baru
- **Testability**: Better test coverage
- **Readability**: Developer experience yang lebih baik
- **Reliability**: Reduced bugs dan improved error handling

Prinsip SOLID telah diterapkan, khususnya Single Responsibility Principle, untuk menciptakan kode yang lebih clean dan professional.
