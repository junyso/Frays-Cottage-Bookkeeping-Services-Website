<?php
/**
 * Email Document Processor
 * Process documents uploaded via uploads@bookkeeping.co.bw
 */

if (!defined('APP_LOADED')) {
    require_once __DIR__ . '/../includes/config.php';
}

class EmailDocumentProcessor {
    
    private $mailHost;
    private $mailUsername;
    private $mailPassword;
    private $mailBox;
    
    public function __construct() {
        $this->mailHost = getenv('MAIL_HOST') ?: '{imap.gmail.com:993/imap/ssl}';
        $this->mailUsername = getenv('MAIL_USERNAME') ?: 'uploads@bookkeeping.co.bw';
        $this->mailPassword = getenv('MAIL_PASSWORD') ?: '';
        
        $this->connect();
    }
    
    /**
     * Connect to mail server
     */
    private function connect() {
        try {
            $this->mailBox = imap_open(
                $this->mailHost,
                $this->mailUsername,
                $this->mailPassword
            );
        } catch (Exception $e) {
            error_log("Email connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Check for new emails
     */
    public function checkNewEmails() {
        if (!$this->mailBox) {
            return ['error' => 'Not connected to mail server'];
        }
        
        $emails = imap_search($this->mailBox, 'UNSEEN');
        
        if (empty($emails)) {
            return ['count' => 0, 'emails' => []];
        }
        
        $emailList = [];
        foreach ($emails as $emailNum) {
            $emailList[] = $this->processEmail($emailNum);
        }
        
        return ['count' => count($emailList), 'emails' => $emailList];
    }
    
    /**
     * Process single email
     */
    private function processEmail($emailNum) {
        $overview = imap_fetch_overview($this->mailBox, $emailNum, FT_UID);
        $email = $overview[0];
        
        $result = [
            'uid' => $email->uid,
            'from' => $email->from,
            'subject' => $email->subject,
            'date' => $email->date,
            'attachments' => [],
            'processed' => false
        ];
        
        // Get attachments
        $attachments = $this->getAttachments($emailNum);
        $result['attachments'] = $attachments;
        
        // Get body (for context)
        $body = $this->getBody($emailNum);
        $result['body'] = $body;
        
        // Process attachments
        if (!empty($attachments)) {
            $result['processed'] = $this->processAttachments($emailNum, $attachments, $email);
        }
        
        return $result;
    }
    
    /**
     * Get email attachments
     */
    private function getAttachments($emailNum) {
        $attachments = [];
        
        $structure = imap_fetchstructure($this->mailBox, $emailNum, FT_UID);
        
        if (isset($structure->parts) && count($structure->parts) > 0) {
            foreach ($structure->parts as $index => $part) {
                if ($part->ifdsp) {
                    $filename = $part->dspfilename;
                    if (!empty($filename)) {
                        $attachments[] = [
                            'filename' => $filename,
                            'part_number' => $index + 1,
                            'encoding' => $part->encoding,
                            'size' => $part->bytes
                        ];
                    }
                }
            }
        }
        
        return $attachments;
    }
    
    /**
     * Download attachment
     */
    private function downloadAttachment($emailNum, $attachment) {
        $partNumber = $attachment['part_number'];
        $filename = $attachment['filename'];
        
        // Get attachment content
        $content = imap_fetchbody($this->mailBox, $emailNum, $partNumber, FT_UID);
        
        // Decode based on encoding
        switch ($attachment['encoding']) {
            case 3: // BASE64
                $content = imap_base64($content);
                break;
            case 4: // QUOTED-PRINTABLE
                $content = imap_qprint($content);
                break;
        }
        
        // Sanitize filename
        $safeFilename = $this->sanitizeFilename($filename);
        
        // Save to uploads directory
        $localPath = DOCAI_UPLOAD_DIR . '/' . uniqid() . '_' . $safeFilename;
        file_put_contents($localPath, $content);
        
        return [
            'filename' => $filename,
            'local_path' => $localPath,
            'size' => filesize($localPath)
        ];
    }
    
    /**
     * Process all attachments from email
     */
    private function processAttachments($emailNum, $attachments, $email) {
        $processed = [];
        
        foreach ($attachments as $attachment) {
            $downloaded = $this->downloadAttachment($emailNum, $attachment);
            
            // Get sender email for client identification
            $senderEmail = $this->extractEmail($email->from);
            $clientId = $this->identifyClient($senderEmail);
            
            $processed[] = [
                'filename' => $downloaded['filename'],
                'local_path' => $downloaded['local_path'],
                'sender' => $senderEmail,
                'client_id' => $clientId,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
        
        return $processed;
    }
    
    /**
     * Get email body
     */
    private function getBody($emailNum) {
        $body = imap_body($this->mailBox, $emailNum, FT_UID);
        
        // Try to get plain text
        $body = imap_fetchbody($this->mailBox, $emailNum, '1.1', FT_UID);
        if (empty(trim($body))) {
            $body = imap_fetchbody($this->mailBox, $emailNum, '1', FT_UID);
        }
        
        return imap_utf8($body);
    }
    
    /**
     * Extract email from "From" header
     */
    private function extractEmail($fromHeader) {
        if (preg_match('/<(.+)>/', $fromHeader, $matches)) {
            return $matches[1];
        }
        return $fromHeader;
    }
    
    /**
     * Identify client from email
     */
    private function identifyClient($email) {
        // Extract domain or check known clients
        $domain = strtolower(substr(strrchr($email, '@'), 1));
        
        // Map common domains to clients
        $domainMap = [
            'gmail.com' => 'personal',
            'yahoo.com' => 'personal',
            'frayscottage.co.bw' => 'frayscottage',
            // Add more mappings as needed
        ];
        
        return $domainMap[$domain] ?? $domain;
    }
    
    /**
     * Sanitize filename
     */
    private function sanitizeFilename($filename) {
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    }
    
    /**
     * Mark email as read/processed
     */
    public function markAsRead($emailNum) {
        imap_setflag_full($this->mailBox, $emailNum, '\\Seen', ST_UID);
    }
    
    /**
     * Delete processed email
     */
    public function deleteEmail($emailNum) {
        imap_delete($this->mailBox, $emailNum, FT_UID);
        imap_expunge($this->mailBox);
    }
    
    /**
     * Get unread count
     */
    public function getUnreadCount() {
        if (!$this->mailBox) {
            return 0;
        }
        
        $emails = imap_search($this->mailBox, 'UNSEEN');
        return count($emails ?: []);
    }
    
    /**
     * Close connection
     */
    public function close() {
        if ($this->mailBox) {
            imap_close($this->mailBox, CL_EXPUNGE);
        }
    }
}

/**
 * Batch Email Processor
 * Process all pending emails
 */
class BatchEmailProcessor {
    
    private $processor;
    private $batchSize = 20; // Max 20 emails per batch
    
    public function __construct() {
        $this->processor = new EmailDocumentProcessor();
    }
    
    /**
     * Process batch of emails
     */
    public function processBatch() {
        $result = [
            'processed' => 0,
            'failed' => 0,
            'total_attachments' => 0,
            'documents' => [],
            'errors' => []
        ];
        
        $emails = $this->processor->checkNewEmails();
        
        if (isset($emails['error'])) {
            return $emails;
        }
        
        $count = 0;
        foreach ($emails['emails'] as $email) {
            if ($count >= $this->batchSize) {
                break;
            }
            
            if ($email['processed']) {
                $result['processed']++;
                $result['total_attachments'] += count($email['attachments']);
                $result['documents'] = array_merge($result['documents'], $email['attachments']);
                
                // Mark as read
                $this->processor->markAsRead($email['uid']);
            } else {
                $result['failed']++;
                $result['errors'][] = [
                    'subject' => $email['subject'],
                    'error' => 'No valid attachments'
                ];
            }
            
            $count++;
        }
        
        return $result;
    }
    
    /**
     * Generate acknowledgment email
     */
    public function generateAcknowledgment($batchResult, $recipientEmail) {
        $subject = "Document Batch Received - Batch #" . date('Ymd');
        
        $body = <<<EMAIL
Dear Client,

Thank you for submitting your documents. We have received your batch.

BATCH SUMMARY:
- Total Documents: {$batchResult['total_attachments']}
- Successfully Processed: {$batchResult['processed']}

NEXT STEPS:
Our team will review and process your documents within 24 hours.

If you have any questions, please reply to this email.

Best regards,
Frays Cottage Bookkeeping Services

---
This is an automated message. Please do not reply directly to this email.
EMAIL;
        
        return [
            'to' => $recipientEmail,
            'subject' => $subject,
            'body' => $body
        ];
    }
}

// AJAX handler
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $processor = new BatchEmailProcessor();
    
    switch ($_POST['action']) {
        case 'check':
            $result = $processor->processBatch();
            echo json_encode($result);
            break;
            
        case 'get_unread':
            $count = (new EmailDocumentProcessor())->getUnreadCount();
            echo json_encode(['count' => $count]);
            break;
            
        default:
            echo json_encode(['error' => 'Unknown action']);
    }
    exit;
}
