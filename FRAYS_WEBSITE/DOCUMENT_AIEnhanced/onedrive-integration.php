<?php
/**
 * OneDrive Integration
 * Upload, download, and manage documents on OneDrive
 */

if (!defined('APP_LOADED')) {
    require_once __DIR__ . '/../includes/config.php';
}

class OneDriveIntegration {
    
    private $clientId;
    private $clientSecret;
    private $tenantId;
    private $accessToken;
    private $refreshToken;
    
    public function __construct() {
        $this->clientId = getenv('ONEDRIVE_CLIENT_ID') ?: '';
        $this->clientSecret = getenv('ONEDRIVE_CLIENT_SECRET') ?: '';
        $this->tenantId = getenv('ONEDRIVE_TENANT_ID') ?: 'common';
        
        $this->loadTokens();
    }
    
    /**
     * Load stored tokens
     */
    private function loadTokens() {
        $tokenFile = DOCAI_PROCESSED_DIR . '/onedrive-tokens.json';
        
        if (file_exists($tokenFile)) {
            $tokens = json_decode(file_get_contents($tokenFile), true);
            $this->accessToken = $tokens['access_token'] ?? null;
            $this->refreshToken = $tokens['refresh_token'] ?? null;
        }
    }
    
    /**
     * Save tokens
     */
    private function saveTokens($accessToken, $refreshToken, $expiresIn = 3600) {
        $tokenFile = DOCAI_PROCESSED_DIR . '/onedrive-tokens.json';
        
        file_put_contents($tokenFile, json_encode([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at' => time() + $expiresIn
        ], JSON_PRETTY_PRINT));
        
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }
    
    /**
     * Get authorization URL
     */
    public function getAuthUrl($redirectUri) {
        $scopes = urlencode('Files.ReadWrite.All offline_access');
        
        return "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/authorize?" .
               "client_id={$this->clientId}" .
               "&response_type=code" .
               "&redirect_uri=" . urlencode($redirectUri) .
               "&scope={$scopes}";
    }
    
    /**
     * Exchange code for tokens
     */
    public function exchangeCode($code, $redirectUri) {
        $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";
        
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];
        
        $response = $this->makeRequest('POST', $url, $data, false);
        
        if (isset($response['access_token'])) {
            $this->saveTokens(
                $response['access_token'],
                $response['refresh_token'],
                $response['expires_in']
            );
            return ['success' => true];
        }
        
        return ['error' => 'Failed to exchange code', 'response' => $response];
    }
    
    /**
     * Refresh access token
     */
    public function refreshAccessToken() {
        if (empty($this->refreshToken)) {
            return ['error' => 'No refresh token available'];
        }
        
        $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";
        
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
            'grant_type' => 'refresh_token'
        ];
        
        $response = $this->makeRequest('POST', $url, $data, false);
        
        if (isset($response['access_token'])) {
            $this->saveTokens(
                $response['access_token'],
                $response['refresh_token'] ?? $this->refreshToken,
                $response['expires_in']
            );
            return ['success' => true];
        }
        
        return ['error' => 'Failed to refresh token'];
    }
    
    /**
     * Upload file to OneDrive
     */
    public function uploadFile($localPath, $onedrivePath) {
        if (empty($this->accessToken)) {
            return ['error' => 'Not authenticated'];
        }
        
        // Ensure folder exists
        $folder = dirname($onedrivePath);
        $this->createFolder($folder);
        
        $url = "https://graph.microsoft.com/v1.0/me/drive/root:{$onedrivePath}:/content";
        
        $content = file_get_contents($localPath);
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: ' . mime_content_type($localPath)
        ];
        
        $response = $this->makeRequest('PUT', $url, $content, true, $headers);
        
        return $response;
    }
    
    /**
     * Create folder in OneDrive
     */
    public function createFolder($path) {
        if (empty($this->accessToken)) {
            return ['error' => 'Not authenticated'];
        }
        
        $parent = dirname($path);
        $name = basename($path);
        
        if (empty($name) || $name === '.') return ['success' => true];
        
        // Check if exists
        $checkUrl = "https://graph.microsoft.com/v1.0/me/drive/root:{$parent}:/children";
        $response = $this->makeRequest('GET', $checkUrl);
        
        if (isset($response['value'])) {
            foreach ($response['value'] as $item) {
                if ($item['name'] === $name && isset($item['folder'])) {
                    return ['success' => true, 'id' => $item['id']];
                }
            }
        }
        
        // Create folder
        $url = "https://graph.microsoft.com/v1.0/me/drive/root:{$parent}:/children";
        $data = json_encode([
            'name' => $name,
            'folder' => new stdClass(),
            '@microsoft.graph.conflictBehavior' => 'rename'
        ]);
        
        return $this->makeRequest('POST', $url, $data, true, [], $data);
    }
    
    /**
     * Download file from OneDrive
     */
    public function downloadFile($onedrivePath, $localPath) {
        if (empty($this->accessToken)) {
            return ['error' => 'Not authenticated'];
        }
        
        $url = "https://graph.microsoft.com/v1.0/me/drive/root:{$onedrivePath}:/content";
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->accessToken],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($httpCode === 200) {
            file_put_contents($localPath, $content);
            return ['success' => true, 'path' => $localPath];
        }
        
        return ['error' => 'Failed to download', 'code' => $httpCode];
    }
    
    /**
     * Move file in OneDrive
     */
    public function moveFile($fromPath, $toPath) {
        if (empty($this->accessToken)) {
            return ['error' => 'Not authenticated'];
        }
        
        // Get item ID
        $itemUrl = "https://graph.microsoft.com/v1.0/me/drive/root:{$fromPath}";
        $item = $this->makeRequest('GET', $itemUrl);
        
        if (!isset($item['id'])) {
            return ['error' => 'File not found'];
        }
        
        $url = "https://graph.microsoft.com/v1.0/me/drive/items/{$item['id']}";
        
        $parentPath = dirname($toPath);
        $fileName = basename($toPath);
        
        $data = json_encode([
            'parent' => [
                'path' => "/drive/root:{$parentPath}"
            ],
            'name' => $fileName
        ]);
        
        return $this->makeRequest('PATCH', $url, $data, true, [], $data);
    }
    
    /**
     * Get folder contents
     */
    public function listFolder($path = '') {
        if (empty($this->accessToken)) {
            return ['error' => 'Not authenticated'];
        }
        
        $url = "https://graph.microsoft.com/v1.0/me/drive/root:" . ($path ?: '/') . ':/children';
        
        return $this->makeRequest('GET', $url);
    }
    
    /**
     * Delete file/folder
     */
    public function delete($path) {
        if (empty($this->accessToken)) {
            return ['error' => 'Not authenticated'];
        }
        
        $url = "https://graph.microsoft.com/v1.0/me/drive/root:{$path}";
        
        return $this->makeRequest('DELETE', $url);
    }
    
    /**
     * Get shareable link
     */
    public function getShareLink($path, $type = 'view') {
        if (empty($this->accessToken)) {
            return ['error' => 'Not authenticated'];
        }
        
        // Get item ID
        $itemUrl = "https://graph.microsoft.com/v1.0/me/drive/root:{$path}";
        $item = $this->makeRequest('GET', $itemUrl);
        
        if (!isset($item['id'])) {
            return ['error' => 'File not found'];
        }
        
        $url = "https://graph.microsoft.com/v1.0/me/drive/items/{$item['id']}/createLink";
        
        $data = json_encode([
            'type' => $type, // view or edit
            'scope' => 'anonymous'
        ]);
        
        return $this->makeRequest('POST', $url, $data, true, [], $data);
    }
    
    /**
     * Make HTTP request
     */
    private function makeRequest($method, $url, $data = null, $hasBody = false, $headers = [], $rawData = null) {
        // Refresh token if expired
        if ($this->isTokenExpired()) {
            $this->refreshAccessToken();
        }
        
        $ch = curl_init();
        
        $defaultHeaders = [
            'Authorization: Bearer ' . $this->accessToken
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        if ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        } elseif ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $rawData ?? http_build_query($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $rawData ?? $data);
        } elseif ($method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $rawData ?? $data);
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $allHeaders = array_merge($defaultHeaders, $headers);
        if ($hasBody && empty($rawData)) {
            $allHeaders[] = 'Content-Type: application/x-www-form-urlencoded';
        } elseif ($hasBody) {
            $allHeaders[] = 'Content-Type: application/json';
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($httpCode === 401) {
            // Token expired, try refresh
            if ($this->refreshAccessToken()) {
                return $this->makeRequest($method, $url, $data, $hasBody, $headers, $rawData);
            }
        }
        
        curl_close($ch);
        
        if ($method === 'DELETE' || $httpCode === 204) {
            return ['success' => true];
        }
        
        return json_decode($response, true) ?: ['error' => 'Invalid response'];
    }
    
    /**
     * Check if token is expired
     */
    private function isTokenExpired() {
        $tokenFile = DOCAI_PROCESSED_DIR . '/onedrive-tokens.json';
        if (!file_exists($tokenFile)) return true;
        
        $tokens = json_decode(file_get_contents($tokenFile), true);
        return ($tokens['expires_at'] ?? 0) < time();
    }
    
    /**
     * Get OneDrive folder structure for Frays Cottage
     */
    public function getFraysCottageStructure() {
        return [
            'Invoices Received' => [
                'Unprocessed' => [
                    'Batch-{date}' => [] // e.g., Batch-2026-02-14
                ],
                '{year}' => [
                    '{month}' => [
                        '{date}' => [] // e.g., 2026/02/14
                    ]
                ]
            ],
            'Bank Statements' => [
                'Unprocessed' => [],
                '{year}' => [
                    '{month}' => []
                ]
            ],
            'POP Issued' => [
                'Unprocessed' => [],
                '{year}' => ['{month}' => []]
            ],
            'POP Received' => [
                'Unprocessed' => [],
                '{year}' => ['{month}' => []]
            ]
        ];
    }
}

// OneDrive Document Manager Class
class OneDriveDocumentManager {
    
    private $onedrive;
    private $basePath = '/Frays Cottage';
    
    public function __construct() {
        $this->onedrive = new OneDriveIntegration();
    }
    
    /**
     * Upload unprocessed document
     */
    public function uploadUnprocessed($localPath, $clientName, $batchId) {
        $dateFolder = date('Y-m-d');
        $onedrivePath = "{$this->basePath}/Invoices Received/Unprocessed/Batch-{$batchId}/{$dateFolder}";
        
        $filename = basename($localPath);
        $result = $this->onedrive->uploadFile($localPath, "{$onedrivePath}/{$filename}");
        
        return $result;
    }
    
    /**
     * Move to processed folder after FA posting
     */
    public function moveToProcessed($onedrivePath, $faReference) {
        $dateFolder = date('Y/m/d');
        $filename = basename($onedrivePath);
        $newPath = str_replace('/Unprocessed/', "/{$dateFolder}/", $onedrivePath);
        
        $result = $this->onedrive->moveFile($onedrivePath, $newPath);
        
        if (isset($result['id'])) {
            // Apply watermark (update filename with reference)
            $newFilename = str_replace($filename, "{$faReference}-{$filename}", $newPath);
            $this->onedrive->moveFile($newPath, $newFilename);
        }
        
        return $result;
    }
    
    /**
     * Upload bank statement
     */
    public function uploadBankStatement($localPath, $bankName, $statementDate) {
        $dateFolder = date('Y/m/d');
        $onedrivePath = "{$this->basePath}/Bank Statements/Unprocessed/{$dateFolder}";
        
        $filename = basename($localPath);
        return $this->onedrive->uploadFile($localPath, "{$onedrivePath}/{$filename}");
    }
    
    /**
     * Get unprocessed documents
     */
    public function getUnprocessedDocuments() {
        $path = "{$this->basePath}/Invoices Received/Unprocessed";
        return $this->onedrive->listFolder($path);
    }
}

// AJAX handler
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $manager = new OneDriveDocumentManager();
    
    switch ($_POST['action']) {
        case 'upload_unprocessed':
            $result = $manager->uploadUnprocessed(
                $_POST['local_path'],
                $_POST['client_name'],
                $_POST['batch_id']
            );
            echo json_encode($result);
            break;
            
        case 'move_to_processed':
            $result = $manager->moveToProcessed(
                $_POST['onedrive_path'],
                $_POST['fa_reference']
            );
            echo json_encode($result);
            break;
            
        case 'upload_bank_statement':
            $result = $manager->uploadBankStatement(
                $_POST['local_path'],
                $_POST['bank_name'],
                $_POST['statement_date']
            );
            echo json_encode($result);
            break;
            
        case 'list_unprocessed':
            echo json_encode($manager->getUnprocessedDocuments());
            break;
            
        default:
            echo json_encode(['error' => 'Unknown action']);
    }
    exit;
}
