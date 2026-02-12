<?php
/**
 * Test FA Database Connections
 * Run: php test-connections.php
 */

require_once __DIR__ . '/FRAYS_WEBSITE/includes/fa-database-creds.php';

echo "Testing FA Database Connections...\n";
echo "Server: 23.235.220.106\n\n";

$results = testFAConnections();

$connected = 0;
$failed = 0;

foreach ($results as $key => $result) {
    $status = $result['status'] ?? 'unknown';
    $instanceName = $FA_INSTANCES[$key]['name'] ?? $key;
    
    if ($status === 'connected') {
        echo "✓ {$instanceName} ({$key}) - {$result['ms']}ms\n";
        $connected++;
    } else {
        echo "✗ {$instanceName} ({$key}) - {$result['error']}\n";
        $failed++;
    }
}

echo "\n--------------------------------\n";
echo "Connected: {$connected}\n";
echo "Failed: {$failed}\n";
