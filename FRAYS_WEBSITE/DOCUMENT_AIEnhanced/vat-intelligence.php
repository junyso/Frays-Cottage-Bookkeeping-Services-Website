<?php
/**
 * VAT Intelligence Engine
 * Detect, validate, and process VAT from documents
 */

if (!defined('APP_LOADED')) {
    require_once __DIR__ . '/../includes/config.php';
}

class VATIntelligence {
    
    private $vatRates = [
        'BWP' => 0.12,  // Botswana VAT rate
        // Add other countries as needed
    ];
    
    private $nonClaimableCategories = [
        'travel',
        'entertainment', 
        'food',
        'accommodation',
        'motor_vehicle',
        'fuel' // May be restricted
    ];
    
    private $vatThreshold = 100; // Minimum for VAT claim
    
    /**
     * Extract VAT information from document
     */
    public function extractVAT($documentText, $filename) {
        $result = [
            'vat_detected' => false,
            'vat_number' => '',
            'vat_amount' => 0,
            'subtotal' => 0,
            'total' => 0,
            'is_claimable' => true,
            'validation_status' => '',
            'exceptions' => [],
            'confidence' => 0
        ];
        
        // Extract amounts
        $amounts = $this->extractAmounts($documentText);
        $result['subtotal'] = $amounts['subtotal'];
        $result['vat_amount'] = $amounts['vat'];
        $result['total'] = $amounts['total'];
        
        // Extract VAT number
        $vatNumber = $this->extractVATNumber($documentText);
        $result['vat_number'] = $vatNumber;
        $result['vat_detected'] = !empty($vatNumber);
        
        // Detect VAT rate
        $detectedRate = $this->detectVATRate($documentText);
        
        // Validate VAT
        if ($result['vat_detected']) {
            $validation = $this->validateVAT($result['vat_number'], $result['vat_amount'], $detectedRate);
            $result['validation_status'] = $validation['status'];
            $result['exceptions'] = $validation['exceptions'];
        }
        
        // Check if claimable
        $result['is_claimable'] = $this->isClaimable($documentText, $filename, $result['vat_amount']);
        
        // Calculate confidence
        $result['confidence'] = $this->calculateConfidence($result, $documentText);
        
        return $result;
    }
    
    /**
     * Extract monetary amounts from text
     */
    private function extractAmounts($text) {
        $amounts = [
            'total' => 0,
            'vat' => 0,
            'subtotal' => 0
        ];
        
        // Look for patterns like "Total: P1,234.56" or "VAT: P123.45"
        $patterns = [
            'total' => '/(?:total|amount due|grand total|sum total)[:\s]*B?P?[\d,]+\.\d{2}/i',
            'vat' => '/(?:vat|gst|tax)[:\s]*B?P?[\d,]+\.\d{2}/i',
            'subtotal' => '/(?:subtotal|sub total|net amount)[:\s]*B?P?[\d,]+\.\d{2}/i'
        ];
        
        foreach ($patterns as $key => $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[0] as $match) {
                    $amount = $this->parseAmount($match);
                    if ($amount > 0) {
                        $amounts[$key] = $amount;
                        break;
                    }
                }
            }
        }
        
        // If total found but not VAT/subtotal, estimate
        if ($amounts['total'] > 0 && $amounts['vat'] === 0) {
            // Estimate VAT at 12%
            $amounts['vat'] = round($amounts['total'] / 1.12 * 0.12, 2);
            $amounts['subtotal'] = $amounts['total'] - $amounts['vat'];
        }
        
        return $amounts;
    }
    
    /**
     * Parse amount from string
     */
    private function parseAmount($str) {
        $cleaned = preg_replace('/[^0-9.]/', '', $str);
        return floatval($cleaned);
    }
    
    /**
     * Extract VAT registration number
     */
    private function extractVATNumber($text) {
        $patterns = [
            // Botswana VAT numbers
            '/VAT\s*(?:No|Number|#)[:\s]*([A-Z0-9]{8,})/i',
            '/([A-Z0-9]{8,})\s*VAT/i',
            '/VAT\s*[:\s]*([A-Z0-9]+)/i',
            // Generic patterns
            '/Tax\s*(?:ID|Number)[:\s]*([A-Z0-9]+)/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $matches[1];
            }
        }
        
        return '';
    }
    
    /**
     * Detect VAT rate from document
     */
    private function detectVATRate($text) {
        // Look for explicit rate mention
        if (preg_match('/(\d+)%(?:\s*vat|\s*tax)/i', $text, $matches)) {
            return floatval($matches[1]) / 100;
        }
        
        // Default to Botswana rate
        return 0.12; // 12%
    }
    
    /**
     * Validate VAT number and amount
     */
    private function validateVAT($vatNumber, $vatAmount, $detectedRate) {
        $validation = [
            'status' => 'valid',
            'exceptions' => []
        ];
        
        // Validate VAT number format (simplified)
        if (!empty($vatNumber)) {
            if (strlen($vatNumber) < 6) {
                $validation['exceptions'][] = 'VAT number appears too short';
                $validation['status'] = 'warning';
            }
        } else {
            $validation['exceptions'][] = 'No VAT number found on document';
            $validation['status'] = 'warning';
        }
        
        // Validate VAT amount reasonableness
        if ($vatAmount > 0 && $vatAmount < 1) {
            $validation['exceptions'][] = 'VAT amount seems too low';
            $validation['status'] = 'warning';
        }
        
        return $validation;
    }
    
    /**
     * Check if VAT is claimable
     */
    private function isClaimable($text, $filename, $vatAmount) {
        $lowerText = strtolower($text);
        $lowerFilename = strtolower($filename);
        
        // Check for non-claimable categories
        foreach ($this->nonClaimableCategories as $category) {
            if (strpos($lowerText, $category) !== false || 
                strpos($lowerFilename, $category) !== false) {
                return false;
            }
        }
        
        // Check amount threshold
        if ($vatAmount > 0 && $vatAmount < $this->vatThreshold) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Calculate confidence score
     */
    private function calculateConfidence($result, $text) {
        $confidence = 0.3; // Base confidence
        
        // Increase if VAT detected
        if ($result['vat_detected']) {
            $confidence += 0.3;
        }
        
        // Increase if VAT number present
        if (!empty($result['vat_number'])) {
            $confidence += 0.2;
        }
        
        // Increase if amounts are consistent
        if ($result['total'] > 0 && $result['subtotal'] > 0) {
            $calculatedTotal = $result['subtotal'] + $result['vat_amount'];
            $difference = abs($result['total'] - $calculatedTotal);
            if ($difference < 1) { // Within P1
                $confidence += 0.2;
            }
        }
        
        return min(0.95, $confidence);
    }
    
    /**
     * Generate VAT report for batch
     */
    public function generateVATReport($documents) {
        $report = [
            'total_documents' => count($documents),
            'vat_detected' => 0,
            'vat_claimable' => 0,
            'vat_non_claimable' => 0,
            'total_vat_amount' => 0,
            'exceptions' => [],
            'by_category' => []
        ];
        
        foreach ($documents as $doc) {
            if ($doc['vat_detected']) {
                $report['vat_detected']++;
                
                if ($doc['is_claimable']) {
                    $report['vat_claimable']++;
                    $report['total_vat_amount'] += $doc['vat_amount'];
                } else {
                    $report['vat_non_claimable']++;
                }
                
                foreach ($doc['exceptions'] as $exception) {
                    $report['exceptions'][] = [
                        'document' => $doc['filename'],
                        'issue' => $exception
                    ];
                }
            }
        }
        
        return $report;
    }
    
    /**
     * Get VAT summary for FA posting
     */
    public function getVATSummary($documents) {
        $claimable = array_filter($documents, fn($d) => $d['is_claimable']);
        $nonClaimable = array_filter($documents, fn($d) => !$d['is_claimable']);
        
        return [
            'claimable_count' => count($claimable),
            'non_claimable_count' => count($nonClaimable),
            'total_vat_claimable' => array_sum(array_column($claimable, 'vat_amount')),
            'total_vat_non_claimable' => array_sum(array_column($nonClaimable, 'vat_amount')),
            'total_vat' => array_sum(array_column($documents, 'vat_amount'))
        ];
    }
}

// VAT Exception Report Generator
class VATExceptionReporter {
    
    public function generateReport($vatResults) {
        $exceptions = [];
        
        foreach ($vatResults as $result) {
            if (!$result['is_claimable']) {
                $reason = $this->getNonClaimableReason($result);
                $exceptions[] = [
                    'filename' => $result['filename'],
                    'reason' => $reason,
                    'vat_amount' => $result['vat_amount']
                ];
            }
            
            foreach ($result['exceptions'] as $exception) {
                $exceptions[] = [
                    'filename' => $result['filename'],
                    'reason' => $exception,
                    'vat_amount' => $result['vat_amount']
                ];
            }
        }
        
        return $exceptions;
    }
    
    private function getNonClaimableReason($result) {
        if (empty($result['vat_number'])) {
            return 'Missing VAT registration number';
        }
        
        if ($result['vat_amount'] < 100) {
            return 'VAT amount below claimable threshold (P100)';
        }
        
        return 'Document falls under non-claimable category';
    }
}

// AJAX handler
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $vat = new VATIntelligence();
    
    if ($_POST['action'] === 'extract_vat') {
        $text = $_POST['text'] ?? '';
        $filename = $_POST['filename'] ?? '';
        $result = $vat->extractVAT($text, $filename);
        echo json_encode($result);
    }
    
    if ($_POST['action'] === 'vat_report') {
        $documents = json_decode($_POST['documents'] ?? '[]', true);
        $report = $vat->generateVATReport($documents);
        echo json_encode($report);
    }
    
    exit;
}
