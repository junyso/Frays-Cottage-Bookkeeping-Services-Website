<?php
/**
 * FRAYSCOTTAGE BOOKKEEPING SERVICES - Logout Handler
 */

require_once __DIR__ . '/../includes/config.php';

logActivity('user_logout', ['user_id' => $_SESSION['user_id'] ?? 0]);
logoutUser();

redirect('/portal');
