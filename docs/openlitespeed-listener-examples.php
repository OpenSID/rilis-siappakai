<?php

// Usage examples for OpenLiteSpeedListenerService

use App\Services\OpenLiteSpeedListenerService;

// Initialize the service
$listenerService = app(OpenLiteSpeedListenerService::class);

// Example 1: Generate HTTP listener with default + custom mappings
$httpListener = $listenerService->generateHttpListener([
    ['vhost' => 'site1', 'domain' => 'site1.com'],
    ['vhost' => 'site2', 'domain' => 'site2.com'],
    ['vhost' => 'site3', 'domain' => 'site3.com'],
    ['vhost' => 'site4', 'domain' => 'site4.com'],
]);

echo "HTTP Listener:\n";
echo $httpListener . "\n";

// Output:
/*
listener Default {
address *:80
secure 0
map dasbor-siappakai siappakai.com
map site1 site1.com
map site2 site2.com
map site3 site3.com
map site4 site4.com
}
*/

// Example 2: Generate HTTPS listener
$httpsListener = $listenerService->generateHttpsListener([
    ['vhost' => 'site1', 'domain' => 'site1.com'],
    ['vhost' => 'site2', 'domain' => 'site2.com'],
]);

echo "HTTPS Listener:\n";
echo $httpsListener . "\n";

// Output:
/*
listener SSL {
address *:443
secure 1
map dasbor-siappakai siappakai.com
map site1 site1.com
map site2 site2.com
}
*/

// Example 3: Generate both HTTP and HTTPS listeners
$completeListeners = $listenerService->generateCompleteListeners([
    ['vhost' => 'site1', 'domain' => 'site1.com'],
    ['vhost' => 'site2', 'domain' => 'site2.com'],
    ['vhost' => 'site3', 'domain' => 'site3.com'],
    ['vhost' => 'site4', 'domain' => 'site4.com'],
]);

echo "Complete Listeners (HTTP + HTTPS):\n";
echo $completeListeners . "\n";

// Example 4: Generate template with variables (like your example)
$templateListeners = $listenerService->generateListenerTemplate(4);

echo "Template Listeners:\n";
echo $templateListeners . "\n";

// Output:
/*
listener Default {
address *:80
secure 0
map dasbor-siappakai siappakai.com
map $vhostName domain
map $vhostName domain
map $vhostName domain
map $vhostName domain
}

listener SSL {
address *:443
secure 1
map dasbor-siappakai siappakai.com
map $vhostName domain
map $vhostName domain
map $vhostName domain
map $vhostName domain
}
*/

// Example 5: Add new mapping to existing configuration
$updatedConfig = $listenerService->addMappingToListeners('new-site', 'newsite.com');

// Example 6: Remove mapping from configuration
$updatedConfig = $listenerService->removeMappingFromListeners('old-site', 'oldsite.com');

// Example 7: Write configuration to file
$success = $listenerService->writeListenersConfig($completeListeners);

if ($success) {
    echo "Configuration written successfully!\n";
} else {
    echo "Failed to write configuration!\n";
}

// Example 8: Get listeners status
$status = $listenerService->getListenersStatus();
print_r($status);

// Example 9: Validate configuration
$isValid = $listenerService->validateListenersConfig($completeListeners);
echo "Configuration is " . ($isValid ? "valid" : "invalid") . "\n";