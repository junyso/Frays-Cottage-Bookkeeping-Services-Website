/**
 * Tesseract OCR Processor - Simple & Reliable
 */

const Tesseract = require('tesseract.js');
const pdfjsLib = require('pdfjs-dist/legacy/build/pdf.js');
const { execSync } = require('child_process');
const path = require('path');
const fs = require('fs');

// Set up pdfjs-dist worker
pdfjsLib.GlobalWorkerOptions.workerSrc = require('pdfjs-dist/legacy/build/pdf.worker.entry');

class OCRProcessor {
    constructor() {
        this.worker = null;
    }

    async initialize() {
        if (!this.worker) {
            this.worker = await Tesseract.createWorker('eng');
        }
        return this.worker;
    }

    async extractText(filePath) {
        const ext = path.extname(filePath).toLowerCase();
        
        if (ext === '.pdf') {
            return this.extractFromPDF(filePath);
        }
        
        return this.extractFromImage(filePath);
    }

    async extractFromPDF(pdfPath) {
        try {
            // Load PDF with pdfjs-dist (needs Uint8Array)
            const dataBuffer = fs.readFileSync(pdfPath);
            const uint8Array = new Uint8Array(dataBuffer);
            
            const loadingTask = pdfjsLib.getDocument({ data: uint8Array });
            const pdf = await loadingTask.promise;
            
            let fullText = '';
            
            // Extract text from all pages
            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                const page = await pdf.getPage(pageNum);
                const textContent = await page.getTextContent();
                const pageText = textContent.items.map(item => item.str).join(' ');
                fullText += `\n--- Page ${pageNum} ---\n${pageText}`;
            }

            if (fullText.length > 30) {
                return {
                    success: true,
                    text: fullText,
                    confidence: 100,
                    words: fullText.split(/\s+/).length,
                    paragraphs: fullText.split('\n\n').length,
                    processingTime: Date.now(),
                    isNativePDF: true
                };
            }
            
            // No text found - likely scanned PDF
            return await this.extractFromScannedPDF(pdfPath);
            
        } catch (error) {
            console.error('PDF extraction error:', error.message);
            return { success: false, error: error.message, text: null, confidence: 0 };
        }
    }

    async extractFromScannedPDF(pdfPath) {
        try {
            const tempDir = fs.mkdtempSync(path.join(require('os').tmpdir(), 'ocr-'));
            const baseName = path.basename(pdfPath, '.pdf');
            
            // Convert PDF to PNG with pdftoppm (200 DPI - good balance)
            execSync(`pdftoppm -png -rx 200 -ry 200 "${pdfPath}" "${path.join(tempDir, baseName)}"`);
            
            const imageFiles = fs.readdirSync(tempDir)
                .filter(f => f.startsWith(baseName) && f.endsWith('.png'))
                .sort();

            let fullText = '';
            let totalConfidence = 0;

            for (const imageFile of imageFiles) {
                const imagePath = path.join(tempDir, imageFile);
                const result = await this.extractFromImage(imagePath);
                
                if (result.success) {
                    fullText += `\n--- ${imageFile} ---\n${result.text}`;
                    totalConfidence += result.confidence;
                }
                
                fs.unlinkSync(imagePath);
            }

            fs.rmSync(tempDir, { recursive: true });

            return {
                success: fullText.length > 10,
                text: fullText,
                confidence: imageFiles.length > 0 ? totalConfidence / imageFiles.length : 0,
                words: fullText.split(/\s+/).length,
                paragraphs: fullText.split('\n\n').length,
                processingTime: Date.now(),
                isScannedPDF: true,
                pageCount: imageFiles.length
            };
        } catch (error) {
            return { success: false, error: error.message, text: null, confidence: 0 };
        }
    }

    async extractFromImage(imagePath) {
        try {
            if (!fs.existsSync(imagePath)) {
                return { success: false, error: 'File not found', text: null, confidence: 0 };
            }
            
            const worker = await this.initialize();
            const { data } = await worker.recognize(imagePath);
            
            return {
                success: true,
                text: data.text,
                confidence: data.confidence,
                words: data.words.length,
                paragraphs: data.paragraphs.length,
                processingTime: Date.now()
            };
        } catch (error) {
            return { success: false, error: error.message, text: null, confidence: 0 };
        }
    }

    async terminate() {
        if (this.worker) {
            await this.worker.terminate();
            this.worker = null;
        }
    }
}

module.exports = OCRProcessor;
