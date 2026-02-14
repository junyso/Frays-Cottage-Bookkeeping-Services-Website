<?php
/**
 * Main Document Processor Class
 * Integrates all document processing functionality
 */

if (!defined('APP_LOADED')) {
    require_once __DIR__ . '/../includes/config.php';
}

class DocumentProcessor {
    
    private $uploadDir;
    private $processedDir;
    private $exportsDir;
    
    public function __construct() {
        $this->uploadDir = DOCAI_UPLOAD_DIR;
        $this->processedDir = DOCAI_PROCESSED_DIR;
        $this->exportsDir = DOCAI_EXPORTS_DIR;
        
        // Ensure directories exist
        foreach ([$this->uploadDir, $this->processedDir, $this->exportsDir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Process single document
     */
    public function processDocument($filePath, $filename) {
        $result = [
            'filename' => $filename,
            'classification' => [],
            'mapping' => [],
            'status' => 'pending',
            'confidence' => 0,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Classify document
        $classification = $this->classifyDocument($filename);
        $result['classification'] = $classification;
        
        // Extract mapping
        $mapping = $this->extractMapping($filePath, $filename, $classification);
        $result['mapping'] = $mapping;
        
        // Calculate confidence
        $result['confidence'] = $this->calculateConfidence($classification, $mapping);
        
        // Save to pending
        $this->savePendingDocument($result);
        
        return $result;
    }
    
    /**
     * Batch process documents
     */
    public function batchProcess($files) {
        $results = [
            'total' => count($files),
            'processed' => 0,
            'duplicates' => 0,
            'errors' => 0,
            'documents' => []
        ];
        
        foreach ($files as $file) {
            $result = $this->processUploadedFile($file);
            
            if ($result['status'] === 'duplicate') {
                $results['duplicates']++;
            } elseif ($result['status'] === 'error') {
                $results['errors']++;
            } else {
                $results['processed']++;
            }
            
            $results['documents'][] = $result;
        }
        
        return $results;
    }
    
    /**
     * Process uploaded file
     */
    private function processUploadedFile($file) {
        $result = [
            'filename' => $file['name'],
            'size' => $file['size'],
            'type' => '',
            'status' => 'success',
            'message' => ''
        ];
        
        // Validate
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'pdf'])) {
            $result['status'] = 'error';
            $result['message'] = 'Invalid file type';
            return $result;
        }
        
        // Check size
        if ($file['size'] > DOC_MAX_FILE_SIZE) {
            $result['status'] = 'error';
            $result['message'] = 'File too large';
            return $result;
        }
        
        // Check duplicate
        $md5 = md5_file($file['tmp_name']);
        if ($this->isDuplicate($md5)) {
            $result['status'] = 'duplicate';
            $result['message'] = 'Duplicate detected';
            return $result;
        }
        
        // Save file
        $newFilename = uniqid() . '_' . sanitizeFilename($file['name']);
        $newPath = $this->uploadDir . '/' . $newFilename;
        
        if (!move_uploaded_file($file['tmp_name'], $newPath)) {
            $result['status'] = 'error';
            $result['message'] = 'Failed to save file';
            return $result;
        }
        
        // Classify
        $classification = $this->classifyDocument($file['name']);
        $result['type'] = $classification['type'];
        $result['confidence'] = $classification['confidence'];
        
        return $result;
    }
    
    /**
     * Check for duplicate
     */
    private function isDuplicate($md5) {
        $hashFile = $this->processedDir . '/file_hashes.json';
        
        if (file_exists($hashFile)) {
            $hashes = json_decode(file_get_contents($hashFile), true) ?: [];
            return isset($hashes[$md5]);
        }
        
        return false;
    }
    
    /**
     * Save document hash
     */
    private function saveDocumentHash($md5, $filename) {
        $hashFile = $this->processedDir . '/file_hashes.json';
        $hashes = [];
        
        if (file_exists($hashFile)) {
            $hashes = json_decode(file_get_contents($hashFile), true) ?: [];
        }
        
        $hashes[$md5] = [
            'filename' => $filename,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($hashFile, json_encode($hashes, JSON_PRETTY_PRINT));
    }
    
    /**
     * Classify document type
     */
    private function classifyDocument($filename) {
        $lower = strtolower($filename);
        
        $types = [
            'invoice' => ['invoice', 'inv', 'bill'],
            'receipt' => ['receipt', 'rcpt'],
            'statement' => ['statement', 'bank'],
            'waybill' => ['waybill', 'delivery', 'deliverynote'],
            'customs' => ['customs', 'import', 'clearance'],
            'pop' => ['proof', 'pop', 'payment']
        ];
        
        foreach ($types as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($lower, $keyword) !== false) {
                    return [
                        'type' => $type,
                        'confidence' => 0.85,
                        'matched_keyword' => $keyword
                    ];
                }
            }
        }
        
        // Default to invoice if unknown
        return [
            'type' => 'invoice',
            'confidence' => 0.5,
            'matched_keyword' => null
        ];
    }
    
    /**
     * Extract mapping suggestions
     */
    private function extractMapping($filePath, $filename, $classification) {
        $type = $classification['type'];
        
        // Default mappings by type
        $defaultMappings = [
            'invoice' => [
                'gl_account' => '5000-Purchases',
                'tax_type' => 'Input VAT',
                'payment_terms' => '30'
            ],
            'receipt' => [
                'gl_account' => '6100-Office Expenses',
                'tax_type' => 'Input VAT',
                'payment_terms' => 'Immediate'
            ],
            'statement' => [
                'gl_account' => '1002-Main Bank',
                'tax_type' => 'N/A',
                'payment_terms' => 'N/A'
            ],
            'waybill' => [
                'gl_account' => '5100-Cost of Sales',
                'tax_type' => 'Input VAT',
                'payment_terms' => '30'
            ],
            'customs' => [
                'gl_account' => '5100-Cost of Sales',
                'tax_type' => 'Import VAT',
                'payment_terms' => 'Immediate'
            ],
            'pop' => [
                'gl_account' => '1001-Petty Cash',
                'tax_type' => 'N/A',
                'payment_terms' => 'N/A'
            ]
        ];
        
        $mapping = $defaultMappings[$type] ?? $defaultMappings['invoice'];
        
        // Try to extract amount from filename
        if (preg_match('/(\d+(?:,\d{3})*(?:\.\d{2})?)/', $filename, $matches)) {
            $mapping['amount'] = floatval(str_replace(',', '', $matches[1]));
        }
        
        return $mapping;
    }
    
    /**
     * Calculate confidence score
     */
    private function calculateConfidence($classification, $mapping) {
        $confidence = $classification['confidence'];
        
        // Boost if we have supplier name
        if (!empty($mapping['supplier'])) {
            $confidence += 0.1;
        }
        
        // Boost if we have amount
        if (!empty($mapping['amount'])) {
            $confidence += 0.1;
        }
        
        return min(0.99, $confidence);
    }
    
    /**
     * Save pending document
     */
    private function savePendingDocument($result) {
        $pendingFile = $this->processedDir . '/pending_documents.json';
        $documents = [];
        
        if (file_exists($pendingFile)) {
            $documents = json_decode(file_get_contents($pendingFile), true) ?: [];
        }
        
        $documents[] = $result;
        
        file_put_contents($pendingFile, json_encode($documents, JSON_PRETTY_PRINT));
    }
    
    /**
     * Get pending documents
     */
    public function getPendingDocuments() {
        $pendingFile = $this->processedDir . '/pending_documents.json';
        
        if (!file_exists($pendingFile)) {
            return [];
        }
        
        return json_decode(file_get_contents($pendingFile), true) ?: [];
    }
    
    /**
     * Update document mapping
     */
    public function updateMapping($filename, $newMapping) {
        $pendingFile = $this->processedDir . '/pending_documents.json';
        $documents = [];
        
        if (file_exists($pendingFile)) {
            $documents = json_decode(file_get_contents($pendingFile), true) ?: [];
        }
        
        foreach ($documents as &$doc) {
            if ($doc['filename'] === $filename) {
                $doc['mapping'] = array_merge($doc['mapping'], $newMapping);
                $doc['status'] = 'ready';
                break;
            }
        }
        
        file_put_contents($pendingFile, json_encode($documents, JSON_PRETTY_PRINT));
        
        return ['success' => true];
    }
    
    /**
     * Mark document as reviewed
     */
    public function markReviewed($filename) {
        $pendingFile = $this->processedDir . '/pending_documents.json';
        $documents = [];
        
        if (file_exists($pendingFile)) {
            $documents = json_decode(file_get_contents($pendingFile), true) ?: [];
        }
        
        $documents = array_filter($documents, fn($d) => $d['filename'] !== $filename);
        
        file_put_contents($pendingFile, json_encode(array_values($documents), JSON_PRETTY_PRINT));
        
        return ['success' => true];
    }
}

// Helper function
function sanitizeFilename($filename) {
    $info = pathinfo($filename);
    $ext = $info['extension'];
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $info['filename']);
    return $name . '.' . $ext;
}
