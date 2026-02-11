<?php
/**
 * SYSTEM SETUP - Initialize unified authentication system
 * 
 * Run this script ONCE to set up the database and sync users
 * Access: http://localhost:8080/portal/setup.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/fa-user-sync.php';

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        
        // Create database tables
        if ($_POST['action'] === 'create_tables') {
            try {
                $db = getDBConnection();
                
                // Create unified_users table
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
                $db->exec($sql);
                
                $message = 'Database tables created successfully!';
            } catch (Exception $e) {
                $error = 'Error creating tables: ' . $e->getMessage();
            }
        }
        
        // Sync users from FA instances
        if ($_POST['action'] === 'sync_users') {
            try {
                ob_start();
                $sync = new FAUserSync();
                $sync->syncAll();
                $output = ob_get_clean();
                $message = 'User sync completed! Check output for details.';
            } catch (Exception $e) {
                $error = 'Sync error: ' . $e->getMessage();
            }
        }
    }
}

// Get current stats
try {
    $db = getDBConnection();
    $stmt = $db->query("SELECT COUNT(*) as count FROM unified_users");
    $userCount = $stmt->fetch()['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM unified_users WHERE last_login IS NOT NULL");
    $activeUsers = $stmt->fetch()['count'];
} catch (Exception $e) {
    $userCount = 0;
    $activeUsers = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Setup - Unified Authentication</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gray-100">
    
    <!-- Top Bar -->
    <div class="fixed top-0 left-0 right-0 z-50 bg-frays-red text-white text-xs md:text-sm py-2 md:py-2.5 shadow-md">
        <div class="max-w-7xl mx-auto px-2">
            <div class="flex justify-center items-center gap-6">
                <span>📦 Bookkeeping Services Portal Setup</span>
            </div>
        </div>
    </div>
    
    <div class="pt-16 md:pt-20 pb-12 px-4">
        <div class="max-w-4xl mx-auto">
            
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🔐 Unified Authentication Setup</h1>
            <p class="text-gray-600 mb-8">Configure single sign-on across all 30+ FA instances</p>
            
            <!-- Stats Cards -->
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="text-4xl font-bold text-frays-red"><?= $userCount ?></div>
                    <div class="text-gray-600">Total Users Synced</div>
                </div>
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="text-4xl font-bold text-frays-red"><?= count($GLOBALS['FA_INSTANCES']) ?></div>
                    <div class="text-gray-600">FA Instances Configured</div>
                </div>
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="text-4xl font-bold text-frays-red"><?= $activeUsers ?></div>
                    <div class="text-gray-600">Active Users</div>
                </div>
            </div>
            
            <!-- Messages -->
            <?php if ($message): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <i class="ri-check-line"></i> <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <i class="ri-error-warning-line"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <!-- Setup Steps -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6">⚙️ Setup Steps</h2>
                
                <div class="space-y-4">
                    <!-- Step 1 -->
                    <div class="border rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold">Step 1: Create Database Tables</h3>
                                <p class="text-sm text-gray-600">Create the unified_users table in your database</p>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="action" value="create_tables">
                                <button type="submit" class="bg-frays-red text-white px-4 py-2 rounded-lg hover:opacity-90">
                                    Create Tables
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="border rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold">Step 2: Sync Users from FA Instances</h3>
                                <p class="text-sm text-gray-600">Import all users from your 30+ FA databases</p>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="action" value="sync_users">
                                <button type="submit" class="bg-frays-yellow text-black px-4 py-2 rounded-lg hover:opacity-90">
                                    Sync Users
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- FA Instances List -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">📋 Configured FA Instances</h2>
                <div class="grid md:grid-cols-2 gap-3">
                    <?php foreach ($GLOBALS['FA_INSTANCES'] as $key => $instance): ?>
                    <div class="flex items-center gap-2 p-2 bg-gray-50 rounded">
                        <i class="ri-database-2-line text-frays-red"></i>
                        <span class="font-medium"><?= htmlspecialchars($instance['name']) ?></span>
                        <span class="text-xs text-gray-500 ml-auto">v<?= htmlspecialchars($instance['version']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        </div>
    </div>
    
</body>
</html>
