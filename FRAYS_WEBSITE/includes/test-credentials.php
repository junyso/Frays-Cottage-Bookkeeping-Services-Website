<?php
/**
 * TEST CREDENTIALS - For Local Development & Testing Only!
 * 
 * Add any test users here for testing document upload functionality.
 * 
 * Usage:
 *   Username: test
 *   Password: test123
 */

$TEST_USERS = [
    [
        'id' => 999,
        'email' => 'test@frayscottage.co.bw',
        'name' => 'Test User',
        'password' => 'test123',
        'fa_instances' => [
            'cleaningguru' => [
                'name' => 'Cleaning Guru',
                'fa_user_id' => 'test@frayscottage.co.bw',
                'role' => 'admin'
            ]
        ],
        'status' => 'active'
    ],
    [
        'id' => 998,
        'email' => 'demo@frayscottage.co.bw',
        'name' => 'Demo User',
        'password' => 'demo456',
        'fa_instances' => [
            'kles' => [
                'name' => 'KLES',
                'fa_user_id' => 'demo@frayscottage.co.bw',
                'role' => 'admin'
            ],
            'madamz' => [
                'name' => 'Madamz',
                'fa_user_id' => 'demo@frayscottage.co.bw',
                'role' => 'admin'
            ]
        ],
        'status' => 'active'
    ],
    [
        'id' => 997,
        'email' => 'julian@frayscottage.co.bw',
        'name' => 'Julian Useya',
        'password' => 'julian123',
        'fa_instances' => [
            'frayscottage' => [
                'name' => 'Frays Cottage',
                'fa_user_id' => 'admin',
                'role' => 'admin'
            ]
        ],
        'status' => 'active'
    ]
];

/**
 * Check if user is a test user
 */
function isTestUser($email) {
    global $TEST_USERS;
    foreach ($TEST_USERS as $user) {
        if ($user['email'] === $email) {
            return true;
        }
    }
    return false;
}

/**
 * Authenticate test user
 */
function authenticateTestUser($email, $password) {
    global $TEST_USERS;
    foreach ($TEST_USERS as $user) {
        if ($user['email'] === $email && $user['password'] === $password) {
            // Return user without password
            $user['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            unset($user['password']);
            return $user;
        }
    }
    return false;
}

/**
 * Get test users list (for dropdown)
 */
function getTestUsersList() {
    global $TEST_USERS;
    $list = [];
    foreach ($TEST_USERS as $user) {
        $list[] = [
            'email' => $user['email'],
            'name' => $user['name'],
            'instances' => count($user['fa_instances'])
        ];
    }
    return $list;
}
