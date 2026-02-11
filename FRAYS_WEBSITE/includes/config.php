<?php
/**
 * FRAYSCOTTAGE BOOKKEEPING SERVICES - Main Configuration
 * 
 * Central configuration for the document processing portal
 * Version: 1.0.0
 */

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Timezone
date_default_timezone_set('Africa/Gaborone');

// Application Info
define('APP_NAME', 'Bookkeeping Services');
define('APP_URL', 'http://localhost:8080');
define('APP_VERSION', '1.0.0');

// Database Configuration - LOCAL DEVELOPMENT
define('DB_HOST', 'localhost');
define('DB_NAME', 'frayscottage_bookkeeping');
define('DB_USER', 'frays_admin');
define('DB_PASS', 'admin123');
define('DB_CHARSET', 'utf8mb4');

// FA Instances Database Configuration
// These are the credentials to connect to each FA instance's database
// IMPORTANT: Change these to match your actual FA database credentials
define('FA_DB_HOST', 'localhost');  // or your FA server IP/hostname
define('FA_DB_USER', 'frays_admin');  // user with access to all FA databases
define('FA_DB_PASS', 'admin123');  // password for FA database user

// FA Instances Configuration
$FA_INSTANCES = [
    'northernwarehouse' => [
        'name' => 'Northern Warehouse',
        'url' => 'https://www.bookkeeping.co.bw/northernwarehouse',
        'version' => '2.4.18'
    ],
    'madamz' => [
        'name' => 'Madamz',
        'url' => 'https://www.bookkeeping.co.bw/madamz',
        'version' => '2.4.18'
    ],
    'cleaningguru' => [
        'name' => 'Cleaning Guru',
        'url' => 'https://www.bookkeeping.co.bw/cleaningguru',
        'version' => '2.4.18'
    ],
    'quanto' => [
        'name' => 'Quanto',
        'url' => 'https://www.bookkeeping.co.bw/quanto',
        'version' => '2.4.18'
    ],
    'spaceinteriors' => [
        'name' => 'Space Interiors',
        'url' => 'https://www.bookkeeping.co.bw/spaceinteriors',
        'version' => '2.4.18'
    ],
    'unlimitedfoods' => [
        'name' => 'Unlimited Foods',
        'url' => 'https://www.bookkeeping.co.bw/unlimitedfoods',
        'version' => '2.4.10'
    ],
    'ernletprojects' => [
        'name' => 'Ernlet Projects',
        'url' => 'https://www.bookkeeping.co.bw/ernletprojects',
        'version' => '2.4.16'
    ],
    'frayscottage' => [
        'name' => 'Frays Cottage',
        'url' => 'https://www.bookkeeping.co.bw/frayscottage',
        'version' => '2.4.16'
    ],
    'constantadaptation' => [
        'name' => 'Constant Adaptation',
        'url' => 'https://www.bookkeeping.co.bw/constantadaptation',
        'version' => '2.4.10'
    ],
    'great-land' => [
        'name' => 'Great-Land',
        'url' => 'https://www.bookkeeping.co.bw/great-land',
        'version' => '2.4.10'
    ],
    'lighteningstrike' => [
        'name' => 'Lightening Strike',
        'url' => 'https://www.bookkeeping.co.bw/lighteningstrike',
        'version' => '2.4.10'
    ],
    'notsa' => [
        'name' => 'NOTSA',
        'url' => 'https://www.bookkeeping.co.bw/notsa',
        'version' => '2.4.10'
    ],
    'thaega' => [
        'name' => 'Thaega',
        'url' => 'https://www.bookkeeping.co.bw/thaega',
        'version' => '2.4.10'
    ],
    'modernhotelsupplies' => [
        'name' => 'Modern Hotel Supplies',
        'url' => 'https://www.bookkeeping.co.bw/modernhotelsupplies',
        'version' => '2.4.10'
    ],
    'training' => [
        'name' => 'Training',
        'url' => 'https://www.bookkeeping.co.bw/training',
        'version' => '2.4.16'
    ],
    'majande' => [
        'name' => 'Majande',
        'url' => 'https://bookkeeping.co.bw/majande',
        'version' => '2.4.11'
    ],
    'guruonks' => [
        'name' => 'Guru Onks',
        'url' => 'https://www.bookkeeping.co.bw/guruonks',
        'version' => '2.4.11'
    ],
    'marctizmo' => [
        'name' => 'Marctizmo',
        'url' => 'https://bookkeeping.co.bw/marctizmo',
        'version' => '2.4.16'
    ],
    '4bnb' => [
        'name' => '4BnB',
        'url' => 'https://www.bookkeeping.co.bw/4bnb',
        'version' => '2.4.16'
    ],
    'noracosmetics' => [
        'name' => 'Nora Cosmetics',
        'url' => 'https://bookkeeping.co.bw/noracosmetics',
        'version' => '2.4.16'
    ],
    '3dworks' => [
        'name' => '3D Works',
        'url' => 'https://www.bookkeeping.co.bw/3dworks',
        'version' => '2.4.16'
    ],
    'westdrayton' => [
        'name' => 'West Drayton',
        'url' => 'https://www.bookkeeping.co.bw/westdrayton',
        'version' => '2.4.17'
    ],
    'ernletprojects2' => [
        'name' => 'Ernlet Projects 2',
        'url' => 'https://www.bookkeeping.co.bw/ernletprojects2',
        'version' => '2.4.18'
    ],
    'ernletgroup' => [
        'name' => 'Ernlet Group',
        'url' => 'https://www.bookkeeping.co.bw/ernletgroup',
        'version' => '2.4.18'
    ],
    'couriersolutions' => [
        'name' => 'Courier Solutions',
        'url' => 'https://www.bookkeeping.co.bw/couriersolutions',
        'version' => '2.4.18'
    ],
    'loremaster' => [
        'name' => 'Loremaster',
        'url' => 'https://www.bookkeeping.co.bw/loremaster',
        'version' => '2.4.18'
    ],
    'coverlot' => [
        'name' => 'Coverlot',
        'url' => 'https://www.bookkeeping.co.bw/coverlot',
        'version' => '2.4.18'
    ],
    'globalstrategies' => [
        'name' => 'Global Strategies',
        'url' => 'https://www.bookkeeping.co.bw/globalstrategies',
        'version' => '2.4.18'
    ],
    'norahbeauty' => [
        'name' => 'Norah Beauty',
        'url' => 'https://www.bookkeeping.co.bw/norahbeauty',
        'version' => '2.4.18'
    ],
    'nidarshini' => [
        'name' => 'Nidarshini',
        'url' => 'https://www.bookkeeping.co.bw/nidarshini',
        'version' => '2.4.18'
    ]
];

// Document Processing Settings
define('DOC_UPLOAD_DIR', __DIR__ . '/uploads');
define('DOC_PROCESSED_DIR', __DIR__ . '/processed');
define('DOC_MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('DOC_ALLOWED_TYPES', ['pdf', 'jpg', 'jpeg', 'png', 'tiff']);
define('DOC_LEGIBILITY_THRESHOLD', 70);

// OneDrive Configuration
define('ONEDRIVE_BASE_PATH', getenv('ONEDRIVE_PATH') ?: '/Users/fraysc5/OneDrive');

// Session Configuration
session_start([
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'gc_maxlifetime' => 86400
]);

// CSRF Protection
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Database Connection
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return null;
        }
    }
    
    return $pdo;
}

// Authentication Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $db = getDBConnection();
    if (!$db) return null;
    
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function isAdmin() {
    $user = getCurrentUser();
    return $user && ($user['role'] === 'admin' || $user['role'] === 'super_admin');
}

function authenticateUser($email, $password) {
    $db = getDBConnection();
    if (!$db) return null;
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    
    return null;
}

function getUserFAInstances($userId) {
    $db = getDBConnection();
    if (!$db) return [];
    
    $stmt = $db->prepare("SELECT fa_instances FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    if ($result && !empty($result['fa_instances'])) {
        return json_decode($result['fa_instances'], true) ?: [];
    }
    
    return [];
}

function loginUser($email, $password) {
    $db = getDBConnection();
    if (!$db) return false;
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['fa_instances'] = json_decode($user['fa_instances'] ?? '[]', true);
        $_SESSION['last_activity'] = time();
        
        // Update last login
        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        return true;
    }
    
    return false;
}

function logoutUser() {
    session_destroy();
    session_start();
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Utility Functions
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

function formatCurrency($amount, $currency = 'BWP') {
    return $currency . ' ' . number_format($amount, 2);
}

function generateReference($type, $clientCode) {
    $date = date('Y-m-d');
    $count = getDBConnection()->query("SELECT COUNT(*) FROM documents WHERE DATE(uploaded_at) = CURDATE()")->fetchColumn() + 1;
    return sprintf('%s-%s-%s-%03d', $type, $clientCode, str_replace('-', '', $date), $count);
}

// Logging
function logActivity($action, $details = []) {
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $_SESSION['user_id'] ?? 0,
        'action' => $action,
        'details' => $details,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $logFile = __DIR__ . '/logs/activity.log';
    file_put_contents($logFile, json_encode($log) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// File Processing
function handleFileUpload($file, $clientCode, $docType) {
    $result = [
        'success' => false,
        'reference' => null,
        'error' => null
    ];
    
    try {
        // Validate file
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, DOC_ALLOWED_TYPES)) {
            throw new Exception("Invalid file type: {$ext}");
        }
        
        if ($file['size'] > DOC_MAX_FILE_SIZE) {
            throw new Exception("File too large: " . round($file['size'] / 1024 / 1024, 2) . "MB");
        }
        
        // Generate reference
        $reference = generateReference($docType, $clientCode);
        $filename = sanitizeFilename($file['name']);
        $newFilename = "{$reference}_{$filename}";
        
        // Ensure upload directory exists
        $uploadDir = DOC_UPLOAD_DIR . '/' . date('Y/m');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filePath = $uploadDir . '/' . $newFilename;
        
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception("Failed to save uploaded file");
        }
        
        // Create watermarked version
        $watermarkedPath = addWatermark($filePath, $reference);
        
        // Save to database
        $db = getDBConnection();
        $stmt = $db->prepare("
            INSERT INTO documents 
            (reference, client_code, doc_type, original_name, file_path, watermarked_path, uploaded_by, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $reference,
            $clientCode,
            $docType,
            $filename,
            $filePath,
            $watermarkedPath,
            $_SESSION['user_id'] ?? 0
        ]);
        
        $result['success'] = true;
        $result['reference'] = $reference;
        $result['id'] = $db->lastInsertId();
        
        logActivity('document_uploaded', ['reference' => $reference, 'client' => $clientCode]);
        
    } catch (Exception $e) {
        $result['error'] = $e->getMessage();
        logActivity('upload_failed', ['error' => $e->getMessage()]);
    }
    
    return $result;
}

function sanitizeFilename($filename) {
    $info = pathinfo($filename);
    $ext = $info['extension'];
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $info['filename']);
    return $name . '.' . $ext;
}

function addWatermark($filePath, $reference) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $watermarkedPath = str_replace('.' . $ext, '_WM.' . $ext, $filePath);
    
    // For PDFs, use a simple copy with metadata (full PDF processing requires additional libraries)
    // For images, add watermark using GD
    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        try {
            if ($ext === 'jpg' || $ext === 'jpeg') {
                $image = imagecreatefromjpeg($filePath);
            } else {
                $image = imagecreatefrompng($filePath);
            }
            
            // Add watermark text
            $text = "PENDING | {$reference}";
            $textColor = imagecolorallocate($image, 255, 0, 0);
            imagestring($image, 5, 10, 10, $text, $textColor);
            
            if ($ext === 'jpg' || $ext === 'jpeg') {
                imagejpeg($image, $watermarkedPath, 90);
            } else {
                imagepng($image, $watermarkedPath);
            }
            
            imagedestroy($image);
        } catch (Exception $e) {
            copy($filePath, $watermarkedPath);
        }
    } else {
        // For PDFs, just copy (watermark would require additional libraries)
        copy($filePath, $watermarkedPath);
    }
    
    return $watermarkedPath;
}

function cleanupProcessedFiles() {
    $processedDir = DOC_PROCESSED_DIR;
    $uploadsDir = DOC_UPLOAD_DIR;
    
    // Check if directories exist and are readable
    if (!is_dir($uploadsDir) || !is_readable($uploadsDir)) {
        return;
    }
    
    // Clean old files from uploads (older than 7 days)
    $cutoff = strtotime('-7 days');
    
    try {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($files as $file) {
            if ($file->isFile() && $file->getMTime() < $cutoff) {
                @unlink($file->getPathname());
            }
        }
    } catch (Exception $e) {
        // Directory iteration failed, skip cleanup
        error_log("Cleanup error: " . $e->getMessage());
    }
    
    logActivity('cleanup_executed');
}

// Initialize on every request
if (is_dir(DOC_UPLOAD_DIR) && is_dir(DOC_PROCESSED_DIR)) {
    cleanupProcessedFiles();
}
