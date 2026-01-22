<?php
/**
 * Booking Form Handler - Book Free Inspection (Home Page)
 * Handles quick booking submissions from the home page CTA section
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require_once __DIR__ . '/../config/mail-config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Sanitize and validate input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    $data = str_replace(["\r", "\n", "%0a", "%0d"], '', $data);
    return $data;
}

function validatePhone($phone) {
    return preg_match('/^[0-9]{10}$/', preg_replace('/[\s\-]/', '', $phone));
}

try {
    // Get and sanitize form data
    $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';

    // Validate required fields
    if (empty($name) || empty($phone)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please provide your name and phone number']);
        exit();
    }

    if (strlen($name) < 2) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please enter a valid name']);
        exit();
    }

    if (!validatePhone($phone)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please enter a valid 10-digit phone number']);
        exit();
    }

    $currentDate = date('d M Y, h:i A');

    // Initialize PHPMailer
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = SMTP_AUTH;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = MAIL_CHARSET;

    $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
    $mail->addAddress(ADMIN_EMAIL);

    $mail->isHTML(true);
    $mail->Subject = "🏠 Quick Booking Request - $name";

    // Professional HTML email template
    $emailBody = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <!-- Header -->
            <tr>
                <td style="background: linear-gradient(135deg, #c9a227 0%, #e8c547 100%); padding: 30px 40px; text-align: center;">
                    <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: 600;">📞 Quick Booking Request</h1>
                    <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 14px;">From Website - Book Free Inspection</p>
                </td>
            </tr>
            
            <!-- Priority Badge -->
            <tr>
                <td style="padding: 20px 40px 0;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="background-color: #dc3545; color: #ffffff; padding: 10px 20px; border-radius: 5px; text-align: center; font-size: 14px; font-weight: 600;">
                                ⚡ ACTION REQUIRED - Customer awaiting callback
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <!-- Content -->
            <tr>
                <td style="padding: 30px 40px;">
                    <!-- Customer Info Card -->
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 8px; margin-bottom: 25px;">
                        <tr>
                            <td style="padding: 30px;">
                                <h2 style="color: #2c3e50; margin: 0 0 25px; font-size: 20px; border-bottom: 3px solid #c9a227; padding-bottom: 12px;">👤 Customer Details</h2>
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="12">
                                    <tr>
                                        <td style="color: #666; font-size: 15px; width: 35%; vertical-align: top;">
                                            <strong>Name:</strong>
                                        </td>
                                        <td style="color: #2c3e50; font-size: 18px; font-weight: 700;">' . $name . '</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #666; font-size: 15px; vertical-align: top;">
                                            <strong>Phone:</strong>
                                        </td>
                                        <td style="font-size: 18px;">
                                            <a href="tel:' . $phone . '" style="color: #c9a227; text-decoration: none; font-weight: 700;">' . $phone . '</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #666; font-size: 15px; vertical-align: top;">
                                            <strong>Received:</strong>
                                        </td>
                                        <td style="color: #2c3e50; font-size: 15px;">' . $currentDate . '</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #666; font-size: 15px; vertical-align: top;">
                                            <strong>Source:</strong>
                                        </td>
                                        <td style="color: #2c3e50; font-size: 15px;">Homepage - Quick Booking Form</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    
                    <!-- Call to Action -->
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: center; padding: 10px 0 20px;">
                                <a href="tel:' . $phone . '" style="display: inline-block; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: #ffffff; text-decoration: none; padding: 18px 50px; border-radius: 30px; font-size: 18px; font-weight: 700; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);">📞 CALL NOW: ' . $phone . '</a>
                            </td>
                        </tr>
                    </table>
                    
                    <!-- Reminder Note -->
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                        <tr>
                            <td style="padding: 20px;">
                                <p style="color: #856404; font-size: 14px; margin: 0; line-height: 1.6;">
                                    <strong>⏰ Reminder:</strong> This customer has requested a free pest inspection. Please call back within 2 hours for best conversion rates.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <!-- Footer -->
            <tr>
                <td style="background-color: #2c3e50; padding: 25px 40px; text-align: center;">
                    <p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 13px;">' . COMPANY_NAME . ' - Lead Management System</p>
                    <p style="color: rgba(255,255,255,0.5); margin: 8px 0 0; font-size: 11px;">This is an automated notification from your website.</p>
                </td>
            </tr>
        </table>
    </body>
    </html>';

    $mail->Body = $emailBody;
    $mail->AltBody = "Quick Booking Request\n\nName: $name\nPhone: $phone\n\nReceived: $currentDate\nSource: Homepage - Quick Booking Form\n\nPlease call the customer as soon as possible.";

    $mail->send();

    echo json_encode([
        'success' => true,
        'message' => 'Thank you, ' . $name . '! Our team will call you shortly to schedule your free inspection.'
    ]);

} catch (Exception $e) {
    error_log("Booking Form Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sorry, there was an error processing your request. Please call us directly at ' . COMPANY_PHONE
    ]);
}
