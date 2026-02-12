<?php
/**
 * DEBUG: Check what tables exist in FA databases
 */

$SYNC_USER = 'bookkeepingco_sync_all';
$SYNC_PASS = 'F@ySync2026!';

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║         DEBUG: Check FA Database Tables                      ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Test first 5 databases
$testDBs = [
    'bookkeepingco_00',
    'bookkeepingco_93',
    'bookkeepingco_75',
    'bookkeepingco_35',
    'bookkeepingco_fron621'
];

foreach ($testDBs as $dbName) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Database: {$dbName}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    try {
        $pdo = new PDO(
            "mysql:host=localhost;dbname={$dbName};charset=utf8mb4",
            $SYNC_USER,
            $SYNC_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Get all tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Tables found: " . count($tables) . "\n";
        
        // Show first 20 tables
        $i = 0;
        foreach ($tables as $table) {
            echo "  - {$table}\n";
            $i++;
            if ($i >= 20) {
                echo "  ... and " . (count($tables) - 20) . " more\n";
                break;
            }
        }
        
        // Check if 'users' table exists
        if (in_array('users', $tables)) {
            echo "\n✓ 'users' table EXISTS\n";
            
            // Check table structure
            $stmt = $pdo->query("DESCRIBE users");
            $columns = $stmt->fetchAll();
            echo "Columns: " . implode(', ', array_column($columns, 'Field')) . "\n";
        } else {
            echo "\n✗ 'users' table NOT found\n";
            
            // Look for similar table names
            $userTables = array_filter($tables, function($t) {
                return stripos($t, 'user') !== false;
            });
            
            if (!empty($userTables)) {
                echo "Similar tables: " . implode(', ', $userTables) . "\n";
            }
        }
        
    } catch (PDOException $e) {
        echo "ERROR: " . substr($e->getMessage(), 0, 60) . "\n";
    }
    
    echo "\n";
}
