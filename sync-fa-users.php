<?php
/**
 * FA USER SYNC - With sync_all user credentials
 */

// Central Database (where unified_users table will be)
$MAIN_DB = [
    'host' => 'localhost',
    'database' => 'bookkeepingco_00',
    'user' => 'bookkeepingco_sync_all',
    'pass' => 'F@ySync2026!'
];

// FA Instances Configuration
$FA_INSTANCES = [
    'northernwarehouse' => ['name' => 'Northern Warehouse', 'version' => '2.4.18'],
    'madamz' => ['name' => 'Madamz', 'version' => '2.4.18'],
    'cleaningguru' => ['name' => 'Cleaning Guru', 'version' => '2.4.18'],
    'quanto' => ['name' => 'Quanto', 'version' => '2.4.18'],
    'spaceinteriors' => ['name' => 'Space Interiors', 'version' => '2.4.18'],
    'unlimitedfoods' => ['name' => 'Unlimited Foods', 'version' => '2.4.10'],
    'ernletprojects' => ['name' => 'Ernlet Projects', 'version' => '2.4.16'],
    'frayscottage' => ['name' => 'Frays Cottage', 'version' => '2.4.16'],
    'constantadaptation' => ['name' => 'Constant Adaptation', 'version' => '2.4.10'],
    'great-land' => ['name' => 'Great-Land', 'version' => '2.4.10'],
    'lighteningstrike' => ['name' => 'Lightening Strike', 'version' => '2.4.10'],
    'notsa' => ['name' => 'NOTSA', 'version' => '2.4.10'],
    'thaega' => ['name' => 'Thaega', 'version' => '2.4.10'],
    'modernhotelsupplies' => ['name' => 'Modern Hotel Supplies', 'version' => '2.4.10'],
    'training' => ['name' => 'Training', 'version' => '2.4.16'],
    'majande' => ['name' => 'Majande', 'version' => '2.4.11'],
    'guruonks' => ['name' => 'Guru Onks', 'version' => '2.4.11'],
    'marctizmo' => ['name' => 'Marctizmo', 'version' => '2.4.16'],
    '4bnb' => ['name' => '4BnB', 'version' => '2.4.16'],
    'noracosmetics' => ['name' => 'Nora Cosmetics', 'version' => '2.4.16'],
    '3dworks' => ['name' => '3D Works', 'version' => '2.4.16'],
    'westdrayton' => ['name' => 'West Drayton', 'version' => '2.4.17'],
    'ernletprojects2' => ['name' => 'Ernlet Projects 2', 'version' => '2.4.18'],
    'ernletgroup' => ['name' => 'Ernlet Group', 'version' => '2.4.18'],
    'couriersolutions' => ['name' => 'Courier Solutions', 'version' => '2.4.18'],
    'loremaster' => ['name' => 'Loremaster', 'version' => '2.4.18'],
    'coverlot' => ['name' => 'Coverlot', 'version' => '2.4.18'],
    'globalstrategies' => ['name' => 'Global Strategies', 'version' => '2.4.18'],
    'norahbeauty' => ['name' => 'Norah Beauty', 'version' => '2.4.18'],
    'nidarshini' => ['name' => 'Nidarshini', 'version' => '2.4.18']
];

$FA_DATABASES = [
    'northernwarehouse' => ['database' => 'bookkeepingco_fron93', 'user' => 'bookkeepingco_fron93', 'pass' => '5]9fmNS4(p'],
    'madamz' => ['database' => 'bookkeepingco_fron75', 'user' => 'bookkeepingco_fron75', 'pass' => 'p5!.09TS03'],
    'cleaningguru' => ['database' => 'bookkeepingco_fron35', 'user' => 'bookkeepingco_fron35', 'pass' => 'S3124![png'],
    'quanto' => ['database' => 'bookkeepingco_fron61', 'user' => 'bookkeepingco_fron61', 'pass' => 'S6m]69h@pJ'],
    'spaceinteriors' => ['database' => 'bookkeepingco_fron89', 'user' => 'bookkeepingco_fron89', 'pass' => '502Si(qp5]'],
    'unlimitedfoods' => ['database' => 'bookkeepingco_fron71', 'user' => 'bookkeepingco_fron71', 'pass' => '27SoQr@p4('],
    'ernletprojects' => ['database' => 'bookkeepingco_fron88', 'user' => 'bookkeepingco_fron88', 'pass' => '1(3@0p094S'],
    'frayscottage' => ['database' => 'bookkeepingco_fron00', 'user' => 'bookkeepingco_fron00', 'pass' => '8pS@h41!17'],
    'constantadaptation' => ['database' => 'bookkeepingco_fron53', 'user' => 'bookkeepingco_fron53', 'pass' => 'Ip72[DS@1e'],
    'great-land' => ['database' => 'bookkeepingco_fron24', 'user' => 'bookkeepingco_fron24', 'pass' => '59tpaS3.6)'],
    'lighteningstrike' => ['database' => 'bookkeepingco_fron70', 'user' => 'bookkeepingco_fron70', 'pass' => 'p5bPS(5!M9'],
    'notsa' => ['database' => 'bookkeepingco_fron60', 'user' => 'bookkeepingco_fron60', 'pass' => '3Z8r9pSq])'],
    'thaega' => ['database' => 'bookkeepingco_fron65', 'user' => 'bookkeepingco_fron65', 'pass' => 'S9)Dp5[sJ6'],
    'modernhotelsupplies' => ['database' => 'bookkeepingco_fron21', 'user' => 'bookkeepingco_fron21', 'pass' => '0Q8S@4p7-2'],
    'training' => ['database' => 'bookkeepingco_fron94', 'user' => 'bookkeepingco_fron94', 'pass' => '9S9BpA-O0!'],
    'majande' => ['database' => 'bookkeepingco_fron84', 'user' => 'bookkeepingco_fron84', 'pass' => '7AD(S8Jp6!'],
    'guruonks' => ['database' => 'bookkeepingco_00onks', 'user' => 'bookkeepingco_00onks', 'pass' => 'p29[USKQ-4'],
    'marctizmo' => ['database' => 'bookkeepingco_fron40', 'user' => 'bookkeepingco_fron40', 'pass' => 'S7-]Y8p4o1'],
    '4bnb' => ['database' => 'bookkeepingco_fron17', 'user' => 'bookkeepingco_fron17', 'pass' => '6h.p990S]9'],
    'noracosmetics' => ['database' => 'bookkeepingco_fron43', 'user' => 'bookkeepingco_fron43', 'pass' => 'p64Su3-5F.'],
    '3dworks' => ['database' => 'bookkeepingco_fron48', 'user' => 'bookkeepingco_fron48', 'pass' => 'e24)(pfbS8'],
    'westdrayton' => ['database' => 'bookkeepingco_01', 'user' => 'bookkeepingco_01', 'pass' => ')pS920NSr.'],
    'ernletprojects2' => ['database' => 'bookkeepingco_fron621', 'user' => 'bookkeepingco_fron621', 'pass' => 'pXi.43S20@'],
    'ernletgroup' => ['database' => 'bookkeepingco_fron114', 'user' => 'bookkeepingco_fron114', 'pass' => 'Mp56US]Z[9'],
    'couriersolutions' => ['database' => 'bookkeepingco_fron895', 'user' => 'bookkeepingco_fron895', 'pass' => '].pA235AdS'],
    'loremaster' => ['database' => 'bookkeepingco_fron558', 'user' => 'bookkeepingco_fron558', 'pass' => ']qS29p9x8]'],
    'coverlot' => ['database' => 'bookkeepingco_fron997', 'user' => 'bookkeepingco_fron997', 'pass' => '2mS-22[3pq'],
    'globalstrategies' => ['database' => 'bookkeepingco_fron143', 'user' => 'bookkeepingco_fron143', 'pass' => '6Slc!p5@56'],
    'norahbeauty' => ['database' => 'bookkeepingco_fron773', 'user' => 'bookkeepingco_fron773', 'pass' => 'oB.9pS32p('],
    'nidarshini' => ['database' => 'bookkeepingco_fron341', 'user' => 'bookkeepingco_fron341', 'pass' => 'w93p6A(S9']
];

runSync();

function runSync() {
    global $FA_INSTANCES, $FA_DATABASES, $MAIN_DB;
    
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║           FA USER SYNC - Central Database Mode             ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    echo "Central DB: {$MAIN_DB['database']} @ {$MAIN_DB['host']}\n";
    echo "User: {$MAIN_DB['user']}\n\n";
    
    $startTime = microtime(true);
    $connected = 0;
    $failed = 0;
    $errors = [];
    
    // Connect to main database
    try {
        $mainPDO = new PDO(
            "mysql:host={$MAIN_DB['host']};dbname={$MAIN_DB['database']};charset=utf8mb4",
            $MAIN_DB['user'],
            $MAIN_DB['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "✓ Connected: {$MAIN_DB['database']}\n\n";
    } catch (PDOException $e) {
        echo "ERROR: Cannot connect to main database:\n" . $e->getMessage() . "\n\n";
        echo "Make sure bookkeepingco_sync_all user is added to {$MAIN_DB['database']} with ALL PRIVILEGES.\n";
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
        last_login DATETIME DEFAULT NULL,
        INDEX idx_email (email),
        INDEX idx_fa_instance (fa_instance),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $mainPDO->exec($sql);
    echo "✓ Table ready.\n\n";
    
    echo "Processing " . count($FA_DATABASES) . " FA instances...\n\n";
    
    foreach ($FA_DATABASES as $key => $dbConfig) {
        echo "[" . str_pad($key, 20) . "] ";
        
        try {
            $faPDO = new PDO(
                "mysql:host=localhost;dbname={$dbConfig['database']};charset=utf8mb4",
                $dbConfig['user'],
                $dbConfig['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $faDBName = $faPDO->query("SELECT DATABASE()")->fetchColumn();
            $stmt = $faPDO->query("SELECT id, user_id, real_name, email, role, password, inactive FROM users WHERE inactive = 0");
            $users = $stmt->fetchAll();
            
            echo "✓ {$faDBName} - " . count($users) . " users - ";
            
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
                    'database' => $faDBName,
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
            echo "synced\n";
            
        } catch (PDOException $e) {
            $failed++;
            $errMsg = $e->getMessage();
            if (strpos($errMsg, '1045') !== false) {
                $errors[] = $key . ": Access denied - need bookkeepingco_sync_all user";
            } else {
                $errors[] = $key . ": " . substr($errMsg, 0, 40);
            }
            echo "✗ ERROR\n";
        }
    }
    
    $elapsed = round(microtime(true) - $startTime, 2);
    
    echo "\n╔══════════════════════════════════════════════════════╗\n";
    echo "║              SYNC COMPLETE                           ║\n";
    echo "╚══════════════════════════════════════════════════════╝\n";
    echo "Time: {$elapsed}s | Success: {$connected}/" . count($FA_DATABASES) . " | Failed: {$failed}\n";
    
    if (!empty($errors)) {
        echo "\nFailed:\n";
        foreach ($errors as $e) echo "  - {$e}\n";
    }
    
    echo "\n✓ Users stored in {$MAIN_DB['database']}.unified_users table!\n";
}
