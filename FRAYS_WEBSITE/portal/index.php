<?php
/**
 * CLIENT PORTAL - Unified Login & Dashboard
 * 
 * Login required for users with valid FA instances
 * After login: Choose UPLOAD Documents or UPDATE Books
 */

require_once __DIR__ . '/../includes/config.php';

$error = '';
$success = '';
$pageTitle = 'Client Portal - ' . APP_NAME;

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logActivity('user_logout', ['user_id' => $_SESSION['user_id'] ?? 0]);
    logoutUser();
    redirect('/portal');
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $user = authenticateUser($email, $password);
        
        if ($user) {
            $userFaInstances = getUserFAInstances($user['id']);
            
            if (empty($userFaInstances)) {
                $error = 'Access denied. Your account does not have access to any FrontAccounting instances.';
                logActivity('login_denied_no_instances', ['email' => $email]);
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['fa_instances'] = $userFaInstances;
                
                logActivity('user_login', ['email' => $email, 'instances' => count($userFaInstances)]);
                $success = 'Login successful!';
            }
        } else {
            $error = 'Invalid username or password';
            logActivity('login_failed', ['email' => $email]);
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
            
            <div class="text-center mb-0">
                <h1 class="font-display text-4xl font-bold text-black mb-2">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></h1>
                <p class="text-gray-600">Choose an action below:</p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-8 mb-0">
                <!-- Option 1: Upload Documents -->
                <a href="#upload-section" class="group">
                    <div class="bg-white rounded-2xl shadow-xl p-4 text-center border-2 border-transparent hover:border-frays-red transition-all duration-300 h-full">
                        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-frays-yellow flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="ri-upload-cloud-line text-4xl text-frays-red"></i>
                        </div>
                        <h2 class="font-display text-2xl font-bold text-black mb-4">Upload Documents</h2>
                        <p class="text-gray-600 mb-6">
                            Submit invoices, receipts, and business documents for processing. 
                            We'll review and post them to your accounting system.
                        </p>
                        <span class="inline-flex items-center gap-1 text-frays-red font-semibold">
                            <i class="ri-arrow-right-line group-hover:translate-x-2 transition-transform"></i>
                            Upload Now
                        </span>
                    </div>
                </a>
                
                <!-- Option 2: Update Books -->
                <a href="/redirect.php?instance=<?= urlencode(reset($_SESSION['fa_instances'] ?? [])) ?>" class="group">
                    <div class="bg-white rounded-2xl shadow-xl p-4 text-center border-2 border-transparent hover:border-frays-red transition-all duration-300 h-full">
                        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-frays-red flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="ri-calculator-line text-4xl text-white"></i>
                        </div>
                        <h2 class="font-display text-2xl font-bold text-black mb-4">Update Books</h2>
                        <p class="text-gray-600 mb-6">
                            Access your FrontAccounting system to view reports, process transactions, 
                            and manage your business finances.
                        </p>
                        <span class="inline-flex items-center gap-1 text-frays-red font-semibold">
                            <i class="ri-arrow-right-line group-hover:translate-x-2 transition-transform"></i>
                            Open Accounting
                        </span>
                    </div>
                </a>
            </div>
            
            <!-- Multiple FA Instances -->
            <?php if (count($_SESSION['fa_instances'] ?? []) > 1): ?>
            <div class="bg-frays-parchment rounded-xl shadow-lg p-6 mb-0">
                <h3 class="font-semibold text-black mb-4 flex items-center gap-1">
                    <i class="ri-links-line text-frays-red"></i>
                    Your FrontAccounting Instances
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <?php foreach ($_SESSION['fa_instances'] as $instance): ?>
                    <a href="/redirect.php?instance=<?= urlencode($instance) ?>" 
                       class="flex items-center gap-1 px-4 py-3 bg-white rounded-lg hover:bg-frays-yellow transition-colors">
                        <i class="ri-building-line text-frays-red"></i>
                        <span class="text-sm font-medium"><?= htmlspecialchars(ucwords(str_replace(['-', '_'], ' ', $instance))) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Upload Section -->
            <div id="upload-section" class="bg-frays-parchment rounded-2xl shadow-xl p-4">
                <h2 class="font-display text-2xl font-bold text-black mb-6 flex items-center gap-1">
                    <i class="ri-upload-line text-frays-red"></i>
                    Upload Documents
                </h2>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="action" value="upload">
                    
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Client Code</label>
                            <select name="client_code" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-frays-red">
                                <option value="DEFAULT">DEFAULT</option>
                                <?php foreach (['KLES', 'MGB', 'FRACOT', 'NORTHERN', 'MADAMZ', 'CLEANING', 'QUANTO', 'SPACE', 'UNLIMITED', 'ERNLET'] as $client): ?>
                                <option value="<?= $client ?>"><?= $client ?></option>
                                <?php endforeach; ?>
                            </select>
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
                    
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-frays-red transition-colors cursor-pointer mb-6">
                        <i class="ri-cloud-upload-line text-5xl text-gray-300 mb-4"></i>
                        <p class="text-gray-600 mb-2">Drag & drop files here or click to browse</p>
                        <p class="text-sm text-gray-400">PDF, JPG, PNG up to 10MB each</p>
                        <input type="file" name="documents[]" id="fileInput" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                    </div>
                    
                    <button type="submit" class="w-full bg-frays-red text-white py-4 rounded-lg font-semibold hover:opacity-90 transition-all flex items-center justify-center gap-2">
                        <i class="ri-upload-line"></i>
                        Upload Documents
                    </button>
                </form>
            </div>
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
        document.querySelector('.border-dashed')?.addEventListener('click', function() {
            document.getElementById('fileInput')?.click();
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