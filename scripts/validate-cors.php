#!/usr/bin/env php
<?php

/**
 * CORS Configuration Validator
 * 
 * This script validates that the CORS configuration is secure and does not
 * allow arbitrary third-party domains to access the API.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "========================================\n";
echo "CORS Configuration Security Validator\n";
echo "========================================\n\n";

// Check allowed origins
$allowedOrigins = config('cors.allowed_origins');
echo "✓ Checking allowed origins...\n";
echo "  Current origins: " . json_encode($allowedOrigins) . "\n";

// Verify no wildcards
$hasWildcard = in_array('*', $allowedOrigins);
if ($hasWildcard) {
    echo "  ✗ FAIL: Wildcard (*) origin detected - This is a security vulnerability!\n";
    exit(1);
} else {
    echo "  ✓ PASS: No wildcard origins found\n";
}

// Verify origins are not empty
if (empty($allowedOrigins)) {
    echo "  ✗ FAIL: No origins configured\n";
    exit(1);
} else {
    echo "  ✓ PASS: Origins are configured\n";
}

// Check allowed methods
$allowedMethods = config('cors.allowed_methods');
echo "\n✓ Checking allowed methods...\n";
echo "  Current methods: " . json_encode($allowedMethods) . "\n";

if (in_array('*', $allowedMethods)) {
    echo "  ⚠ WARNING: Wildcard (*) method detected\n";
} else {
    echo "  ✓ PASS: Explicit methods defined\n";
}

// Check allowed headers
$allowedHeaders = config('cors.allowed_headers');
echo "\n✓ Checking allowed headers...\n";
echo "  Current headers: " . json_encode($allowedHeaders) . "\n";

if (in_array('*', $allowedHeaders)) {
    echo "  ⚠ WARNING: Wildcard (*) header detected\n";
} else {
    echo "  ✓ PASS: Explicit headers defined\n";
}

// Summary
echo "\n========================================\n";
echo "CORS Configuration Security Status: ";
if (!$hasWildcard && !empty($allowedOrigins)) {
    echo "✓ SECURE\n";
    echo "========================================\n";
    exit(0);
} else {
    echo "✗ VULNERABLE\n";
    echo "========================================\n";
    exit(1);
}
