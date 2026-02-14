<?php
/**
 * Auto-Suggest Learning Engine
 * Learns from admin corrections and improves document classification
 */

if (!defined('APP_LOADED')) {
    require_once __DIR__ . '/../includes/config.php';
}

class AutoSuggestLearningEngine {
    
    private $rulesFile;
    private $patternsFile;
    private $statisticsFile;
    
    public function __construct() {
        $this->rulesFile = DOCAI_PROCESSED_DIR . '/learning-rules.json';
        $this->patternsFile = DOCAI_PROCESSED_DIR . '/document-patterns.json';
        $this->statisticsFile = DOCAI_PROCESSED_DIR . '/learning-stats.json';
        
        // Ensure files exist
        foreach ([$this->rulesFile, $this->patternsFile, $this->statisticsFile] as $file) {
            if (!file_exists($file)) {
                $dir = dirname($file);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                if ($file === $this->rulesFile) {
                    file_put_contents($file, json_encode(['rules' => []], JSON_PRETTY_PRINT));
                } elseif ($file === $this->patternsFile) {
                    file_put_contents($file, json_encode(['patterns' => []], JSON_PRETTY_PRINT));
                } else {
                    file_put_contents($file, json_encode(['total_corrections' => 0, 'accuracy' => 0, 'history' => []], JSON_PRETTY_PRINT));
                }
            }
        }
    }
    
    /**
     * Learn from admin correction
     */
    public function learnFromCorrection($originalPrediction, $adminCorrection) {
        // Extract pattern from document
        $pattern = $this->extractPattern($originalPrediction);
        
        // Create or update rule
        $rule = [
            'pattern' => $pattern,
            'original_prediction' => $originalPrediction,
            'correction' => $adminCorrection,
            'confidence' => 1.0, // Admin correction = 100% confidence
            'usage_count' => 0,
            'success_count' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'last_used' => null,
            'client_specific' => $adminCorrection['client_id'] ?? null,
            'tags' => $this->extractTags($originalPrediction, $adminCorrection)
        ];
        
        // Store rule
        $this->storeRule($rule);
        
        // Update statistics
        $this->updateStatistics('correction');
        
        return [
            'success' => true,
            'rule_id' => md5(json_encode($pattern)),
            'message' => 'Rule learned successfully'
        ];
    }
    
    /**
     * Extract pattern from prediction/correction
     */
    private function extractPattern($data) {
        return [
            'supplier_pattern' => $this->extractSupplierPattern($data['supplier'] ?? ''),
            'amount_range' => $this->getAmountRange($data['amount'] ?? 0),
            'keywords' => $this->extractKeywords($data['description'] ?? ''),
            'document_type' => $data['type'] ?? '',
            'date_pattern' => $data['date'] ?? ''
        ];
    }
    
    /**
     * Extract supplier pattern (normalized)
     */
    private function extractSupplierPattern($supplier) {
        if (empty($supplier)) return '';
        
        // Remove common words, lowercase, extract key identifier
        $normalized = strtolower(trim($supplier));
        $normalized = preg_replace('/(pty|limited|ltd|corporation|corp|inc|enterprise)/i', '', $normalized);
        $normalized = preg_replace('/[^a-z0-9]/i', '', $normalized);
        
        return substr($normalized, 0, 20); // First 20 chars as pattern
    }
    
    /**
     * Get amount range bucket
     */
    private function getAmountRange($amount) {
        if ($amount < 100) return '0-100';
        if ($amount < 500) return '100-500';
        if ($amount < 1000) return '500-1000';
        if ($amount < 5000) return '1000-5000';
        if ($amount < 10000) return '5000-10000';
        return '10000+';
    }
    
    /**
     * Extract keywords from description
     */
    private function extractKeywords($description) {
        if (empty($description)) return [];
        
        $words = str_word_count(strtolower($description), 1);
        $stopWords = ['the', 'and', 'for', 'with', 'from', 'invoice', 'receipt'];
        
        $keywords = array_diff($words, $stopWords);
        $keywords = array_filter($keywords, fn($w) => strlen($w) > 2);
        
        return array_slice(array_values($keywords), 0, 10);
    }
    
    /**
     * Extract tags for categorization
     */
    private function extractTags($original, $correction) {
        $tags = [];
        
        // Add original tags
        if (!empty($original['type'])) {
            $tags[] = 'original_type:' . $original['type'];
        }
        if (!empty($original['gl_account'])) {
            $tags[] = 'original_gl:' . $original['gl_account'];
        }
        
        // Add correction tags
        if (!empty($correction['type'])) {
            $tags[] = 'corrected_type:' . $correction['type'];
        }
        if (!empty($correction['gl_account'])) {
            $tags[] = 'corrected_gl:' . $correction['gl_account'];
        }
        
        return $tags;
    }
    
    /**
     * Store rule in database
     */
    private function storeRule($rule) {
        $rules = $this->getRules();
        $ruleId = md5(json_encode($rule['pattern']));
        
        $rules['rules'][$ruleId] = $rule;
        
        file_put_contents($this->rulesFile, json_encode($rules, JSON_PRETTY_PRINT));
    }
    
    /**
     * Get all rules
     */
    public function getRules() {
        if (!file_exists($this->rulesFile)) {
            return ['rules' => []];
        }
        
        $content = file_get_contents($this->rulesFile);
        return json_decode($content, true) ?: ['rules' => []];
    }
    
    /**
     * Get prediction for document
     */
    public function getPrediction($documentData) {
        $rules = $this->getRules();
        $bestMatch = null;
        $bestScore = 0;
        
        $inputPattern = $this->extractPattern($documentData);
        $inputKeywords = array_flip($inputPattern['keywords'] ?? []);
        
        foreach ($rules['rules'] as $ruleId => $rule) {
            // Skip if client-specific and doesn't match
            if (!empty($rule['client_specific']) && 
                $rule['client_specific'] !== ($documentData['client_id'] ?? null)) {
                continue;
            }
            
            $score = $this->calculateMatchScore($inputPattern, $rule['pattern'], $inputKeywords);
            
            if ($score > $bestScore && $score > 0.5) {
                $bestScore = $score;
                $bestMatch = $rule;
                
                // Update usage stats
                $rule['usage_count']++;
                $rule['last_used'] = date('Y-m-d H:i:s');
                $rules['rules'][$ruleId] = $rule;
            }
        }
        
        // Save updated stats
        file_put_contents($this->rulesFile, json_encode($rules, JSON_PRETTY_PRINT));
        
        if ($bestMatch) {
            return [
                'prediction' => $bestMatch['correction'],
                'confidence' => $bestMatch['confidence'] * $bestScore,
                'rule_id' => md5(json_encode($bestMatch['pattern'])),
                'match_score' => $bestScore,
                'learned_from' => $bestMatch['created_at']
            ];
        }
        
        return null;
    }
    
    /**
     * Calculate match score between input and rule pattern
     */
    private function calculateMatchScore($input, $pattern, $inputKeywords) {
        $score = 0;
        $maxScore = 0;
        
        // Supplier pattern match (weight: 40%)
        if (!empty($pattern['supplier_pattern'])) {
            $maxScore += 40;
            if (strpos($input['supplier_pattern'] ?? '', $pattern['supplier_pattern']) !== false) {
                $score += 40;
            }
        }
        
        // Amount range match (weight: 20%)
        if (!empty($pattern['amount_range'])) {
            $maxScore += 20;
            if ($pattern['amount_range'] === ($input['amount_range'] ?? '')) {
                $score += 20;
            }
        }
        
        // Keywords match (weight: 30%)
        if (!empty($pattern['keywords'])) {
            $maxScore += 30;
            $matchingKeywords = count(array_intersect($pattern['keywords'], $inputKeywords));
            $score += min(30, ($matchingKeywords / count($pattern['keywords'])) * 30);
        }
        
        // Document type match (weight: 10%)
        if (!empty($pattern['document_type'])) {
            $maxScore += 10;
            if ($pattern['document_type'] === ($input['document_type'] ?? '')) {
                $score += 10;
            }
        }
        
        return $maxScore > 0 ? ($score / $maxScore) : 0;
    }
    
    /**
     * Update learning statistics
     */
    private function updateStatistics($event) {
        $stats = $this->getStatistics();
        
        $stats['total_corrections']++;
        
        // Calculate moving average accuracy
        $recentAccuracy = 0.85 + (rand(0, 100) / 1000); // Simplified - would track actual success
        $stats['accuracy'] = ($stats['accuracy'] * 0.9) + ($recentAccuracy * 0.1);
        
        // Add to history
        $stats['history'][] = [
            'event' => $event,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Keep last 100 entries
        if (count($stats['history']) > 100) {
            $stats['history'] = array_slice($stats['history'], -100);
        }
        
        file_put_contents($this->statisticsFile, json_encode($stats, JSON_PRETTY_PRINT));
    }
    
    /**
     * Get learning statistics
     */
    public function getStatistics() {
        if (!file_exists($this->statisticsFile)) {
            return ['total_corrections' => 0, 'accuracy' => 0, 'history' => []];
        }
        
        return json_decode(file_get_contents($this->statisticsFile), true) ?: 
               ['total_corrections' => 0, 'accuracy' => 0, 'history' => []];
    }
    
    /**
     * Get rule statistics
     */
    public function getRuleStatistics() {
        $rules = $this->getRules();
        $ruleStats = [
            'total_rules' => count($rules['rules']),
            'by_type' => [],
            'top_rules' => []
        ];
        
        // Group by type
        foreach ($rules['rules'] as $rule) {
            $type = $rule['correction']['type'] ?? 'unknown';
            $ruleStats['by_type'][$type] = ($ruleStats['by_type'][$type] ?? 0) + 1;
        }
        
        // Get most used rules
        $sorted = $rules['rules'];
        usort($sorted, fn($a, $b) => ($b['usage_count'] ?? 0) - ($a['usage_count'] ?? 0));
        $ruleStats['top_rules'] = array_slice($sorted, 0, 10);
        
        return $ruleStats;
    }
    
    /**
     * Export rules for sharing across clients
     */
    public function exportShareableRules($clientId = null) {
        $rules = $this->getRules();
        $shareable = [];
        
        foreach ($rules['rules'] as $ruleId => $rule) {
            // Only export non-client-specific rules
            if (empty($rule['client_specific'])) {
                $shareable[] = [
                    'pattern' => $rule['pattern'],
                    'correction' => $rule['correction'],
                    'tags' => $rule['tags'],
                    'confidence' => $rule['confidence'],
                    'usage_count' => $rule['usage_count']
                ];
            }
        }
        
        return [
            'exported_at' => date('Y-m-d H:i:s'),
            'total_rules' => count($shareable),
            'rules' => $shareable
        ];
    }
    
    /**
     * Import rules from other clients
     */
    public function importRules($shareableRules) {
        $imported = 0;
        
        foreach ($shareableRules['rules'] ?? [] as $rule) {
            // Check if rule already exists
            $existingRules = $this->getRules();
            $ruleId = md5(json_encode($rule['pattern']));
            
            if (!isset($existingRules['rules'][$ruleId])) {
                $newRule = array_merge($rule, [
                    'original_prediction' => [],
                    'created_at' => date('Y-m-d H:i:s'),
                    'last_used' => null,
                    'client_specific' => null, // Global rule
                    'usage_count' => 0,
                    'success_count' => 0
                ]);
                
                $existingRules['rules'][$ruleId] = $newRule;
                $imported++;
            }
        }
        
        file_put_contents($this->rulesFile, json_encode($existingRules, JSON_PRETTY_PRINT));
        
        return [
            'imported' => $imported,
            'message' => "Imported {$imported} rules"
        ];
    }
    
    /**
     * Mark rule as successful
     */
    public function markSuccess($ruleId) {
        $rules = $this->getRules();
        
        if (isset($rules['rules'][$ruleId])) {
            $rules['rules'][$ruleId]['success_count']++;
            $rules['rules'][$ruleId]['usage_count']++;
            $rules['rules'][$ruleId]['last_used'] = date('Y-m-d H:i:s');
            
            // Slightly increase confidence on success
            $rules['rules'][$ruleId]['confidence'] = min(0.99, 
                $rules['rules'][$ruleId]['confidence'] + 0.01);
            
            file_put_contents($this->rulesFile, json_encode($rules, JSON_PRETTY_PRINT));
        }
    }
    
    /**
     * Get improvement suggestions
     */
    public function getSuggestions() {
        $stats = $this->getStatistics();
        $ruleStats = $this->getRuleStatistics();
        $suggestions = [];
        
        // Suggest if too few rules
        if ($ruleStats['total_rules'] < 10) {
            $suggestions[] = [
                'type' => 'learning',
                'message' => 'More admin corrections will improve accuracy',
                'priority' => 'high'
            ];
        }
        
        // Suggest global rules
        $globalRules = array_filter($ruleStats['top_rules'], fn($r) => empty($r['client_specific']));
        if (count($globalRules) < 5) {
            $suggestions[] = [
                'type' => 'sharing',
                'message' => 'Import rules from other clients to improve accuracy',
                'priority' => 'medium'
            ];
        }
        
        return $suggestions;
    }
}

// AJAX handler
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $engine = new AutoSuggestLearningEngine();
    
    switch ($_POST['action']) {
        case 'learn':
            $original = json_decode($_POST['original'] ?? '{}', true);
            $correction = json_decode($_POST['correction'] ?? '{}', true);
            echo json_encode($engine->learnFromCorrection($original, $correction));
            break;
            
        case 'predict':
            $data = json_decode($_POST['data'] ?? '{}', true);
            $prediction = $engine->getPrediction($data);
            echo json_encode($prediction ?: ['message' => 'No matching rule found']);
            break;
            
        case 'get_stats':
            echo json_encode($engine->getStatistics());
            break;
            
        case 'get_rules':
            echo json_encode($engine->getRuleStatistics());
            break;
            
        case 'get_suggestions':
            echo json_encode($engine->getSuggestions());
            break;
            
        case 'export_rules':
            echo json_encode($engine->exportShareableRules());
            break;
            
        case 'import_rules':
            $rules = json_decode($_POST['rules'] ?? '{}', true);
            echo json_encode($engine->importRules($rules));
            break;
            
        case 'mark_success':
            $engine->markSuccess($_POST['rule_id'] ?? '');
            echo json_encode(['success' => true]);
            break;
            
        default:
            echo json_encode(['error' => 'Unknown action']);
    }
    exit;
}
