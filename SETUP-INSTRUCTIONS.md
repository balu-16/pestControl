# Email Form Setup Instructions

## Prerequisites
- cPanel hosting with PHP 7.4+ support
- Composer installed (or manual PHPMailer installation)
- SMTP access enabled

## Installation Steps

### 1. Upload Files
Upload all files to your cPanel `public_html` directory (or subdirectory).

### 2. Install PHPMailer

**Option A: Using Composer (Recommended)**
```bash
# SSH into your server or use cPanel Terminal
cd /home/yourusername/public_html
composer install
```

**Option B: Manual Installation**
1. Download PHPMailer from: https://github.com/PHPMailer/PHPMailer/releases
2. Extract and upload the `src` folder to `/vendor/phpmailer/phpmailer/src/`
3. Create `/vendor/autoload.php` with:
```php
<?php
require_once __DIR__ . '/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/phpmailer/src/SMTP.php';
```

### 3. Configure SMTP Credentials

Edit `config/mail-config.php`:
```php
define('SMTP_PASSWORD', 'YOUR_EMAIL_PASSWORD_HERE');
```

**IMPORTANT:** Replace `YOUR_EMAIL_PASSWORD_HERE` with the actual password for `interns@nighatechglobal.com`

### 4. Set File Permissions
```bash
chmod 644 config/mail-config.php
chmod 755 api/
chmod 644 api/*.php
```

### 5. Test the Forms
1. Open your website
2. Submit the booking form on the home page
3. Submit the contact form on the contact page
4. Check `interns@nighatechglobal.com` for received emails

## Troubleshooting

### Email Not Sending
1. Check PHP error logs in cPanel
2. Verify SMTP credentials are correct
3. Ensure port 465 is not blocked by firewall
4. Try using port 587 with TLS if SSL fails

### CORS Issues
If testing locally, you may see CORS errors. This is normal - forms will work on the production server.

### JSON Parse Errors
Ensure PHP is not outputting any warnings. Check `error_reporting` in `php.ini`.

## Security Notes

- **NEVER** commit `config/mail-config.php` with real passwords to Git
- The `.htaccess` files protect sensitive directories
- All inputs are sanitized server-side
- Header injection is prevented

## File Structure
```
├── api/
│   ├── booking-handler.php    # Home page form handler
│   └── contact-handler.php    # Contact page form handler
├── config/
│   ├── .htaccess              # Blocks direct access
│   └── mail-config.php        # SMTP configuration
├── vendor/                     # PHPMailer (after composer install)
├── .htaccess                   # Security & caching rules
├── composer.json               # Dependencies
├── index.html                  # Home page
├── contact.html                # Contact page
└── main.js                     # Form handling JavaScript
```

## SMTP Configuration Reference
```
Host: mail.nighatechglobal.com
Port: 465
Security: SSL
Username: interns@nighatechglobal.com
```
