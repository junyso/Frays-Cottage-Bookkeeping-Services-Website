<?php
/**
 * FA USER SYNC - Sync users from all FA instances to central database
 * 
 * This script connects to each FA instance and syncs users to the central
 * unified_users table for single sign-on across all instances.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/fa-database-creds.php';

class FAUserSync {
    
    private $centralDB;
    private $instances;
    private $syncedCount = 0;
    private $errors = [];
    private $connectionStats = [];
    
    public function __construct() {
        $this->centralDB = getDBConnection();
        $this->instances = $GLOBALS['FA_INSTANCES'];
    }
    
    /**
     * Sync all users from all FA instances
     */
    public function syncAll() {
        echo "Starting FA User Sync...\n";
        echo "Found " . count($this->instances) . " instances to process.\n\n";
        
        foreach ($this->instances as $key => $instance) {
            echo "Processing: {$instance['name']} ({$key})...\n";
            $this->syncInstance($key, $instance);
        }
        
        echo "\n========================================\n";
        echo "Sync Complete!\n";
        echo "Total new users synced: {$this->syncedCount}\n";
        echo "Errors: " . count($this->errors) . "\n";
        
        if (!empty($this->errors)) {
            echo "\nErrors:\n";
            foreach ($this->errors as $error) {
                echo "  - {$error}\n";
            }
        }
        
        return [
            'synced' => $this->syncedCount,
            'errors' => count($this->errors),
            'connections' => $this->connectionStats
        ];
    }
    
    /**
     * Sync users from a single FA instance
     */
    private function syncInstance($instanceKey, $instance) {
        $faPDO = getFAConnection($instanceKey);
        
        if (!$faPDO) {
            $this->errors[] = "{$instance['name']}: Could not connect to database";
            $this->connectionStats[$instanceKey] = 'failed';
            echo "  ERROR: Could not connect to database\n";
            return;
        }
        
        $this->connectionStats[$instanceKey] = 'connected';
        echo "  ✓ Connected to database\n";
        
        try {
            // Get database name
            $faDBName = $faPDO->query("SELECT DATABASE()")->fetchColumn();
            
            // Get all users from FA instance
            $stmt = $faPDO->query("
                SELECT id, user_id, real_name, email, role, password, inactive 
                FROM users 
                WHERE inactive = 0
            ");
            $faUsers = $stmt->fetchAll();
            
            echo "  Found " . count($faUsers) . " active users\n";
            
            foreach ($faUsers as $faUser) {
                $this->syncUser($instanceKey, $instance, $faUser, $faDBName);
            }
            
        } catch (PDOException $e) {
            $error = "{$instance['name']}: " . $e->getMessage();
            $this->errors[] = $error;
            echo "  ERROR: {$error}\n";
        }
    }
    
    /**
     * Sync a single user to central database
     */
    private function syncUser($instanceKey, $instance, $faUser, $faDBName) {
        // Check if user already exists in central DB
        $stmt = $this->centralDB->prepare("
            SELECT id, fa_instances FROM unified_users 
            WHERE email = ? OR (fa_instance = ? AND fa_user_id = ?)
        ");
        $stmt->execute([$faUser['email'], $instanceKey, $faUser['user_id']]);
        $existingUser = $stmt->fetch();
        
        $faInstances = [];
        if ($existingUser && !empty($existingUser['fa_instances'])) {
            $faInstances = json_decode($existingUser['fa_instances'], true);
        }
        
        // Add this instance to user's access list
        $faInstances[$instanceKey] = [
            'name' => $instance['name'],
            'fa_user_id' => $faUser['user_id'],
            'role' => $faUser['role'],
            'database' => $faDBName,
            'added_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existingUser) {
            // Update existing user
            $stmt = $this->centralDB->prepare("
                UPDATE unified_users SET
                    name = ?,
                    fa_instances = ?,
                    password_hash = ?,
                    role = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $faUser['real_name'],
                json_encode($faInstances),
                $faUser['password'],
                $faUser['role'],
                $existingUser['id']
            ]);
        } else {
            // Insert new user
            $stmt = $this->centralDB->prepare("
                INSERT INTO unified_users 
                (email, name, fa_instance, fa_user_id, password_hash, role, fa_instances, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $faUser['email'],
                $faUser['real_name'],
                $instanceKey,
                $faUser['user_id'],
                $faUser['password'],
                $faUser['role'],
                json_encode($faInstances)
            ]);
            $this->syncedCount++;
        }
    }
    
    /**
     * Get connection statistics
     */
    public function getConnectionStats() {
        return $this->connectionStats;
    }
}

// Create unified_users table if not exists
function createUnifiedUsersTable() {
    $db = getDBConnection();
    
    $sql = "
        CREATE TABLE IF NOT EXISTS unified_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            fa_instance VARCHAR(100) DEFAULT NULL,
            fa_user_id VARCHAR(100) DEFAULT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'client',
            fa_instances JSON DEFAULT NULL,
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login DATETIME DEFAULT NULL,
            INDEX idx_email (email),
            INDEX idx_fa_instance (fa_instance),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    
    try {
        $db->exec($sql);
        echo "Unified users table created/verified.\n";
    } catch (PDOException $e) {
        echo "Table creation error: " . $e->getMessage() . "\n";
    }
}

/**
 * Test all FA database connections
 */
function testFAConnections() {
    echo "Testing FA Database Connections...\n\n";
    
    $results = testFAConnections();
    
    $connected = 0;
    $failed = 0;
    
    foreach ($results as $key => $result) {
        $status = $result['status'] ?? 'unknown';
        $instanceName = $GLOBALS['FA_INSTANCES'][$key]['name'] ?? $key;
        
        if ($status === 'connected') {
            echo "  ✓ {$instanceName} ({$key}) - {$result['ms']}ms\n";
            $connected++;
        } else {
            echo "  ✗ {$instanceName} ({$key}) - {$result['error']}\n";
            $failed++;
        }
    }
    
    echo "\n--------------------------------\n";
    echo "Connected: {$connected}\n";
    echo "Failed: {$failed}\n";
    
    return $results;
}

// Run if executed directly
if (php_sapi_name() === 'cli' || basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    if (isset($argv[1]) && $argv[1] === 'test') {
        testFAConnections();
    } else {
        createUnifiedUsersTable();
        $sync = new FAUserSync();
        $sync->syncAll();
    }
}
