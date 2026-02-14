<?php
/**
 * Admin Review Interface
 * Review uploaded documents, edit mappings, push to FA
 */

if (!defined('APP_LOADED')) {
    require_once __DIR__ . '/../includes/config.php';
}

class AdminReviewInterface {
    
    private $uploadDir;
    private $processedDir;
    private $exportDir;
    
    public function __construct() {
        $this->uploadDir = DOCAI_UPLOAD_DIR;
        $this->processedDir = DOCAI_PROCESSED_DIR;
        $this->exportDir = DOCAI_EXPORT_DIR;
    }
    
    /**
     * Get pending documents for review
     */
    public function getPendingDocuments() {
        $documents = [];
        $files = glob($this->uploadDir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $doc = [
                    'filename' => basename($file),
                    'path' => $file,
                    'size' => filesize($file),
                    'uploaded' => date('Y-m-d H:i:s', filemtime($file)),
                    'status' => 'pending',
                    'classification' => $this->getClassification(basename($file)),
                    'mapping' => $this->getMapping(basename($file))
                ];
                $documents[] = $doc;
            }
        }
        
        return $documents;
    }
    
    /**
     * Get document classification from fingerprint
     */
    private function getClassification($filename) {
        $fingerprintFile = $this->processedDir . '/fingerprints.json';
        
        if (file_exists($fingerprintFile)) {
            $fingerprints = json_decode(file_get_contents($fingerprintFile), true) ?: [];
            
            foreach ($fingerprints as $fp) {
                if ($fp['filename'] === $filename) {
                    return [
                        'type' => $fp['type'] ?? 'expense',
                        'confidence' => $fp['confidence'] ?? 0.5
                    ];
                }
            }
        }
        
        return ['type' => 'expense', 'confidence' => 0.5];
    }
    
    /**
     * Get existing mapping for document
     */
    private function getMapping($filename) {
        $mappingFile = $this->processedDir . '/mappings.json';
        
        if (file_exists($mappingFile)) {
            $mappings = json_decode(file_get_contents($mappingFile), true) ?: [];
            
            if (isset($mappings[$filename])) {
                return $mappings[$filename];
            }
        }
        
        return [
            'gl_account' => '6100-Office Expenses',
            'dimension1' => '',
            'dimension2' => '',
            'dimension3' => '',
            'supplier' => '',
            'vat_amount' => 0,
            'cashbook' => '',
            'payment_status' => 'unpaid'
        ];
    }
    
    /**
     * Update document mapping
     */
    public function updateMapping($filename, $mapping) {
        $mappingFile = $this->processedDir . '/mappings.json';
        
        $mappings = [];
        if (file_exists($mappingFile)) {
            $mappings = json_decode(file_get_contents($mappingFile), true) ?: [];
        }
        
        $mappings[$filename] = array_merge(
            $this->getMapping($filename),
            $mapping
        );
        
        file_put_contents($mappingFile, json_encode($mappings, JSON_PRETTY_PRINT));
        
        return ['success' => true];
    }
    
    /**
     * Generate CSV for admin review
     */
    public function generateReviewCSV($batchId = null) {
        $documents = $this->getPendingDocuments();
        
        $csv = "Line,Filename,Type,GL Account,Supplier,VAT Amount,Cashbook,Dimension1,Dimension2,Payment Status,Confidence\n";
        
        $line = 1;
        foreach ($documents as $doc) {
            $map = $doc['mapping'];
            $csv .= "{$line},{$doc['filename']},{$doc['classification']['type']},";
            $csv .= "{$map['gl_account']},{$map['supplier']},";
            $csv .= "{$map['vat_amount']},{$map['cashbook']},";
            $csv .= "{$map['dimension1']},{$map['dimension2']},";
            $csv .= "{$map['payment_status']},{$doc['classification']['confidence']}\n";
            $line++;
        }
        
        return $csv;
    }
    
    /**
     * Generate exception report
     */
    public function generateExceptionReport() {
        $documents = $this->getPendingDocuments();
        $exceptions = [];
        
        foreach ($documents as $doc) {
            // Check for exceptions
            $map = $doc['mapping'];
            
            // Low confidence exception
            if ($doc['classification']['confidence'] < 0.7) {
                $exceptions[] = [
                    'type' => 'low_confidence',
                    'filename' => $doc['filename'],
                    'issue' => 'AI confidence below 70%',
                    'action' => 'Manual review required'
                ];
            }
            
            // Missing supplier
            if (empty($map['supplier'])) {
                $exceptions[] = [
                    'type' => 'missing_supplier',
                    'filename' => $doc['filename'],
                    'issue' => 'Supplier not identified',
                    'action' => 'Enter supplier name'
                ];
            }
            
            // Unpaid with VAT
            if ($map['vat_amount'] > 0 && $map['payment_status'] === 'unpaid') {
                $exceptions[] = [
                    'type' => 'unpaid_vat',
                    'filename' => $doc['filename'],
                    'issue' => 'Unpaid invoice with VAT',
                    'action' => 'Verify payment status'
                ];
            }
        }
        
        return $exceptions;
    }
    
    /**
     * Generate acknowledgment report
     */
    public function generateAcknowledgmentReport($batchId, $recipientEmail) {
        $documents = $this->getPendingDocuments();
        
        $stats = [
            'total' => count($documents),
            'by_type' => []
        ];
        
        foreach ($documents as $doc) {
            $type = $doc['classification']['type'];
            $stats['by_type'][$type] = ($stats['by_type'][$type] ?? 0) + 1;
        }
        
        $report = [
            'batch_id' => $batchId,
            'recipient' => $recipientEmail,
            'timestamp' => date('Y-m-d H:i:s'),
            'statistics' => $stats,
            'documents' => $documents
        ];
        
        return $report;
    }
    
    /**
     * Mark document as reviewed
     */
    public function markReviewed($filename) {
        $reviewFile = $this->processedDir . '/reviewed.json';
        
        $reviewed = [];
        if (file_exists($reviewFile)) {
            $reviewed = json_decode(file_get_contents($reviewFile), true) ?: [];
        }
        
        $reviewed[$filename] = [
            'reviewed_by' => 'admin',
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => 'reviewed'
        ];
        
        file_put_contents($reviewFile, json_encode($reviewed, JSON_PRETTY_PRINT));
        
        return ['success' => true];
    }
    
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $stats = [
            'pending_review' => 0,
            'reviewed_today' => 0,
            'posted_to_fa' => 0,
            'exceptions' => count($this->generateExceptionReport()),
            'by_type' => []
        ];
        
        // Count pending
        $pending = $this->getPendingDocuments();
        $stats['pending_review'] = count($pending);
        
        foreach ($pending as $doc) {
            $type = $doc['classification']['type'];
            $stats['by_type'][$type] = ($stats['by_type'][$type] ?? 0) + 1;
        }
        
        return $stats;
    }
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $interface = new AdminReviewInterface();
    
    switch ($_POST['action']) {
        case 'get_pending':
            echo json_encode($interface->getPendingDocuments());
            break;
            
        case 'update_mapping':
            $filename = $_POST['filename'] ?? '';
            $mapping = $_POST['mapping'] ?? [];
            echo json_encode($interface->updateMapping($filename, $mapping));
            break;
            
        case 'generate_csv':
            echo $interface->generateReviewCSV();
            break;
            
        case 'get_exceptions':
            echo json_encode($interface->generateExceptionReport());
            break;
            
        case 'get_stats':
            echo json_encode($interface->getDashboardStats());
            break;
            
        default:
            echo json_encode(['error' => 'Unknown action']);
    }
    exit;
}
