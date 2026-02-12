<?php
/**
 * Document AI API Proxy
 * Bridges PHP portal to Node.js Document AI service
 * 
 * Document AI runs on: http://localhost:3000
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configuration
define('DOCAI_HOST', 'http://localhost:3000');
define('DOCAI_TIMEOUT', 60);

/**
 * Make request to Document AI service
 */
function callDocAI($endpoint, $method = 'GET', $data = null, $files = null) {
    $url = DOCAI_HOST . $endpoint;
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, DOCAI_TIMEOUT);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        
        if ($files) {
            // Handle multipart/form-data with files
            $multipart = [];
            
            // Add data fields
            if ($data) {
                foreach ($data as $key => $value) {
                    $multipart[] = [
                        'name' => $key,
                        'contents' => $value
                    ];
                }
            }
            
            // Add files
            foreach ($files as $key => $file) {
                $multipart[] = [
                    'name' => $key,
                    'filename' => basename($file['tmp_name']),
                    'type' => $file['type'],
                    'contents' => fopen($file['tmp_name'], 'r')
                ];
            }
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $multipart);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'Document AI service error: ' . $error
        ];
    }
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'data' => json_decode($response, true),
        'http_code' => $httpCode
    ];
}

/**
 * Simple POST without files
 */
function postDocAI($endpoint, $data) {
    return callDocAI($endpoint, 'POST', $data, null);
}

// ============================================
// ROUTES
// ============================================

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = str_replace('/api/', '', $requestUri);

// Health check
if ($requestUri === 'health' || $requestUri === '') {
    echo json_encode([
        'service' => 'Document AI Proxy',
        'status' => 'running',
        'timestamp' => date('c')
    ]);
    exit;
}

// Route: Process single document
if ($requestUri === 'process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['document'])) {
        echo json_encode(['success' => false, 'error' => 'No document uploaded']);
        exit;
    }
    
    $result = callDocAI('/api/process', 'POST', null, [
        'document' => [
            'tmp_name' => $_FILES['document']['tmp_name'],
            'type' => $_FILES['document']['type']
        ]
    ]);
    
    echo json_encode($result);
    exit;
}

// Route: Batch process
if ($requestUri === 'process/batch' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $files = [];
    
    if (!empty($_FILES['documents']['name'][0])) {
        foreach ($_FILES['documents']['name'] as $idx => $name) {
            $files['documents'][] = [
                'tmp_name' => $_FILES['documents']['tmp_name'][$idx],
                'type' => $_FILES['documents']['type'][$idx]
            ];
        }
    }
    
    $result = callDocAI('/api/process/batch', 'POST', null, $files);
    echo json_encode($result);
    exit;
}

// Route: Export to CSV
if ($requestUri === 'export/csv' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $result = postDocAI('/api/export/csv', $data);
    echo json_encode($result);
    exit;
}

// Route: Push to FrontAccounting
if ($requestUri === 'export/fa' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $result = postDocAI('/api/export/fa', $data);
    echo json_encode($result);
    exit;
}

// Route: Test FA connection
if ($requestUri === 'fa/test' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = callDocAI('/api/fa/test');
    echo json_encode($result);
    exit;
}

// Route: Get FA suppliers
if ($requestUri === 'fa/suppliers' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = callDocAI('/api/fa/suppliers');
    echo json_encode($result);
    exit;
}

// Route: Get FA customers
if ($requestUri === 'fa/customers' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = callDocAI('/api/fa/customers');
    echo json_encode($result);
    exit;
}

// Route: Get exports list
if ($requestUri === 'exports' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = callDocAI('/api/exports');
    echo json_encode($result);
    exit;
}

// Route: Get stats
if ($requestUri === 'stats' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = callDocAI('/api/stats');
    echo json_encode($result);
    exit;
}

// 404
http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => 'Endpoint not found: ' . $requestUri
]);
