<?php
/**
 * Enhanced Document Upload System
 * Max 100 files, progress tracking, classification, duplicate detection
 */

if (!defined('APP_LOADED')) {
    require_once __DIR__ . '/../includes/config.php';
}

class EnhancedDocumentUpload {
    
    private $uploadDir;
    private $processedDir;
    private $exportDir;
    private $maxFiles = 100;
    private $maxFileSize = 10485760; // 10MB
    
    public function __construct() {
        $this->uploadDir = DOCAI_UPLOAD_DIR;
        $this->processedDir = DOCAI_PROCESSED_DIR;
        $this->exportDir = DOCAI_EXPORT_DIR;
        
        foreach ([$this->uploadDir, $this->processedDir, $this->exportDir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Process batch upload (max 100 files)
     */
    public function processBatchUpload($files) {
        $results = [
            'total' => 0,
            'successful' => 0,
            'failed' => 0,
            'duplicates' => 0,
            'documents' => [],
            'errors' => []
        ];
        
        $totalFiles = count($files['name']);
        
        if ($totalFiles > $this->maxFiles) {
            return ['error' => "Maximum {$this->maxFiles} files allowed. Got {$totalFiles}"];
        }
        
        $results['total'] = $totalFiles;
        
        for ($i = 0; $i < $totalFiles; $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            $result = $this->processSingleFile($file);
            
            if ($result['status'] === 'duplicate') {
                $results['duplicates']++;
                $results['documents'][] = $result;
            } elseif ($result['status'] === 'success') {
                $results['successful']++;
                $results['documents'][] = $result;
            } else {
                $results['failed']++;
                $results['errors'][] = $result['error'];
            }
        }
        
        return $results;
    }
    
    /**
     * Process single file with classification
     */
    private function processSingleFile($file) {
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'status' => 'error',
                'error' => "Upload error: " . $file['error'],
                'filename' => $file['name']
            ];
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            return [
                'status' => 'error',
                'error' => "File too large. Max 10MB allowed.",
                'filename' => $file['name']
            ];
        }
        
        // Validate type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mimeType = mime_content_type($file['tmp_name']);
        
        if (!in_array($ext, $allowedExtensions) || !in_array($mimeType, $allowedTypes)) {
            return [
                'status' => 'error',
                'error' => "Invalid file type. Allowed: jpg, png, gif, pdf",
                'filename' => $file['name']
            ];
        }
        
        // Check for duplicates
        $fingerprint = $this->calculateFingerprint($file['tmp_name']);
        if ($this->isDuplicate($fingerprint)) {
            return [
                'status' => 'duplicate',
                'filename' => $file['name'],
                'fingerprint' => $fingerprint,
                'message' => 'Duplicate document - already uploaded'
            ];
        }
        
        // Generate unique filename
        $newFilename = uniqid() . '_' . $this->sanitizeFilename($file['name']);
        $destination = $this->uploadDir . '/' . $newFilename;
        
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'status' => 'error',
                'error' => "Failed to save file",
                'filename' => $file['name']
            ];
        }
        
        // Classify document
        $classification = $this->classifyDocument($destination, $file['name']);
        
        // Store fingerprint
        $this->storeFingerprint($fingerprint, $newFilename, $classification['type']);
        
        return [
            'status' => 'success',
            'filename' => $file['name'],
            'saved_as' => $newFilename,
            'path' => $destination,
            'size' => $file['size'],
            'type' => $classification['type'],
            'confidence' => $classification['confidence'],
            'fingerprint' => $fingerprint,
            'suggested_gl' => $classification['suggested_gl'],
            'suggested_dimension' => $classification['suggested_dimension'],
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Calculate document fingerprint for duplicate detection
     */
    private function calculateFingerprint($filePath) {
        $filesize = filesize($filePath);
        $hash = hash_file('md5', $filePath);
        return md5($hash . $filesize . filesize($filePath));
    }
    
    /**
     * Check if document is duplicate
     */
    private function isDuplicate($fingerprint) {
        $fingerprintFile = $this->processedDir . '/fingerprints.json';
        
        if (!file_exists($fingerprintFile)) {
            return false;
        }
        
        $fingerprints = json_decode(file_get_contents($fingerprintFile), true) ?: [];
        
        return isset($fingerprints[$fingerprint]);
    }
    
    /**
     * Store fingerprint for duplicate detection
     */
    private function storeFingerprint($fingerprint, $filename, $type) {
        $fingerprintFile = $this->processedDir . '/fingerprints.json';
        
        $fingerprints = [];
        if (file_exists($fingerprintFile)) {
            $fingerprints = json_decode(file_get_contents($fingerprintFile), true) ?: [];
        }
        
        $fingerprints[$fingerprint] = [
            'filename' => $filename,
            'type' => $type,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($fingerprintFile, json_encode($fingerprints, JSON_PRETTY_PRINT));
    }
    
    /**
     * Classify document type
     */
    private function classifyDocument($filePath, $filename) {
        $lowerFilename = strtolower($filename);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Text-based classification
        $typeIndicators = [
            'invoice' => ['invoice', 'inv-', 'tax invoice', 'receipt', 'official receipt'],
            'statement' => ['statement', 'bank statement', 'account statement'],
            'waybill' => ['waybill', 'delivery note', 'delivery order'],
            'customs' => ['customs', 'import declaration', 'duty document'],
            'pop' => ['proof of payment', 'pop-', 'payment proof', 'receipt payment']
        ];
        
        $documentText = '';
        
        // Extract text for classification
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            // Would use OCR here - simplified for now
            $documentText = $lowerFilename;
        } else {
            $documentText = $lowerFilename;
        }
        
        // Detect type
        $detectedType = 'expense'; // default
        $confidence = 0.5;
        $suggestedGL = '6100-Office Expenses';
        $suggestedDimension = '';
        
        foreach ($typeIndicators as $type => $indicators) {
            foreach ($indicators as $indicator) {
                if (strpos($documentText, $indicator) !== false) {
                    $detectedType = $type;
                    $confidence = 0.85;
                    
                    // Set default GL based on type
                    switch ($type) {
                        case 'invoice':
                            $suggestedGL = '5000-Purchases';
                            break;
                        case 'statement':
                            $suggestedGL = '1002-Bank Account';
                            break;
                        case 'waybill':
                            $suggestedGL = '5200-Transport Expenses';
                            break;
                        case 'customs':
                            $suggestedGL = '5400-Import Duties';
                            break;
                        case 'pop':
                            $suggestedGL = '1001-Petty Cash';
                            break;
                    }
                    break 2;
                }
            }
        }
        
        return [
            'type' => $detectedType,
            'confidence' => $confidence,
            'suggested_gl' => $suggestedGL,
            'suggested_dimension' => $suggestedDimension
        ];
    }
    
    /**
     * Sanitize filename
     */
    private function sanitizeFilename($filename) {
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    }
    
    /**
     * Get upload statistics
     */
    public function getStats() {
        $stats = [
            'total_uploaded' => 0,
            'by_type' => [],
            'recent' => []
        ];
        
        // Count uploaded files
        $uploadedFiles = glob($this->uploadDir . '/*');
        $stats['total_uploaded'] = count($uploadedFiles);
        
        // Count by type from fingerprints
        $fingerprintFile = $this->processedDir . '/fingerprints.json';
        if (file_exists($fingerprintFile)) {
            $fingerprints = json_decode(file_get_contents($fingerprintFile), true) ?: [];
            foreach ($fingerprints as $fp) {
                $type = $fp['type'] ?? 'unknown';
                $stats['by_type'][$type] = ($stats['by_type'][$type] ?? 0) + 1;
            }
        }
        
        return $stats;
    }
}

// AJAX handler for batch upload
if (isset($_POST['action']) && $_POST['action'] === 'batch_upload') {
    header('Content-Type: application/json');
    
    $uploader = new EnhancedDocumentUpload();
    $result = $uploader->processBatchUpload($_FILES['documents']);
    
    echo json_encode($result);
    exit;
}
