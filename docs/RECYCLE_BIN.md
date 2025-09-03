# RecycleBin Functionality Documentation

## Overview

The RecycleBin functionality provides a safe file deletion mechanism for the SiapPakai dashboard. Instead of permanently deleting files and folders, they are moved to a recycle bin where they can be recovered if needed and are automatically cleaned up after a specified period.

## Features

### 1. Soft Delete (Move to Recycle Bin)
- Files and folders are copied to `storage/app/recycleBin` before deletion
- Original files are only deleted after successful copy
- Metadata is preserved including original path, deletion timestamp, size, and type
- Unique naming prevents conflicts (timestamp-based naming with collision handling)

### 2. Automatic Cleanup
- Scheduled daily cleanup at midnight (00:00 Asia/Jakarta timezone)
- Configurable retention period (default: 30 days)
- Logs cleanup activity for monitoring

### 3. Error Handling
- Robust error handling with rollback on partial failures
- Detailed logging for debugging
- Exception handling with meaningful error messages

## Usage

### Using FileService (Recommended)

```php
use App\Services\FileService;

$fileService = new FileService();

// Soft delete (move to recycle bin)
try {
    $recycleBinPath = $fileService->deleteToRecycleBin('/path/to/file_or_folder');
    echo "File moved to recycle bin: $recycleBinPath";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Permanent delete (use with caution!)
$success = $fileService->deletePermanent('/path/to/file_or_folder');
```

### Using RecycleBinService Directly

```php
use App\Services\RecycleBinService;

$recycleBinService = new RecycleBinService();

// Move file/folder to recycle bin
$recycleBinPath = $recycleBinService->moveToRecycleBin('/path/to/item');

// Get recycle bin information
$info = $recycleBinService->getRecycleBinInfo();
echo "Total items: " . $info['total_items'];
echo "Total size: " . $info['total_size'] . " bytes";

// Manual cleanup (remove files older than specified days)
$result = $recycleBinService->cleanOldItems(30); // 30 days
echo "Deleted " . $result['deleted_count'] . " items";
```

## Console Commands

### Clean Recycle Bin
```bash
# Clean files older than 30 days (default)
php artisan recycle-bin:clean

# Clean files older than 7 days
php artisan recycle-bin:clean --days=7
```

### Test Functionality
```bash
# Run tests to verify recycle bin functionality
php artisan test:recycle-bin
```

## Directory Structure

```
storage/app/recycleBin/
├── 2025-08-10_05-22-11_deleted_file.txt
├── 2025-08-10_05-22-11_deleted_file.txt.meta
├── 2025-08-10_05-22-11_deleted_folder/
└── 2025-08-10_05-22-11_deleted_folder.meta
```

### Metadata Format

Each deleted item has an associated `.meta` file containing:

```json
{
    "original_path": "/original/path/to/item",
    "deleted_at": "2025-08-10T05:22:11+00:00",
    "size": 1024,
    "type": "file"
}
```

## Scheduling

The recycle bin cleanup is automatically scheduled to run daily at midnight. The schedule is managed through the `JadwalTugas` model in the database:

```php
// Schedule entry in pengaturan_jadwal_tugas table
[
    'command' => 'recycle-bin:clean',
    'timezone' => 'Asia/Jakarta', 
    'jam' => '00:00',
    'keterangan' => 'Jadwal Tugas Pembersihan Recycle Bin'
]
```

## Configuration

### Default Settings
- **Retention Period**: 30 days
- **Cleanup Schedule**: Daily at 00:00 Asia/Jakarta
- **Storage Path**: `storage/app/recycleBin`

### Customization
To modify the default retention period, pass the `--days` option to the cleanup command:

```bash
php artisan recycle-bin:clean --days=7
```

## Error Scenarios & Recovery

### Partial Copy Failures
If a file copy operation fails partially, the service automatically cleans up any partially copied files to prevent inconsistent state.

### Missing Original Files
The service checks for file existence before attempting to move them and throws appropriate exceptions.

### Storage Space Issues
Monitor available storage space as recycle bin files consume disk space until cleanup.

## Monitoring & Logging

All recycle bin operations are logged with appropriate levels:
- **INFO**: Successful operations
- **WARNING**: Non-critical issues (e.g., malformed metadata)
- **ERROR**: Critical failures

Log entries include context such as file paths, operation results, and error details.

## Testing

### Automated Tests
```bash
# Run unit tests
php artisan test --filter=RecycleBinServiceTest
```

### Manual Testing
```bash
# Run the provided manual test script
php test_recycle_bin.php
```

## Security Considerations

1. **File Permissions**: Recycle bin maintains original file permissions
2. **Path Traversal**: Service validates paths to prevent directory traversal attacks  
3. **Storage Isolation**: Recycle bin is isolated within the application storage directory
4. **Metadata Protection**: Metadata files use `.meta` extension to avoid confusion with user files

## Troubleshooting

### Common Issues

1. **Permission Denied**: Ensure web server has write access to `storage/app/` directory
2. **Storage Full**: Monitor disk space and adjust retention period if needed
3. **Long File Names**: Service handles long file names by truncating when necessary
4. **Special Characters**: Service preserves special characters in file names safely

### Debug Mode
Enable application debug mode to see detailed error messages and stack traces for troubleshooting.