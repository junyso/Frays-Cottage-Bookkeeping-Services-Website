# Frays Cottage Bookkeeping Services - Client Portal

## 🚀 Quick Start

### 1. Access the Portal
- **Portal Login**: http://localhost:8080/portal
- **Setup Page**: http://localhost:8080/portal/setup.php
- **Admin Dashboard**: http://localhost:8080/portal/admin.php

### 2. Initial Setup

1. Run the setup script: `http://localhost:8080/portal/setup.php`
2. Click **"Create Tables"** to create the database schema
3. Click **"Sync Users"** to import users from all FA instances

### 3. Configure FA Database Credentials

Edit `FRAYS_WEBSITE/includes/config.php`:

```php
// FA Instances Database Configuration
define('FA_DB_HOST', 'localhost');  // Your FA server
define('FA_DB_USER', 'your_fa_db_user');  // User with access to all FA databases
define('FA_DB_PASS', 'your_password');  // Password
```

---

## 📋 Features

### ✅ Unified Authentication
- **Single login** for all 30+ FA instances
- Auto-detects which instances a user has access to
- Redirects to correct instance after login

### 📤 Document Upload
- Web upload via portal
- Email attachments forwarded to uploads@bookkeeping.co.bw
- Documents tagged with client code and type

### ✅ Approval Workflow
- Admin dashboard for document approval
- One-click approve/reject
- Audit trail of all actions

### 🔐 Security
- CSRF protection on all forms
- Session management
- Password hashing

---

## 📁 File Structure

```
FRAYS_WEBSITE/
├── portal/
│   ├── index.php          # Main portal page
│   ├── setup.php          # Initial setup & user sync
│   ├── admin.php          # Admin dashboard for approvals
│   └── logout.php        # Logout handler
├── includes/
│   ├── config.php         # Main configuration
│   ├── unified-auth.php   # Unified authentication logic
│   └── fa-user-sync.php   # FA user synchronization
├── sql/
│   └── unified_users.sql  # Database schema
└── assets/
    └── images/
        └── carousel/      # Homepage carousel images
```

---

## 🔧 Configuration

### Adding New FA Instances

Edit `FRAYS_WEBSITE/includes/config.php`:

```php
$FA_INSTANCES = [
    'newinstance' => [
        'name' => 'New Instance Name',
        'url' => 'https://www.bookkeeping.co.bw/newinstance',
        'version' => '2.4.18'
    ],
    // ... more instances
];
```

### Email Processing (uploads@bookkeeping.co.bw)

Configure email forwarding:
1. Set up email forwarder to forward attachments to your portal
2. Or configure IMAP in a cron job script

### WhatsApp Integration

**Quick option (tonight):** Forward WhatsApp documents to uploads@bookkeeping.co.bw

**Later:** Set up WhatsApp Business API or use Twilio

---

## 📊 FA Instances (30 Total)

| # | Instance | Version |
|---|----------|---------|
| 1 | Northern Warehouse | 2.4.18 |
| 2 | Madamz | 2.4.18 |
| 3 | Cleaning Guru | 2.4.18 |
| 4 | Quanto | 2.4.18 |
| 5 | Space Interiors | 2.4.18 |
| 6 | Unlimited Foods | 2.4.10 |
| 7 | Ernlet Projects | 2.4.16 |
| 8 | Frays Cottage | 2.4.16 |
| 9 | Constant Adaptation | 2.4.10 |
| 10 | Great-Land | 2.4.10 |
| 11 | Lightening Strike | 2.4.10 |
| 12 | NOTSA | 2.4.10 |
| 13 | Thaega | 2.4.10 |
| 14 | Modern Hotel Supplies | 2.4.10 |
| 15 | Training | 2.4.16 |
| 16 | Majande | 2.4.11 |
| 17 | Guru Onks | 2.4.11 |
| 18 | Marctizmo | 2.4.16 |
| 19 | 4BnB | 2.4.16 |
| 20 | Nora Cosmetics | 2.4.16 |
| 21 | 3D Works | 2.4.16 |
| 22 | West Drayton | 2.4.17 |
| 23 | Ernlet Projects 2 | 2.4.18 |
| 24 | Ernlet Group | 2.4.18 |
| 25 | Courier Solutions | 2.4.18 |
| 26 | Loremaster | 2.4.18 |
| 27 | Coverlot | 2.4.18 |
| 28 | Global Strategies | 2.4.18 |
| 29 | Norah Beauty | 2.4.18 |
| 30 | Nidarshini | 2.4.18 |

---

## 🚀 Deployment

### Push to GitHub
```bash
cd /Users/julianuseya/.openclaw/workspace
git add .
git commit -m "Add unified authentication system"
git push origin main
```

### Sync from GitHub
```bash
cd /Users/julianuseya/.openclaw/workspace
git pull origin main
```

---

## 📝 To-Do (Tonight)

- [x] Create unified authentication
- [x] Create admin dashboard for approvals
- [x] Create setup & sync scripts
- [ ] Test FA database connections
- [ ] Configure FA database credentials
- [ ] Run initial user sync
- [ ] Test login flow
- [ ] Configure email processing

---

## 💡 Support

For issues:
1. Check PHP error logs: `FRAYS_WEBSITE/includes/logs/php_errors.log`
2. Check activity logs: `FRAYS_WEBSITE/includes/logs/activity.log`

---

Built with ❤️ by OpenClaw
