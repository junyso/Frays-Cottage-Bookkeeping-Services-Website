/**
 * FrontAccounting API Integration
 * Push invoices, suppliers, and transactions
 */

const axios = require('axios');
const dotenv = require('dotenv');

dotenv.config();

class FAIntegration {
    constructor() {
        this.baseUrl = process.env.FA_API_URL || 'http://localhost/fa/api';
        this.username = process.env.FA_USERNAME || 'admin';
        this.password = process.env.FA_PASSWORD || 'password';
        
        this.client = axios.create({
            baseURL: this.baseUrl,
            auth: {
                username: this.username,
                password: this.password
            },
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
    }

    /**
     * Get all suppliers from FA
     */
    async getSuppliers() {
        try {
            const response = await this.client.get('/suppliers');
            return {
                success: true,
                data: response.data,
                count: response.data.length
            };
        } catch (error) {
            return {
                success: false,
                error: error.message,
                data: []
            };
        }
    }

    /**
     * Get all customers from FA
     */
    async getCustomers() {
        try {
            const response = await this.client.get('/customers');
            return {
                success: true,
                data: response.data,
                count: response.data.length
            };
        } catch (error) {
            return {
                success: false,
                error: error.message,
                data: []
            };
        }
    }

    /**
     * Search supplier by name
     */
    async findSupplier(name) {
        try {
            const response = await this.client.get('/suppliers', {
                params: { search: name }
            });
            
            const suppliers = response.data || [];
            const exactMatch = suppliers.find(s => 
                s.name.toLowerCase() === name.toLowerCase()
            );
            
            return {
                success: true,
                found: !!exactMatch,
                supplier: exactMatch || suppliers[0],
                allMatches: suppliers
            };
        } catch (error) {
            return {
                success: false,
                found: false,
                error: error.message,
                supplier: null
            };
        }
    }

    /**
     * Create supplier invoice/bill in FA
     */
    async createSupplierInvoice(invoiceData) {
        const faData = {
            supplier_id: invoiceData.supplierId || 0,
            tran_date: invoiceData.date || new Date().toISOString().split('T')[0],
            due_date: invoiceData.dueDate || invoiceData.date,
            reference: invoiceData.reference || `INV-${Date.now()}`,
            supplier_reference: invoiceData.invoiceNumber || '',
            comments: invoiceData.description || '',
            tax_group_id: invoiceData.taxAmount > 0 ? 1 : 0,
            ov_amount: invoiceData.subtotal || 0,
            ov_gst: invoiceData.taxAmount || 0,
            ov_total: invoiceData.total || 0,
            items: this.formatLineItems(invoiceData.items || [])
        };
        
        try {
            const response = await this.client.post('/supplier-invoices', faData);
            return {
                success: true,
                invoiceId: response.data?.id,
                reference: faData.reference
            };
        } catch (error) {
            return {
                success: false,
                error: error.response?.data?.message || error.message,
                invoiceId: null
            };
        }
    }

    /**
     * Create customer invoice in FA
     */
    async createCustomerInvoice(invoiceData) {
        const faData = {
            customer_id: invoiceData.customerId || 0,
            tran_date: invoiceData.date || new Date().toISOString().split('T')[0],
            due_date: invoiceData.dueDate || invoiceData.date,
            reference: invoiceData.reference || `INV-${Date.now()}`,
            comments: invoiceData.description || '',
            tax_group_id: invoiceData.taxAmount > 0 ? 1 : 0,
            ov_amount: invoiceData.subtotal || 0,
            ov_gst: invoiceData.taxAmount || 0,
            ov_total: invoiceData.total || 0,
            items: this.formatLineItems(invoiceData.items || [])
        };
        
        try {
            const response = await this.client.post('/customer-invoices', faData);
            return {
                success: true,
                invoiceId: response.data?.id,
                reference: faData.reference
            };
        } catch (error) {
            return {
                success: false,
                error: error.response?.data?.message || error.message,
                invoiceId: null
            };
        }
    }

    /**
     * Format line items for FA API
     */
    formatLineItems(items) {
        return items.map((item, index) => ({
            id: index + 1,
            description: item.description || item.name,
            unit_price: item.unitPrice || item.price || 0,
            quantity: item.quantity || 1,
            units: item.units || 'each',
            discount_percent: item.discount || 0
        }));
    }

    /**
     * Get chart of accounts
     */
    async getGLAccounts() {
        try {
            const response = await this.client.get('/gl-accounts');
            return {
                success: true,
                accounts: response.data
            };
        } catch (error) {
            return {
                success: false,
                error: error.message,
                accounts: []
            };
        }
    }

    /**
     * Post general ledger entry
     */
    async postGLEntry(entryData) {
        const glData = {
            tran_date: entryData.date || new Date().toISOString().split('T')[0],
            reference: entryData.reference || `GL-${Date.now()}`,
            memo_: entryData.description || '',
            items: entryData.lines.map(line => ({
                account_code: line.accountCode,
                dimension_id: line.dimensionId || 0,
                dimension2_id: line.departmentId || 0,
                amount: line.amount,
                text: line.description
            }))
        };
        
        try {
            const response = await this.client.post('/gl-transfers', glData);
            return {
                success: true,
                transactionId: response.data?.id
            };
        } catch (error) {
            return {
                success: false,
                error: error.message,
                transactionId: null
            };
        }
    }

    /**
     * Test API connection
     */
    async testConnection() {
        try {
            const response = await this.client.get('/company');
            return {
                success: true,
                company: response.data?.name || 'Connected',
                version: response.data?.version || 'Unknown'
            };
        } catch (error) {
            return {
                success: false,
                error: error.message
            };
        }
    }
}

module.exports = FAIntegration;
