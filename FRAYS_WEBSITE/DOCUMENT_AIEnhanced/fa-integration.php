<?php
/**
 * FA Integration Module
 * Post documents to FrontAccounting via API
 */

if (!defined('APP_LOADED')) {
    require_once __DIR__ . '/../includes/config.php';
    require_once __DIR__ . '/../includes/fa-database-creds.php';
}

class FAIntegration {
    
    private $faDatabases;
    
    public function __construct() {
        global $faDatabases;
        $this->faDatabases = $faDatabases;
    }
    
    /**
     * Get list of available FA databases
     */
    public function getDatabaseList() {
        $databases = [];
        
        foreach ($this->faDatabases as $name => $config) {
            $databases[] = [
                'name' => $name,
                'display' => $config['display'] ?? $name,
                'database' => $config['database']
            ];
        }
        
        return $databases;
    }
    
    /**
     * Connect to FA database
     */
    private function connectToFA($databaseName) {
        if (!isset($this->faDatabases[$databaseName])) {
            return ['error' => "Database not found: {$databaseName}"];
        }
        
        $config = $this->faDatabases[$databaseName];
        
        $conn = mysqli_connect(
            $config['host'] ?? 'localhost',
            $config['username'] ?? 'root',
            $config['password'] ?? '',
            $config['database']
        );
        
        if (!$conn) {
            return ['error' => mysqli_connect_error()];
        }
        
        return ['connection' => $conn, 'config' => $config];
    }
    
    /**
     * Get chart of accounts
     */
    public function getChartOfAccounts($databaseName) {
        $connection = $this->connectToFA($databaseName);
        
        if (isset($connection['error'])) {
            return $connection;
        }
        
        $conn = $connection['connection'];
        
        $result = mysqli_query($conn, "
            SELECT account_code, account_name, account_type
            FROM chart_of_accounts
            WHERE !inactive
            ORDER BY account_code
        ");
        
        $accounts = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $accounts[] = $row;
        }
        
        return $accounts;
    }
    
    /**
     * Get suppliers from FA
     */
    public function getSuppliers($databaseName) {
        $connection = $this->connectToFA($databaseName);
        
        if (isset($connection['error'])) {
            return $connection;
        }
        
        $conn = $connection['connection'];
        
        $result = mysqli_query($conn, "
            SELECT supplier_id, supp_name, curr_code, inactive
            FROM suppliers
            WHERE !inactive
            ORDER BY supp_name
        ");
        
        $suppliers = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $suppliers[] = $row;
        }
        
        return $suppliers;
    }
    
    /**
     * Get dimensions (cost centers)
     */
    public function getDimensions($databaseName, $level = 0) {
        $connection = $this->connectToFA($databaseName);
        
        if (isset($connection['error'])) {
            return $connection;
        }
        
        $conn = $connection['connection'];
        
        $field = $level === 0 ? 'dim_id' : ($level === 1 ? 'dim2_id' : 'dim3_id');
        $table = $level === 0 ? 'dimensions' : ($level === 1 ? 'dimensions' : 'dimensions');
        $nameField = $level === 0 ? 'name' : ($level === 1 ? 'name' : 'name');
        
        $result = mysqli_query($conn, "
            SELECT {$field} as id, {$nameField} as name
            FROM {$table}
            WHERE !inactive
            ORDER BY id
        ");
        
        $dimensions = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $dimensions[] = $row;
        }
        
        return $dimensions;
    }
    
    /**
     * Create new supplier in FA
     */
    public function createSupplier($databaseName, $supplierData) {
        $connection = $this->connectToFA($databaseName);
        
        if (isset($connection['error'])) {
            return $connection;
        }
        
        $conn = $connection['connection'];
        
        $supp_name = mysqli_real_escape_string($conn, $supplierData['name']);
        $curr_code = mysqli_real_escape_string($conn, $supplierData['currency'] ?? 'BWP');
        $tax_group_id = intval($supplierData['tax_group_id'] ?? 1);
        
        $result = mysqli_query($conn, "
            INSERT INTO suppliers (supp_name, curr_code, tax_group_id, inactive)
            VALUES ('{$supp_name}', '{$curr_code}', {$tax_group_id}, 0)
        ");
        
        if ($result) {
            return [
                'success' => true,
                'supplier_id' => mysqli_insert_id($conn),
                'message' => 'Supplier created successfully'
            ];
        }
        
        return ['error' => mysqli_error($conn)];
    }
    
    /**
     * Post invoice to FA (simplified - creates GL entry)
     */
    public function postInvoice($databaseName, $invoiceData) {
        $connection = $this->connectToFA($databaseName);
        
        if (isset($connection['error'])) {
            return $connection;
        }
        
        $conn = $connection['connection'];
        
        // This is simplified - actual implementation would create proper FA transactions
        // For now, we'll create a journal entry
        
        $transNo = $this->getNextTransactionNumber($conn, 'journal');
        
        $date = mysqli_real_escape_string($conn, $invoiceData['date'] ?? date('Y-m-d'));
        $reference = mysqli_real_escape_string($conn, $invoiceData['reference'] ?? 'INV-AUTO');
        $amount = floatval($invoiceData['amount'] ?? 0);
        $glAccount = mysqli_real_escape_string($conn, $invoiceData['gl_account']);
        $supplier = mysqli_real_escape_string($conn, $invoiceData['supplier']);
        $dimension1 = intval($invoiceData['dimension1'] ?? 0);
        $dimension2 = intval($invoiceData['dimension2'] ?? 0);
        $memo = mysqli_real_escape_string($conn, $invoiceData['memo'] ?? 'Document AI Upload');
        
        // Insert GL entry
        $result = mysqli_query($conn, "
            INSERT INTO gl_trans (
                trans_no, type, tran_date, account, dimension1_id, dimension2_id,
                amount, reference, person_type_id, person_id, memo_
            ) VALUES (
                {$transNo}, 20, '{$date}', '{$glAccount}', {$dimension1}, {$dimension2},
                {$amount}, '{$reference}', 1, '{$supplier}', '{$memo}'
            )
        ");
        
        if (!$result) {
            return ['error' => mysqli_error($conn)];
        }
        
        return [
            'success' => true,
            'transaction_no' => $transNo,
            'message' => "Invoice posted successfully. Ref: {$reference}"
        ];
    }
    
    /**
     * Get next transaction number
     */
    private function getNextTransactionNumber($conn, $type) {
        $result = mysqli_query($conn, "SELECT MAX(trans_no) + 1 as next_no FROM gl_trans WHERE type = 20");
        $row = mysqli_fetch_assoc($result);
        return intval($row['next_no'] ?? 1);
    }
    
    /**
     * Test database connection
     */
    public function testConnection($databaseName) {
        $connection = $this->connectToFA($databaseName);
        
        if (isset($connection['error'])) {
            return ['success' => false, 'error' => $connection['error']];
        }
        
        mysqli_close($connection['connection']);
        
        return ['success' => true, 'message' => "Connected to {$databaseName}"];
    }
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $fa = new FAIntegration();
    
    switch ($_POST['action']) {
        case 'get_databases':
            echo json_encode($fa->getDatabaseList());
            break;
            
        case 'test_connection':
            $db = $_POST['database'] ?? '';
            echo json_encode($fa->testConnection($db));
            break;
            
        case 'get_gl_accounts':
            $db = $_POST['database'] ?? '';
            echo json_encode($fa->getChartOfAccounts($db));
            break;
            
        case 'get_suppliers':
            $db = $_POST['database'] ?? '';
            echo json_encode($fa->getSuppliers($db));
            break;
            
        case 'get_dimensions':
            $db = $_POST['database'] ?? '';
            $level = intval($_POST['level'] ?? 0);
            echo json_encode($fa->getDimensions($db, $level));
            break;
            
        case 'create_supplier':
            $db = $_POST['database'] ?? '';
            $data = $_POST['data'] ?? [];
            echo json_encode($fa->createSupplier($db, $data));
            break;
            
        case 'post_invoice':
            $db = $_POST['database'] ?? '';
            $data = $_POST['data'] ?? [];
            echo json_encode($fa->postInvoice($db, $data));
            break;
            
        default:
            echo json_encode(['error' => 'Unknown action']);
    }
    exit;
}
