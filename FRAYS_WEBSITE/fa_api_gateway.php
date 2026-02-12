<?php
/**
 * FrontAccounting Multi-Instance API Gateway
 * 
 * This API provides RESTful endpoints to interact with multiple 
 * FrontAccounting instances via PHP proxy.
 * 
 * @author OpenClaw Agent
 * @version 1.0.0
 */

// Configuration - Load from secure location
$config = [
    'instances' => [
        // Main/Frays Cottage
        'frayscottage' => [
            'url' => 'https://bookkeeping.co.bw/frayscottage/',
            'username' => getenv('FA_FRAYS_USERNAME') ?: 'admin',
            'password' => getenv('FA_FRAYS_PASSWORD') ?: '',
        ],
        // Client Instances
        'northernwarehouse' => [
            'url' => 'https://www.bookkeeping.co.bw/northernwarehouse/',
            'username' => getenv('FA_NW_USERNAME') ?: '',
            'password' => getenv('FA_NW_PASSWORD') ?: '',
        ],
        'madamz' => [
            'url' => 'https://www.bookkeeping.co.bw/madamz/',
            'username' => getenv('FA_MADAMZ_USERNAME') ?: '',
            'password' => getenv('FA_MADAMZ_PASSWORD') ?: '',
        ],
        'cleaningguru' => [
            'url' => 'https://www.bookkeeping.co.bw/cleaningguru/',
            'username' => getenv('FA_CG_USERNAME') ?: '',
            'password' => getenv('FA_CG_PASSWORD') ?: '',
        ],
        'quanto' => [
            'url' => 'https://www.bookkeeping.co.bw/quanto/',
            'username' => getenv('FA_QUANTO_USERNAME') ?: '',
            'password' => getenv('FA_QUANTO_PASSWORD') ?: '',
        ],
        'spaceinteriors' => [
            'url' => 'https://www.bookkeeping.co.bw/spaceinteriors/',
            'username' => getenv('FA_SPACE_USERNAME') ?: '',
            'password' => getenv('FA_SPACE_PASSWORD') ?: '',
        ],
        'unlimitedfoods' => [
            'url' => 'https://www.bookkeeping.co.bw/unlimitedfoods/',
            'username' => getenv('FA_UF_USERNAME') ?: '',
            'password' => getenv('FA_UF_PASSWORD') ?: '',
        ],
        'ernletprojects' => [
            'url' => 'https://www.bookkeeping.co.bw/ernletprojects/',
            'username' => getenv('FA_EP_USERNAME') ?: '',
            'password' => getenv('FA_EP_PASSWORD') ?: '',
        ],
        'constantadaptation' => [
            'url' => 'https://www.bookkeeping.co.bw/constantadaptation/',
            'username' => getenv('FA_CA_USERNAME') ?: '',
            'password' => getenv('FA_CA_PASSWORD') ?: '',
        ],
        'great-land' => [
            'url' => 'https://www.bookkeeping.co.bw/great-land/',
            'username' => getenv('FA_GL_USERNAME') ?: '',
            'password' => getenv('FA_GL_PASSWORD') ?: '',
        ],
        'lighteningstrike' => [
            'url' => 'https://www.bookkeeping.co.bw/lighteningstrike/',
            'username' => getenv('FA_LS_USERNAME') ?: '',
            'password' => getenv('FA_LS_PASSWORD') ?: '',
        ],
        'notsa' => [
            'url' => 'https://www.bookkeeping.co.bw/notsa/',
            'username' => getenv('FA_NOTSA_USERNAME') ?: '',
            'password' => getenv('FA_NOTSA_PASSWORD') ?: '',
        ],
        'thaega' => [
            'url' => 'https://www.bookkeeping.co.bw/thaega/',
            'username' => getenv('FA_THAEGA_USERNAME') ?: '',
            'password' => getenv('FA_THAEGA_PASSWORD') ?: '',
        ],
        'modernhotelsupplies' => [
            'url' => 'https://www.bookkeeping.co.bw/modernhotelsupplies/',
            'username' => getenv('FA_MHS_USERNAME') ?: '',
            'password' => getenv('FA_MHS_PASSWORD') ?: '',
        ],
        'training' => [
            'url' => 'https://www.bookkeeping.co.bw/training/',
            'username' => getenv('FA_TRAINING_USERNAME') ?: '',
            'password' => getenv('FA_TRAINING_PASSWORD') ?: '',
        ],
        'majande' => [
            'url' => 'https://bookkeeping.co.bw/majande/',
            'username' => getenv('FA_MAJANDE_USERNAME') ?: '',
            'password' => getenv('FA_MAJANDE_PASSWORD') ?: '',
        ],
        'guruonks' => [
            'url' => 'https://www.bookkeeping.co.bw/guruonks/',
            'username' => getenv('FA_GO_USERNAME') ?: '',
            'password' => getenv('FA_GO_PASSWORD') ?: '',
        ],
        'marctizmo' => [
            'url' => 'https://bookkeeping.co.bw/marctizmo/',
            'username' => getenv('FA_MARC_USERNAME') ?: '',
            'password' => getenv('FA_MARC_PASSWORD') ?: '',
        ],
        '4bnb' => [
            'url' => 'https://www.bookkeeping.co.bw/4bnb/',
            'username' => getenv('FA_4BNB_USERNAME') ?: '',
            'password' => getenv('FA_4BNB_PASSWORD') ?: '',
        ],
        'noracosmetics' => [
            'url' => 'https://bookkeeping.co.bw/noracosmetics/',
            'username' => getenv('FA_NC_USERNAME') ?: '',
            'password' => getenv('FA_NC_PASSWORD') ?: '',
        ],
        '3dworks' => [
            'url' => 'https://www.bookkeeping.co.bw/3dworks/',
            'username' => getenv('FA_3DW_USERNAME') ?: '',
            'password' => getenv('FA_3DW_PASSWORD') ?: '',
        ],
        'westdrayton' => [
            'url' => 'https://www.bookkeeping.co.bw/westdrayton/',
            'username' => getenv('FA_WD_USERNAME') ?: '',
            'password' => getenv('FA_WD_PASSWORD') ?: '',
        ],
        'ernletprojects2' => [
            'url' => 'https://www.bookkeeping.co.bw/ernletprojects2/',
            'username' => getenv('FA_EP2_USERNAME') ?: '',
            'password' => getenv('FA_EP2_PASSWORD') ?: '',
        ],
        'ernletgroup' => [
            'url' => 'https://www.bookkeeping.co.bw/ernletgroup/',
            'username' => getenv('FA_EG_USERNAME') ?: '',
            'password' => getenv('FA_EG_PASSWORD') ?: '',
        ],
        'couriersolutions' => [
            'url' => 'https://www.bookkeeping.co.bw/couriersolutions/',
            'username' => getenv('FA_CS_USERNAME') ?: '',
            'password' => getenv('FA_CS_PASSWORD') ?: '',
        ],
        'loremaster' => [
            'url' => 'https://www.bookkeeping.co.bw/loremaster/',
            'username' => getenv('FA_LM_USERNAME') ?: '',
            'password' => getenv('FA_LM_PASSWORD') ?: '',
        ],
        'coverlot' => [
            'url' => 'https://www.bookkeeping.co.bw/coverlot/',
            'username' => getenv('FA_CL_USERNAME') ?: '',
            'password' => getenv('FA_CL_PASSWORD') ?: '',
        ],
        'globalstrategies' => [
            'url' => 'https://www.bookkeeping.co.bw/globalstrategies/',
            'username' => getenv('FA_GS_USERNAME') ?: '',
            'password' => getenv('FA_GS_PASSWORD') ?: '',
        ],
        'norahbeauty' => [
            'url' => 'https://www.bookkeeping.co.bw/norahbeauty/',
            'username' => getenv('FA_NB_USERNAME') ?: '',
            'password' => getenv('FA_NB_PASSWORD') ?: '',
        ],
        'nidarshini' => [
            'url' => 'https://www.bookkeeping.co.bw/nidarshini/',
            'username' => getenv('FA_NID_USERNAME') ?: '',
            'password' => getenv('FA_NID_PASSWORD') ?: '',
        ],
    ],
    
    'api_key' => getenv('FA_API_KEY') ?: 'your-secret-api-key-here',
    'log_file' => '/var/log/fa_api.log',
];

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * FA API Gateway Class
 */
class FAAPIGateway {
    private $config;
    private $cookieJar;
    
    public function __construct($config) {
        $this->config = $config;
        $this->cookieJar = tempnam(sys_get_temp_dir(), 'fa_cookie_');
    }
    
    /**
     * Authenticate with an FA instance
     */
    public function authenticate($instance) {
        if (!isset($this->config['instances'][$instance])) {
            return ['error' => "Instance '$instance' not found"];
        }
        
        $inst = $this->config['instances'][$instance];
        $loginUrl = $inst['url'] . 'index.php/login.php';
        
        $postData = [
            'company_name' => '',
            'user_name_value' => $inst['username'],
            'password' => $inst['password'],
            'SubmitUser' => 'Login',
        ];
        
        $response = $this->curlRequest($loginUrl, 'POST', $postData);
        
        // Check if login was successful
        if (strpos($response['content'], 'Dashboard') !== false || 
            strpos($response['content'], 'Administrator') !== false) {
            return ['success' => true, 'message' => 'Authenticated'];
        }
        
        return ['error' => 'Authentication failed', 'details' => substr($response['content'], 0, 500)];
    }
    
    /**
     * Get customer information
     */
    public function getCustomer($instance, $customerId) {
        $inst = $this->config['instances'][$instance];
        $url = $inst['url'] . 'sales/inquiry/customer_inquiry.php?customer_id=' . $customerId;
        
        $response = $this->curlRequest($url);
        return $this->parseCustomerData($response['content']);
    }
    
    /**
     * Add a new customer
     */
    public function addCustomer($instance, $data) {
        $inst = $this->config['instances'][$instance];
        $url = $inst['url'] . 'sales/manage/customers.php?NewCustomer=Yes';
        
        $postData = [
            'CustName' => $data['name'] ?? '',
            'cust_ref' => $data['reference'] ?? '',
            'address' => $data['address'] ?? '',
            'tax_id' => $data['tax_id'] ?? '',
            'dimension_id' => $data['dimension_id'] ?? 0,
            'dimension2_id' => $data['dimension2_id'] ?? 0,
            'curr_code' => $data['currency'] ?? 'BWP',
            'sales_type' => $data['sales_type'] ?? 1,
            'payment_terms' => $data['payment_terms'] ?? 1,
            'discount' => $data['discount'] ?? 0,
            'credit_limit' => $data['credit_limit'] ?? 1000,
            'credit_status' => $data['credit_status'] ?? 1,
            'phone' => $data['phone'] ?? '',
            'phone2' => $data['phone2'] ?? '',
            'fax' => $data['fax'] ?? '',
            'email' => $data['email'] ?? '',
            'bank_account' => $data['bank_account'] ?? '',
            'sales_person' => $data['sales_person'] ?? 0,
            'add_customer' => 'Add New Customer',
        ];
        
        $response = $this->curlRequest($url, 'POST', $postData);
        
        if (strpos($response['content'], 'Customer') !== false && 
            strpos($response['content'], 'already exists') === false) {
            return ['success' => true, 'message' => 'Customer added'];
        }
        
        return ['error' => 'Failed to add customer', 'details' => substr($response['content'], 0, 500)];
    }
    
    /**
     * Create a journal entry
     */
    public function createJournalEntry($instance, $data) {
        $inst = $this->config['instances'][$instance];
        $url = $inst['url'] . 'gl/gl_journal.php?NewJournal=Yes';
        
        $postData = [
            'Date_' => $data['date'] ?? date('d/m/Y'),
            'ref' => $data['reference'] ?? '',
            'memo_' => $data['memo'] ?? '',
            'gl_seq' => '',
            'Amount' => $data['amount'] ?? 0,
            'account' => $data['account'] ?? 0,
            'dimension_id' => $data['dimension'] ?? 0,
            'dimension2_id' => $data['dimension2'] ?? 0,
            'save' => 'Enter',
        ];
        
        $response = $this->curlRequest($url, 'POST', $postData);
        
        if (strpos($response['content'], 'Journal Entry') !== false) {
            return ['success' => true, 'message' => 'Journal entry created'];
        }
        
        return ['error' => 'Failed to create journal entry'];
    }
    
    /**
     * Create a sales invoice
     */
    public function createInvoice($instance, $data) {
        $inst = $this->config['instances'][$instance];
        $url = $inst['url'] . 'sales/sales_order_entry.php?NewInvoice=0';
        
        $postData = [
            'customer_id' => $data['customer_id'] ?? 0,
            'branch_id' => $data['branch_id'] ?? 0,
            'date_' => $data['date'] ?? date('d/m/Y'),
            'del_date' => $data['delivery_date'] ?? date('d/m/Y'),
            'reference' => $data['reference'] ?? '',
            'Comments' => $data['comments'] ?? '',
            'ship_via' => $data['ship_via'] ?? 0,
            'ob_disc' => $data['discount'] ?? 0,
            'cust_ref' => $data['customer_reference'] ?? '',
            'sales_type_id' => $data['sales_type'] ?? 1,
            'add_invoice' => 'Process Invoice',
        ];
        
        // Add line items
        if (isset($data['items']) && is_array($data['items'])) {
            $i = 0;
            foreach ($data['items'] as $item) {
                $postData["stock_id[$i]"] = $item['stock_id'] ?? '';
                $postData["description[$i]"] = $item['description'] ?? '';
                $postData["qty[$i]"] = $item['quantity'] ?? 1;
                $postData["unit_price[$i]"] = $item['unit_price'] ?? 0;
                $postData["discount[$i]"] = $item['discount'] ?? 0;
                $postData["tax_type_id[$i]"] = $item['tax_type'] ?? 1;
                $i++;
            }
        }
        
        $response = $this->curlRequest($url, 'POST', $postData);
        
        if (strpos($response['content'], 'Invoice') !== false) {
            return ['success' => true, 'message' => 'Invoice created'];
        }
        
        return ['error' => 'Failed to create invoice'];
    }
    
    /**
     * Get aged debtors report
     */
    public function getAgedDebtors($instance, $date = null) {
        $inst = $this->config['instances'][$instance];
        $url = $inst['url'] . 'reporting/reports_main.php?Class=0&REP_ID=102';
        
        $response = $this->curlRequest($url);
        return $this->parseTableData($response['content']);
    }
    
    /**
     * Get VAT summary
     */
    public function getVatSummary($instance, $fromDate, $toDate) {
        $inst = $this->config['instances'][$instance];
        $url = $inst['url'] . 'tax/inquiry/tax_inquiry.php?';
        
        $params = http_build_query([
            'trans_date_from' => $fromDate,
            'trans_date_to' => $toDate,
        ]);
        
        $response = $this->curlRequest($url . $params);
        return $this->parseTableData($response['content']);
    }
    
    /**
     * List all customers
     */
    public function listCustomers($instance) {
        $inst = $this->config['instances'][$instance];
        $url = $inst['url'] . 'sales/manage/customers.php?';
        
        $response = $this->curlRequest($url);
        return $this->parseCustomerList($response['content']);
    }
    
    /**
     * Get instance info
     */
    public function getInstanceInfo($instance) {
        if (!isset($this->config['instances'][$instance])) {
            return ['error' => "Instance '$instance' not found"];
        }
        
        $inst = $this->config['instances'][$instance];
        $url = $inst['url'];
        
        $response = $this->curlRequest($url);
        
        // Extract version
        preg_match('/FrontAccounting\s+([0-9.]+)/', $response['content'], $matches);
        
        return [
            'instance' => $instance,
            'url' => $url,
            'version' => $matches[1] ?? 'unknown',
            'status' => 'accessible',
        ];
    }
    
    /**
     * List all available instances
     */
    public function listInstances() {
        $instances = [];
        foreach ($this->config['instances'] as $name => $config) {
            $instances[] = [
                'name' => $name,
                'url' => $config['url'],
                'configured' => !empty($config['username']),
            ];
        }
        return ['instances' => $instances];
    }
    
    /**
     * Execute custom SQL query (USE WITH CAUTION)
     */
    public function executeQuery($instance, $query) {
        // Only allow SELECT queries for safety
        if (stripos(trim($query), 'SELECT') !== 0) {
            return ['error' => 'Only SELECT queries are allowed'];
        }
        
        // This would require direct DB access
        // Not implemented in proxy layer for security
        return ['error' => 'Direct SQL access not enabled via API'];
    }
    
    /**
     * Helper: Make cURL request
     */
    private function curlRequest($url, $method = 'GET', $data = []) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieJar);
        curl_setopt($ch, CURLOPT_USERAGENT, 'FA-API-Client/1.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        return [
            'content' => $content,
            'http_code' => $httpCode,
        ];
    }
    
    /**
     * Helper: Parse customer data from HTML
     */
    private function parseCustomerData($html) {
        // Simplified parsing - in production use DOMDocument
        return [
            'raw_html' => substr($html, 0, 1000),
            'note' => 'Use DOM parsing for detailed extraction',
        ];
    }
    
    /**
     * Helper: Parse table data
     */
    private function parseTableData($html) {
        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/s', $html, $matches);
        
        $data = [];
        foreach ($matches[0] as $row) {
            preg_match_all('/<td[^>]*>(.*?)<\/td>/s', $row, $cells);
            if (!empty($cells[1])) {
                $data[] = array_map('strip_tags', $cells[1]);
            }
        }
        
        return ['data' => $data];
    }
    
    /**
     * Helper: Parse customer list
     */
    private function parseCustomerList($html) {
        preg_match_all('/<option[^>]*value="(\d+)"[^>]*>([^<]+)/s', $html, $matches);
        
        $customers = [];
        for ($i = 0; $i < count($matches[1]); $i++) {
            $customers[] = [
                'id' => $matches[1][$i],
                'name' => trim($matches[2][$i]),
            ];
        }
        
        return ['customers' => $customers];
    }
    
    public function __destruct() {
        if (file_exists($this->cookieJar)) {
            unlink($this->cookieJar);
        }
    }
}

// Initialize API
$api = new FAAPIGateway($config);

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$queryString = parse_url($requestUri, PHP_URL_QUERY);

// Parse request body
$requestBody = file_get_contents('php://input');
$jsonData = json_decode($requestBody, true) ?? [];

// API Key authentication
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
    if ($token !== $config['api_key']) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid API key']);
        exit;
    }
} else if ($path !== '/api/health') {
    http_response_code(401);
    echo json_encode(['error' => 'API key required. Use: Authorization: Bearer <key>']);
    exit;
}

// Route requests
try {
    switch ($path) {
        // Health check
        case '/api/health':
            echo json_encode(['status' => 'ok', 'timestamp' => date('c')]);
            break;
            
        // List all instances
        case '/api/instances':
            echo json_encode($api->listInstances());
            break;
            
        // Get instance info
        case '/api/instance':
            $instance = $queryString ? $_GET['instance'] ?? '' : '';
            if (empty($instance)) {
                http_response_code(400);
                echo json_encode(['error' => 'Instance parameter required']);
            } else {
                echo json_encode($api->getInstanceInfo($instance));
            }
            break;
            
        // Authenticate
        case '/api/auth':
            $instance = $jsonData['instance'] ?? '';
            echo json_encode($api->authenticate($instance));
            break;
            
        // List customers
        case '/api/customers':
            $instance = $_GET['instance'] ?? '';
            echo json_encode($api->listCustomers($instance));
            break;
            
        // Get single customer
        case '/api/customer':
            $instance = $_GET['instance'] ?? '';
            $customerId = $_GET['id'] ?? 0;
            echo json_encode($api->getCustomer($instance, $customerId));
            break;
            
        // Add customer
        case '/api/customer':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            } else {
                $instance = $jsonData['instance'] ?? '';
                $data = $jsonData['data'] ?? [];
                echo json_encode($api->addCustomer($instance, $data));
            }
            break;
            
        // Create invoice
        case '/api/invoice':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            } else {
                $instance = $jsonData['instance'] ?? '';
                $data = $jsonData['data'] ?? [];
                echo json_encode($api->createInvoice($instance, $data));
            }
            break;
            
        // Create journal entry
        case '/api/journal':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            } else {
                $instance = $jsonData['instance'] ?? '';
                $data = $jsonData['data'] ?? [];
                echo json_encode($api->createJournalEntry($instance, $data));
            }
            break;
            
        // Get aged debtors
        case '/api/aged-debtors':
            $instance = $_GET['instance'] ?? '';
            echo json_encode($api->getAgedDebtors($instance));
            break;
            
        // Get VAT summary
        case '/api/vat':
            $instance = $_GET['instance'] ?? '';
            $fromDate = $_GET['from'] ?? date('d/m/Y', strtotime('-1 month'));
            $toDate = $_GET['to'] ?? date('d/m/Y');
            echo json_encode($api->getVatSummary($instance, $fromDate, $toDate));
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'error' => 'Not found',
                'available_endpoints' => [
                    'GET  /api/health' => 'Health check',
                    'GET  /api/instances' => 'List all instances',
                    'GET  /api/instance?instance=X' => 'Get instance info',
                    'POST /api/auth' => ['instance' => 'name'],
                    'GET  /api/customers?instance=X' => 'List customers',
                    'POST /api/customer' => ['instance' => 'name', 'data' => {...}],
                    'POST /api/invoice' => ['instance' => 'name', 'data' => {...}],
                    'POST /api/journal' => ['instance' => 'name', 'data' => {...}],
                    'GET  /api/aged-debtors?instance=X' => 'Aged debtors report',
                    'GET  /api/vat?instance=X&from=DD/MM/YYYY&to=DD/MM/YYYY' => 'VAT summary',
                ],
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
