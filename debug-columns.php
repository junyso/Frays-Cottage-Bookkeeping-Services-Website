<?php
/**
 * DEBUG: Check 0_users table columns
 */

$SYNC_USER = 'bookkeepingco_sync_all';
$SYNC_PASS = 'F@ySync2026!';

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║         DEBUG: Check 0_users Table Columns                  ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$testDBs = ['bookkeepingco_00', 'bookkeepingco_93'];

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
        
        // Get columns
        $stmt = $pdo->query("DESCRIBE 0_users");
        $columns = $stmt->fetchAll();
        
        echo "All columns in 0_users:\n";
        foreach ($columns as $col) {
            echo "  - {$col['Field']} ({$col['Type']})" . ($col['Key'] ? " [KEY]" : "") . "\n";
        }
        
        // Try the query
        echo "\nTrying query...\n";
        $stmt = $pdo->query("SELECT id, user_id, real_name, email, role, password, inactive FROM 0_users WHERE inactive = 0 LIMIT 1");
        $user = $stmt->fetch();
        
        if ($user) {
            echo "✓ Query successful!\n";
            echo "Sample data:\n";
            print_r($user);
        }
        
    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}
