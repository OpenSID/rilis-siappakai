# Subscription Expiry Management

This feature automatically manages subscription expiration for OpenSID customers by checking expiry dates and deactivating expired websites.

## Overview

The system monitors two types of subscriptions:
- **Premium Subscription** (`tgl_akhir_premium`) - Controls `status_langganan_opensid`
- **SaaS Subscription** (`tgl_akhir_saas`) - Controls `status_langganan_saas`

## Business Logic

### Subscription Status Updates
- When `tgl_akhir_premium` expires → set `status_langganan_opensid = 3` (inactive)
- When `tgl_akhir_saas` expires → set `status_langganan_saas = 3` (inactive)

### Website Deactivation Logic
The system determines which date to use as the effective expiry date:

- If `tgl_akhir_saas` year is **9999** → use `tgl_akhir_premium` as reference
- Otherwise → use `tgl_akhir_saas` as reference

When the effective date has passed, the website is deactivated by redirecting its Apache vhost to display an expired subscription page.

## Command Usage

### Manual Execution
```bash
# Check and apply changes
php artisan siappakai:check-subscription-expiry

# Preview changes without applying them
php artisan siappakai:check-subscription-expiry --dry-run
```

### Automated Scheduling
The command is automatically scheduled to run daily at 1:00 AM (Asia/Jakarta timezone) via the Laravel scheduler.

## Technical Implementation

### Files Created/Modified
- `app/Console/Commands/CheckSubscriptionExpiry.php` - Main command
- `storage/app/halaman/expired.html` - Expiry page template
- `database/migrations/2025_08_13_020000_add_subscription_expiry_check_to_jadwal_tugas.php` - Scheduler entry

### Features
- **Dry-run mode** for safe testing
- **Environment validation** before execution
- **Individual error handling** (one failed customer doesn't stop others)
- **Vhost backup** before overwriting configurations
- **SSL certificate revalidation** for affected domains
- **Comprehensive logging** with detailed success/error reports

### Security Measures
- Domain name sanitization
- Path validation before file operations
- Proper error handling for system commands
- Write permission validation

## Error Handling

The command is designed to be resilient:
- Validates environment before starting
- Continues processing even if individual customers fail
- Logs detailed error messages for troubleshooting
- Returns proper exit codes for monitoring systems

## Monitoring

The command provides detailed output including:
- Total expired subscriptions processed
- Total websites deactivated  
- Error count for failed operations
- Individual customer processing status

## Expired Page

When a website is deactivated, visitors see a professional expired subscription page (`expired.html`) that includes:
- Clear expiry message
- Contact information for renewals
- Links to renewal services
- Professional styling consistent with OpenSID branding