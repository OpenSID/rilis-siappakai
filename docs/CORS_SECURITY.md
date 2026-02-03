# CORS Security Configuration

## Issue Fixed
Cross-Origin Resource Sharing (CORS) misconfiguration was allowing arbitrary third-party domains to access the API endpoints.

## Changes Made

### 1. CORS Configuration (`config/cors.php`)
- **Changed `allowed_origins`** from wildcard `['*']` to environment-based configuration
  - Now defaults to `APP_URL` if `CORS_ALLOWED_ORIGINS` is not set
  - Supports comma-separated list of allowed origins via `CORS_ALLOWED_ORIGINS` environment variable
  
- **Changed `allowed_methods`** from wildcard `['*']` to explicit list:
  - `GET`, `POST`, `PUT`, `PATCH`, `DELETE`, `OPTIONS`
  
- **Changed `allowed_headers`** from wildcard `['*']` to explicit list:
  - `Content-Type`, `X-Requested-With`, `Authorization`, `Accept`, `Origin`

### 2. Environment Configuration (`.env.example`)
Added new environment variable:
```env
CORS_ALLOWED_ORIGINS=
```

## Configuration Guide

### Default Configuration (Recommended for Production)
Leave `CORS_ALLOWED_ORIGINS` empty in your `.env` file. The application will use `APP_URL` as the only allowed origin:
```env
APP_URL=https://yourdomain.com
CORS_ALLOWED_ORIGINS=
```

### Multiple Allowed Origins
If you need to allow multiple domains (e.g., for mobile apps or multiple frontends), specify them as a comma-separated list:
```env
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com,https://mobile.yourdomain.com
```

**Note:** Whitespace around commas will be automatically trimmed, so both formats work:
- `https://domain1.com,https://domain2.com` ✓
- `https://domain1.com, https://domain2.com` ✓ (spaces will be trimmed)

## Security Benefits

1. **Restricts Cross-Origin Access**: Only explicitly allowed domains can make cross-origin requests to the API
2. **Prevents Data Leakage**: Reduces the risk of sensitive data being accessible to malicious third-party websites
3. **Explicit Configuration**: Makes security policies explicit rather than permissive by default
4. **Environment-Specific**: Different environments can have different CORS policies (development, staging, production)

## API Endpoints Protected

The CORS policy applies to:
- `api/*` - All API routes
- `sanctum/csrf-cookie` - CSRF token endpoint

## Testing

A test suite has been added in `tests/Feature/CorsConfigurationTest.php` to verify:
- Wildcard origins are not allowed
- Only valid URLs are configured as allowed origins
- Methods and headers are explicitly defined
- CORS headers are properly set in responses

Run tests with:
```bash
php artisan test --filter=CorsConfigurationTest
```

## Backward Compatibility

⚠️ **Breaking Change**: This is a security fix that changes default behavior.

If your application was relying on unrestricted CORS access:
1. Identify all legitimate origins that need API access
2. Add them to `CORS_ALLOWED_ORIGINS` in your `.env` file
3. Test thoroughly before deploying to production

## References

- [Mozilla CORS Documentation](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)
- [Fortify CORS Security](https://vulncat.fortify.com/en/detail?category=HTML5&subcategory=Overly%20Permissive%20CORS%20Policy)
- [Laravel CORS Configuration](https://laravel.com/docs/10.x/routing#cors)
