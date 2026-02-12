<?php
/**
 * Document AI Processor - Pure PHP Implementation
 * 
 * Integrated into Frays Website - runs on port 8080
 * No separate Node.js server needed!
 * 
 * Features:
 * - File upload & management
 * - OCR text extraction (Tesseract or fallback)
 * - CSV export
 * - FrontAccounting integration
 */

// Only load config if not API endpoint
$isAPI = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;
if (!$isAPI && !defined('APP_LOADED')) {
    require_once __DIR__ . '/../includes/config.php';
}

// Configuration
define('DOCAI_UPLOAD_DIR', __DIR__ . '/../uploads');
define('DOCAI_EXPORT_DIR', __DIR__ . '/../exports');
define('DOCAI_TEMP_DIR', __DIR__ . '/../processed');

/**
 * Main Document AI Processor Class
 */
class DocumentAI {
    
    private $uploadDir;
    private $exportDir;
    private $processedDir;
    
    public function __construct() {
        $this->uploadDir = DOCAI_UPLOAD_DIR;
        $this->exportDir = DOCAI_EXPORT_DIR;
        $this->processedDir = DOCAI_TEMP_DIR;
        
        // Ensure directories exist
        foreach ([$this->uploadDir, $this->exportDir, $this->processedDir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Process a single document
     */
    public function processDocument($file, $options = []) {
        $result = [
            'success' => false,
            'filename' => $file['name'],
            'extracted_text' => '',
            'data' => [],
            'confidence' => 0,
            'processing_time' => 0,
            'errors' => []
        ];
        
        $startTime = microtime(true);
        
        try {
            // Validate file
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Upload error: " . $file['error']);
            }
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $mimeType = mime_content_type($file['tmp_name']);
            
            if (!in_array($ext, $allowedExtensions) || !in_array($mimeType, $allowedTypes)) {
                throw new Exception("Invalid file type. Allowed: jpg, png, gif, pdf");
            }
            
            // Generate unique filename
            $newFilename = uniqid() . '_' . sanitizeFilename($file['name']);
            $destination = $this->uploadDir . '/' . $newFilename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new Exception("Failed to save uploaded file");
            }
            
            $result['saved_path'] = $destination;
            
            // Extract text based on file type
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $ocrResult = $this->performOCR($destination);
                $result['extracted_text'] = $ocrResult['text'];
                $result['confidence'] = $ocrResult['confidence'];
            } else {
                // For PDFs, try to extract text
                $result['extracted_text'] = $this->extractPDFText($destination);
                $result['confidence'] = 0.7; // Estimate for PDFs
            }
            
            // Parse extracted data
            $result['data'] = $this->parseDocumentData($result['extracted_text'], $ext);
            
            // Auto-export to CSV if requested
            if (!empty($options['auto_export'])) {
                $result['csv_path'] = $this->exportToCSV($result['data'], $file['name']);
            }
            
            // Push to FrontAccounting if requested
            if (!empty($options['push_fa'])) {
                $result['fa_result'] = $this->pushToFrontAccounting($result['data']);
            }
            
            $result['success'] = true;
            
        } catch (Exception $e) {
            $result['errors'][] = $e->getMessage();
        }
        
        $result['processing_time'] = round((microtime(true) - $startTime) * 1000, 2);
        
        return $result;
    }
    
    /**
     * Perform OCR on image
     */
    private function performOCR($imagePath) {
        $result = [
            'text' => '',
            'confidence' => 0
        ];
        
        // Try Tesseract OCR first (if installed)
        $tesseractPath = trim(shell_exec('which tesseract 2>/dev/null') ?? '');
        
        if (!empty($tesseractPath)) {
            $tempFile = tempnam(sys_get_temp_dir(), 'ocr_');
            $output = shell_exec("tesseract " . escapeshellarg($imagePath) . " " . escapeshellarg($tempFile) . " 2>&1");
            
            if (file_exists($tempFile . '.txt')) {
                $result['text'] = file_get_contents($tempFile . '.txt');
                $result['confidence'] = 0.85; // Tesseract is generally good
                @unlink($tempFile . '.txt');
            }
            @unlink($tempFile);
        } else {
            // Fallback: Simple text extraction using ImageMagick
            $imPath = trim(shell_exec('which convert 2>/dev/null') ?? '');
            
            if (!empty($imPath)) {
                $tempFile = tempnam(sys_get_temp_dir(), 'ocr_') . '.txt';
                $output = shell_exec("convert -density 300 -depth 8 -quality 85 " . escapeshellarg($imagePath) . " txt:- | grep -v '^0,0,0' | cut -d' ' -f4 | head -50");
                $result['text'] = $output ?: '';
                $result['confidence'] = 0.5; // Lower confidence without OCR
            } else {
                // Ultimate fallback: basic image info
                $result['text'] = $this->extractImageMetadata($imagePath);
                $result['confidence'] = 0.3;
            }
        }
        
        return $result;
    }
    
    /**
     * Extract text from PDF
     */
    private function extractPDFText($pdfPath) {
        // Try pdftotext first
        $pdftotextPath = trim(shell_exec('which pdftotext 2>/dev/null') ?? '');
        
        if (!empty($pdftotextPath)) {
            $tempFile = tempnam(sys_get_temp_dir(), 'pdf_') . '.txt';
            $output = shell_exec("pdftotext " . escapeshellarg($pdfPath) . " " . escapeshellarg($tempFile) . " 2>&1");
            
            if (file_exists($tempFile)) {
                $text = file_get_contents($tempFile);
                @unlink($tempFile);
                return $text;
            }
        }
        
        // Fallback: Use PHP's PDF functions or return placeholder
        return "[PDF text extraction requires pdftotext or similar tool]\n" .
               "File: " . basename($pdfPath) . "\n" .
               "Size: " . filesize($pdfPath) . " bytes";
    }
    
    /**
     * Extract image metadata as fallback
     */
    private function extractImageMetadata($imagePath) {
        $info = getimagesize($imagePath);
        return "[Image Metadata]\n" .
               "Width: " . ($info[0] ?? 'unknown') . "px\n" .
               "Height: " . ($info[1] ?? 'unknown') . "px\n" .
               "Type: " . ($info['mime'] ?? 'unknown') . "\n" .
               "For text extraction, install Tesseract OCR:\n" .
               "  macOS: brew install tesseract\n" .
               "  Linux: apt-get install tesseract-ocr\n" .
               "  Windows: Download from https://github.com/UB-Mannheim/tesseract/wiki";
    }
    
    /**
     * Parse document data from extracted text
     */
    private function parseDocumentData($text, $fileType) {
        $data = [
            'raw_text' => $text,
            'type' => $this->detectDocumentType($text, $fileType),
            'vendor' => '',
            'invoice_number' => '',
            'date' => '',
            'total' => 0,
            'subtotal' => 0,
            'vat' => 0,
            'line_items' => [],
            'confidence' => 0
        ];
        
        // Extract invoice number
        $invoicePatterns = [
            '/(?:invoice|inv|inv[.#]?|receipt|#)\s*[:#]?\s*([A-Z0-9-]+)/i',
            '/[#]\s*([A-Z0-9-]+)/'
        ];
        
        foreach ($invoicePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $data['invoice_number'] = trim($matches[1]);
                break;
            }
        }
        
        // Extract dates
        $datePatterns = [
            '/(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4})/',
            '/(\d{4}[\/\-\.]\d{1,2}[\/\-\.]\d{1,2})/',
            '/(?:date|dated)[:\s]*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4})/i'
        ];
        
        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $data['date'] = $this->normalizeDate($matches[1]);
                break;
            }
        }
        
        // Extract amounts (look for largest number after currency symbols)
        $amountPatterns = [
            '/P\s*([\d,]+\.?\d*)/',  // Botswana Pula
            '/BWP\s*([\d,]+\.?\d*)/',
            '/([\d,]+\.?\d*)\s*(?:P|BWP)/',
            '/Total[:\s]*P?\s*([\d,]+\.?\d*)/i',
            '/Amount[:\s]*P?\s*([\d,]+\.?\d*)/i'
        ];
        
        $amounts = [];
        foreach ($amountPatterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[1] as $amount) {
                    $clean = (float)str_replace(',', '', $amount);
                    if ($clean > 0) {
                        $amounts[] = $clean;
                    }
                }
            }
        }
        
        if (!empty($amounts)) {
            // Usually the largest amount is the total
            $data['total'] = max($amounts);
            // VAT is often 14% of total
            $data['vat'] = round($data['total'] * 0.14, 2);
            $data['subtotal'] = round($data['total'] - $data['vat'], 2);
        }
        
        // Extract vendor name (first substantial line that's not a header)
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        foreach ($lines as $i => $line) {
            if (strlen($line) > 3 && !preg_match('/^(invoice|total|date|payment|terms)/i', $line)) {
                if ($i < 3) { // Vendor is usually in first few lines
                    $data['vendor'] = $line;
                    break;
                }
            }
        }
        
        // Calculate confidence based on what we found
        $found = 0;
        if (!empty($data['invoice_number'])) $found++;
        if (!empty($data['date'])) $found++;
        if ($data['total'] > 0) $found++;
        if (!empty($data['vendor'])) $found++;
        $data['confidence'] = $found / 4;
        
        return $data;
    }
    
    /**
     * Detect document type from content
     */
    private function detectDocumentType($text, $fileType) {
        $textLower = strtolower($text);
        
        if (preg_match('/(invoice|inv|receipt|tax invoice)/', $textLower)) {
            return 'invoice';
        }
        if (preg_match('/(statement|account)/', $textLower)) {
            return 'statement';
        }
        if (preg_match('/(waybill|delivery|dispatch)/', $textLower)) {
            return 'waybill';
        }
        if (preg_match('/(purchase|order|purchase order)/', $textLower)) {
            return 'purchase_order';
        }
        
        // Default based on file extension
        return match($fileType) {
            'pdf' => 'invoice',
            default => 'general'
        };
    }
    
    /**
     * Normalize date to YYYY-MM-DD format
     */
    private function normalizeDate($dateStr) {
        $formats = [
            'd/m/Y', 'd-m-Y', 'd.m.Y',
            'm/d/Y', 'm-d-Y',
            'Y/m/d', 'Y-m-d'
        ];
        
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $dateStr);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }
        
        return $dateStr; // Return original if parsing fails
    }
    
    /**
     * Export data to CSV
     */
    public function exportToCSV($data, $originalFilename) {
        $baseName = pathinfo($originalFilename, PATHINFO_FILENAME);
        $filename = 'docai_' . $baseName . '_' . date('Y-m-d_His') . '.csv';
        $filepath = $this->exportDir . '/' . $filename;
        
        $headers = [
            'Invoice Number', 'Date', 'Vendor', 'Subtotal', 'VAT', 'Total',
            'Document Type', 'Confidence', 'Raw Text'
        ];
        
        $row = [
            $data['invoice_number'] ?? '',
            $data['date'] ?? '',
            $data['vendor'] ?? '',
            $data['subtotal'] ?? 0,
            $data['vat'] ?? 0,
            $data['total'] ?? 0,
            $data['type'] ?? 'general',
            $data['confidence'] ?? 0,
            // Don't include full raw text in CSV, just preview
            substr($data['raw_text'] ?? '', 0, 100) . '...'
        ];
        
        $handle = fopen($filepath, 'w');
        fputcsv($handle, $headers);
        fputcsv($handle, $row);
        fclose($handle);
        
        return $filepath;
    }
    
    /**
     * Push data to FrontAccounting
     */
    private function pushToFrontAccounting($data) {
        // This would call the FA API Gateway
        // For now, return placeholder result
        
        return [
            'success' => false,
            'message' => 'FrontAccounting integration requires FA API Gateway',
            'data' => $data
        ];
    }
    
    /**
     * List processed documents
     */
    public function listDocuments() {
        $documents = [];
        
        foreach ([$this->uploadDir, $this->processedDir] as $dir) {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    $documents[] = [
                        'filename' => basename($file),
                        'path' => $file,
                        'size' => filesize($file),
                        'modified' => date('Y-m-d H:i:s', filemtime($file)),
                        'type' => mime_content_type($file)
                    ];
                }
            }
        }
        
        return $documents;
    }
    
    /**
     * Delete a document
     */
    public function deleteDocument($filename) {
        $paths = [
            $this->uploadDir . '/' . $filename,
            $this->processedDir . '/' . $filename,
            $this->exportDir . '/' . $filename
        ];
        
        $deleted = false;
        foreach ($paths as $path) {
            if (file_exists($path)) {
                unlink($path);
                $deleted = true;
            }
        }
        
        return $deleted;
    }
    
    /**
     * Get storage stats
     */
    public function getStats() {
        return [
            'uploads_count' => count(glob($this->uploadDir . '/*')),
            'uploads_size' => $this->getDirSize($this->uploadDir),
            'exports_count' => count(glob($this->exportDir . '/*')),
            'exports_size' => $this->getDirSize($this->exportDir),
            'processed_count' => count(glob($this->processedDir . '/*')),
            'processed_size' => $this->getDirSize($this->processedDir)
        ];
    }
    
    private function getDirSize($dir) {
        $size = 0;
        foreach (glob($dir . '/*') as $file) {
            if (is_file($file)) {
                $size += filesize($file);
            }
        }
        return $size;
    }
}

/**
 * Helper: Sanitize filename (only if not already defined)
 */
if (!function_exists('sanitizeFilename')) {
    function sanitizeFilename($filename) {
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    }
}

/**
 * API Endpoint Handler
 */
function handleAPIRequest() {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    // Remove /api/document-ai.php or /api/document-ai prefix
    $path = preg_replace('#^/api/document-ai\.php#', '', $path);
    $path = preg_replace('#^/api/document-ai#', '', $path);
    $path = trim($path, '/');
    
    $docAI = new DocumentAI();
    
    // Health check
    if ($path === 'health' || $path === '') {
        header('Content-Type: application/json');
        echo json_encode([
            'service' => 'Document AI (PHP)',
            'status' => 'running',
            'version' => '1.0.0',
            'timestamp' => date('c'),
            'features' => [
                'ocr' => !empty(shell_exec('which tesseract 2>/dev/null')),
                'pdf_text' => !empty(shell_exec('which pdftotext 2>/dev/null')),
                'fa_integration' => false
            ],
            'stats' => $docAI->getStats()
        ]);
        return;
    }
    
    // List documents
    if ($path === 'documents' && $method === 'GET') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'documents' => $docAI->listDocuments()
        ]);
        return;
    }
    
    // Get stats
    if ($path === 'stats' && $method === 'GET') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'stats' => $docAI->getStats()
        ]);
        return;
    }
    
    // Process single document
    if ($path === 'process' && $method === 'POST') {
        if (!isset($_FILES['document'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No document uploaded']);
            return;
        }
        
        $options = [
            'auto_ocr' => !empty($_POST['auto_ocr']),
            'auto_export' => !empty($_POST['auto_export']),
            'push_fa' => !empty($_POST['push_fa'])
        ];
        
        $result = $docAI->processDocument($_FILES['document'], $options);
        
        header('Content-Type: application/json');
        echo json_encode($result);
        return;
    }
    
    // Delete document
    if (preg_match('#^delete/(.+)$#', $path, $matches) && $method === 'DELETE') {
        $filename = urldecode($matches[1]);
        $deleted = $docAI->deleteDocument($filename);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $deleted,
            'filename' => $filename
        ]);
        return;
    }
    
    // Export to CSV
    if ($path === 'export/csv' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $filepath = $docAI->exportToCSV($data['data'] ?? [], $data['filename'] ?? 'document');
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => file_exists($filepath),
            'filepath' => basename($filepath),
            'download_url' => '/exports/' . basename($filepath)
        ]);
        return;
    }
    
    // 404
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
}

// Handle CLI
if (php_sapi_name() === 'cli') {
    echo "Document AI Processor - Pure PHP\n";
    echo "================================\n\n";
    
    $docAI = new DocumentAI();
    echo "Stats:\n";
    print_r($docAI->getStats());
}

// Handle web API requests
if (php_sapi_name() !== 'cli') {
    handleAPIRequest();
}
