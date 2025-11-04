<?php

// Advanced usage examples for OpenLiteSpeedListenerService
// Reading vhosts from file system

use App\Services\OpenLiteSpeedListenerService;

$listenerService = app(OpenLiteSpeedListenerService::class);

echo "=== OpenLiteSpeed Listener Service - Advanced Examples ===\n\n";

// Example 1: Discover vhosts from file system
echo "1. Discovering vhosts from file system:\n";
echo "Path: /usr/local/lsws/conf/vhosts-enabled/\n\n";

$vhosts = $listenerService->discoverVhostsFromFileSystem();

echo "Found " . count($vhosts) . " vhosts:\n";
foreach ($vhosts as $vhost) {
    echo "- VHost: {$vhost['vhost_name']} → Domain: {$vhost['domain']}\n";
    echo "  Config: {$vhost['config_file']}\n";
}
echo "\n";

// Example 2: Generate listeners from existing vhosts
echo "2. Generating listeners from discovered vhosts:\n";
$config = $listenerService->generateListenersFromVhosts();
echo $config . "\n";

// Example 3: Get specific vhost details
echo "3. Getting specific vhost details:\n";
$vhostDetails = $listenerService->getVhostDetails('siappakai.com');
if ($vhostDetails) {
    echo "VHost Name: {$vhostDetails['vhost_name']}\n";
    echo "Domain: {$vhostDetails['domain']}\n";
    echo "Config File: {$vhostDetails['config_file']}\n";
    echo "Config Dir: {$vhostDetails['config_dir']}\n";
} else {
    echo "VHost not found or no domain configured\n";
}
echo "\n";

// Example 4: Check if vhost exists
echo "4. Checking if vhost exists:\n";
$exists = $listenerService->vhostExists('siappakai.com');
echo "VHost 'siappakai.com' exists: " . ($exists ? "Yes" : "No") . "\n\n";

// Example 5: Auto-generate and write configuration
echo "5. Auto-generating listeners configuration:\n";
$success = $listenerService->autoGenerateAndWriteListeners();
echo "Auto-generation " . ($success ? "successful" : "failed") . "\n\n";

// Example 6: Get all vhosts with details
echo "6. Getting all vhosts with full details:\n";
$allVhosts = $listenerService->getAllVhostsDetails();

foreach ($allVhosts as $vhost) {
    echo "VHost: {$vhost['vhost_name']}\n";
    echo "  Domain: {$vhost['domain']}\n";
    echo "  Config: {$vhost['config_file']}\n";
    echo "  Directory: {$vhost['config_dir']}\n";
    echo "\n";
}

// Example 7: Sync listeners with current vhosts
echo "7. Syncing listeners with current vhosts:\n";
$syncedConfig = $listenerService->syncListenersWithVhosts();
echo "Synced configuration:\n";
echo $syncedConfig . "\n";

// Example 8: Real-time monitoring
echo "8. Monitoring vhosts status:\n";
$status = $listenerService->getListenersStatus();
echo "Current status:\n";
echo "- Configuration exists: " . ($status['exists'] ? "Yes" : "No") . "\n";
echo "- Mappings count: {$status['mappings_count']}\n";
if ($status['last_modified']) {
    echo "- Last modified: " . date('Y-m-d H:i:s', $status['last_modified']) . "\n";
}
echo "\n";

// Example 9: File system structure example
echo "9. Expected file system structure:\n";
echo "/usr/local/lsws/conf/vhosts-enabled/\n";
echo "├── siappakai.com/\n";
echo "│   └── dasbor-siappakai-vh.conf    (contains: vhDomain siappakai.com)\n";
echo "├── example.com/\n";
echo "│   └── example-vh.conf             (contains: vhDomain example.com)\n";
echo "├── test.local/\n";
echo "│   └── test-site-vh.conf           (contains: vhDomain test.local)\n";
echo "└── multisite/\n";
echo "    ├── tenant1.com/\n";
echo "    │   └── tenant1-vh.conf         (contains: vhDomain tenant1.com)\n";
echo "    └── tenant2.com/\n";
echo "        └── tenant2-vh.conf         (contains: vhDomain tenant2.com)\n";
echo "\n";

// Example 10: Template variable parsing
echo "10. How template variables are handled:\n";
echo "File content: vhDomain {\$domain}\n";
echo "→ If template variable found, uses parent directory name\n";
echo "→ If parent dir is 'siappakai.com', domain becomes 'siappakai.com'\n";
echo "\n";
echo "File content: vhDomain example.com\n";
echo "→ Uses actual domain value: 'example.com'\n";
echo "\n";

echo "=== End of Examples ===\n";