<?php
/**
 * FA USER SYNC - Correct Database Names
 */

// Use bookkeepingco_sync_all for ALL connections
$SYNC_USER = 'bookkeepingco_sync_all';
$SYNC_PASS = 'F@ySync2026!';

// Central Database (where unified_users table will be)
$MAIN_DB = [
    'host' => 'localhost',
    'database' => 'bookkeepingco_00',
    'user' => $SYNC_USER,
    'pass' => $SYNC_PASS
];

// FA Instances Configuration - CORRECTED DATABASE NAMES
$FA_INSTANCES = [
    'northernwarehouse' => ['name' => 'Northern Warehouse'],
    'madamz' => ['name' => 'Madamz'],
    'cleaningguru' => ['name' => 'Cleaning Guru'],
    'quanto' => ['name' => 'Quanto'],
    'spaceinteriors' => ['name' => 'Space Interiors'],
    'unlimitedfoods' => ['name' => 'Unlimited Foods'],
    'ernletprojects' => ['name' => 'Ernlet Projects'],
    'frayscottage' => ['name' => 'Frays Cottage'],
    'constantadaptation' => ['name' => 'Constant Adaptation'],
    'great-land' => ['name' => 'Great-Land'],
    'lighteningstrike' => ['name' => 'Lightening Strike'],
    'notsa' => ['name' => 'NOTSA'],
    'thaega' => ['name' => 'Thaega'],
    'modernhotelsupplies' => ['name' => 'Modern Hotel Supplies'],
    'training' => ['name' => 'Training'],
    'majande' => ['name' => 'Majande'],
    'guruonks' => ['name' => 'Guru Onks'],
    'marctizmo' => ['name' => 'Marctizmo'],
    '4bnb' => ['name' => '4BnB'],
    'noracosmetics' => ['name' => 'Nora Cosmetics'],
    '3dworks' => ['name' => '3D Works'],
    'westdrayton' => ['name' => 'West Drayton'],
    'ernletprojects2' => ['name' => 'Ernlet Projects 2'],
    'ernletgroup' => ['name' => 'Ernlet Group'],
    'couriersolutions' => ['name' => 'Courier Solutions'],
    'loremaster' => ['name' => 'Loremaster'],
    'coverlot' => ['name' => 'Coverlot'],
    'globalstrategies' => ['name' => 'Global Strategies'],
    'norahbeauty' => ['name' => 'Norah Beauty'],
    'nidarshini' => ['name' => 'Nidarshini']
];

// CORRECTED FA Database Names (from cPanel)
$FA_DATABASES = [
    'northernwarehouse' => 'bookkeepingco_93',
    'madamz' => 'bookkeepingco_75',
    'cleaningguru' => 'bookkeepingco_35',
    'quanto' => 'bookkeepingco_61',
    'spaceinteriors' => 'bookkeepingco_89',
    'unlimitedfoods' => 'bookkeepingco_71',
    'ernletprojects' => 'bookkeepingco_88',
    'frayscottage' => 'bookkeepingco_00',
    'constantadaptation' => 'bookkeepingco_53',
    'great-land' => 'bookkeepingco_24',
    'lighteningstrike' => 'bookkeepingco_70',
    'notsa' => 'bookkeepingco_60',
    'thaega' => 'bookkeepingco_65',
    'modernhotelsupplies' => 'bookkeepingco_21',
    'training' => 'bookkeepingco_94',
    'majande' => 'bookkeepingco_84',
    'guruonks' => 'bookkeepingco_00onks',
    'marctizmo' => 'bookkeepingco_40',
    '4bnb' => 'bookkeepingco_17',
    'noracosmetics' => 'bookkeepingco_43',
    '3dworks' => 'bookkeepingco_48',
    'westdrayton' => 'bookkeepingco_01',
    'ernletprojects2' => 'bookkeepingco_fron621',
    'ernletgroup' => 'bookkeepingco_fron114',
    'couriersolutions' => 'bookkeepingco_fron895',
    'loremaster' => 'bookkeepingco_fron558',
    'coverlot' => 'bookkeepingco_fron997',
    'globalstrategies' => 'bookkeepingco_fron143',
    'norahbeauty' => 'bookkeepingco_fron773',
    'nidarshini' => 'bookkeepingco_fron341'
];

runSync();

function runSync() {
    global $FA_INSTANCES, $FA_DATABASES, $MAIN_DB, $SYNC_USER, $SYNC_PASS;
    
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║           FA USER SYNC - CORRECTED DATABASE NAMES         ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    echo "Using: {$SYNC_USER} @ {$MAIN_DB['host']}\n\n";
    
    $startTime = microtime(true);
    $connected = 0;
    $failed = 0;
    $errors = [];
    $totalUsers = 0;
    
    // Connect to main database
    try {
        $mainPDO = new PDO(
            "mysql:host={$MAIN_DB['host']};dbname={$MAIN_DB['database']};charset=utf8mb4",
            $SYNC_USER,
            $SYNC_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "✓ Connected: {$MAIN_DB['database']}\n\n";
    } catch (PDOException $e) {
        echo "ERROR: Cannot connect to main database:\n" . $e->getMessage() . "\n\n";
        exit(1);
    }
    
    // Create unified_users table
    echo "Creating unified_users table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS unified_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        name VARCHAR(255) NOT NULL,
        fa_instance VARCHAR(100) DEFAULT NULL,
        fa_user_id VARCHAR(100) DEFAULT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'client',
        fa_instances JSON DEFAULT NULL,
        status ENUM('active','inactive','suspended') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_fa_instance (fa_instance),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $mainPDO->exec($sql);
    echo "✓ Table ready.\n\n";
    
    echo "Processing " . count($FA_DATABASES) . " FA instances...\n\n";
    
    foreach ($FA_DATABASES as $key => $dbName) {
        echo "[" . str_pad($key, 20) . "] ";
        
        try {
            $faPDO = new PDO(
                "mysql:host=localhost;dbname={$dbName};charset=utf8mb4",
                $SYNC_USER,
                $SYNC_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $stmt = $faPDO->query("SELECT id, user_id, real_name, email, role_id AS role, password, inactive FROM 0_users WHERE inactive = 0");
            $users = $stmt->fetchAll();
            
            echo "✓ {$dbName} - " . count($users) . " users - ";
            
            foreach ($users as $faUser) {
                $stmt = $mainPDO->prepare("SELECT id, fa_instances FROM unified_users WHERE email = ? OR (fa_instance = ? AND fa_user_id = ?)");
                $stmt->execute([$faUser['email'], $key, $faUser['user_id']]);
                $existingUser = $stmt->fetch();
                
                $faInstances = [];
                if ($existingUser && !empty($existingUser['fa_instances'])) {
                    $faInstances = json_decode($existingUser['fa_instances'], true);
                }
                
                $faInstances[$key] = [
                    'name' => $FA_INSTANCES[$key]['name'],
                    'fa_user_id' => $faUser['user_id'],
                    'role' => $faUser['role'],
                    'database' => $dbName,
                    'added_at' => date('Y-m-d H:i:s')
                ];
                
                if ($existingUser) {
                    $stmt = $mainPDO->prepare("UPDATE unified_users SET name=?, fa_instances=?, role=?, updated_at=NOW() WHERE id=?");
                    $stmt->execute([$faUser['real_name'], json_encode($faInstances), $faUser['role'], $existingUser['id']]);
                } else {
                    $stmt = $mainPDO->prepare("INSERT INTO unified_users (email, name, fa_instance, fa_user_id, password_hash, role, fa_instances, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$faUser['email'], $faUser['real_name'], $key, $faUser['user_id'], $faUser['password'], $faUser['role'], json_encode($faInstances)]);
                }
            }
            
            $connected++;
            $totalUsers += count($users);
            echo "synced\n";
            
        } catch (PDOException $e) {
            $failed++;
            $errMsg = $e->getMessage();
            if (strpos($errMsg, '1044') !== false || strpos($errMsg, '1045') !== false) {
                $errors[] = $key . ": No access";
            } elseif (strpos($errMsg, '1146') !== false || strpos($errMsg, '42S02') !== false) {
                $errors[] = $key . ": No 0_users table";
            } else {
                $errors[] = $key . ": " . substr($errMsg, 0, 25);
            }
            echo "✗ ERROR\n";
        }
    }
    
    $elapsed = round(microtime(true) - $startTime, 2);
    
    echo "\n╔══════════════════════════════════════════════════════╗\n";
    echo "║              SYNC COMPLETE                           ║\n";
    echo "╚══════════════════════════════════════════════════════╝\n";
    echo "Time: {$elapsed}s | Connected: {$connected}/" . count($FA_DATABASES) . " | Failed: {$failed}\n";
    echo "Total FA users synced: {$totalUsers}\n";
    
    if (!empty($errors)) {
        echo "\nFailed:\n";
        foreach ($errors as $e) echo "  - {$e}\n";
    }
    
    echo "\n✓ Users stored in {$MAIN_DB['database']}.unified_users!\n";
}
