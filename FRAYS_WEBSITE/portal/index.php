<?php
/**
 * CLIENT PORTAL - Unified Login & Dashboard
 * 
 * Login required for users with valid FA instances
 * After login: Choose UPLOAD Documents or UPDATE Books
 * Supports single login across ALL 30+ FA instances
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/unified-auth.php';
require_once __DIR__ . '/../includes/test-credentials.php';

$error = '';
$success = '';
$pageTitle = 'Client Portal - ' . APP_NAME;

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logActivity('user_logout', ['user_id' => $_SESSION['user_id'] ?? 0]);
    logoutUser();
    redirect('/portal');
}

// Handle login - UNIFIED AUTHENTICATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    error_log("LOGIN: Received POST request");
    error_log("LOGIN: Email = " . ($_POST['email'] ?? 'EMPTY'));
    
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        error_log("LOGIN: CSRF validation failed");
        $error = 'Invalid request';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        error_log("LOGIN: Checking test: " . $email);
        
        // First check if it's a test user
        $testUser = authenticateTestUser($email, $password);
        error_log("LOGIN: Test user result = " . ($testUser ? 'FOUND' : 'NOT FOUND'));
        
        if ($testUser) {
            error_log("LOGIN: Setting test session");
            $_SESSION['user_id'] = $testUser['id'];
            $_SESSION['user_name'] = $testUser['name'];
            $_SESSION['user_email'] = $testUser['email'];
            $_SESSION['fa_instances'] = $testUser['fa_instances'];
            $_SESSION['is_unified'] = false;
            $_SESSION['is_test'] = true;
            $success = 'Test login successful!';
        } else {
            error_log("LOGIN: Trying unified auth");
            // Try real FA authentication
            $user = authenticateUserUnified($email, $password);
            
            if ($user) {
                $userFaInstances = getUserFAInstancesUnified($user['id']);
                
                if (empty($userFaInstances)) {
                    $error = 'Access denied. Your account does not have access to any FrontAccounting instances.';
                    logActivity('login_denied_no_instances', ['email' => $email]);
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['fa_instances'] = $userFaInstances;
                    $_SESSION['is_unified'] = true;
                    
                    logActivity('user_login_unified', ['email' => $email, 'instances' => count($userFaInstances)]);
                    $success = 'Login successful!';
                }
            } else {
                $error = 'Invalid username or password';
                logActivity('login_failed', ['email' => $email]);
            }
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
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="Client portal for document upload and accounting access">
    
    <link rel="icon" href="/assets/images/favicon.png" type="image/png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lato:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'frays-red': '#990000',
                        'frays-yellow': '#CCCC66',
                        'frays-parchment': '#F1F1D4'
                    },
                    fontFamily: {
                        'display': ['Playfair Display', 'serif'],
                        'sans': ['Lato', 'sans-serif']
                    }
                }
            }
        }
        
        // Fill test credentials
        function fillTestLogin(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            
            // Visual feedback
            const buttons = document.querySelectorAll('button[onclick^="fillTestLogin"]');
            buttons.forEach(btn => btn.style.backgroundColor = '');
            event.target.closest('button').style.backgroundColor = '#e0e7ff';
        }
    </script>
    
    <style>
        body { color: #000000; background-color: #FFFFFF; font-family: 'Lato', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Playfair Display', serif; color: #000000; }
    </style>
</head>
<body class="min-h-screen bg-white">
    
    <!-- Top Contact Bar - Single Line (Fixed) -->
    <div class="fixed top-0 left-0 right-0 z-50 bg-frays-red text-white text-xs md:text-sm py-2 md:py-2.5 shadow-md">
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
    
    <!-- Back to Home -->
    <div class="fixed top-6 left-6 z-50">
        <a href="/" class="inline-flex items-center gap-1 bg-white/90 backdrop-blur-sm px-5 py-2.5 rounded-lg shadow-lg hover:shadow-xl transition-all text-gray-700 hover:text-frays-red border border-gray-200">
            <i class="ri-arrow-left-line text-lg"></i>
            <span class="font-medium">Back to Home</span>
        </a>
    </div>

    <?php if (!isLoggedIn()): ?>
    <!-- LOGIN PAGE - Side by Side Layout -->
    <section class="relative bg-white overflow-hidden min-h-screen flex items-center pt-0 md:pt-0 pb-0">
        <div class="absolute inset-0">
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-frays-parchment/30 to-white"></div>
        </div>
        
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <!-- Logo -->
            <div class="text-center mb-0">
                <img src="/assets/images/logo.png" alt="Bookkeeping Services Logo" class="h-24 w-auto mx-auto">
                <div class="flex flex-col text-center mt-4">
                    <span class="font-display text-3xl font-bold text-black leading-none">Bookkeeping</span>
                    <span class="font-display text-3xl font-bold text-frays-red leading-none">Services</span>
                </div>
            </div>
            
            <!-- Login Form & Photo - Side by Side -->
            <div class="grid md:grid-cols-4 gap-6 items-stretch">
                <!-- Inspirational Write-up -->
                <div class="text-center md:text-left hidden md:flex md:flex-col md:justify-center md:col-span-1">
                    <h2 class="font-display text-3xl lg:text-4xl font-bold mb-6 leading-tight">
                        <span class="text-black">Your Success</span><br>
                        <span class="text-frays-red">Is Our</span><br>
                        <span class="text-black">Passion</span>
                    </h2>
                    <p class="text-gray-600 mb-6 text-lg leading-relaxed">
                        We help your business thrive with professional bookkeeping and accounting solutions, <a href="https://payroll.co.bw/" target="_blank" class="text-frays-red hover:underline font-semibold">payroll services</a>, and tax consultancy tailored to your needs.
                    </p>
                    <div class="flex items-center gap-3 justify-center md:justify-start">
                        <div class="w-12 h-1 bg-frays-yellow rounded-full"></div>
                        <span class="text-sm text-gray-500 font-medium">Trusted by 100+ Clients</span>
                    </div>
                </div>
                
                <!-- Login Form -->
                <div class="bg-white rounded-2xl shadow-xl p-6 lg:p-8 border border-frays-yellow/30 flex flex-col justify-center md:col-span-2">
                    <div class="text-center mb-4">
                        <h2 class="font-display text-xl lg:text-2xl font-bold text-black mb-1">Client Portal</h2>
                        <p class="text-gray-600 text-sm">Enter your credentials to access</p>
                    </div>
                
                <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-1">
                    <i class="ri-error-warning-line"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-1">
                    <i class="ri-check-line"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <div class="relative">
                                <i class="ri-user-line absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="text" id="email" name="email" required
                                    class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-frays-red focus:border-transparent text-sm"
                                    placeholder="Enter your username">
                            </div>
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <i class="ri-lock-line absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="password" id="password" name="password" required
                                        class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-frays-red focus:border-transparent text-sm"
                                        placeholder="••••••••">
                                </div>
                            </div>
                            
                            <button type="submit" class="w-full bg-frays-yellow text-black py-2.5 rounded-lg font-semibold hover:opacity-90 transition-all flex items-center justify-center gap-2 mt-4">
                                <i class="ri-login-box-line text-frays-red"></i>
                                Sign In to Portal
                            </button>
                        </div>
                    </form>
                    
                    <!-- Test Credentials -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <p class="text-xs text-center text-gray-500 mb-3">
                            <i class="ri-flash-line"></i>
                            TEST MODE - Click to fill test credentials
                        </p>
                        <div class="grid grid-cols-1 gap-2">
                            <button type="button" onclick="fillTestLogin('test@frayscottage.co.bw', 'test123')" 
                                    class="text-left px-3 py-2 bg-gray-50 hover:bg-gray-100 rounded-lg text-sm transition-colors">
                                <span class="font-medium text-gray-700">Test User</span>
                                <span class="text-gray-400 ml-2">test@frayscottage.co.bw / test123</span>
                            </button>
                            <button type="button" onclick="fillTestLogin('demo@frayscottage.co.bw', 'demo456')" 
                                    class="text-left px-3 py-2 bg-gray-50 hover:bg-gray-100 rounded-lg text-sm transition-colors">
                                <span class="font-medium text-gray-700">Demo User (2 instances)</span>
                                <span class="text-gray-400 ml-2">demo@frayscottage.co.bw / demo456</span>
                            </button>
                            <button type="button" onclick="fillTestLogin('julian@frayscottage.co.bw', 'julian123')" 
                                    class="text-left px-3 py-2 bg-gray-50 hover:bg-gray-100 rounded-lg text-sm transition-colors">
                                <span class="font-medium text-gray-700">Julian</span>
                                <span class="text-gray-400 ml-2">julian@frayscottage.co.bw / julian123</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200 text-center">
                        <p class="text-xs text-gray-500 flex items-center justify-center gap-1">
                            <i class="ri-shield-check-line"></i>
                            Only clients with valid FrontAccounting instances
                        </p>
                    </div>
                </div>
                
                <!-- Corporate Photo - Right Side (Desktop) -->
                <div class="hidden md:flex md:items-center md:col-span-1">
                    <img src="/assets/images/julian-corporate.jpg" alt="Julian Corporate Shot" class="w-full h-64 object-contain rounded-xl shadow-lg border border-frays-yellow/20">
                </div>
            </div>
            
            <!-- Mobile: Photo & Inspirational Text -->
            <div class="md:hidden mt-6">
                <div class="bg-frays-parchment rounded-xl p-6 text-center">
                    <h2 class="font-display text-2xl font-bold mb-4">
                        <span class="text-black">Your Success</span><br>
                        <span class="text-frays-red">Is Our</span><br>
                        <span class="text-black">Passion</span>
                    </h2>
                    <p class="text-gray-600 mb-4 text-sm">
                        We help your business thrive with professional bookkeeping and accounting solutions, <a href="https://payroll.co.bw/" target="_blank" class="text-frays-red hover:underline font-semibold">payroll services</a>, and tax consultancy tailored to your needs.
                    </p>
                    <img src="/assets/images/julian-corporate.jpg" alt="Julian Corporate Shot" class="w-full h-40 object-cover rounded-lg shadow-lg">
                </div>
            </div>
        </div>
    </section>
    
    <?php else: ?>
    <!-- DASHBOARD - After Login -->
    <section class="pt-0 md:pt-0 pb-0 px-4 sm:px-6 lg:px-8 bg-white">
        <div class="max-w-6xl mx-auto">
            
            <!-- Welcome Header -->
            <div class="text-center mb-8">
                <h1 class="font-display text-4xl font-bold text-black mb-2">
                    Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>! 👋
                </h1>
                <p class="text-gray-600 text-lg mb-4">What would you like to do today?</p>
                <a href="/portal?action=logout" class="inline-flex items-center gap-2 text-gray-500 hover:text-frays-red transition-colors text-sm">
                    <i class="ri-logout-box-line"></i>
                    Sign Out
                </a>
            </div>
            
            <!-- FA Instance Banner -->
            <?php 
            $faInstances = $_SESSION['fa_instances'] ?? [];
            $firstInstance = !empty($faInstances) ? array_key_first($faInstances) : 'default';
            $instanceName = !empty($faInstances) ? ($faInstances[array_key_first($faInstances)]['name'] ?? array_key_first($faInstances)) : 'Default';
            ?>
            <?php if (!empty($faInstances)): ?>
            <div class="bg-frays-parchment rounded-xl p-4 mb-8 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="ri-building-4-line text-frays-red text-2xl"></i>
                    <div>
                        <p class="text-sm text-gray-500">Your FrontAccounting Instance</p>
                        <p class="font-semibold text-black">
                            <?= htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $instanceName))) ?>
                        </p>
                    </div>
                </div>
                <a href="/redirect.php?instance=<?= urlencode($firstInstance) ?>" 
                   class="bg-frays-red text-white px-4 py-2 rounded-lg hover:opacity-90 transition-colors flex items-center gap-2">
                    <i class="ri-arrow-right-line"></i>
                    Open Accounting
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Two Main Options -->
            <div class="grid md:grid-cols-2 gap-8 mb-0">
                
                <!-- Option 1: Go to FA Instance -->
                <a href="/redirect.php?instance=<?= urlencode($firstInstance) ?>" 
                   class="group bg-gradient-to-br from-frays-red to-red-800 rounded-2xl shadow-xl p-8 text-white hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-24 h-24 mb-6 rounded-full bg-white/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="ri-calculator-line text-5xl"></i>
                        </div>
                        <h2 class="font-display text-3xl font-bold mb-4">Update My Books</h2>
                        <p class="text-white/80 mb-6 text-lg">
                            Access your FrontAccounting system to view reports, process transactions, and manage your business finances.
                        </p>
                        <span class="inline-flex items-center gap-2 bg-white text-frays-red px-6 py-3 rounded-lg font-semibold">
                            <i class="ri-login-box-line"></i>
                            Go to Accounting
                        </span>
                    </div>
                </a>
                
                <!-- Option 2: Upload Documents -->
                <a href="#upload-section" 
                   class="group bg-gradient-to-br from-frays-yellow to-yellow-600 rounded-2xl shadow-xl p-8 text-black hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-24 h-24 mb-6 rounded-full bg-white/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="ri-upload-cloud-line text-5xl text-frays-red"></i>
                        </div>
                        <h2 class="font-display text-3xl font-bold mb-4">Upload Documents</h2>
                        <p class="text-black/70 mb-6 text-lg">
                            Submit invoices, receipts, and business documents for AI-powered processing and automatic extraction.
                        </p>
                        <span class="inline-flex items-center gap-2 bg-frays-red text-white px-6 py-3 rounded-lg font-semibold">
                            <i class="ri-upload-line"></i>
                            Upload Now
                        </span>
                    </div>
                </a>
                
            </div>
            
            <!-- Multiple FA Instances -->
            <?php 
            $allInstances = !empty($faInstances) ? array_keys($faInstances) : [];
            if (count($allInstances) > 1): ?>
            <div class="mt-8 bg-frays-parchment rounded-xl shadow-lg p-6">
                <h3 class="font-semibold text-black mb-4 flex items-center gap-1">
                    <i class="ri-links-line text-frays-red"></i>
                    Your Other FrontAccounting Instances
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <?php foreach (array_slice($allInstances, 1) as $instanceKey): 
                    $instanceName = $faInstances[$instanceKey]['name'] ?? $instanceKey;
                    ?>
                    <a href="/redirect.php?instance=<?= urlencode($instanceKey) ?>" 
                       class="flex items-center gap-2 px-4 py-3 bg-white rounded-lg hover:bg-frays-yellow transition-colors">
                        <i class="ri-building-line text-frays-red"></i>
                        <span class="text-sm font-medium"><?= htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $instanceName))) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Upload Section with Integrated Document AI -->
            <div id="upload-section" class="bg-frays-parchment rounded-2xl shadow-xl p-4">
                <h2 class="font-display text-2xl font-bold text-black mb-2 flex items-center gap-2">
                    <i class="ri-scan-line text-frays-red"></i>
                    Smart Document Upload
                </h2>
                <p class="text-gray-600 mb-4 text-sm">
                    <i class="ri-magic-line text-frays-yellow"></i>
                    AI-powered document processing with OCR • Runs on port 8080
                </p>
                
                <!-- Document AI Status -->
                <div id="docai-status" class="mb-4 p-3 rounded-lg bg-white border border-gray-200 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        <span class="text-green-700 font-medium">Document AI Connected</span>
                    </div>
                    <div class="mt-2 text-xs text-gray-500" id="docai-features">
                        <span id="ocr-status">🔍 OCR: Checking...</span> •
                        <span id="pdf-status">📄 PDF: Checking...</span> •
                        <span id="fa-status">🏢 FA: Integrated</span>
                    </div>
                </div>
                
                <form id="upload-form" enctype="multipart/form-data">
                    <!-- Hidden: User's FA Instance (auto-filled on login) -->
                    <input type="hidden" name="fa_instance" id="fa-instance" value="<?= htmlspecialchars($firstInstance ?? '') ?>">
                    <input type="hidden" name="user_id" id="user-id" value="<?= htmlspecialchars($_SESSION['user_id'] ?? '') ?>">
                    <input type="hidden" name="user_email" id="user-email" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
                    
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="ri-building-line text-frays-red"></i>
                                Client / FA Instance
                            </label>
                            <?php if (!empty($_SESSION['fa_instances'])): ?>
                                <div class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 text-gray-600">
                                    <?php foreach ($_SESSION['fa_instances'] as $instance): ?>
                                        <div class="flex items-center gap-2">
                                            <i class="ri-building-4-line text-frays-red"></i>
                                            <span class="font-medium"><?= htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $instance))) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="ri-information-line"></i>
                                    Documents will be linked to this FA instance
                                </p>
                            <?php else: ?>
                                <select name="client_code" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-frays-red">
                                    <option value="DEFAULT">DEFAULT</option>
                                    <?php foreach (['KLES', 'MGB', 'FRACOT', 'NORTHERN', 'MADAMZ', 'CLEANING', 'QUANTO', 'SPACE', 'UNLIMITED', 'ERNLET'] as $client): ?>
                                    <option value="<?= $client ?>"><?= $client ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                            <select name="doc_type" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-frays-red">
                                <option value="invoice">Invoice</option>
                                <option value="receipt">Receipt</option>
                                <option value="waybill">Waybill</option>
                                <option value="statement">Statement</option>
                                <option value="general">General</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Processing Options -->
                    <div class="bg-white rounded-lg p-4 mb-6">
                        <p class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-1">
                            <i class="ri-settings-3-line text-frays-red"></i>
                            Processing Options
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="auto_ocr" value="1" checked class="rounded text-frays-red">
                                <span class="text-sm text-gray-700">
                                    <i class="ri-scan-line text-frays-red"></i>
                                    Extract Text (OCR)
                                </span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="auto_export" value="1" class="rounded text-frays-red">
                                <span class="text-sm text-gray-700">
                                    <i class="ri-file-excel-line text-green-600"></i>
                                    Export to CSV
                                </span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="push_fa" value="1" class="rounded text-frays-red">
                                <span class="text-sm text-gray-700">
                                    <i class="ri-building-line text-blue-600"></i>
                                    Push to FA
                                </span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-frays-red transition-colors cursor-pointer mb-6" id="dropzone">
                        <i class="ri-cloud-upload-line text-5xl text-gray-300 mb-4"></i>
                        <p class="text-gray-600 mb-2">Drag & drop files here or click to browse</p>
                        <p class="text-sm text-gray-400">PDF, JPG, PNG up to 10MB each</p>
                        <input type="file" name="documents[]" id="fileInput" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                        <div id="file-list" class="mt-4 text-left"></div>
                    </div>
                    
                    <!-- Progress -->
                    <div id="upload-progress" class="hidden mb-4">
                        <div class="flex justify-between text-sm mb-1">
                            <span id="progress-text">Processing...</span>
                            <span id="progress-percent">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div id="progress-bar" class="bg-frays-red h-2.5 rounded-full transition-all" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <!-- Results -->
                    <div id="upload-results" class="hidden mb-4"></div>
                    
                    <!-- Document Preview -->
                    <div id="document-preview" class="hidden mb-4"></div>
                    
                    <button type="submit" id="submit-btn" class="w-full bg-frays-red text-white py-4 rounded-lg font-semibold hover:opacity-90 transition-all flex items-center justify-center gap-2">
                        <i class="ri-upload-line"></i>
                        Process & Upload Documents
                    </button>
                </form>
            </div>
            
            <script>
            // Document AI Integration
            const API_URL = '/api/document-ai.php';
            
            // Check Document AI status on load
            async function checkDocAIStatus() {
                try {
                    const response = await fetch(API_URL + '?health');
                    const data = await response.json();
                    const indicator = document.getElementById('docai-indicator');
                    const message = document.getElementById('docai-message');
                    
                    if (data.status === 'running') {
                        indicator.className = 'inline-block w-2 h-2 rounded-full bg-green-500';
                        message.textContent = 'Connected - Ready for processing';
                        message.className = 'text-green-600 ml-2';
                    } else {
                        indicator.className = 'inline-block w-2 h-2 rounded-full bg-yellow-500';
                        message.textContent = 'Checking...';
                    }
                } catch (error) {
                    document.getElementById('docai-indicator').className = 'inline-block w-2 h-2 rounded-full bg-red-500';
                    document.getElementById('docai-message').textContent = 'Document AI not connected';
                    document.getElementById('docai-message').className = 'text-red-600 ml-2';
                }
            }
            
            // File selection handling
            document.getElementById('dropzone').addEventListener('click', function() {
                document.getElementById('fileInput').click();
            });
            
            document.getElementById('fileInput').addEventListener('change', function(e) {
                const list = document.getElementById('file-list');
                list.innerHTML = '';
                
                Array.from(e.target.files).forEach((file, i) => {
                    list.innerHTML += `
                        <div class="flex items-center gap-2 p-2 bg-white rounded mb-2">
                            <i class="ri-file-line text-frays-red"></i>
                            <span class="text-sm flex-1 truncate">${file.name}</span>
                            <span class="text-xs text-gray-400">${(file.size / 1024).toFixed(1)} KB</span>
                        </div>
                    `;
                });
            });
            
            // Drag and drop
            const dropzone = document.getElementById('dropzone');
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, () => {
                    dropzone.classList.add('border-frays-red', 'bg-frays-parchment/30');
                });
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, () => {
                    dropzone.classList.remove('border-frays-red', 'bg-frays-parchment/30');
                });
            });
            
            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                document.getElementById('fileInput').files = files;
                
                // Trigger change event
                const event = new Event('change');
                document.getElementById('fileInput').dispatchEvent(event);
            });
            
            // Form submission
            document.getElementById('upload-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const files = document.getElementById('fileInput').files;
                
                if (files.length === 0) {
                    alert('Please select at least one file');
                    return;
                }
                
                // Show progress
                document.getElementById('upload-progress').classList.remove('hidden');
                document.getElementById('submit-btn').disabled = true;
                document.getElementById('progress-text').textContent = 'Uploading and processing...';
                
                let completed = 0;
                let results = [];
                
                // Process each file
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const fileFormData = new FormData();
                    fileFormData.append('document', file);
                    fileFormData.append('client_code', formData.get('client_code') || document.getElementById('fa-instance')?.value || 'DEFAULT');
                    fileFormData.append('fa_instance', document.getElementById('fa-instance')?.value || '');
                    fileFormData.append('user_email', document.getElementById('user-email')?.value || '');
                    fileFormData.append('doc_type', formData.get('doc_type'));
                    fileFormData.append('auto_ocr', formData.get('auto_ocr') ? '1' : '0');
                    fileFormData.append('auto_export', formData.get('auto_export') ? '1' : '0');
                    fileFormData.append('push_fa', formData.get('push_fa') ? '1' : '0');
                    
                    try {
                        const response = await fetch(API_URL + '/process', {
                            method: 'POST',
                            body: fileFormData
                        });
                        
                        const data = await response.json();
                        results.push({
                            filename: file.name,
                            success: data.success,
                            data: data.data || data.error
                        });
                    } catch (error) {
                        results.push({
                            filename: file.name,
                            success: false,
                            error: error.message
                        });
                    }
                    
                    completed++;
                    const percent = Math.round((completed / files.length) * 100);
                    document.getElementById('progress-bar').style.width = percent + '%';
                    document.getElementById('progress-percent').textContent = percent + '%';
                }
                
                // Show results
                document.getElementById('upload-progress').classList.add('hidden');
                document.getElementById('submit-btn').disabled = false;
                
                const resultsDiv = document.getElementById('upload-results');
                resultsDiv.classList.remove('hidden');
                
                let html = '<div class="space-y-2">';
                results.forEach(r => {
                    if (r.success) {
                        html += `
                            <div class="flex items-center gap-2 p-3 bg-green-50 rounded-lg">
                                <i class="ri-check-line text-green-600"></i>
                                <span class="text-sm text-green-800 flex-1">${r.filename}</span>
                                <span class="text-xs text-green-600">Processed</span>
                            </div>
                        `;
                    } else {
                        html += `
                            <div class="flex items-center gap-2 p-3 bg-red-50 rounded-lg">
                                <i class="ri-error-warning-line text-red-600"></i>
                                <span class="text-sm text-red-800 flex-1">${r.filename}</span>
                                <span class="text-xs text-red-600">${r.error || 'Failed'}</span>
                            </div>
                        `;
                    }
                });
                html += '</div>';
                resultsDiv.innerHTML = html;
                
                // Clear file input
                document.getElementById('fileInput').value = '';
                document.getElementById('file-list').innerHTML = '';
            });
            
            // Initialize
            checkDocAIStatus();
            </script>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Footer -->
    <footer class="bg-frays-parchment py-2">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-gray-600 text-sm mb-2">© <?= date('Y') ?> Frays Cottage Bookkeeping Services. All rights reserved.</p>
            <a href="https://www.frayscottage.co.bw/" target="_blank" class="text-frays-red text-xs hover:text-frays-yellow transition-colors">
                Designed by Frays Cottage
            </a>
        </div>
    </footer>
    
    <script>
    // =============================================
    // Integrated Document AI - Pure PHP Version
    // Runs on port 8080 with the main website
    // =============================================
    
    const API_URL = '/api/document-ai.php';
    
    // Check Document AI status on load
    async function checkDocAIStatus() {
        try {
            const response = await fetch(API_URL + '?health');
            const data = await response.json();
            
            const ocrStatus = document.getElementById('ocr-status');
            const pdfStatus = document.getElementById('pdf-status');
            
            if (data.features && data.features.ocr) {
                ocrStatus.textContent = '🔍 OCR: Ready';
                ocrStatus.className = 'text-green-600';
            } else {
                ocrStatus.textContent = '🔍 OCR: Limited';
                ocrStatus.className = 'text-yellow-600';
            }
            
            if (data.features && data.features.pdf_text) {
                pdfStatus.textContent = '📄 PDF: Ready';
                pdfStatus.className = 'text-green-600';
            } else {
                pdfStatus.textContent = '📄 PDF: Basic';
                pdfStatus.className = 'text-yellow-600';
            }
            
        } catch (error) {
            console.log('Document AI status check failed:', error);
        }
    }
    
    // File selection handling with preview
    document.getElementById('dropzone').addEventListener('click', function() {
        document.getElementById('fileInput').click();
    });
    
    document.getElementById('fileInput').addEventListener('change', function(e) {
        const list = document.getElementById('file-list');
        list.innerHTML = '';
        
        Array.from(e.target.files).forEach((file, i) => {
            let preview = '';
            if (file.type.startsWith('image/')) {
                preview = `<img src="${URL.createObjectURL(file)}" class="w-12 h-12 object-cover rounded-lg mr-3">`;
            } else {
                preview = `<div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mr-3"><i class="ri-file-pdf-line text-xl text-red-500"></i></div>`;
            }
            
            list.innerHTML += `
                <div class="flex items-center p-2 bg-white rounded-lg mb-2">
                    ${preview}
                    <div class="flex-1 truncate">
                        <div class="text-sm font-medium truncate">${file.name}</div>
                        <div class="text-xs text-gray-400">${(file.size / 1024).toFixed(1)} KB</div>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-gray-400 hover:text-red-500">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            `;
        });
    });
    
    // Drag and drop
    const dropzone = document.getElementById('dropzone');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            dropzone.classList.add('border-frays-red', 'bg-frays-parchment/30');
        });
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            dropzone.classList.remove('border-frays-red', 'bg-frays-parchment/30');
        });
    });
    
    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        document.getElementById('fileInput').files = files;
        const event = new Event('change');
        document.getElementById('fileInput').dispatchEvent(event);
    });
    
    // Form submission
    document.getElementById('upload-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const files = document.getElementById('fileInput').files;
        
        if (files.length === 0) {
            alert('Please select at least one file');
            return;
        }
        
        document.getElementById('upload-progress').classList.remove('hidden');
        document.getElementById('submit-btn').disabled = true;
        document.getElementById('progress-text').textContent = 'Processing documents...';
        
        let completed = 0;
        let results = [];
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileFormData = new FormData();
            fileFormData.append('document', file);
            fileFormData.append('auto_ocr', formData.get('auto_ocr') ? '1' : '0');
            fileFormData.append('auto_export', formData.get('auto_export') ? '1' : '0');
            fileFormData.append('push_fa', formData.get('push_fa') ? '1' : '0');
            
            document.getElementById('progress-text').textContent = `Processing ${i + 1}/${files.length}: ${file.name}`;
            
            try {
                const response = await fetch(API_URL + '/process', {
                    method: 'POST',
                    body: fileFormData
                });
                
                const data = await response.json();
                results.push({
                    filename: file.name,
                    success: data.success,
                    data: data.data || data.error
                });
            } catch (error) {
                results.push({
                    filename: file.name,
                    success: false,
                    error: error.message
                });
            }
            
            completed++;
            document.getElementById('progress-bar').style.width = Math.round(10 + (completed / files.length) * 80) + '%';
        }
        
        document.getElementById('upload-progress').classList.add('hidden');
        document.getElementById('submit-btn').disabled = false;
        document.getElementById('progress-bar').style.width = '100%';
        
        const resultsDiv = document.getElementById('upload-results');
        resultsDiv.classList.remove('hidden');
        
        let html = '<div class="space-y-3">';
        results.forEach(r => {
            const faInstance = document.getElementById('fa-instance')?.value || '';
            if (r.success && r.data) {
                const d = r.data;
                html += `
                    <div class="bg-white rounded-lg p-4 border border-green-200">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="ri-check-circle-line text-green-500"></i>
                            <span class="font-medium">${r.filename}</span>
                            <span class="badge-green ml-auto">${d.type || 'document'}</span>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                            ${d.invoice_number ? `<div><span class="text-gray-500">Inv:</span> ${d.invoice_number}</div>` : ''}
                            ${d.vendor ? `<div><span class="text-gray-500">Vendor:</span> ${d.vendor.substring(0, 20)}</div>` : ''}
                            ${d.date ? `<div><span class="text-gray-500">Date:</span> ${d.date}</div>` : ''}
                            ${d.total > 0 ? `<div><span class="text-gray-500">Total:</span> P${parseFloat(d.total).toLocaleString()}</div>` : ''}
                        </div>
                        ${faInstance ? `<div class="mt-2 text-xs text-blue-600"><i class="ri-building-4-line"></i> Linked to: ${faInstance}</div>` : ''}
                        ${d.confidence < 0.5 ? `<div class="mt-2 text-xs text-yellow-600"><i class="ri-error-warning-line"></i> Low confidence - please verify data</div>` : ''}
                    </div>
                `;
            } else {
                html += `
                    <div class="flex items-center gap-2 p-3 bg-red-50 rounded-lg">
                        <i class="ri-error-warning-line text-red-600"></i>
                        <span class="text-sm text-red-800 flex-1">${r.filename}</span>
                        <span class="text-xs text-red-600">${r.error || 'Failed'}</span>
                    </div>
                `;
            }
        });
        html += '</div>';
        resultsDiv.innerHTML = html;
        
        document.getElementById('fileInput').value = '';
        document.getElementById('file-list').innerHTML = '';
    });
    
    // Initialize
    checkDocAIStatus();
    </script>
    
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