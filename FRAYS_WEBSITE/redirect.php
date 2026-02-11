<?php
/**
 * FRAYSCOTTAGE BOOKKEEPING SERVICES - FA Instance Redirect
 * 
 * Single sign-on redirect to specific FrontAccounting instance
 */

require_once __DIR__ . '/../includes/config.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('/portal?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? ''));
}

$instance = sanitizeInput($_GET['instance'] ?? '');

if (empty($instance) || !isset($FA_INSTANCES[$instance])) {
    $_SESSION['error'] = 'Invalid instance requested';
    redirect('/portal/');
}

// Log the access
logActivity('fa_redirect', [
    'user_id' => $_SESSION['user_id'],
    'instance' => $instance
]);

// Redirect to the FA instance
$faUrl = $FA_INSTANCES[$instance]['url'];
redirect($faUrl);
