<?php
/**
 * FRAYSCOTTAGE BOOKKEEPING SERVICES - Server Configuration
 * 
 * For: bookkeeping.co.bw (Live Server)
 */

// Error reporting - disable for production
error_reporting(0);
ini_set('display_errors', 0);

// Timezone
date_default_timezone_set('Africa/Gaborone');

// Application Info
define('APP_NAME', 'Bookkeeping Services');
define('APP_URL', 'https://bookkeeping.co.bw');
define('APP_VERSION', '1.0.0');

// Server Database Configuration - bookkeepingco_00
define('DB_HOST', 'localhost');
define('DB_NAME', 'bookkeepingco_00');
define('DB_USER', 'bookkeepingco_sync_all');
define('DB_PASS', 'F@ySync2026!');
define('DB_CHARSET', 'utf8mb4');

// FA Instances Configuration
$FA_INSTANCES = [
    'northernwarehouse' => ['name' => 'Northern Warehouse', 'url' => 'https://www.bookkeeping.co.bw/northernwarehouse', 'version' => '2.4.18'],
    'madamz' => ['name' => 'Madamz', 'url' => 'https://www.bookkeeping.co.bw/madamz', 'version' => '2.4.18'],
    'cleaningguru' => ['name' => 'Cleaning Guru', 'url' => 'https://www.bookkeeping.co.bw/cleaningguru', 'version' => '2.4.18'],
    'quanto' => ['name' => 'Quanto', 'url' => 'https://www.bookkeeping.co.bw/quanto', 'version' => '2.4.18'],
    'spaceinteriors' => ['name' => 'Space Interiors', 'url' => 'https://www.bookkeeping.co.bw/spaceinteriors', 'version' => '2.4.18'],
    'unlimitedfoods' => ['name' => 'Unlimited Foods', 'url' => 'https://www.bookkeeping.co.bw/unlimitedfoods', 'version' => '2.4.10'],
    'ernletprojects' => ['name' => 'Ernlet Projects', 'url' => 'https://www.bookkeeping.co.bw/ernletprojects', 'version' => '2.4.16'],
    'frayscottage' => ['name' => 'Frays Cottage', 'url' => 'https://www.bookkeeping.co.bw/frayscottage', 'version' => '2.4.16'],
    'constantadaptation' => ['name' => 'Constant Adaptation', 'url' => 'https://www.bookkeeping.co.bw/constantadaptation', 'version' => '2.4.10'],
    'great-land' => ['name' => 'Great-Land', 'url' => 'https://www.bookkeeping.co.bw/great-land', 'version' => '2.4.10'],
    'lighteningstrike' => ['name' => 'Lightening Strike', 'url' => 'https://www.bookkeeping.co.bw/lighteningstrike', 'version' => '2.4.10'],
    'notsa' => ['name' => 'NOTSA', 'url' => 'https://www.bookkeeping.co.bw/notsa', 'version' => '2.4.10'],
    'thaega' => ['name' => 'Thaega', 'url' => 'https://www.bookkeeping.co.bw/thaega', 'version' => '2.4.10'],
    'modernhotelsupplies' => ['name' => 'Modern Hotel Supplies', 'url' => 'https://www.bookkeeping.co.bw/modernhotelsupplies', 'version' => '2.4.10'],
    'training' => ['name' => 'Training', 'url' => 'https://www.bookkeeping.co.bw/training', 'version' => '2.4.16'],
    'majande' => ['name' => 'Majande', 'url' => 'https://www.bookkeeping.co.bw/majande', 'version' => '2.4.11'],
    'guruonks' => ['name' => 'Guru Onks', 'url' => 'https://www.bookkeeping.co.bw/guruonks', 'version' => '2.4.11'],
    'marctizmo' => ['name' => 'Marctizmo', 'url' => 'https://www.bookkeeping.co.bw/marctizmo', 'version' => '2.4.16'],
    '4bnb' => ['name' => '4BnB', 'url' => 'https://www.bookkeeping.co.bw/4bnb', 'version' => '2.4.16'],
    'noracosmetics' => ['name' => 'Nora Cosmetics', 'url' => 'https://www.bookkeeping.co.bw/noracosmetics', 'version' => '2.4.16'],
    '3dworks' => ['name' => '3D Works', 'url' => 'https://www.bookkeeping.co.bw/3dworks', 'version' => '2.4.16'],
    'westdrayton' => ['name' => 'West Drayton', 'url' => 'https://www.bookkeeping.co.bw/westdrayton', 'version' => '2.4.17'],
    'ernletprojects2' => ['name' => 'Ernlet Projects 2', 'url' => 'https://www.bookkeeping.co.bw/ernletprojects2', 'version' => '2.4.18'],
    'ernletgroup' => ['name' => 'Ernlet Group', 'url' => 'https://www.bookkeeping.co.bw/ernletgroup', 'version' => '2.4.18'],
    'couriersolutions' => ['name' => 'Courier Solutions', 'url' => 'https://www.bookkeeping.co.bw/couriersolutions', 'version' => '2.4.18'],
    'loremaster' => ['name' => 'Loremaster', 'url' => 'https://www.bookkeeping.co.bw/loremaster', 'version' => '2.4.18'],
    'coverlot' => ['name' => 'Coverlot', 'url' => 'https://www.bookkeeping.co.bw/coverlot', 'version' => '2.4.18'],
    'globalstrategies' => ['name' => 'Global Strategies', 'url' => 'https://www.bookkeeping.co.bw/globalstrategies', 'version' => '2.4.18'],
    'norahbeauty' => ['name' => 'Norah Beauty', 'url' => 'https://www.bookkeeping.co.bw/norahbeauty', 'version' => '2.4.18'],
    'nidarshini' => ['name' => 'Nidarshini', 'url' => 'https://www.bookkeeping.co.bw/nidarshini', 'version' => '2.4.18']
];

// Document Processing Settings
define('DOC_UPLOAD_DIR', __DIR__ . '/uploads');
define('DOC_PROCESSED_DIR', __DIR__ . '/processed');
define('DOC_MAX_FILE_SIZE', 10 * 1024 * 1024);
define('DOC_ALLOWED_TYPES', ['pdf', 'jpg', 'jpeg', 'png']);

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
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            return null;
        }
    }
    return $pdo;
}

// Authentication Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function logoutUser() {
    session_destroy();
    session_start();
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function logActivity($action, $details = []) {
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $_SESSION['user_id'] ?? 0,
        'action' => $action,
        'details' => $details
    ];
    $logFile = __DIR__ . '/logs/activity.log';
    @file_put_contents($logFile, json_encode($log) . PHP_EOL, FILE_APPEND | LOCK_EX);
}
