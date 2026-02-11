# FrontAccounting API Gateway - Deployment Guide

## 🚀 Quick Deploy (3 Steps)

### Step 1: Upload Files to Server
```bash
# SSH to your server
ssh root@your-server

# Upload files
scp fa_api_gateway.php root@your-server:/var/www/html/api/
scp fa_api_config.env root@your-server:/var/www/html/api/
scp .htaccess root@your-server:/var/www/html/api/
```

### Step 2: Set Environment Variables
Edit `/etc/environment` or create a `.env` file:
```bash
# Core API
export FA_API_KEY="your-super-secret-api-key"

# Instance Credentials
export FA_FRAYS_USERNAME="admin"
export FA_FRAYS_PASSWORD="your-password"

export FA_NW_USERNAME="admin"
export FA_NW_PASSWORD="..."

export FA_MADAMZ_USERNAME="..."
# ... add all instances
```

### Step 3: Configure Web Server

**For cPanel:**
1. Create subdomain: `api.bookkeeping.co.bw`
2. Point to `/var/www/html/api/`
3. Enable .htaccess

**Or create virtual host:**
```apache
<VirtualHost *:443>
    ServerName api.bookkeeping.co.bw
    DocumentRoot /var/www/html/api
    
    <Directory /var/www/html/api>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## 📡 API Endpoints

### Base URL
```
https://api.bookkeeping.co.bw/
```

### Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/health` | Health check |
| GET | `/api/instances` | List all FA instances |
| GET | `/api/instance?instance=X` | Get instance info |
| POST | `/api/auth` | Authenticate with instance |
| GET | `/api/customers?instance=X` | List all customers |
| POST | `/api/customer` | Add new customer |
| GET | `/api/customer?id=X` | Get customer details |
| POST | `/api/invoice` | Create sales invoice |
| POST | `/api/journal` | Create journal entry |
| GET | `/api/aged-debtors?instance=X` | Aged debtors report |
| GET | `/api/vat?instance=X&from=DD/MM/YYYY&to=DD/MM/YYYY` | VAT summary |

---

## 💻 Usage Examples

### cURL

```bash
# Set API key
API_KEY="your-secret-key"

# Health check
curl https://api.bookkeeping.co.bw/api/health

# List instances
curl https://api.bookkeeping.co.bw/api/instances \
  -H "Authorization: Bearer $API_KEY"

# Get instance info
curl "https://api.bookkeeping.co.bw/api/instance?instance=frayscottage" \
  -H "Authorization: Bearer $API_KEY"

# List customers
curl "https://api.bookkeeping.co.bw/api/customers?instance=frayscottage" \
  -H "Authorization: Bearer $API_KEY"

# Add customer
curl -X POST https://api.bookkeeping.co.bw/api/customer \
  -H "Authorization: Bearer $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "instance": "frayscottage",
    "data": {
      "name": "Test Company",
      "reference": "TEST001",
      "address": "123 Test St",
      "email": "test@test.com"
    }
  }'

# Create invoice
curl -X POST https://api.bookkeeping.co.bw/api/invoice \
  -H "Authorization: Bearer $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "instance": "frayscottage",
    "data": {
      "customer_id": 1,
      "date": "09/02/2026",
      "items": [
        {
          "stock_id": "SRV001",
          "description": "Bookkeeping Service",
          "quantity": 1,
          "unit_price": 1000
        }
      ]
    }
  }'

# Create journal entry (VAT posting)
curl -X POST https://api.bookkeeping.co.bw/api/journal \
  -H "Authorization: Bearer $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "instance": "frayscottage",
    "data": {
      "date": "09/02/2026",
      "reference": "VAT-FEB-2026",
      "memo": "VAT Payable - February 2026",
      "account": 2150,
      "amount": -10000,
      "dimension": 0
    }
  }'

# Get aged debtors
curl "https://api.bookkeeping.co.bw/api/aged-debtors?instance=frayscottage" \
  -H "Authorization: Bearer $API_KEY"

# Get VAT summary
curl "https://api.bookkeeping.co.bw/api/vat?instance=frayscottage&from=01/02/2026&to=28/02/2026" \
  -H "Authorization: Bearer $API_KEY"
```

### JavaScript/Node.js

```javascript
const API_KEY = 'your-secret-key';
const BASE_URL = 'https://api.bookkeeping.co.bw';

// Helper function
async function request(endpoint, method = 'GET', data = null) {
    const options = {
        method,
        headers: {
            'Authorization': `Bearer ${API_KEY}`,
            'Content-Type': 'application/json',
        },
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    const response = await fetch(`${BASE_URL}${endpoint}`, options);
    return response.json();
}

// Examples
async function examples() {
    // List instances
    const instances = await request('/api/instances');
    console.log('Instances:', instances);
    
    // Get instance info
    const info = await request('/api/instance?instance=frayscottage');
    console.log('Info:', info);
    
    // List customers
    const customers = await request('/api/customers?instance=frayscottage');
    console.log('Customers:', customers);
    
    // Add customer
    const newCustomer = await request('/api/customer', 'POST', {
        instance: 'frayscottage',
        data: {
            name: 'New Client Ltd',
            reference: 'NEW001',
            email: 'client@new.com'
        }
    });
    console.log('New Customer:', newCustomer);
    
    // Create invoice
    const invoice = await request('/api/invoice', 'POST', {
        instance: 'frayscottage',
        data: {
            customer_id: 1,
            date: '09/02/2026',
            items: [{
                stock_id: 'SRV001',
                description: 'Service',
                quantity: 1,
                unit_price: 1000
            }]
        }
    });
    console.log('Invoice:', invoice);
    
    // Aged debtors
    const aged = await request('/api/aged-debtors?instance=frayscottage');
    console.log('Aged Debtors:', aged);
}

examples();
```

### Python

```python
import requests
import os

API_KEY = os.getenv('FA_API_KEY', 'your-secret-key')
BASE_URL = 'https://api.bookkeeping.co.bw'

headers = {
    'Authorization': f'Bearer {API_KEY}',
    'Content-Type': 'application/json',
}

# Health check
r = requests.get(f'{BASE_URL}/api/health')
print(r.json())

# List instances
r = requests.get(f'{BASE_URL}/api/instances', headers=headers)
print(r.json())

# Get instance info
r = requests.get(
    f'{BASE_URL}/api/instance',
    params={'instance': 'frayscottage'},
    headers=headers
)
print(r.json())

# Add customer
customer_data = {
    'instance': 'frayscottage',
    'data': {
        'name': 'Test Company',
        'reference': 'TEST001',
        'email': 'test@test.com'
    }
}
r = requests.post(
    f'{BASE_URL}/api/customer',
    json=customer_data,
    headers=headers
)
print(r.json())

# Create invoice
invoice_data = {
    'instance': 'frayscottage',
    'data': {
        'customer_id': 1,
        'date': '09/02/2026',
        'items': [{
            'stock_id': 'SRV001',
            'description': 'Bookkeeping Service',
            'quantity': 1,
            'unit_price': 1000
        }]
    }
}
r = requests.post(
    f'{BASE_URL}/api/invoice',
    json=invoice_data,
    headers=headers
)
print(r.json())

# Aged debtors
r = requests.get(
    f'{BASE_URL}/api/aged-debtors',
    params={'instance': 'frayscottage'},
    headers=headers
)
print(r.json())

# VAT summary
r = requests.get(
    f'{BASE_URL}/api/vat',
    params={
        'instance': 'frayscottage',
        'from': '01/02/2026',
        'to': '28/02/2026'
    },
    headers=headers
)
print(r.json())
```

---

## 🔧 OpenClaw Integration

### Register in OpenClaw

```yaml
# In your OpenClaw config
tools:
  frays_accounting_api:
    base_url: https://api.bookkeeping.co.bw
    api_key: ${FA_API_KEY}
```

### Use in Workflow

```javascript
// Example OpenClaw workflow
async function monthlyBookkeepingWorkflow() {
    // 1. Get VAT due dates from calendar
    const vatDates = await calendar.getDueDates('VAT');
    
    // 2. For each instance, create VAT journal entry
    const instances = await faApi.listInstances();
    
    for (const inst of instances.instances) {
        if (inst.configured) {
            // Get current VAT balance
            const vatSummary = await faApi.getVatSummary(
                inst.name,
                '01/02/2026',
                '28/02/2026'
            );
            
            // Create journal entry
            await faApi.createJournalEntry({
                instance: inst.name,
                date: '28/02/2026',
                reference: `VAT-${inst.name.toUpperCase()}-FEB2026`,
                account: 2150, // VAT Payable
                amount: -vatSummary.totalVat,
                memo: `VAT Posting - ${inst.name}`
            });
            
            console.log(`✅ VAT posted for ${inst.name}`);
        }
    }
}
```

---

## 📊 Monitoring

### Check API Health
```bash
curl https://api.bookkeeping.co.bw/api/health
```

Response:
```json
{
  "status": "ok",
  "timestamp": "2026-02-09T19:43:00+00:00"
}
```

### View Logs
```bash
# Server logs
tail -f /var/log/fa_api.log

# Error logs
tail -f /var/log/httpd/error_log
```

---

## 🚨 Troubleshooting

### 401 Unauthorized
- Check API key is correct
- Ensure "Bearer " prefix is included

### 500 Internal Server Error
- Check PHP error logs
- Verify credentials are set

### Instance Not Found
- Check instance name spelling
- Verify instance is in config

### Login Failed
- Verify FA username/password
- Check instance is accessible via browser

---

## 📝 TODO: Next Phase

- [ ] Add webhook support
- [ ] Implement rate limiting
- [ ] Add request logging
- [ ] Create admin dashboard
- [ ] Add OAuth2 authentication
- [ ] Implement caching layer
- [ ] Add automated testing suite

---

## 📞 Support

For issues or questions, check:
1. Server error logs
2. OpenClaw documentation
3. FrontAccounting wiki
