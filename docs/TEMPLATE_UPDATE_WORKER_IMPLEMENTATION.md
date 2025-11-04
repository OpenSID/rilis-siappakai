# Template Update Worker Implementation (Simplified)

## Overview
This implementation converts the synchronous `updateTemplate()` function into a background worker (job) to handle file processing operations that involve different file ownership permissions outside the application root directory. This is a simplified version without Service Provider complexity.

## What Was Changed

### 1. **Created Interface** - `app/Contracts/TemplateRepositoryInterface.php`
- Defines the contract for template processing operations
- Provides methods for processing templates for all customers or individual customers

### 2. **Created Repository** - `app/Repositories/TemplateRepository.php` 
- Implements `TemplateRepositoryInterface`
- Handles all template processing business logic
- Uses simple constructor instantiation (no complex dependency injection)
- Separates data access concerns from business logic

### 3. **Created Worker Job** - `app/Jobs/UpdateTemplateJob.php`
- Implements `ShouldQueue` for background processing
- Uses `IsMonitored` trait for queue monitoring 
- Has proper error handling and retry mechanisms
- Timeout set to 2 hours for large template processing
- Logs success and failure events
- Instantiates repository directly in handle method

### 4. **Refactored AplikasiService** - `app/Services/AplikasiService.php`
- `updateTemplate()` method now dispatches `UpdateTemplateJob` instead of processing directly
- Removed business logic methods (moved to repository)
- Cleaner, more focused service with single responsibility

## Benefits

### 🔒 **Permission Handling**
- Background worker runs with appropriate permissions for file operations
- Avoids permission issues when processing files outside application root

### ⚡ **Performance** 
- Non-blocking template updates (runs in background)
- Better user experience - no waiting for template processing to complete

### 🏗️ **Architecture**
- Clear separation of concerns (Controller → Service → Repository → Job)
- Simple instantiation without complex dependency injection
- Easily maintainable and understandable code
- Interface-based programming for consistency

### 🔍 **Monitoring**
- Job monitoring with `IsMonitored` trait
- Error logging and retry mechanisms  
- Failed job handling

## Usage

```php
// In AplikasiController or anywhere you need template updates
$aplikasiService = new AplikasiService();
$aplikasiService->updateTemplate(); // Dispatches job to queue

// Job will process templates for all customers in background
```

## Queue Configuration

Make sure your queue system is properly configured:

```bash
# Start queue worker
php artisan queue:work

# Or if using supervisor
sudo supervisorctl start siappakai-worker:*
```

## File Structure
```
app/
├── Contracts/
│   └── TemplateRepositoryInterface.php
├── Jobs/
│   └── UpdateTemplateJob.php
├── Repositories/
│   └── TemplateRepository.php
└── Services/
    └── AplikasiService.php (modified)
```

## Dependencies
- Laravel Queue System
- romanzipp/laravel-queue-monitor (for job monitoring)
- Existing FileService, EmailService, TemaService

This simplified implementation ensures that template processing operations are handled efficiently in the background while maintaining clean, readable, and maintainable code architecture without the complexity of Service Providers.