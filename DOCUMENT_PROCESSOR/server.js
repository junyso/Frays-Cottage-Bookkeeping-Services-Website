/**
 * Document Processing Pipeline - Main Server
 * 
 * Complete document management system:
 * - Multi-source input (WhatsApp, Email, Web)
 * - Document processing (rename, watermark)
 * - OneDrive integration
 * - Admin backend
 * - FA posting workflow
 */

const express = require('express');
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const cors = require('cors');
const { v4: uuidv4 } = require('uuid');
const sharp = require('sharp');  // For watermarking
const fetch = require('node-fetch');
const dotenv = require('dotenv');

dotenv.config();

const app = express();
const PORT = process.env.PORT || 3001;

// ============================================
// CONFIGURATION & SETTINGS
// ============================================

const CONFIG = {
    // Filing reference format: {TYPE}-{CLIENT}-{YYYY-MM-DD}-{NNN}
    referenceFormat: '{TYPE}-{CLIENT}-{YYYY-MM-DD}-{NNN}',
    
    // OneDrive paths
    onedrive: {
        basePath: process.env.ONEDRIVE_PATH || '/Users/julianuseya/OneDrive',
        incomingPath: 'Business/Incoming/Invoices/Unprocessed',
        processedPath: 'Business/Invoices/Processed',
        rejectedPath: 'Business/Invoices/Rejected'
    },
    
    // WhatsApp
    whatsapp: {
        enabled: process.env.WHATSAPP_ENABLED === 'true',
        businessId: process.env.WHATSAPP_BUSINESS_ID,
        phoneId: process.env.WHATSAPP_PHONE_ID,
        webhookToken: process.env.WHATSAPP_WEBHOOK_TOKEN,
        clerkNumber: process.env.WHATSAPP_CLERK_NUMBER
    },
    
    // Email
    email: {
        enabled: process.env.EMAIL_ENABLED === 'true',
        imapHost: process.env.EMAIL_IMAP_HOST,
        imapUser: process.env.EMAIL_IMAP_USER,
        imapPassword: process.env.EMAIL_IMAP_PASSWORD
    },
    
    // FrontAccounting
    fa: {
        apiUrl: process.env.FA_API_URL || 'http://localhost:8080/fa/api',
        username: process.env.FA_USERNAME || 'admin',
        password: process.env.FA_PASSWORD || 'password'
    },
    
    // Processing
    processing: {
        minLegibilityScore: 70,  // Minimum OCR confidence
        watermarkText: 'PENDING PROCESSING - DO NOT USE',
        supportedFormats: ['.pdf', '.jpg', '.jpeg', '.png', '.tiff'],
        maxFileSize: 10 * 1024 * 1024  // 10MB
    }
};

// ============================================
// DATA STORES
// ============================================

// In-memory storage (use database in production)
let documentStore = {
    documents: [],
    batches: [],
    settings: {
        referencePrefix: 'INV',
        defaultClient: 'DEFAULT',
        autoPostToFA: false,
        notificationChannels: ['whatsapp'],
        clerkContact: '+267XXXXXXXX',
        clerkEmail: 'clerk@company.co.bw'
    }
};

// Load from file if exists
const DATA_FILE = path.join(__dirname, 'data', 'store.json');
if (fs.existsSync(DATA_FILE)) {
    try {
        const data = JSON.parse(fs.readFileSync(DATA_FILE, 'utf8'));
        documentStore = { ...documentStore, ...data };
    } catch (e) {
        console.warn('Could not load data store:', e.message);
    }
}

// ============================================
// MIDDLEWARE
// ============================================

app.use(cors());
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));
app.use(express.static('public'));
app.use('/api/uploads', express.static('uploads'));

// Ensure directories exist
const dirs = ['uploads', 'processed', 'rejected', 'data'];
dirs.forEach(dir => {
    const dirPath = path.join(__dirname, dir);
    if (!fs.existsSync(dirPath)) {
        fs.mkdirSync(dirPath, { recursive: true });
    }
});

// Multer config for file uploads
const storage = multer.diskStorage({
    destination: (req, file, cb) => cb(null, 'uploads/'),
    filename: (req, file, cb) => {
        const ext = path.extname(file.originalname);
        cb(null, `${Date.now()}-${uuidv4()}${ext}`);
    }
});
const upload = multer({ 
    storage,
    limits: { fileSize: CONFIG.processing.maxFileSize }
});

// Save data store periodically
function saveData() {
    try {
        fs.writeFileSync(DATA_FILE, JSON.stringify(documentStore, null, 2));
    } catch (e) {
        console.error('Failed to save data:', e.message);
    }
}

// ============================================
// DOCUMENT PROCESSING ENGINE
// ============================================

class DocumentProcessor {
    /**
     * Generate filing reference
     */
    generateReference(docType, clientCode) {
        const now = new Date();
        const dateStr = now.toISOString().split('T')[0];
        const count = documentStore.documents.filter(d => {
            const ref = d.reference || '';
            return ref.includes(dateStr) && ref.startsWith(docType);
        }).length + 1;
        
        const ref = CONFIG.referenceFormat
            .replace('{TYPE}', docType)
            .replace('{CLIENT}', clientCode)
            .replace('{YYYY-MM-DD}', dateStr)
            .replace('{NNN}', String(count).padStart(3, '0'));
        
        return ref;
    }
    
    /**
     * Process document: rename, watermark, check legibility
     */
    async processDocument(file, metadata = {}) {
        const result = {
            id: uuidv4(),
            originalName: file.originalname || file.filename,
            uploadedAt: new Date().toISOString(),
            uploadedVia: metadata.source || 'web',
            clientCode: metadata.clientCode || documentStore.settings.defaultClient,
            status: 'pending',
            processingLog: []
        };
        
        try {
            // Step 1: Generate reference
            const docType = metadata.docType || 'INV';
            result.reference = this.generateReference(docType, result.clientCode);
            result.processingLog.push({ step: 'reference', ref: result.reference });
            
            // Step 2: Get file info
            const filePath = path.join(__dirname, 'uploads', file.filename);
            const fileStats = fs.statSync(filePath);
            result.fileSize = fileStats.size;
            result.processingLog.push({ step: 'file_info', size: result.fileSize });
            
            // Step 3: Convert to standard format (PDF)
            const pdfPath = await this.convertToPDF(filePath, file.originalname);
            result.pdfPath = pdfPath;
            result.processingLog.push({ step: 'converted', path: pdfPath });
            
            // Step 4: Watermark
            const watermarkedPath = await this.addWatermark(pdfPath, result.reference);
            result.watermarkedPath = watermarkedPath;
            result.processingLog.push({ step: 'watermarked', path: watermarkedPath });
            
            // Step 5: Skip OCR legibility check (Tesseract has network issues)
            // Document will be reviewed manually
            result.legibilityScore = null;
            result.ocrPreview = '';
            result.needsManualReview = true;
            result.processingLog.push({ 
                step: 'legibility', 
                skipped: true,
                reason: 'OCR unavailable - marked for manual review'
            });
            
            // Step 6: File document (always goes to incoming for manual review)
            await this.moveToOneDrive(watermarkedPath, result.reference, 'incoming');
            result.status = 'manual_review';
            result.processingLog.push({ 
                step: 'filed', 
                location: 'OneDrive/Incoming',
                status: 'manual_review'
            });
            
            // Send notification to clerk
            await this.notifyClerk(result);
            result.processingLog.push({ step: 'notification_sent' });
            
        } catch (error) {
            result.status = 'error';
            result.error = error.message;
            result.processingLog.push({ step: 'error', message: error.message });
        }
        
        // Save to store
        documentStore.documents.unshift(result);
        saveData();
        
        return result;
    }
    
    async convertToPDF(filePath, originalName) {
        const ext = path.extname(originalName).toLowerCase();
        
        if (ext === '.pdf') {
            return filePath;  // Already PDF
        }
        
        // Convert image to PDF using sharp
        const pdfPath = filePath.replace(/\.[^.]+$/, '.pdf');
        await sharp(filePath)
            .resize({ width: 2480, withoutEnlargement: true })  // A4 width at 300 DPI
            .flatten({ background: '#ffffff' })
            .toFormat('png')
            .toFile(pdfPath.replace('.pdf', '.png'))
            .then(() => {
                // For PDF conversion, we'll use the PNG
            });
        
        // Return PNG path for further processing
        return pdfPath.replace(/\.pdf$/, '.png');
    }
    
    async addWatermark(imagePath, reference) {
        const ext = path.extname(imagePath);
        const outputPath = imagePath.replace(ext, `_WM${ext}`);
        
        const watermarkText = `${CONFIG.processing.watermarkText} | REF: ${reference}`;
        
        try {
            await sharp(imagePath)
                .flatten({ background: '#ffffff' })
                .composite([{
                    input: Buffer.from(`
                        <svg width="200" height="100">
                            <rect width="100%" height="100%" fill="white" opacity="0.7"/>
                            <text x="10" y="30" font-size="16" fill="red">${watermarkText}</text>
                        </svg>
                    `),
                    top: 10,
                    left: 10
                }])
                .toFile(outputPath);
            
            return outputPath;
        } catch (error) {
            // If SVG composite fails, just copy
            fs.copyFileSync(imagePath, outputPath);
            return outputPath;
        }
    }
    
    async moveToOneDrive(filePath, reference, folder) {
        const onedriveBase = CONFIG.onedrive.basePath;
        const targetFolder = {
            'incoming': CONFIG.onedrive.incomingPath,
            'processed': CONFIG.onedrive.processedPath,
            'rejected': CONFIG.onedrive.rejectedPath
        }[folder] || CONFIG.onedrive.incomingPath;
        
        const targetDir = path.join(onedriveBase, targetFolder);
        const targetPath = path.join(targetDir, `${reference}${path.extname(filePath)}`);
        
        // Ensure directory exists
        if (!fs.existsSync(targetDir)) {
            fs.mkdirSync(targetDir, { recursive: true });
        }
        
        // Copy file
        fs.copyFileSync(filePath, targetPath);
        
        return targetPath;
    }
    
    async notifyClerk(document) {
        const settings = documentStore.settings;
        const message = `📄 Document Uploaded\n\n` +
            `Reference: ${document.reference}\n` +
            `Client: ${document.clientCode}\n` +
            `Status: Ready for review\n` +
            `Legibility Score: ${document.legibilityScore?.toFixed(1)}%\n\n` +
            `Please review and post to FA.`;
        
        // WhatsApp notification
        if (settings.notificationChannels.includes('whatsapp') && CONFIG.whatsapp.enabled) {
            await this.sendWhatsApp(CONFIG.whatsapp.clerkNumber, message);
        }
        
        // Email notification
        if (settings.notificationChannels.includes('email')) {
            await this.sendEmail(settings.clerkEmail, `Document: ${document.reference}`, message);
        }
        
        return true;
    }
    
    async sendWhatsApp(phone, message) {
        if (!CONFIG.whatsapp.enabled) {
            console.log('[WHATSAPP DISABLED] Would send:', message);
            return;
        }
        
        try {
            const response = await fetch('https://graph.facebook.com/v18.0/' + CONFIG.whatsapp.phoneId + '/messages', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${process.env.WHATSAPP_TOKEN}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    messaging_product: 'whatsapp',
                    to: phone,
                    type: 'text',
                    text: { body: message }
                })
            });
            
            const data = await response.json();
            console.log('[WHATSAPP] Sent:', data);
            return data;
        } catch (error) {
            console.error('[WHATSAPP] Failed:', error.message);
            return { error: error.message };
        }
    }
    
    async sendEmail(to, subject, body) {
        console.log(`[EMAIL] Would send to ${to}: ${subject}`);
        // Integrate with nodemailer for actual email
        return { status: 'queued' };
    }
}

const processor = new DocumentProcessor();

// ============================================
// API ROUTES - DOCUMENT UPLOAD
// ============================================

// Web upload
app.post('/api/upload', upload.array('documents', 20), async (req, res) => {
    try {
        const results = [];
        
        for (const file of req.files) {
            const result = await processor.processDocument(file, {
                source: 'web',
                clientCode: req.body.clientCode || documentStore.settings.defaultClient,
                docType: req.body.docType || 'INV'
            });
            results.push(result);
        }
        
        res.json({
            success: true,
            processed: results.filter(r => r.status !== 'error').length,
            rejected: results.filter(r => r.status === 'rejected').length,
            manualReview: results.filter(r => r.status === 'manual_review').length,
            results
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// WhatsApp webhook (receive documents)
app.post('/api/whatsapp/webhook', async (req, res) => {
    try {
        const messages = req.body.entry?.[0]?.changes?.[0]?.value?.messages || [];
        
        for (const msg of messages) {
            if (msg.type === 'document') {
                const document = msg.document;
                const phone = msg.from;
                
                // Download document
                const response = await fetch(document.id.startsWith('http') ? document.id : 
                    `https://graph.facebook.com/v18.0/${document.id}`, {
                    headers: { 'Authorization': `Bearer ${process.env.WHATSAPP_TOKEN}` }
                });
                
                const buffer = await response.buffer();
                const filename = `${Date.now()}-${document.filename || 'doc.pdf'}`;
                const filePath = path.join(__dirname, 'uploads', filename);
                fs.writeFileSync(filePath, buffer);
                
                // Process document
                await processor.processDocument({ filename, originalname: document.filename }, {
                    source: 'whatsapp',
                    clientCode: phone  // Use phone as client code temporarily
                });
            }
        }
        
        res.sendStatus(200);
    } catch (error) {
        console.error('WhatsApp webhook error:', error);
        res.sendStatus(500);
    }
});

// WhatsApp webhook verification
app.get('/api/whatsapp/webhook', (req, res) => {
    const mode = req.query['hub.mode'];
    const token = req.query['hub.verify_token'];
    const challenge = req.query['hub.challenge'];
    
    if (mode === 'subscribe' && token === CONFIG.whatsapp.webhookToken) {
        res.send(challenge);
    } else {
        res.sendStatus(403);
    }
});

// ============================================
// API ROUTES - ADMIN
// ============================================

// Get all documents
app.get('/api/documents', (req, res) => {
    const { status, client, from, to } = req.query;
    
    let docs = [...documentStore.documents];
    
    if (status) {
        docs = docs.filter(d => d.status === status);
    }
    if (client) {
        docs = docs.filter(d => d.clientCode === client);
    }
    if (from) {
        docs = docs.filter(d => new Date(d.uploadedAt) >= new Date(from));
    }
    if (to) {
        docs = docs.filter(d => new Date(d.uploadedAt) <= new Date(to));
    }
    
    res.json({ documents: docs.slice(0, 100) });  // Limit to 100
});

// Get document details
app.get('/api/documents/:id', (req, res) => {
    const doc = documentStore.documents.find(d => d.id === req.params.id);
    if (!doc) {
        return res.status(404).json({ error: 'Document not found' });
    }
    res.json(doc);
});

// Settings
app.get('/api/settings', (req, res) => {
    res.json(documentStore.settings);
});

app.put('/api/settings', (req, res) => {
    documentStore.settings = { ...documentStore.settings, ...req.body };
    saveData();
    res.json(documentStore.settings);
});

// ============================================
// API ROUTES - BATCH MANAGEMENT & FA POSTING
// ============================================

// Create batch from documents
app.post('/api/batches', (req, res) => {
    const { documentIds, clientCode } = req.body;
    
    const documents = documentStore.documents.filter(d => 
        documentIds.includes(d.id) && d.status === 'ready_for_review'
    );
    
    if (documents.length === 0) {
        return res.status(400).json({ error: 'No valid documents for batch' });
    }
    
    const batch = {
        id: uuidv4(),
        createdAt: new Date().toISOString(),
        createdBy: 'admin',
        clientCode: clientCode || documents[0].clientCode,
        documents: documents.map(d => d.id),
        status: 'pending_review',
        faPostStatus: null
    };
    
    documentStore.batches.unshift(batch);
    saveData();
    
    res.json(batch);
});

// Get all batches
app.get('/api/batches', (req, res) => {
    res.json({ 
        batches: documentStore.batches.slice(0, 50),
        stats: {
            pending: documentStore.batches.filter(b => b.status === 'pending_review').length,
            ready: documentStore.batches.filter(b => b.status === 'ready_for_fa').length,
            posted: documentStore.batches.filter(b => b.status === 'posted').length
        }
    });
});

// Get batch with full document data
app.get('/api/batches/:id', (req, res) => {
    const batch = documentStore.batches.find(b => b.id === req.params.id);
    if (!batch) {
        return res.status(404).json({ error: 'Batch not found' });
    }
    
    const documents = batch.documents.map(id => 
        documentStore.documents.find(d => d.id === id)
    ).filter(d => d);
    
    // Generate CSV preview
    const csvPreview = generateCSV(documents);
    
    res.json({ ...batch, documents, csvPreview });
});

// Approve batch for FA posting
app.post('/api/batches/:id/approve', (req, res) => {
    const batch = documentStore.batches.find(b => b.id === req.params.id);
    if (!batch) {
        return res.status(404).json({ error: 'Batch not found' });
    }
    
    batch.status = 'ready_for_fa';
    batch.approvedAt = new Date().toISOString();
    batch.approvedBy = req.body.approvedBy || 'admin';
    
    saveData();
    res.json(batch);
});

// Post batch to FrontAccounting
app.post('/api/batches/:id/post-to-fa', async (req, res) => {
    const batch = documentStore.batches.find(b => b.id === req.params.id);
    if (!batch) {
        return res.status(404).json({ error: 'Batch not found' });
    }
    
    try {
        const documents = batch.documents.map(id => 
            documentStore.documents.find(d => d.id === id)
        ).filter(d => d);
        
        // Convert to FA format
        const faData = convertToFAFormat(documents);
        
        // Post to FA API
        const faResponse = await postToFA(faData);
        
        if (faResponse.success) {
            batch.status = 'posted';
            batch.faPostStatus = {
                success: true,
                postedAt: new Date().toISOString(),
                transactionIds: faResponse.transactionIds
            };
            
            // Move documents to processed folder
            for (const doc of documents) {
                doc.status = 'posted';
                if (doc.watermarkedPath && fs.existsSync(doc.watermarkedPath)) {
                    await processor.moveToOneDrive(
                        doc.watermarkedPath, 
                        doc.reference, 
                        'processed'
                    );
                }
            }
        } else {
            batch.faPostStatus = {
                success: false,
                error: faResponse.error
            };
        }
        
        saveData();
        res.json(batch);
    } catch (error) {
        batch.faPostStatus = { success: false, error: error.message };
        saveData();
        res.status(500).json({ error: error.message });
    }
});

// Reject batch
app.post('/api/batches/:id/reject', (req, res) => {
    const batch = documentStore.batches.find(b => b.id === req.params.id);
    if (!batch) {
        return res.status(404).json({ error: 'Batch not found' });
    }
    
    batch.status = 'rejected';
    batch.rejectedAt = new Date().toISOString();
    batch.rejectionReason = req.body.reason;
    
    saveData();
    res.json(batch);
});

// Generate CSV for FA import
function generateCSV(documents) {
    const headers = ['Type', 'Date', 'Reference', 'Description', 'Amount', 'Tax', 'Account', 'Customer', 'DueDate'];
    const rows = documents.map(doc => [
        'INVOICE',
        doc.uploadedAt.split('T')[0],
        doc.reference,
        `Invoice from ${doc.clientCode}`,
        doc.ocrPreview?.match(/[\$£€BWP]?\s?[\d,]+\.?\d{0,2}/)?.[0] || '0.00',
        '',
        '4000',  // Sales account
        doc.clientCode,
        ''
    ]);
    
    return [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
}

// Convert to FA API format
function convertToFAFormat(documents) {
    return documents.map(doc => ({
        type: 'FA',
        trans_type: 10,  // Sales invoice
        trans_no: 0,  // New
        tran_date: doc.uploadedAt.split('T')[0],
        reference: doc.reference,
        debtor_id: doc.clientCode,
        items: [{
            stock_id: 'INV',
            description: `Invoice ${doc.reference}`,
            quantity: 1,
            unit_price: parseFloat(doc.ocrPreview?.match(/[\d,]+\.?\d{0,2}/)?.[0] || '0').toFixed(2),
            tax_type_id: 1
        }]
    }));
}

// Post to FrontAccounting API
async function postToFA(data) {
    try {
        const response = await fetch(`${CONFIG.fa.apiUrl}/sales/invoice`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Basic ' + Buffer.from(`${CONFIG.fa.username}:${CONFIG.fa.password}`).toString('base64')
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        return { success: response.ok, transactionIds: [result.id], ...result };
    } catch (error) {
        console.error('FA API Error:', error.message);
        return { success: false, error: error.message };
    }
}

// ============================================
// DASHBOARD & ANALYTICS
// ============================================

app.get('/api/dashboard', (req, res) => {
    const docs = documentStore.documents;
    const batches = documentStore.batches;
    
    const today = new Date().toISOString().split('T')[0];
    const todayDocs = docs.filter(d => d.uploadedAt.startsWith(today));
    
    res.json({
        stats: {
            totalDocuments: docs.length,
            todayDocuments: todayDocs.length,
            pendingReview: docs.filter(d => d.status === 'ready_for_review').length,
            postedToday: docs.filter(d => d.status === 'posted' && d.uploadedAt.startsWith(today)).length,
            rejectedToday: docs.filter(d => d.status === 'rejected' && d.uploadedAt.startsWith(today)).length,
            legibilityScore: docs.length > 0 
                ? (docs.reduce((sum, d) => sum + (d.legibilityScore || 0), 0) / docs.length).toFixed(1)
                : 0
        },
        recentDocuments: docs.slice(0, 5),
        recentBatches: batches.slice(0, 5),
        settings: documentStore.settings
    });
});

// ============================================
// START SERVER
// ============================================

app.listen(PORT, () => {
    console.log(`
╔════════════════════════════════════════════════════════════════╗
║         DOCUMENT PROCESSOR & FA INTEGRATION SERVER            ║
╠════════════════════════════════════════════════════════════════╣
║  Server:        http://localhost:${PORT}                        ║
║  Admin Panel:   http://localhost:${PORT}/admin.html             ║
║                                                                 ║
║  Endpoints:                                                     ║
║  POST /api/upload              - Upload documents              ║
║  POST /api/whatsapp/webhook    - WhatsApp integration         ║
║  GET  /api/documents           - List documents                ║
║  GET  /api/batches             - List batches                  ║
║  POST /api/batches/:id/post    - Post to FA                    ║
║  GET  /api/dashboard           - Dashboard stats               ║
║  GET  /api/settings            - Settings                       ║
║  PUT  /api/settings            - Update settings                ║
╚════════════════════════════════════════════════════════════════╝
    `);
});

module.exports = app;
