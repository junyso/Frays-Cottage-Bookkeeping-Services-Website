<?php
/**
 * Bank Statement Processor
 * Extract transactions from bank statements and process payments
 */

if (!defined('APP_LOADED')) {
    require_once __DIR__ . '/../includes/config.php';
}

class BankStatementProcessor {
    
    private $uploadDir;
    private $processedDir;
    
    public function __construct() {
        $this->uploadDir = DOCAI_UPLOAD_DIR;
        $this->processedDir = DOCAI_PROCESSED_DIR;
    }
    
    /**
     * Process bank statement document
     */
    public function processStatement($filePath, $filename) {
        $result = [
            'filename' => $filename,
            'transactions' => [],
            'summary' => [],
            'extracted_text' => '',
            'confidence' => 0
        ];
        
        // Extract text from document
        $text = $this->extractText($filePath, $filename);
        $result['extracted_text'] = $text;
        
        // Parse transactions
        $transactions = $this->parseTransactions($text, $filename);
        $result['transactions'] = $transactions;
        
        // Generate summary
        $result['summary'] = $this->generateSummary($transactions);
        
        // Calculate confidence
        $result['confidence'] = $this->calculateConfidence($transactions, $text);
        
        return $result;
    }
    
    /**
     * Extract text from bank statement
     */
    private function extractText($filePath, $filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            // Would use OCR - simplified for now
            return file_get_contents($filePath);
        } else {
            // PDF text extraction
            return $this->extractPDFText($filePath);
        }
    }
    
    /**
     * Extract text from PDF
     */
    private function extractPDFText($filePath) {
        // Simplified - would use PDF parser in production
        return file_get_contents($filePath);
    }
    
    /**
     * Parse transactions from extracted text
     */
    private function parseTransactions($text, $filename) {
        $transactions = [];
        $lines = explode("\n", $text);
        
        $transactionPatterns = [
            // Date patterns
            '/(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/' => 'date',
            '/(\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2})/' => 'date',
            
            // Amount patterns  
            '/([0-9,]+\.\d{2})/' => 'amount',
            '/BWP\s*([0-9,]+\.\d{2})/' => 'amount',
            '/P\s*([0-9,]+\.\d{2})/' => 'amount',
        ];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Try to identify transaction components
            $transaction = $this->parseLine($line);
            if ($transaction) {
                $transactions[] = $transaction;
            }
        }
        
        return $transactions;
    }
    
    /**
     * Parse single line for transaction data
     */
    private function parseLine($line) {
        $transaction = [
            'date' => '',
            'description' => '',
            'amount' => 0,
            'type' => '', // credit or debit
            'balance' => 0,
            'reference' => ''
        ];
        
        // Extract date
        if (preg_match('/(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/', $line, $matches)) {
            $transaction['date'] = $this->normalizeDate($matches[1]);
        }
        
        // Extract amount (handle Botswana currency)
        $amountPatterns = [
            '/BWP\s*([\d,]+\.\d{2})/',
            '/P\s*([\d,]+\.\d{2})/',
            '/([\d,]+\.\d{2})/'
        ];
        
        foreach ($amountPatterns as $pattern) {
            if (preg_match($pattern, $line, $matches)) {
                $amount = str_replace(',', '', $matches[1]);
                $transaction['amount'] = floatval($amount);
                break;
            }
        }
        
        // Determine type (credit/debit)
        if (preg_match('/(credit|cr|in|received|deposit|payment in)/i', $line)) {
            $transaction['type'] = 'credit';
        } elseif (preg_match('/(debit|dr|out|payment|withdrawal|debit order)/i', $line)) {
            $transaction['type'] = 'debit';
        }
        
        // Extract description (everything else)
        $description = preg_replace('/[\d\s\-\/]+$/', '', $line);
        $transaction['description'] = trim($description);
        
        // Extract reference if present
        if (preg_match('/(ref[:\s]*([A-Z0-9]+))/i', $line, $matches)) {
            $transaction['reference'] = $matches[2];
        }
        
        // Only return if we have meaningful data
        if (!empty($transaction['date']) && $transaction['amount'] > 0) {
            return $transaction;
        }
        
        return null;
    }
    
    /**
     * Normalize date format
     */
    private function normalizeDate($dateStr) {
        // Convert various date formats to YYYY-MM-DD
        $timestamp = strtotime($dateStr);
        if ($timestamp) {
            return date('Y-m-d', $timestamp);
        }
        return $dateStr;
    }
    
    /**
     * Generate summary of transactions
     */
    private function generateSummary($transactions) {
        $summary = [
            'total_transactions' => count($transactions),
            'total_credits' => 0,
            'total_debits' => 0,
            'net_flow' => 0,
            'date_range' => [
                'first' => null,
                'last' => null
            ],
            'by_type' => []
        ];
        
        foreach ($transactions as $t) {
            if ($t['type'] === 'credit') {
                $summary['total_credits'] += $t['amount'];
            } else {
                $summary['total_debits'] += $t['amount'];
            }
            
            // Track date range
            if (empty($summary['date_range']['first']) || $t['date'] < $summary['date_range']['first']) {
                $summary['date_range']['first'] = $t['date'];
            }
            if (empty($summary['date_range']['last']) || $t['date'] > $summary['date_range']['last']) {
                $summary['date_range']['last'] = $t['date'];
            }
        }
        
        $summary['net_flow'] = $summary['total_credits'] - $summary['total_debits'];
        
        return $summary;
    }
    
    /**
     * Calculate confidence score
     */
    private function calculateConfidence($transactions, $text) {
        if (empty($text)) return 0;
        
        // Base confidence from text extraction
        $confidence = 0.5;
        
        // Increase confidence if we found transactions
        if (count($transactions) > 0) {
            $confidence += 0.3;
        }
        
        // Increase confidence if transactions have dates
        $dated = array_filter($transactions, fn($t) => !empty($t['date']));
        if (count($dated) > 0) {
            $confidence += 0.1;
        }
        
        // Increase confidence if transactions have amounts
        $withAmounts = array_filter($transactions, fn($t) => $t['amount'] > 0);
        if (count($withAmounts) > 0) {
            $confidence += 0.1;
        }
        
        return min(0.95, $confidence);
    }
    
    /**
     * Match transactions to invoices
     */
    public function matchToInvoices($transactions, $databaseName) {
        $matches = [];
        
        foreach ($transactions as $t) {
            $match = [
                'transaction' => $t,
                'matched_invoice' => null,
                'confidence' => 0,
                'action' => 'review'
            ];
            
            // Would query FA database for matching invoices
            // Match by amount, date range, description
            
            $matches[] = $match;
        }
        
        return $matches;
    }
    
    /**
     * Generate payment suggestion
     */
    public function generatePaymentSuggestion($transaction) {
        $suggestion = [
            'action' => 'unmatched',
            'cashbook' => '',
            'gl_account' => '',
            'dimension1' => '',
            'dimension2' => '',
            'memo' => ''
        ];
        
        // Analyze description for clues
        $desc = strtolower($transaction['description']);
        
        // Determine cashbook
        if (preg_match('/(atm|cash|withdrawal)/i', $desc)) {
            $suggestion['cashbook'] = '1001-Petty Cash';
        } elseif (preg_match('/(eft|transfer|wire|bank)/i', $desc)) {
            $suggestion['cashbook'] = '1002-Main Bank';
        } else {
            $suggestion['cashbook'] = '1002-Main Bank'; // Default
        }
        
        // Determine GL account
        if (preg_match('/(salary|payroll|wages)/i', $desc)) {
            $suggestion['gl_account'] = '6600-Salaries';
        } elseif (preg_match('/(rent|lease)/i', $desc)) {
            $suggestion['gl_account'] = '6300-Rent Expenses';
        } elseif (preg_match('/(utility|electric|water|power)/i', $desc)) {
            $suggestion['gl_account'] = '6400-Utilities';
        } elseif (preg_match('/(transport|fuel|mileage)/i', $desc)) {
            $suggestion['gl_account'] = '6500-Transport';
        } elseif (preg_match('/(insurance|premium)/i', $desc)) {
            $suggestion['gl_account'] = '6900-Other Expenses';
        } else {
            $suggestion['gl_account'] = '6100-Office Expenses'; // Default
        }
        
        // Set memo
        $suggestion['memo'] = $transaction['description'];
        
        return $suggestion;
    }
}

// AJAX handler
if (isset($_POST['action']) {
    header('Content-Type: application/json');
    
    $processor = new BankStatementProcessor();
    
    if ($_POST['action'] === 'process_statement' && isset($_FILES['statement'])) {
        $result = $processor->processStatement(
            $_FILES['statement']['tmp_name'],
            $_FILES['statement']['name']
        );
        echo json_encode($result);
    }
    
    exit;
}
