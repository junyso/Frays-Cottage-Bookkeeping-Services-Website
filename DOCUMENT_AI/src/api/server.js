/**
 * Document AI Pipeline - Express API Server
 * FREE Tesseract OCR + GPT-3.5 Turbo
 */

require('dotenv').config();
const express = require('express');
const multer = require('multer');
const path = require('path');
const fs = require('fs');

const OCRProcessor = require('../processors/ocr');
const TextProcessor = require('../processors/textProcessor');
const CSVGenerator = require('../utils/csvGenerator');
const FAIntegration = require('../utils/faIntegration');

const app = express();
const PORT = process.env.PORT || 3000;

// CORS for local development
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    if (req.method === 'OPTIONS') {
        return res.sendStatus(200);
    }
    next();
});

// Initialize processors
const ocr = new OCRProcessor();
const textProc = new TextProcessor();
const csvGen = new CSVGenerator();
const fa = new FAIntegration();

// Configure multer for file uploads
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        const uploadDir = 'uploads';
        if (!fs.existsSync(uploadDir)) {
            fs.mkdirSync(uploadDir, { recursive: true });
        }
        cb(null, uploadDir);
    },
    filename: (req, file, cb) => {
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, uniqueSuffix + path.extname(file.originalname));
    }
});

const upload = multer({ 
    storage,
    limits: { fileSize: 10 * 1024 * 1024 }, // 10MB limit
    fileFilter: (req, file, cb) => {
        const allowedTypes = /jpeg|jpg|png|pdf|tiff|bmp/;
        const extname = allowedTypes.test(path.extname(file.originalname).toLowerCase());
        const mimetype = allowedTypes.test(file.mimetype);
        
        if (extname && mimetype) {
            cb(null, true);
        } else {
            cb(new Error('Only images and PDFs are allowed'));
        }
    }
});

// Middleware
app.use(express.json());
app.use(express.static('public'));
app.use('/uploads', express.static('uploads'));
app.use('/exports', express.static('exports'));

// Health check
app.get('/api/health', (req, res) => {
    res.json({ 
        status: 'running',
        timestamp: new Date().toISOString(),
        version: '1.0.0'
    });
});

// Favicon placeholder
app.get('/favicon.ico', (req, res) => {
    res.status(204).send();
});

// Process single document
app.post('/api/process', upload.single('document'), async (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({ error: 'No file uploaded' });
        }

        const filePath = req.file.path;
        const startTime = Date.now();

        // Step 1: OCR Extraction (FREE)
        const ocrResult = await ocr.extractText(filePath);
        
        if (!ocrResult.success) {
            return res.status(500).json({ 
                error: 'OCR failed',
                details: ocrResult.error 
            });
        }

        // Step 2: AI Text Processing (GPT-3.5 Turbo - CHEAP)
        const processedResult = await textProc.processDocument(ocrResult.text);

        // Calculate stats
        const processingTime = Date.now() - startTime;
        
        const result = {
            success: true,
            file: req.file.filename,
            originalName: req.file.originalname,
            documentType: processedResult.documentType,
            extractedData: processedResult.extractedData,
            customerMatch: processedResult.customerMatch,
            processingTime: `${processingTime}ms`,
            cost: processedResult.cost.toFixed(4),
            tokens: processedResult.tokens
        };

        res.json(result);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// Process batch of documents
app.post('/api/process/batch', upload.array('documents', 20), async (req, res) => {
    try {
        if (!req.files || req.files.length === 0) {
            return res.status(400).json({ error: 'No files uploaded' });
        }

        const results = [];
        const startTime = Date.now();

        for (const file of req.files) {
            console.log(`Processing: ${file.originalname}`);
            
            const ocrResult = await ocr.extractText(file.path);
            console.log(`OCR result: success=${ocrResult.success}, words=${ocrResult.words || 0}, error=${ocrResult.error || 'none'}`);
            
            let processed = {
                file: file.filename,
                originalName: file.originalname,
                ocrSuccess: ocrResult.success,
                confidence: ocrResult.confidence,
                ocrError: ocrResult.error
            };

            if (ocrResult.success) {
                const textResult = await textProc.processDocument(ocrResult.text);
                processed = {
                    ...processed,
                    documentType: textResult.documentType,
                    extractedData: textResult.extractedData,
                    customerMatch: textResult.customerMatch,
                    cost: textResult.cost,
                    success: textResult.success !== false
                };
            } else {
                processed.success = false;
            }

            results.push(processed);
        }

        const totalTime = Date.now() - startTime;

        res.json({
            success: true,
            processed: results.filter(r => r.success).length,
            failed: results.filter(r => !r.success).length,
            totalCost: results.reduce((sum, r) => sum + (r.cost || 0), 0),
            processingTime: `${totalTime}ms`,
            results
        });
    } catch (error) {
        console.error('Batch processing error:', error);
        res.status(500).json({ error: error.message });
    }
});

// Export to CSV
app.post('/api/export/csv', async (req, res) => {
    try {
        const { documents, format = 'fa' } = req.body;
        
        if (!documents || documents.length === 0) {
            return res.status(400).json({ error: 'No documents to export' });
        }

        const result = csvGen.generateFromDocuments(documents, format);
        
        res.json({
            success: true,
            ...result,
            downloadUrl: `/exports/${result.filename}`
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// Export to FA Import Format
app.post('/api/export/fa', async (req, res) => {
    try {
        const { documents } = req.body;
        
        if (!documents || documents.length === 0) {
            return res.status(400).json({ error: 'No documents to export' });
        }

        const filepath = csvGen.generateFAImport(documents);
        
        res.json({
            success: true,
            filepath,
            downloadUrl: `/exports/${path.basename(filepath)}`
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// FA Integration endpoints
app.get('/api/fa/test', async (req, res) => {
    const result = await fa.testConnection();
    res.json(result);
});

app.get('/api/fa/suppliers', async (req, res) => {
    const result = await fa.getSuppliers();
    res.json(result);
});

app.get('/api/fa/customers', async (req, res) => {
    const result = await fa.getCustomers();
    res.json(result);
});

app.post('/api/fa/supplier-invoice', async (req, res) => {
    const result = await fa.createSupplierInvoice(req.body);
    res.json(result);
});

app.post('/api/fa/customer-invoice', async (req, res) => {
    const result = await fa.createCustomerInvoice(req.body);
    res.json(result);
});

// List exports
app.get('/api/exports', (req, res) => {
    const files = csvGen.listExports();
    res.json({ files, count: files.length });
});

// Get processing statistics
app.get('/api/stats', async (req, res) => {
    const uploadDir = 'uploads';
    const processedDir = 'processed';
    
    const stats = {
        uploads: fs.existsSync(uploadDir) ? fs.readdirSync(uploadDir).length : 0,
        exports: csvGen.listExports().length,
        ocrEngine: 'Tesseract.js (FREE)',
        aiModel: 'GPT-3.5 Turbo ($0.0005/1K tokens)',
        estimatedCost: {
            perDocument: '$0.001',
            per100: '$0.10',
            per1000: '$1.00'
        }
    };
    
    res.json(stats);
});

// Error handling
app.use((err, req, res, next) => {
    console.error(err.stack);
    if (res && typeof res.status === 'function') {
        res.status(500).json({ error: err.message });
    } else {
        console.error('Error:', err.message);
    }
});

// Start server
app.listen(PORT, () => {
    console.log(`
╔═══════════════════════════════════════════════════════╗
║       Document AI Pipeline - API Server Started        ║
╠═══════════════════════════════════════════════════════╣
║  OCR Engine:    Tesseract.js (FREE)                   ║
║  AI Model:     GPT-3.5 Turbo ($0.0005/1K tokens)      ║
║  Server:       http://localhost:${PORT}                   ║
╠═══════════════════════════════════════════════════════╣
║  Endpoints:                                           ║
║  POST /api/process        - Process single doc       ║
║  POST /api/process/batch  - Process multiple docs    ║
║  POST /api/export/csv     - Export to CSV             ║
║  POST /api/export/fa      - Export to FA format       ║
║  GET  /api/stats          - View pricing stats        ║
╚═══════════════════════════════════════════════════════╝
    `);
});

module.exports = app;
