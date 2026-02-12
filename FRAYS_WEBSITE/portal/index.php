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
    <section class="pt-24 pb-12 px-4">
        <div class="max-w-7xl mx-auto">
            
            <?php 
            $faInstances = $_SESSION['fa_instances'] ?? [];
            $currentInstance = !empty($faInstances) ? array_key_first($faInstances) : 'default';
            $instanceName = !empty($faInstances) ? ($faInstances[$currentInstance]['name'] ?? $currentInstance) : 'Default';
            ?>
            
            <!-- Welcome Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="font-display text-3xl font-bold text-black">
                        Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>! 👋
                    </h1>
                    <p class="text-gray-500 mt-1">
                        <i class="ri-building-line"></i>
                        <?= htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $instanceName))) ?>
                    </p>
                </div>
                <a href="/portal?action=logout" class="inline-flex items-center gap-2 text-gray-400 hover:text-frays-red text-sm transition-colors">
                    <i class="ri-logout-box-line"></i>
                    Sign Out
                </a>
            </div>
            
            <!-- Main Content Grid -->
            <div class="grid lg:grid-cols-3 gap-6">
                
                <!-- Left Column: Statistics -->
                <div class="lg:col-span-1 space-y-6">
                    
                    <!-- Document Statistics -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h3 class="font-semibold text-black mb-4 flex items-center gap-2">
                            <i class="ri-file-chart-line text-frays-red"></i>
                            Document Statistics
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-frays-parchment rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold text-frays-red">127</div>
                                <div class="text-xs text-gray-500">Lifetime</div>
                            </div>
                            <div class="bg-frays-yellow/20 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold text-green-700">23</div>
                                <div class="text-xs text-gray-500">MTD</div>
                            </div>
                            <div class="bg-green-50 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold text-green-600">89</div>
                                <div class="text-xs text-gray-500">YTD</div>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold text-blue-600">5</div>
                                <div class="text-xs text-gray-500">Pending</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- FA Statistics -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h3 class="font-semibold text-black mb-4 flex items-center gap-2">
                            <i class="ri-calculator-line text-frays-red"></i>
                            <?= htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $instanceName))) ?>
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-600">Suppliers</span>
                                <span class="font-medium">47</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-600">Cashbooks</span>
                                <span class="font-medium">3</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-600">Dimensions</span>
                                <span class="font-medium">8</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-600">Bank Accounts</span>
                                <span class="font-medium">2</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User History -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h3 class="font-semibold text-black mb-4 flex items-center gap-2">
                            <i class="ri-history-line text-frays-red"></i>
                            Recent Activity
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-start gap-2">
                                <i class="ri-login-circle-line text-green-600 mt-0.5"></i>
                                <div>
                                    <div class="text-gray-800">Login successful</div>
                                    <div class="text-xs text-gray-400">Today, 9:15 AM</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <i class="ri-upload-cloud-line text-blue-600 mt-0.5"></i>
                                <div>
                                    <div class="text-gray-800">Uploaded 3 invoices</div>
                                    <div class="text-xs text-gray-400">Yesterday, 2:30 PM</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <i class="ri-file-chart-line text-frays-red mt-0.5"></i>
                                <div>
                                    <div class="text-gray-800">Processed 12 documents</div>
                                    <div class="text-xs text-gray-400">Feb 10, 2026</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Right Column: Actions -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Update My Books -->
                    <a href="/redirect.php?instance=<?= urlencode($currentInstance) ?>" 
                       class="block bg-gradient-to-r from-green-700 to-green-500 rounded-xl p-5 text-white hover:shadow-lg transition-all group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                                    <i class="ri-calculator-line text-2xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold">Update My Books</h2>
                                    <p class="text-white/80 text-sm">
                                        <i class="ri-building-line"></i>
                                        <?= htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $instanceName))) ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-white/80 text-sm">Access Accounting</span>
                                <i class="ri-arrow-right-line text-2xl text-white/60 group-hover:translate-x-1 transition-all"></i>
                            </div>
                        </div>
                    </a>
                    
                    <!-- Upload Documents -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50 px-5 py-4 border-b border-gray-100">
                            <h3 class="font-semibold text-black flex items-center gap-2">
                                <i class="ri-upload-cloud-line text-frays-red"></i>
                                Upload Documents
                            </h3>
                        </div>
                        
                        <form id="upload-form" enctype="multipart/form-data" class="p-5">
                            <input type="hidden" name="fa_instance" value="<?= htmlspecialchars($currentInstance) ?>">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($_SESSION['user_id'] ?? '') ?>">
                            <input type="hidden" name="user_email" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
                            <input type="hidden" name="auto_ocr" value="1">
                            <input type="hidden" name="doc_type" value="auto">
                            
                            <!-- Dropzone -->
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-frays-red cursor-pointer mb-4 transition-all" id="dropzone">
                                <i class="ri-cloud-upload-line text-4xl text-gray-300 mb-3"></i>
                                <p class="text-gray-600 font-medium mb-2">Drag files here or click to browse</p>
                                <p class="text-sm text-gray-400 mb-4">Upload invoices, receipts, waybills & statements</p>
                                
                                <!-- Guidelines -->
                                <div class="bg-frays-parchment rounded-lg p-4 text-left max-w-md mx-auto">
                                    <p class="text-xs font-semibold text-gray-600 mb-2">📋 Guidelines:</p>
                                    <ul class="text-xs text-gray-500 space-y-1">
                                        <li>• <strong>Max 20 files</strong> per upload</li>
                                        <li>• <strong>Types:</strong> PDF, JPG, PNG</li>
                                        <li>• <strong>Max size:</strong> 10MB per file</li>
                                        <li>• <strong>AI auto-detects</strong> document type</li>
                                    </ul>
                                </div>
                                
                                <input type="file" name="documents[]" id="fileInput" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                            </div>
                            
                            <!-- Progress -->
                            <div id="upload-progress" class="hidden mb-4">
                                <div class="flex justify-between text-sm mb-1">
                                    <span id="progress-text">Processing...</span>
                                    <span id="progress-percent">0%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div id="progress-bar" class="bg-frays-red h-2 rounded-full" style="width: 0%"></div>
                                </div>
                            </div>
                            
                            <!-- Results with Extracted Data Table -->
                            <div id="upload-results" class="hidden mb-4">
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                    <div class="flex items-center gap-2">
                                        <i class="ri-checkbox-circle-line text-green-600 text-xl"></i>
                                        <div>
                                            <div class="font-medium text-green-800">Upload Successful!</div>
                                            <div class="text-sm text-green-600">Data extracted and ready for admin review</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <h4 class="font-medium text-black mb-2">Extracted Data Preview</h4>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="text-left px-3 py-2 font-medium text-gray-600">File</th>
                                                <th class="text-left px-3 py-2 font-medium text-gray-600">Type</th>
                                                <th class="text-left px-3 py-2 font-medium text-gray-600">Vendor</th>
                                                <th class="text-left px-3 py-2 font-medium text-gray-600">Date</th>
                                                <th class="text-left px-3 py-2 font-medium text-gray-600">Amount</th>
                                                <th class="text-left px-3 py-2 font-medium text-gray-600">Confidence</th>
                                            </tr>
                                        </thead>
                                        <tbody id="results-table-body">
                                            <!-- Filled by JS -->
                                        </tbody>
                                    </table>
                                </div>
                                <p class="text-xs text-gray-400 mt-3">
                                    <i class="ri-lock-line"></i>
                                    Full CSV export available to admin only
                                </p>
                            </div>
                            
                            <!-- Submit -->
                            <button type="submit" id="submit-btn" class="w-full bg-[#990000] text-white py-3 rounded-lg font-medium hover:opacity-90 transition-all">
                                <i class="ri-upload-line"></i> Upload & Process
                            </button>
                        </form>
                    </div>
                    
                </div>
                
            </div>
        </div>
    </section>
            
            <script>
            const API_URL = '/api/document-ai.php';
            
            // File selection handling
            document.getElementById('dropzone').addEventListener('click', function() {
                document.getElementById('fileInput').click();
            });
            
            document.getElementById('fileInput').addEventListener('change', function(e) {
                const files = e.target.files;
                if (files.length > 0) {
                    document.getElementById('submit-btn').innerHTML = '<i class="ri-upload-line"></i> Upload ' + files.length + ' File' + (files.length > 1 ? 's' : '');
                }
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
                
                // Build table rows
                let tableRows = '';
                results.forEach(r => {
                    if (r.success && r.data) {
                        const d = r.data;
                        const confidence = d.confidence ? Math.round(d.confidence * 100) : '-';
                        const confClass = d.confidence && d.confidence < 0.5 ? 'text-yellow-600' : 'text-green-600';
                        tableRows += `
                            <tr class="border-b border-gray-100">
                                <td class="px-3 py-2 text-gray-800 truncate max-w-[150px]">${r.filename}</td>
                                <td class="px-3 py-2 text-gray-600 capitalize">${d.type || 'General'}</td>
                                <td class="px-3 py-2 text-gray-600 truncate max-w-[120px]">${d.vendor || '-'}</td>
                                <td class="px-3 py-2 text-gray-600">${d.date || '-'}</td>
                                <td class="px-3 py-2 text-gray-800 font-medium">${d.total ? 'P' + parseFloat(d.total).toLocaleString() : '-'}</td>
                                <td class="px-3 py-2 ${confClass}">${confidence}%</td>
                            </tr>
                        `;
                    } else {
                        tableRows += `
                            <tr class="border-b border-gray-100 bg-red-50">
                                <td class="px-3 py-2 text-red-800 truncate max-w-[150px]">${r.filename}</td>
                                <td class="px-3 py-2 text-red-600" colspan="5">${r.error || 'Processing failed'}</td>
                            </tr>
                        `;
                    }
                });
                
                document.getElementById('results-table-body').innerHTML = tableRows;
                
                // Scroll to results
                resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                
                // Clear file input
                document.getElementById('fileInput').value = '';
                document.getElementById('submit-btn').innerHTML = '<i class="ri-upload-line"></i> Upload & Process';
            });
            
            </script>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Footer -->
    <footer class="bg-frays-parchment py-8 md:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-12 mb-8 md:mb-12">
                <div class="col-span-2 md:col-span-1">
                    <div class="mb-3 md:mb-4">
                        <img src="/assets/images/logo.png" alt="Bookkeeping Services Logo" class="h-14 md:h-20 w-auto">
                    </div>
                    <p class="text-gray-600 text-xs md:text-sm">Professional bookkeeping and accounting services for businesses across Botswana.</p>
                </div>
                
                <div>
                    <h4 class="font-semibold text-black mb-3 md:mb-4 text-sm md:text-base">Services</h4>
                    <ul class="space-y-1.5 md:space-y-2 text-gray-600 text-xs md:text-sm">
                        <li>Bookkeeping</li>
                        <li>Tax Compliance</li>
                        <li>Financial Reporting</li>
                        <li>Payroll Services</li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold text-black mb-3 md:mb-4 text-sm md:text-base">Quick Links</h4>
                    <ul class="space-y-1.5 md:space-y-2 text-gray-600 text-xs md:text-sm">
                        <li><a href="/">Home</a></li>
                        <li><a href="/portal">Client Portal</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-3 md:mb-4 text-sm md:text-base">Connect With Us</h4>
                    <div class="flex gap-2 md:gap-3">
                        <a href="#" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-frays-yellow flex items-center justify-center hover:opacity-80">
                            <i class="ri-facebook-fill text-frays-red text-sm md:text-base"></i>
                        </a>
                        <a href="#" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-frays-yellow flex items-center justify-center hover:opacity-80">
                            <i class="ri-linkedin-fill text-frays-red text-sm md:text-base"></i>
                        </a>
                        <a href="https://wa.me/2673966011" target="_blank" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-frays-yellow flex items-center justify-center hover:opacity-80">
                            <i class="ri-whatsapp-fill text-frays-red text-sm md:text-base"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-frays-yellow pt-6 md:pt-8 text-center text-gray-600 text-xs md:text-sm">
                © <?= date('Y') ?> Frays Cottage Bookkeeping Services. All rights reserved.
            </div>
        </div>
    </footer>
    
    <script>
    // =============================================
    // Integrated Document AI - Pure PHP Version
    // Runs on port 8080 with the main website
    // =============================================
    
    const API_URL = '/api/document-ai.php';
    
    // File selection handling
    document.getElementById('dropzone').addEventListener('click', function() {
        document.getElementById('fileInput').click();
    });
    
    document.getElementById('fileInput').addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            document.getElementById('submit-btn').innerHTML = '<i class="ri-upload-line"></i> Upload ' + e.target.files.length + ' File' + (e.target.files.length > 1 ? 's' : '');
        }
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
        document.getElementById('submit-btn').innerHTML = '<i class="ri-upload-line"></i> Upload & Process';
    });
    
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