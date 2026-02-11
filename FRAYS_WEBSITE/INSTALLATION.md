# FRAYSCOTTAGE BOOKKEEPING SERVICES
## Complete Website Installation Guide

---

## 📋 Table of Contents

1. [Server Requirements](#server-requirements)
2. [Pre-Installation Checklist](#pre-installation-checklist)
3. [Step-by-Step Installation](#step-by-step-installation)
4. [Configuration](#configuration)
5. [Testing](#testing)
6. [Maintenance](#maintenance)

---

## 🖥️ Server Requirements

| Requirement | Minimum | Recommended |
|------------|---------|-------------|
| PHP Version | 7.4+ | 8.0+ |
| MySQL Version | 5.7+ | 8.0+ |
| RAM | 512 MB | 1 GB+ |
| Disk Space | 500 MB | 1 GB+ |
| Extensions | PDO, GD, JSON, CURL | PDO, GD, JSON, CURL, Mbstring |

### cPanel Requirements (Verified for Your Server)
- ✅ CentOS 7.9.2009
- ✅ cPanel 110.0.87
- ✅ PHP-FPM
- ✅ MySQL 5.7+
- ✅ Apache 2.4

---

## 📋 Pre-Installation Checklist

### 1. Create Database in cPanel

1. Log into cPanel: `https://www.bookkeeping.co.bw/cpanel`
2. Navigate to **MySQL® Database Wizard**
3. Create a new database: `fraysc5_bookkeeping`
4. Create a database user with full privileges
5. Note down the credentials:
   - Database Name: `fraysc5_bookkeeping`
   - Username: `fraysc5_admin`
   - Password: `[your-password]`
   - Host: `localhost`

### 2. Configure PHP Version (Recommended: PHP 8.0)

1. In cPanel, go to **MultiPHP Manager**
2. Select your domain: `www.bookkeeping.co.bw`
3. Set PHP version to: `ea-php80` or `ea-php81`

### 3. Upload Files via FTP/cPanel File Manager

**Option A: cPanel File Manager**
1. Go to **File Manager** in cPanel
2. Navigate to `public_html/`
3. Create folder: `frayscottage`
4. Upload all files to: `/public_html/frayscottage/`

**Option B: FTP**
```bash
ftp vps52870.servconfig.com
# Upload files to /public_html/frayscottage/
```

---

## 🚀 Step-by-Step Installation

### Step 1: Upload Files

Upload the complete `FRAYS_WEBSITE` folder contents to:
```
/public_html/frayscottage/
```

### Step 2: Set Permissions

```bash
# Set proper permissions
chmod 755 includes/config.php
chmod 755 logs/
chmod 755 uploads/
chmod 755 processed/
chmod 644 .htaccess
```

**Via cPanel Terminal:**
1. Go to **Advanced > Terminal**
2. Run:
```bash
cd /home/fraysc5/public_html/frayscottage
chmod 755 includes/config.php
chmod 755 logs/ uploads/ processed/
```

### Step 3: Configure Database

Edit `includes/config.php` with your database credentials:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'fraysc5_bookkeeping');
define('DB_USER', 'fraysc5_admin');
define('DB_PASS', 'YOUR_DATABASE_PASSWORD_HERE');
```

### Step 4: Run Database Setup

1. Open browser and navigate to:
   ```
   https://www.bookkeeping.co.bw/frayscottage/setup/
   ```

2. Click **Run Installation**

3. **Default Login Credentials:**
   - Email: `admin@frayscottage.co.bw`
   - Password: `admin123`

4. ⚠️ **IMPORTANT:** Change the admin password immediately after first login!

---

## ⚙️ Configuration

### Environment Variables (Optional)

Create a `.env` file in the root directory:

```env
DB_HOST=localhost
DB_NAME=fraysc5_bookkeeping
DB_USER=fraysc5_admin
DB_PASS=your_secure_password
ONEDRIVE_PATH=/home/fraysc5/OneDrive
```

### Email Configuration

For email notifications, configure SMTP in `includes/config.php`:

```php
define('SMTP_HOST', 'your-smtp-host');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@frayscottage.co.bw');
define('SMTP_PASS', 'your-email-password');
```

### FA Instance Configuration

Edit the `$FA_INSTANCES` array in `includes/config.php`:

```php
$FA_INSTANCES = [
    'instance-slug' => [
        'name' => 'Instance Display Name',
        'url' => 'https://www.bookkeeping.co.bw/instance-slug',
        'version' => '2.4.18'
    ],
    // Add more instances...
];
```

---

## ✅ Testing Checklist

Run these tests after installation:

- [ ] Homepage loads correctly: `https://www.bookkeeping.co.bw/frayscottage/`
- [ ] Login page accessible: `https://www.bookkeeping.co.bw/frayscottage/login`
- [ ] Admin login works with default credentials
- [ ] Document upload works
- [ ] FA instance redirect works
- [ ] All pages load without errors

### Test Admin Login
1. Go to: `https://www.bookkeeping.co.bw/frayscottage/login`
2. Email: `admin@frayscottage.co.bw`
3. Password: `admin123`

### Test Document Upload
1. Login as admin
2. Go to: `/portal/`
3. Upload a test PDF invoice

---

## 📁 Directory Structure

```
frayscottage/
├── index.php              # Homepage/Landing
├── login.php              # Unified login
├── logout.php             # Logout handler
├── redirect.php           # FA instance redirect
├── setup/                 # Database setup wizard
│   └── index.php
├── includes/
│   └── config.php         # Main configuration
├── api/                   # API endpoints (future)
├── portal/
│   └── index.php         # Document portal
├── uploads/              # Temporary uploads
├── processed/            # Processed documents
├── logs/                 # Application logs
├── .htaccess             # Apache config
└── README.md             # This file
```

---

## 🔒 Security Checklist

- [ ] Change default admin password
- [ ] Set strong database password
- [ ] Enable HTTPS/SSL (free via cPanel AutoSSL)
- [ ] Restrict `includes/` directory access
- [ ] Set proper file permissions
- [ ] Enable firewall in cPanel
- [ ] Configure backup schedule

---

## 📊 Maintenance

### Regular Tasks

1. **Daily:**
   - Monitor error logs: `/logs/error.log`
   - Check failed login attempts

2. **Weekly:**
   - Review uploaded documents
   - Clean old temporary files
   - Check storage usage

3. **Monthly:**
   - Update FA instances list
   - Review user access
   - Backup database

### Backup Commands

```bash
# Database backup
mysqldump -u fraysc5_admin -p fraysc5_bookkeeping > backup_$(date +%Y%m%d).sql

# Files backup
tar -czvf files_backup_$(date +%Y%m%d).tar.gz uploads/ processed/
```

---

## 📞 Support

For issues or questions:

1. Check the error logs first: `/logs/error.log`
2. Verify all prerequisites are met
3. Contact: [Your Contact Info]

---

## 🎯 Quick Reference

| URL | Purpose |
|-----|---------|
| `https://www.bookkeeping.co.bw/frayscottage/` | Homepage |
| `https://www.bookkeeping.co.bw/frayscottage/login` | Login Page |
| `https://www.bookkeeping.co.bw/frayscottage/setup/` | Database Setup |
| `https://www.bookkeeping.co.bw/frayscottage/portal/` | Document Portal |

---

**Version:** 1.0.0  
**Last Updated:** February 2026  
**Author:** Frays Cottage IT
