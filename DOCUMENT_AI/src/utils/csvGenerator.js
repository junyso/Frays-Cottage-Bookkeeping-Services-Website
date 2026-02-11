/**
 * CSV Generator - FrontAccounting Ready Exports
 */

const fs = require('fs');
const path = require('path');

class CSVGenerator {
    constructor() {
        this.exportDir = 'exports';
        this.ensureExportDir();
    }

    ensureExportDir() {
        if (!fs.existsSync(this.exportDir)) {
            fs.mkdirSync(this.exportDir, { recursive: true });
        }
    }

    /**
     * Generate CSV from processed documents
     * @param {object[]} documents - Array of processed documents
     * @param {string} format - Export format (fa, general, custom)
     * @returns {string} - CSV file path
     */
    generateFromDocuments(documents, format = 'fa') {
        const headers = this.getHeaders(format);
        const rows = documents.map(doc => this.formatRow(doc, format));
        
        const csvContent = [
            headers.join(','),
            ...rows.map(row => row.join(','))
        ].join('\n');
        
        const filename = `${format}_${Date.now()}.csv`;
        const filepath = path.join(this.exportDir, filename);
        
        fs.writeFileSync(filepath, csvContent);
        
        return {
            path: filepath,
            filename: filename,
            records: documents.length,
            format: format
        };
    }

    /**
     * Get headers for format
     */
    getHeaders(format) {
        switch (format) {
            case 'fa':
                return [
                    'Date',
                    'Type',
                    'Supplier',
                    'InvoiceNo',
                    'Amount',
                    'Tax',
                    'Total',
                    'Description',
                    'Reference',
                    'CustomerMatch',
                    'Confidence'
                ];
            case 'invoice':
                return [
                    'InvoiceDate',
                    'VendorName',
                    'InvoiceNumber',
                    'Subtotal',
                    'TaxAmount',
                    'TotalAmount',
                    'Currency',
                    'PaymentTerms'
                ];
            case 'receipt':
                return [
                    'Date',
                    'StoreName',
                    'Items',
                    'Subtotal',
                    'Tax',
                    'Total',
                    'PaymentMethod'
                ];
            default:
                return ['Date', 'Type', 'Description', 'Amount', 'Source'];
        }
    }

    /**
     * Format document row for CSV
     */
    formatRow(doc, format) {
        const data = doc.extractedData || {};
        
        switch (format) {
            case 'fa':
                return [
                    data.date || '',
                    doc.documentType || '',
                    data.vendor || data.store || data.supplier || '',
                    data.invoiceNumber || data.receiptNumber || '',
                    data.subtotal || '',
                    data.taxAmount || '',
                    data.totalAmount || data.total || '',
                    this.escapeCSV(data.description || JSON.stringify(data.items || '')),
                    doc.file || '',
                    doc.customerMatch?.matched_name || '',
                    doc.customerMatch?.confidence || ''
                ];
                
            case 'invoice':
                return [
                    data.date || '',
                    data.vendor || '',
                    data.invoiceNumber || '',
                    data.subtotal || '',
                    data.taxAmount || '',
                    data.totalAmount || '',
                    data.currency || 'BWP',
                    data.paymentTerms || ''
                ];
                
            case 'receipt':
                return [
                    data.date || '',
                    data.store || '',
                    this.escapeCSV(JSON.stringify(data.items || [])),
                    data.subtotal || '',
                    data.tax || '',
                    data.total || '',
                    data.paymentMethod || ''
                ];
                
            default:
                return [
                    data.date || '',
                    doc.documentType || '',
                    this.escapeCSV(data.description || JSON.stringify(data)),
                    data.amount || data.total || '',
                    doc.file || ''
                ];
        }
    }

    /**
     * Escape CSV special characters
     */
    escapeCSV(value) {
        if (value === null || value === undefined) return '';
        const str = String(value);
        if (str.includes(',') || str.includes('"') || str.includes('\n')) {
            return `"${str.replace(/"/g, '""')}"`;
        }
        return str;
    }

    /**
     * Generate FA-specific import format
     */
    generateFAImport(documents) {
        const rows = documents.map(doc => {
            const data = doc.extractedData || {};
            const date = data.date || new Date().toISOString().split('T')[0];
            
            return {
                tran_date: date,
                reference: doc.file || `DOC-${Date.now()}`,
                supp_name: data.vendor || data.store || data.supplier || 'Unknown',
                supp_account: '',
                tax_group: data.taxAmount > 0 ? '1' : '0',
                tax_included: data.taxAmount > 0 ? '1' : '0',
                amount: data.totalAmount || data.total || 0,
                note: JSON.stringify(data.items || data.description || '')
            };
        });
        
        const headers = Object.keys(rows[0] || {});
        const csvContent = [
            headers.join(','),
            ...rows.map(row => headers.map(h => this.escapeCSV(row[h])).join(','))
        ].join('\n');
        
        const filepath = path.join(this.exportDir, `fa_import_${Date.now()}.csv`);
        fs.writeFileSync(filepath, csvContent);
        
        return filepath;
    }

    /**
     * Get all export files
     */
    listExports() {
        return fs.readdirSync(this.exportDir)
            .filter(f => f.endsWith('.csv'))
            .map(f => ({
                name: f,
                path: path.join(this.exportDir, f),
                size: fs.statSync(path.join(this.exportDir, f)).size,
                created: fs.statSync(path.join(this.exportDir, f)).birthtime
            }));
    }
}

module.exports = CSVGenerator;
