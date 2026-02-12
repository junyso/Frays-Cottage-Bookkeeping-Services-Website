<?php
/**
 * IMPORT: Run locally to import unified_users
 * 
 * Place your exported .sql file in the same folder
 * Run: php import-unified-users.php [filename.sql]
 */

$sqlFile = $argv[1] ?? 'unified_users_' . date('Y-m-d') . '.sql';

if (!file_exists($sqlFile)) {
    echo "ERROR: File not found: {$sqlFile}\n";
    echo "Usage: php import-unified-users.php [filename.sql]\n";
    exit(1);
}

echo "Importing: {$sqlFile}\n\n";

// Connect to local database
$host = 'localhost';
$dbname = 'frayscottage_bookkeeping';
$user = 'root';  // Change if needed
$pass = '';      // Change if needed

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to: {$dbname}\n\n";
    
    // Read and execute SQL
    $sql = file_get_contents($sqlFile);
    
    // Split by semicolons (simple approach)
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $count = 0;
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) continue;
        
        try {
            $pdo->exec($statement);
            $count++;
        } catch (PDOException $e) {
            echo "Warning: " . substr($e->getMessage(), 0, 80) . "\n";
        }
    }
    
    echo "✓ Import complete! ({$count} statements executed)\n\n";
    
    // Verify
    $stmt = $pdo->query("SELECT COUNT(*) FROM unified_users");
    $total = $stmt->fetchColumn();
    echo "Total users in unified_users: {$total}\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "\nMake sure to update the database credentials in this file:\n";
    echo "  \$user = 'your_username';\n";
    echo "  \$pass = 'your_password';\n";
}
