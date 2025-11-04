# SSL Certificate Management Documentation

## Overview

The OpenLiteSpeedService now includes SSL certificate management using acme.sh. Certificates are automatically generated, installed, and stored in `/usr/local/lsws/conf/cert/{domain}/`.

## Prerequisites

### 1. Install acme.sh
```bash
# Install acme.sh
curl https://get.acme.sh | sh -s email=admin@yourdomain.com

# Set default CA to Let's Encrypt
~/.acme.sh/acme.sh --set-default-ca --server letsencrypt
```

### 2. Environment Configuration
Add these to your `.env` file:

```env
# SSL Configuration
SSL_ENABLED=true
SSL_DEFAULT_EMAIL=admin@opensid.my.id
ACME_PATH=/root/.acme.sh/acme.sh
SSL_CERT_DIRECTORY=/usr/local/lsws/conf/cert
SSL_CA_SERVER=letsencrypt
SSL_STAGING=false
SSL_WEBROOT=/var/www/html
SSL_AUTO_RENEW=true
SSL_RENEW_DAYS_BEFORE=30
```

## Usage Examples

### 1. Programmatic SSL Certificate Generation

```php
use App\Services\OpenLiteSpeedService;

$olsService = app(OpenLiteSpeedService::class);

// Generate SSL certificate
try {
    $result = $olsService->generateSSLCertificate('example.com', 'admin@example.com');
    
    if ($result['success']) {
        echo "Certificate generated successfully!\n";
        echo "Cert dir: {$result['cert_dir']}\n";
        echo "Certificate: {$result['cert_file']}\n";
        echo "Private key: {$result['key_file']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### 2. Create Virtual Host with SSL

```php
// Create virtual host with SSL enabled
$config = $olsService->createVirtualHost(
    kodeDesa: 'DESA001',
    domain: 'desa001.opensid.my.id',
    documentRoot: '/var/www/html/desa001/public',
    enableSSL: true,
    email: 'admin@desa001.opensid.my.id'
);

if ($config['ssl_enabled']) {
    echo "Virtual host created with SSL certificate\n";
    echo "SSL certificate directory: {$config['ssl']['cert_dir']}\n";
}
```

### 3. Check Certificate Status

```php
$status = $olsService->checkSSLCertificate('example.com');

if ($status['exists']) {
    echo "Certificate exists: " . ($status['valid'] ? 'Valid' : 'Invalid') . "\n";
    echo "Expires: {$status['expires_at']}\n";
    echo "Days until expiry: {$status['days_until_expiry']}\n";
} else {
    echo "No certificate found for domain\n";
}
```

### 4. Renew Certificate

```php
try {
    $success = $olsService->renewSSLCertificate('example.com');
    
    if ($success) {
        echo "Certificate renewed successfully\n";
    }
} catch (Exception $e) {
    echo "Renewal failed: " . $e->getMessage() . "\n";
}
```

## Artisan Commands

### Generate Certificate
```bash
# Generate SSL certificate for a domain
php artisan ssl:manage generate example.com --email=admin@example.com
```

### Check Certificate Status
```bash
# Check certificate status
php artisan ssl:manage check example.com
```

### Renew Certificate
```bash
# Renew certificate
php artisan ssl:manage renew example.com

# Force renewal (even if not near expiration)
php artisan ssl:manage renew example.com --force
```

### Remove Certificate
```bash
# Remove certificate
php artisan ssl:manage remove example.com
```

## File Structure

When a certificate is generated, the following structure is created:

```
/usr/local/lsws/conf/cert/
└── example.com/
    ├── cert.pem         # Domain certificate
    ├── key.pem          # Private key
    ├── ca.pem           # CA certificate
    └── fullchain.pem    # Full certificate chain
```

## Automatic Features

### 1. Auto-Installation
Certificates are automatically installed to the OpenLiteSpeed certificate directory with proper file structure.

### 2. Auto-Reload
OpenLiteSpeed is automatically reloaded when certificates are installed or renewed.

### 3. Backup and Logging
All SSL operations are logged for audit purposes.

## Integration Examples

### 1. Site Deployment with SSL
```php
public function deploySiteWithSSL(string $kodeDesa, string $domain)
{
    $olsService = app(OpenLiteSpeedService::class);
    
    // Create virtual host with SSL
    $config = $olsService->createVirtualHost(
        kodeDesa: $kodeDesa,
        domain: $domain,
        documentRoot: "/var/www/html/{$kodeDesa}/public",
        enableSSL: true
    );
    
    // Update listeners configuration
    $listenerService = app(OpenLiteSpeedListenerService::class);
    $listenerService->autoGenerateAndWriteListeners();
    
    // Restart OpenLiteSpeed to apply changes
    $olsService->restart();
    
    return $config;
}
```

### 2. Bulk Certificate Renewal
```php
public function renewAllCertificates()
{
    $olsService = app(OpenLiteSpeedService::class);
    $domains = ['site1.com', 'site2.com', 'site3.com'];
    
    foreach ($domains as $domain) {
        try {
            $status = $olsService->checkSSLCertificate($domain);
            
            // Renew if expires within 30 days
            if ($status['exists'] && $status['days_until_expiry'] <= 30) {
                $olsService->renewSSLCertificate($domain);
                echo "Renewed certificate for {$domain}\n";
            }
        } catch (Exception $e) {
            echo "Failed to renew {$domain}: {$e->getMessage()}\n";
        }
    }
}
```

### 3. Cron Job for Auto-Renewal
Add to your crontab:
```bash
# Check and renew certificates daily at 2 AM
0 2 * * * cd /path/to/your/project && php artisan ssl:manage check-all --auto-renew
```

## Error Handling

The service includes comprehensive error handling:

- **acme.sh not installed**: Clear error message with installation instructions
- **Domain validation failure**: Detailed error from acme.sh
- **File permission issues**: Logs and throws appropriate exceptions
- **Network connectivity**: Handles timeout and connection errors
- **Rate limiting**: Respects Let's Encrypt rate limits

## Security Considerations

1. **File Permissions**: Certificate files are created with secure permissions (600)
2. **Directory Structure**: Each domain has its own isolated directory
3. **Logging**: All operations are logged for security audit
4. **Validation**: Domain ownership is validated before certificate issuance
5. **Backup**: Original configurations are backed up before changes

## Troubleshooting

### Common Issues

1. **acme.sh not found**
   ```bash
   # Install acme.sh
   curl https://get.acme.sh | sh
   source ~/.bashrc
   ```

2. **Permission denied**
   ```bash
   # Fix permissions for certificate directory
   sudo chown -R lsws:lsws /usr/local/lsws/conf/cert/
   sudo chmod -R 600 /usr/local/lsws/conf/cert/
   ```

3. **Domain validation failed**
   - Ensure domain points to your server
   - Check that webroot is accessible
   - Verify firewall allows port 80/443

4. **Rate limit exceeded**
   - Use staging environment for testing
   - Wait for rate limit reset (usually 1 hour)

This comprehensive SSL management system provides automated certificate generation, renewal, and management for your OpenLiteSpeed virtual hosts.