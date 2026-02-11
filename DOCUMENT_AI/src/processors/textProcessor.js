/**
 * Text Processor with AI Classification (Groq + OpenAI)
 * Cleans OCR text and structures document data
 */

const OpenAI = require('openai');
const fetch = require('node-fetch');
const dotenv = require('dotenv');

dotenv.config();

class TextProcessor {
    constructor() {
        // Initialize OpenAI if available
        this.openai = null;
        if (process.env.OPENAI_API_KEY && process.env.OPENAI_API_KEY.length > 10) {
            this.openai = new OpenAI({ apiKey: process.env.OPENAI_API_KEY });
        }
        
        // Groq API key (free, fast)
        this.groqKey = process.env.GROQ_API_KEY || '';
        this.hasAI = !!this.openai || (this.groqKey.length > 10);
        
        // Document classification prompts
        this.prompts = {
            invoice: `Extract invoice data (JSON):
{
  "vendor": "Company name",
  "invoice_number": "INV-123",
  "date": "YYYY-MM-DD",
  "due_date": "YYYY-MM-DD",
  "subtotal": 0.00,
  "tax": 0.00,
  "total": 0.00,
  "line_items": [{"description": "", "quantity": 1, "unit_price": 0.00, "total": 0.00}],
  "currency": "BWP/USD"
}`,
            receipt: `Extract receipt data (JSON):
{
  "store": "Store name",
  "date": "YYYY-MM-DD",
  "time": "HH:MM",
  "items": [{"name": "", "price": 0.00}],
  "subtotal": 0.00,
  "tax": 0.00,
  "total": 0.00,
  "payment_method": "CASH/CARD"
}`,
            waybill: `Extract waybill/courier data (JSON):
{
  "waybill_number": "",
  "date": "YYYY-MM-DD",
  "sender": {"name": "", "company": "", "phone": "", "address": ""},
  "receiver": {"name": "", "company": "", "phone": "", "address": ""},
  "shipment": {"pieces": 0, "weight": 0, "description": ""},
  "charges": {"shipping": 0, "taxes": 0, "insurance": 0, "total": 0}
}`,
            statement: `Extract bank statement data (JSON):
{
  "account": {"holder": "", "number": "", "period": ""},
  "opening_balance": 0.00,
  "closing_balance": 0.00,
  "transactions": [{"date": "", "description": "", "amount": 0.00, "type": "CREDIT/DEBIT"}]
}`,
            general: `Extract key information from document (JSON):
{
  "document_type": "",
  "date": "",
  "parties": [],
  "amounts": [],
  "reference_numbers": [],
  "summary": ""
}`
        };
    }

    /**
     * Clean OCR text before processing
     */
    cleanOCRText(text) {
        return text
            // Fix common OCR errors
            .replace(/\|/g, 'I')           // Pipe to I
            .replace(/0O/g, '00')          // Fix 0O confusion
            .replace(/\s+/g, ' ')          // Normalize spaces
            // Remove page markers
            .replace(/--- Page \d+ ---/g, '')
            // Fix common misreads
            .replace(/SHiPP/g, 'SHIP')
            .replace(/RECEl/g, 'REC')
            .replace(/ACCOUNT/g, 'ACCOUNT')
            .replace(/NUMBER/g, 'NUMBER')
            // Clean up formatting
            .replace(/\n{3,}/g, '\n\n')
            .trim();
    }

    /**
     * Process document - clean, classify, extract
     */
    async processDocument(text, docType = null) {
        // Clean OCR text
        const cleanedText = this.cleanOCRText(text);
        
        // Auto-classify if not provided
        if (!docType) {
            docType = await this.classifyDocument(cleanedText);
        }
        
        // Get structured extraction from AI
        const extraction = await this.extractData(cleanedText, docType);
        
        return {
            documentType: docType,
            cleanedText: cleanedText.substring(0, 500) + '...',
            extractedData: extraction.data,
            customerMatch: extraction.match,
            cost: extraction.cost,
            success: true
        };
    }

    /**
     * Classify document type using AI
     */
    async classifyDocument(text) {
        if (!this.hasAI) {
            return this.basicClassify(text);
        }
        
        try {
            const response = await this.callAI(
                'Classify this document: invoice, receipt, waybill, statement, contract, or general. Reply with one word only.',
                text.substring(0, 500)
            );
            return response.trim().toLowerCase();
        } catch (error) {
            console.warn('Classification failed:', error.message);
            return this.basicClassify(text);
        }
    }

    /**
     * Basic keyword classification (fallback)
     */
    basicClassify(text) {
        const lower = text.toLowerCase();
        if (lower.includes('invoice') || lower.includes('inv')) return 'invoice';
        if (lower.includes('receipt') || lower.includes('paid')) return 'receipt';
        if (lower.includes('waybill') || lower.includes('courier') || lower.includes('shipment')) return 'waybill';
        if (lower.includes('statement') || lower.includes('account') || lower.includes('bank')) return 'statement';
        if (lower.includes('contract') || lower.includes('agreement')) return 'contract';
        return 'general';
    }

    /**
     * Call AI API (Groq preferred, then OpenAI)
     */
    async callAI(systemPrompt, userText) {
        const context = userText.substring(0, 1500);
        
        // Try Groq first (free, fast)
        if (this.groqKey) {
            try {
                const res = await fetch('https://api.groq.com/openai/v1/chat/completions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.groqKey}`
                    },
                    body: JSON.stringify({
                        model: 'llama3-8b-8192',
                        messages: [
                            { role: 'system', content: systemPrompt },
                            { role: 'user', content: context }
                        ],
                        temperature: 0,
                        max_tokens: 500
                    })
                });
                const data = await res.json();
                if (data.choices?.[0]?.message?.content) {
                    return data.choices[0].message.content;
                }
            } catch (e) {
                console.warn('Groq error:', e.message);
            }
        }
        
        // Fallback to OpenAI
        if (this.openai) {
            const res = await this.openai.chat.completions.create({
                model: 'gpt-3.5-turbo',
                messages: [
                    { role: 'system', content: systemPrompt },
                    { role: 'user', content: context }
                ],
                temperature: 0,
                max_tokens: 500
            });
            return res.choices[0]?.message?.content || '';
        }
        
        throw new Error('No AI API available');
    }

    /**
     * Extract structured data from document
     */
    async extractData(text, docType) {
        const prompt = this.prompts[docType] || this.prompts.general;
        
        try {
            const response = await this.callAI(
                `${prompt}\n\nExtract from this document. Return ONLY valid JSON, no markdown.`,
                text
            );
            
            // Parse JSON
            let data;
            try {
                // Try direct parse
                data = JSON.parse(response);
            } catch (e) {
                // Try extracting from code blocks
                const match = response.match(/```(?:json)?\s*([\s\S]*?)\s*```/);
                if (match) {
                    data = JSON.parse(match[1]);
                } else {
                    data = { raw: response };
                }
            }
            
            return { data, match: true, cost: 0.001 };
        } catch (error) {
            console.warn('Extraction failed:', error.message);
            
            // Fallback to basic extraction
            return {
                data: this.basicExtract(text, docType),
                match: false,
                cost: 0
            };
        }
    }

    /**
     * Basic extraction without AI
     */
    basicExtract(text, docType) {
        const result = { documentType: docType };
        
        // Extract amounts
        const amounts = text.match(/[\$£€BWP]?\s?[\d,]+\.?\d{0,2}/g) || [];
        result.amounts = [...new Set(amounts)].slice(0, 10);
        
        // Extract dates
        const dates = text.match(/\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/g) || [];
        result.dates = [...new Set(dates)].slice(0, 5);
        
        // Extract reference numbers
        const refs = text.match(/(?:INV|WAY|BL|#)[\s\-:]*[\d]+/gi) || [];
        result.references = [...new Set(refs)];
        
        return result;
    }
}

module.exports = TextProcessor;
