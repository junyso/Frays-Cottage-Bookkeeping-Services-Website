<?php
/**
 * FRAYSCOTTAGE BOOKKEEPING SERVICES - Database Setup
 * 
 * Run this script once to set up the database
 * Access: https://www.bookkeeping.co.bw/setup/
 */

require_once __DIR__ . '/../includes/config.php';

$setupStatus = [
    'success' => false,
    'message' => '',
    'steps' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $setupStatus['message'] = 'Invalid CSRF token';
    } else {
        try {
            $db = getDBConnection();
            
            if (!$db) {
                throw new Exception('Database connection failed');
            }
            
            // Create tables
            $queries = [
                // Users table
                "CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    phone VARCHAR(50),
                    role ENUM('admin', 'clerk', 'client') DEFAULT 'client',
                    fa_instances JSON,
                    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    last_login DATETIME,
                    INDEX idx_email (email),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // FA Instances table
                "CREATE TABLE IF NOT EXISTS fa_instances (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    slug VARCHAR(100) NOT NULL UNIQUE,
                    name VARCHAR(255) NOT NULL,
                    url VARCHAR(500) NOT NULL,
                    version VARCHAR(20),
                    api_key VARCHAR(255),
                    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_slug (slug),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Documents table
                "CREATE TABLE IF NOT EXISTS documents (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    reference VARCHAR(100) NOT NULL UNIQUE,
                    client_code VARCHAR(50) NOT NULL,
                    doc_type ENUM('invoice', 'receipt', 'waybill', 'statement', 'general') NOT NULL,
                    original_name VARCHAR(500) NOT NULL,
                    file_path VARCHAR(1000),
                    watermarked_path VARCHAR(1000),
                    ocr_text TEXT,
                    ocr_confidence DECIMAL(5,2),
                    extracted_data JSON,
                    status ENUM('pending', 'manual_review', 'ready', 'posted', 'archived', 'rejected') DEFAULT 'pending',
                    uploaded_by INT,
                    processed_by INT,
                    fa_instance_id INT,
                    fa_transaction_id VARCHAR(100),
                    notes TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    processed_at DATETIME,
                    posted_at DATETIME,
                    INDEX idx_reference (reference),
                    INDEX idx_client (client_code),
                    INDEX idx_status (status),
                    INDEX idx_uploaded_at (uploaded_at),
                    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
                    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Document batches table
                "CREATE TABLE IF NOT EXISTS document_batches (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    batch_number VARCHAR(50) NOT NULL UNIQUE,
                    client_code VARCHAR(50) NOT NULL,
                    fa_instance_id INT,
                    status ENUM('draft', 'pending_review', 'approved', 'posted', 'rejected') DEFAULT 'draft',
                    created_by INT,
                    reviewed_by INT,
                    approved_by INT,
                    review_notes TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    reviewed_at DATETIME,
                    approved_at DATETIME,
                    posted_at DATETIME,
                    INDEX idx_batch_number (batch_number),
                    INDEX idx_status (status),
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
                    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
                    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Batch documents junction table
                "CREATE TABLE IF NOT EXISTS batch_documents (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    batch_id INT NOT NULL,
                    document_id INT NOT NULL,
                    position INT DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (batch_id) REFERENCES document_batches(id) ON DELETE CASCADE,
                    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
                    UNIQUE KEY unique_batch_document (batch_id, document_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Settings table
                "CREATE TABLE IF NOT EXISTS settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    `key` VARCHAR(100) NOT NULL UNIQUE,
                    value TEXT,
                    type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_key (`key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                // Activity log table
                "CREATE TABLE IF NOT EXISTS activity_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    action VARCHAR(100) NOT NULL,
                    details JSON,
                    ip_address VARCHAR(45),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user (user_id),
                    INDEX idx_action (action),
                    INDEX idx_created (created_at),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            ];
            
            foreach ($queries as $i => $query) {
                try {
                    $db->exec($query);
                    $setupStatus['steps'][] = [
                        'step' => $i + 1,
                        'query' => substr($query, 0, 50) . '...',
                        'success' => true
                    ];
                } catch (PDOException $e) {
                    $setupStatus['steps'][] = [
                        'step' => $i + 1,
                        'query' => substr($query, 0, 50) . '...',
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            // Insert default admin user
            $adminExists = $db->query("SELECT COUNT(*) FROM users WHERE email = 'admin@frayscottage.co.bw'")->fetchColumn();
            
            if (!$adminExists) {
                $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    INSERT INTO users (email, password, name, role, fa_instances, status)
                    VALUES ('admin@frayscottage.co.bw', ?, 'System Administrator', 'admin', ?, 'active')
                ");
                $stmt->execute([$adminPassword, json_encode(array_keys($GLOBALS['FA_INSTANCES']))]);
                $setupStatus['steps'][] = [
                    'step' => 'Admin User',
                    'query' => 'Created default admin user',
                    'success' => true
                ];
            }
            
            // Insert default settings
            $settings = [
                ['min_legibility_score', '70', 'integer'],
                ['watermark_text', 'PENDING PROCESSING - DO NOT USE', 'string'],
                ['notification_clerk_email', 'clerk@frayscottage.co.bw', 'string'],
                ['notification_clerk_phone', '+267XXXXXXXX', 'string'],
                ['auto_post_to_fa', 'false', 'boolean'],
                ['cleanup_after_days', '7', 'integer'],
                ['max_file_size_mb', '10', 'integer'],
                ['company_name', 'Bookkeeping Services', 'string'],
                ['company_website', 'https://www.frayscottage.co.bw', 'string']
            ];
            
            foreach ($settings as $setting) {
                $stmt = $db->prepare("
                    INSERT INTO settings (`key`, value, type)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE value = VALUES(value)
                ");
                $stmt->execute($setting);
            }
            
            $setupStatus['success'] = true;
            $setupStatus['message'] = 'Database setup completed successfully!';
            
            logActivity('database_setup', ['success' => true]);
            
        } catch (Exception $e) {
            $setupStatus['message'] = $e->getMessage();
            logActivity('database_setup', ['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Bookkeeping Services</title>
    <link rel="icon" href="/assets/images/favicon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { color: #000000; }
        h1, h2, h3, h4, h5, h6 { color: #000000; }
        .frays-gradient { background: linear-gradient(135deg, #990000 0%, #8B0000 100%); }
        .step-success { border-left: 4px solid #CCCC66; }
        .step-error { border-left: 4px solid #990000; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <!-- Top Contact Bar - Single Line -->
    <div class="bg-frays-red text-white text-[9px] md:text-[10px] lg:text-xs py-1 overflow-x-auto">
        <div class="max-w-7xl mx-auto px-2">
            <div class="flex justify-center items-center whitespace-nowrap gap-6">
                <a href="https://www.google.com/maps/search/?api=1&query=Plot+68287%2C+Unit+203%2C+Phakalane+Industrial%2C+Gaborone%2C+Botswana" target="_blank" class="flex items-center gap-1.5 hover:text-frays-yellow transition-colors">
                    <i class="ri-map-pin-line text-[10px] md:text-xs"></i>
                    <span>Plot 68287, Unit 203, Phakalane Industrial, Gaborone, Botswana</span>
                </a>
                <div class="flex items-center gap-1.5">
                    <a href="mailto:helpdesk@frayscottage.co.bw" class="flex items-center gap-1.5 hover:text-frays-yellow transition-colors">
                        <i class="ri-mail-line text-[10px] md:text-xs"></i>
                        <span class="hidden md:inline">helpdesk@frayscottage.co.bw</span>
                    </a>
                    <a href="tel:+2673966011" class="flex items-center gap-1.5 hover:text-frays-yellow transition-colors">
                        <i class="ri-phone-line text-[10px] md:text-xs"></i>
                        <span>(+267) 396 6011</span>
                    </a>
                    <a href="https://wa.me/2673966011" target="_blank" class="flex items-center gap-1.5 hover:text-frays-yellow transition-colors">
                        <i class="ri-whatsapp-line text-[10px] md:text-xs"></i>
                        <span>(+267) 396 6011</span>
                    </a>
                </div>
                <div class="flex items-center gap-1.5">
                    <i class="ri-time-line text-[10px] md:text-xs"></i>
                    <span>Mon-Fri 8am-5pm | Closed Weekends</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-2xl w-full bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="frays-gradient py-8 px-6">
                <div class="flex items-center justify-center gap-4">
                    <i class="ri-database-2-line text-4xl text-white"></i>
                    <h1 class="text-2xl font-bold text-white">Database Setup</h1>
                </div>
                <p class="text-center text-gray-300 mt-2">Bookkeeping Services</p>
            </div>
            
            <div class="p-8">
                <?php if ($setupStatus['success']): ?>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <i class="ri-check-circle-line text-3xl text-green-600"></i>
                            <div>
                                <h2 class="text-xl font-semibold text-green-800">Setup Complete!</h2>
                                <p class="text-green-600"><?= htmlspecialchars($setupStatus['message']) ?></p>
                            </div>
                        </div>
                        
                        <h3 class="font-semibold text-green-800 mb-3">Database Tables Created:</h3>
                        <ul class="space-y-2">
                            <?php foreach ($setupStatus['steps'] as $step): ?>
                                <li class="flex items-center gap-1 text-sm">
                                    <?php if ($step['success']): ?>
                                        <i class="ri-check-line text-green-500"></i>
                                        <span><?= htmlspecialchars($step['query']) ?></span>
                                    <?php else: ?>
                                        <i class="ri-error-warning-line text-red-500"></i>
                                        <span class="text-red-600"><?= htmlspecialchars($step['error'] ?? $step['query']) ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <h3 class="font-semibold text-blue-800 mb-2">Default Login Credentials:</h3>
                        <div class="bg-white rounded p-4 font-mono text-sm">
                            <p><strong>Email:</strong> admin@frayscottage.co.bw</p>
                            <p><strong>Password:</strong> admin123</p>
                        </div>
                        <p class="text-sm text-blue-600 mt-2">⚠️ Please change the password after first login!</p>
                    </div>
                    
                    <div class="flex gap-4">
                        <a href="/portal" class="flex-1 bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700 font-medium">
                            Go to Login
                        </a>
                        <a href="/" class="flex-1 border border-gray-300 text-gray-700 text-center py-3 rounded-lg hover:bg-gray-50 font-medium">
                            Go to Homepage
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center mb-8">
                        <i class="ri-database-line text-6xl text-gray-300 mb-4"></i>
                        <h2 class="text-xl font-semibold text-gray-800">Ready to Install</h2>
                        <p class="text-gray-600 mt-2">This will create all necessary database tables and the default admin user.</p>
                    </div>
                    
                    <?php if (!empty($setupStatus['message'])): ?>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center gap-1 text-red-700">
                                <i class="ri-error-warning-line"></i>
                                <span><?= htmlspecialchars($setupStatus['message']) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="action" value="install">
                        
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <h3 class="font-semibold text-yellow-800 mb-2">⚠️ Before proceeding:</h3>
                            <ul class="text-sm text-yellow-700 space-y-1">
                                <li>• Make sure you have created the database in cPanel</li>
                                <li>• Update includes/config.php with your database credentials</li>
                                <li>• This will NOT overwrite existing data</li>
                            </ul>
                        </div>
                        
                        <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-lg hover:bg-blue-700 font-semibold text-lg flex items-center justify-center gap-2">
                            <i class="ri-install-line"></i>
                            Run Installation
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            
            <div class="bg-gray-50 px-8 py-4 border-t">
                <p class="text-center text-sm text-gray-500">
                    Version <?= APP_VERSION ?> | © <?= date('Y') ?> Bookkeeping Services
                </p>
            </div>
        </div>
    </div>
    
    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/2673966011" target="_blank" class="fixed bottom-6 right-6 z-50 group">
        <div class="relative">
            <!-- Pulse Animation -->
            <div class="absolute inset-0 rounded-full bg-green-500 animate-ping opacity-75"></div>
            <div class="absolute inset-0 rounded-full bg-green-500 opacity-50 animate-pulse"></div>
            <!-- Button -->
            <div class="relative bg-green-500 text-white px-5 py-3 rounded-full shadow-2xl flex items-center gap-3 hover:bg-green-600 transition-all transform hover:scale-105">
                <i class="ri-whatsapp-line text-2xl"></i>
                <span class="font-medium whitespace-nowrap hidden sm:block">Talk to Us, we are here to help you!</span>
            </div>
        </div>
    </a>
</body>
</html>
