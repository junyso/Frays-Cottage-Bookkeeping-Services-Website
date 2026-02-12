<?php
/**
 * EXPORT: Download unified_users table as SQL
 * 
 * Run this on the server to download the unified_users table
 * Then import into your local database
 */

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="unified_users_' . date('Y-m-d') . '.sql"');

// Connect using bookkeepingco_sync_all
$host = 'localhost';
$dbname = 'bookkeepingco_00';
$user = 'bookkeepingco_sync_all';
$pass = 'F@ySync2026!';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get create table statement
    $stmt = $pdo->query("SHOW CREATE TABLE unified_users");
    $createTable = $stmt->fetch();
    
    echo "-- ========================================\n";
    echo "-- UNIFIED_USERS TABLE EXPORT\n";
    echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    echo "-- Database: {$dbname}\n";
    echo "-- ========================================\n\n";
    
    echo "DROP TABLE IF EXISTS unified_users;\n\n";
    echo $createTable['Create Table'] . ";\n\n";
    
    // Get all data
    $stmt = $pdo->query("SELECT * FROM unified_users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "-- Total users: " . count($users) . "\n\n";
    
    if (count($users) > 0) {
        echo "INSERT INTO unified_users (id, email, name, fa_instance, fa_user_id, password_hash, role, fa_instances, status, created_at, updated_at, last_login) VALUES\n";
        
        $rows = [];
        foreach ($users as $u) {
            $id = $u['id'];
            $email = $pdo->quote($u['email']);
            $name = $pdo->quote($u['name']);
            $faInstance = $u['fa_instance'] ? $pdo->quote($u['fa_instance']) : 'NULL';
            $faUserId = $u['fa_user_id'] ? $pdo->quote($u['fa_user_id']) : 'NULL';
            $passwordHash = $pdo->quote($u['password_hash']);
            $role = $pdo->quote($u['role']);
            $faInstances = $u['fa_instances'] ? $pdo->quote($u['fa_instances']) : 'NULL';
            $status = $pdo->quote($u['status']);
            $createdAt = $u['created_at'] ? $pdo->quote($u['created_at']) : 'NULL';
            $updatedAt = $u['updated_at'] ? $pdo->quote($u['updated_at']) : 'NULL';
            $lastLogin = $u['last_login'] ? $pdo->quote($u['last_login']) : 'NULL';
            
            $rows[] = "({$id}, {$email}, {$name}, {$faInstance}, {$faUserId}, {$passwordHash}, {$role}, {$faInstances}, {$status}, {$createdAt}, {$updatedAt}, {$lastLogin})";
        }
        
        echo implode(",\n", $rows) . ";\n";
    }
    
    echo "\n-- Export complete!\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
