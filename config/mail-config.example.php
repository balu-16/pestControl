<?php
/**
 * SMTP Mail Configuration
 * This file loads credentials from .env file
 * Copy .env.example to .env and fill in your credentials
 * DO NOT commit .env with real credentials to version control
 */

// Load environment variables from .env file
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        // Parse key=value pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Set as environment variable
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// SMTP Configuration
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'localhost');
define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 465));
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: '');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');
define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'ssl');
define('SMTP_AUTH', true);

// Mail Settings
define('MAIL_FROM_EMAIL', getenv('MAIL_FROM_EMAIL') ?: '');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'Website Contact Form');
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: '');  // Admin receives form notifications
define('MAIL_CHARSET', 'UTF-8');

// Company details for email templates
define('COMPANY_NAME', getenv('COMPANY_NAME') ?: 'Indian Pest Control');
define('COMPANY_PHONE', getenv('COMPANY_PHONE') ?: '+91 8662436234');
define('COMPANY_EMAIL', getenv('COMPANY_EMAIL') ?: 'support@indianpestcontrol.com');
define('COMPANY_ADDRESS', getenv('COMPANY_ADDRESS') ?: '29-29-10/B, Moulana St, Eluru Road, Opp. Canara Bank, Arundalpet, Governor Peta, Vijayawada, Andhra Pradesh 520002');
define('COMPANY_WEBSITE', getenv('COMPANY_WEBSITE') ?: 'https://www.indianpestcontrol.com');
