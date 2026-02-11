<?php
/**
 * UNIFIED AUTHENTICATION - Single login for all FA instances
 * 
 * This module handles authentication across all FA instances
 * and redirects users to their appropriate instance.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/fa-database-creds.php';

/**
 * Authenticate user against all FA instances
 * 
 * @param string $email User's email
 * @param string $password User's password
 * @return array|false User data or false if authentication fails
 */
function authenticateUserUnified($email, $password) {
    // First check in central unified_users table
    $db = getDBConnection();
    
    $stmt = $db->prepare("
        SELECT * FROM unified_users 
        WHERE email = ? AND status = 'active'
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // User not in unified table, try all FA instances
        return authenticateAgainstAllFAInstances($email, $password);
    }
    
    // User found in unified table, verify password
    // Try central password first (if set)
    if (!empty($user['password_hash']) && strlen($user['password_hash']) < 40) {
        // Plain text or MD5 from FA
        $passwordHash = md5($password);
        if ($user['password_hash'] === $passwordHash) {
            return $user;
        }
    } elseif (!empty($user['password_hash'])) {
        // Hashed password
        if (password_verify($password, $user['password_hash'])) {
            return $user;
        }
        
        // Try MD5 fallback (older FA versions)
        if (md5($password) === $user['password_hash']) {
            return $user;
        }
    }
    
    // Check FA instances for correct password
    $faInstances = json_decode($user['fa_instances'] ?? '{}', true);
    foreach ($faInstances as $instanceKey => $instanceData) {
        $faUser = authenticateAgainstFAInstance($instanceKey, $email, $password);
        if ($faUser) {
            // Update password in central DB
            updateUserPassword($user['id'], $password);
            return $user;
        }
    }
    
    return false;
}

/**
 * Try to authenticate user against ALL FA instances
 * (For users not yet in unified table)
 */
function authenticateAgainstAllFAInstances($email, $password) {
    $instances = $GLOBALS['FA_INSTANCES'];
    
    foreach ($instances as $key => $instance) {
        $faUser = authenticateAgainstFAInstance($key, $email, $password);
        if ($faUser) {
            // Found user! Add to unified table
            return addUserToUnifiedTable($key, $instance, $faUser, $password);
        }
    }
    
    return false;
}

/**
 * Authenticate against a single FA instance
 */
function authenticateAgainstFAInstance($instanceKey, $email, $password) {
    $faPDO = getFAConnection($instanceKey);
    
    if (!$faPDO) {
        return false;
    }
    
    try {
        // Try to find user by email
        $stmt = $faPDO->prepare("
            SELECT user_id, user_id as id, real_name, email, role, password, inactive
            FROM {$faPDO->query("SELECT DATABASE()")->fetchColumn()}.users 
            WHERE email = ? AND inactive = 0
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Try username (some FA versions use user_id as username)
            $stmt = $faPDO->prepare("
                SELECT user_id, user_id as id, real_name, email, role, password, inactive
                FROM {$faPDO->query("SELECT DATABASE()")->fetchColumn()}.users 
                WHERE user_id = ? AND inactive = 0
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        }
        
        if ($user) {
            // Verify password (FA uses MD5)
            if (md5($password) === $user['password']) {
                return $user;
            }
        }
        
    } catch (PDOException $e) {
        error_log("FA Instance {$instanceKey} authentication failed: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Add authenticated user to unified table
 */
function addUserToUnifiedTable($instanceKey, $instance, $faUser, $password) {
    $db = getDBConnection();
    
    $faInstances = [
        $instanceKey => [
            'name' => $instance['name'],
            'fa_user_id' => $faUser['user_id'],
            'role' => $faUser['role'],
            'added_at' => date('Y-m-d H:i:s')
        ]
    ];
    
    $stmt = $db->prepare("
        INSERT INTO unified_users 
        (email, name, fa_instance, fa_user_id, password_hash, role, fa_instances, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            fa_instance = VALUES(fa_instance),
            fa_user_id = VALUES(fa_user_id),
            password_hash = VALUES(password_hash),
            role = VALUES(role),
            fa_instances = VALUES(fa_instances),
            updated_at = NOW()
    ");
    
    $stmt->execute([
        $faUser['email'],
        $faUser['real_name'],
        $instanceKey,
        $faUser['user_id'],
        password_hash($password, PASSWORD_DEFAULT),
        $faUser['role'],
        json_encode($faInstances)
    ]);
    
    // Fetch and return the unified user
    $stmt = $db->prepare("SELECT * FROM unified_users WHERE email = ?");
    $stmt->execute([$faUser['email']]);
    return $stmt->fetch();
}

/**
 * Update user's password in unified table
 */
function updateUserPassword($userId, $password) {
    $db = getDBConnection();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("UPDATE unified_users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$hash, $userId]);
}

/**
 * Get redirect URL for user based on their FA instances
 */
function getUserRedirectUrl($user) {
    $faInstances = json_decode($user['fa_instances'] ?? '{}', true);
    
    if (empty($faInstances)) {
        // No FA access, go to portal dashboard
        return '/portal';
    }
    
    // If user has access to multiple instances, show selection or go to first
    if (count($faInstances) === 1) {
        $instanceKey = array_key_first($faInstances);
        $instanceUrl = $GLOBALS['FA_INSTANCES'][$instanceKey]['url'] ?? '';
        
        if (!empty($instanceUrl)) {
            return '/redirect.php?instance=' . urlencode($instanceKey);
        }
    }
    
    // Multiple instances - show selection or default to first
    $instanceKey = array_key_first($faInstances);
    $instanceUrl = $GLOBALS['FA_INSTANCES'][$instanceKey]['url'] ?? '';
    
    if (!empty($instanceUrl)) {
        return '/redirect.php?instance=' . urlencode($instanceKey);
    }
    
    return '/portal';
}

/**
 * Get all FA instances user has access to
 */
function getUserFAInstancesUnified($userId) {
    $db = getDBConnection();
    
    $stmt = $db->prepare("SELECT fa_instances FROM unified_users WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    if ($result && !empty($result['fa_instances'])) {
        return json_decode($result['fa_instances'], true);
    }
    
    return [];
}

/**
 * Check if user is admin across any instance
 */
function isUnifiedAdmin($user) {
    $faInstances = json_decode($user['fa_instances'] ?? '{}', true);
    
    foreach ($faInstances as $instance) {
        if (in_array($instance['role'] ?? '', ['admin', 'super_admin'])) {
            return true;
        }
    }
    
    return false;
}
