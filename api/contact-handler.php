<?php
/**
 * Contact Form Handler - Request a Free Inspection
 * Handles submissions from Contact Us page
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
    // Prevent header injection
    $data = str_replace(["\r", "\n", "%0a", "%0d"], '', $data);
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhone($phone) {
    return preg_match('/^[0-9]{10}$/', preg_replace('/[\s\-]/', '', $phone));
}

try {
    // Get and sanitize form data
    $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $city = isset($_POST['city']) ? sanitizeInput($_POST['city']) : '';
    $service = isset($_POST['service']) ? sanitizeInput($_POST['service']) : '';
    $propertyType = isset($_POST['property-type']) ? sanitizeInput($_POST['property-type']) : '';
    $message = isset($_POST['message']) ? sanitizeInput($_POST['message']) : '';
    $callback = isset($_POST['callback']) ? true : false;

    // Validate required fields
    if (empty($name) || empty($phone) || empty($city) || empty($service) || empty($propertyType)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        exit();
    }

    if (!validatePhone($phone)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please enter a valid 10-digit phone number']);
        exit();
    }

    if (!empty($email) && !validateEmail($email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
        exit();
    }

    // Service mapping
    $serviceLabels = [
        'bed-bug' => 'Bed Bug Control',
        'termite' => 'Termite Control',
        'cockroach' => 'Cockroach Control',
        'rodent' => 'Rodent Control',
        'mosquito' => 'Mosquito Control',
        'general' => 'Ant & General Pest',
        'commercial' => 'Commercial/AMC',
        'other' => 'Other Service'
    ];

    $propertyLabels = [
        'residential' => 'Residential - Apartment',
        'independent' => 'Residential - Independent House',
        'villa' => 'Residential - Villa',
        'office' => 'Commercial - Office',
        'restaurant' => 'Commercial - Restaurant',
        'hotel' => 'Commercial - Hotel',
        'hospital' => 'Commercial - Hospital',
        'school' => 'Commercial - School/College',
        'other-commercial' => 'Commercial - Other'
    ];

    $serviceName = $serviceLabels[$service] ?? $service;
    $propertyName = $propertyLabels[$propertyType] ?? $propertyType;
    $currentDate = date('d M Y, h:i A');

    // Initialize PHPMailer for admin notification
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
    
    if (!empty($email)) {
        $mail->addReplyTo($email, $name);
    }

    $mail->isHTML(true);
    $mail->Subject = "New Inspection Request - $serviceName | $name";

    // Professional HTML email template for admin
    $adminEmailBody = '
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
                    <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">🏠 New Inspection Request</h1>
                    <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 14px;">Received on ' . $currentDate . '</p>
                </td>
            </tr>
            
            <!-- Content -->
            <tr>
                <td style="padding: 40px;">
                    <!-- Customer Info Card -->
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 8px; margin-bottom: 25px;">
                        <tr>
                            <td style="padding: 25px;">
                                <h2 style="color: #2c3e50; margin: 0 0 20px; font-size: 18px; border-bottom: 2px solid #c9a227; padding-bottom: 10px;">👤 Customer Information</h2>
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="8">
                                    <tr>
                                        <td style="color: #666; font-size: 14px; width: 40%;">Full Name:</td>
                                        <td style="color: #2c3e50; font-size: 14px; font-weight: 600;">' . $name . '</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #666; font-size: 14px;">Phone Number:</td>
                                        <td style="color: #2c3e50; font-size: 14px; font-weight: 600;"><a href="tel:' . $phone . '" style="color: #c9a227; text-decoration: none;">' . $phone . '</a></td>
                                    </tr>
                                    ' . (!empty($email) ? '<tr>
                                        <td style="color: #666; font-size: 14px;">Email Address:</td>
                                        <td style="color: #2c3e50; font-size: 14px; font-weight: 600;"><a href="mailto:' . $email . '" style="color: #c9a227; text-decoration: none;">' . $email . '</a></td>
                                    </tr>' : '') . '
                                    <tr>
                                        <td style="color: #666; font-size: 14px;">City:</td>
                                        <td style="color: #2c3e50; font-size: 14px; font-weight: 600;">' . $city . '</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    
                    <!-- Service Details Card -->
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #fff8e6; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #c9a227;">
                        <tr>
                            <td style="padding: 25px;">
                                <h2 style="color: #2c3e50; margin: 0 0 20px; font-size: 18px;">🛠️ Service Details</h2>
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="8">
                                    <tr>
                                        <td style="color: #666; font-size: 14px; width: 40%;">Service Required:</td>
                                        <td style="color: #2c3e50; font-size: 14px; font-weight: 600;">' . $serviceName . '</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #666; font-size: 14px;">Property Type:</td>
                                        <td style="color: #2c3e50; font-size: 14px; font-weight: 600;">' . $propertyName . '</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #666; font-size: 14px;">Callback Requested:</td>
                                        <td style="color: #2c3e50; font-size: 14px; font-weight: 600;">' . ($callback ? '✅ Yes' : '❌ No') . '</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    
                    ' . (!empty($message) ? '
                    <!-- Message Card -->
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 8px; margin-bottom: 25px;">
                        <tr>
                            <td style="padding: 25px;">
                                <h2 style="color: #2c3e50; margin: 0 0 15px; font-size: 18px;">💬 Additional Details</h2>
                                <p style="color: #555; font-size: 14px; line-height: 1.6; margin: 0; white-space: pre-wrap;">' . $message . '</p>
                            </td>
                        </tr>
                    </table>
                    ' : '') . '
                    
                    <!-- Action Button -->
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: center; padding: 20px 0;">
                                <a href="tel:' . $phone . '" style="display: inline-block; background: linear-gradient(135deg, #c9a227 0%, #e8c547 100%); color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 30px; font-size: 16px; font-weight: 600;">📞 Call Customer Now</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <!-- Footer -->
            <tr>
                <td style="background-color: #2c3e50; padding: 25px 40px; text-align: center;">
                    <p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 12px;">' . COMPANY_NAME . ' | ' . COMPANY_PHONE . '</p>
                    <p style="color: rgba(255,255,255,0.6); margin: 10px 0 0; font-size: 11px;">This email was generated automatically from the website contact form.</p>
                </td>
            </tr>
        </table>
    </body>
    </html>';

    $mail->Body = $adminEmailBody;
    $mail->AltBody = "New Inspection Request\n\nName: $name\nPhone: $phone\nEmail: $email\nCity: $city\nService: $serviceName\nProperty: $propertyName\nCallback: " . ($callback ? 'Yes' : 'No') . "\nMessage: $message\n\nReceived on: $currentDate";

    $mail->send();

    // Send confirmation email to customer if email provided
    if (!empty($email)) {
        $customerMail = new PHPMailer(true);
        
        $customerMail->isSMTP();
        $customerMail->Host = SMTP_HOST;
        $customerMail->SMTPAuth = SMTP_AUTH;
        $customerMail->Username = SMTP_USERNAME;
        $customerMail->Password = SMTP_PASSWORD;
        $customerMail->SMTPSecure = SMTP_SECURE;
        $customerMail->Port = SMTP_PORT;
        $customerMail->CharSet = MAIL_CHARSET;

        $customerMail->setFrom(MAIL_FROM_EMAIL, COMPANY_NAME);
        $customerMail->addAddress($email, $name);
        $customerMail->addReplyTo(COMPANY_EMAIL, COMPANY_NAME);

        $customerMail->isHTML(true);
        $customerMail->Subject = "Thank You for Your Inquiry - " . COMPANY_NAME;

        // Customer confirmation email
        $customerEmailBody = '
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
                    <td style="background: linear-gradient(135deg, #c9a227 0%, #e8c547 100%); padding: 40px; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 600;">Thank You! 🎉</h1>
                        <p style="color: rgba(255,255,255,0.95); margin: 15px 0 0; font-size: 16px;">Your inspection request has been received</p>
                    </td>
                </tr>
                
                <!-- Content -->
                <tr>
                    <td style="padding: 40px;">
                        <p style="color: #2c3e50; font-size: 16px; line-height: 1.6; margin: 0 0 25px;">Dear <strong>' . $name . '</strong>,</p>
                        
                        <p style="color: #555; font-size: 15px; line-height: 1.8; margin: 0 0 25px;">Thank you for choosing <strong>' . COMPANY_NAME . '</strong>! We have received your request for a free pest inspection and our team will contact you within <strong>24 hours</strong>.</p>
                        
                        <!-- Request Summary -->
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 8px; margin-bottom: 25px;">
                            <tr>
                                <td style="padding: 25px;">
                                    <h3 style="color: #2c3e50; margin: 0 0 20px; font-size: 16px; border-bottom: 2px solid #c9a227; padding-bottom: 10px;">📋 Your Request Summary</h3>
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="10">
                                        <tr>
                                            <td style="color: #666; font-size: 14px; width: 40%;">Reference Date:</td>
                                            <td style="color: #2c3e50; font-size: 14px; font-weight: 600;">' . $currentDate . '</td>
                                        </tr>
                                        <tr>
                                            <td style="color: #666; font-size: 14px;">Service Requested:</td>
                                            <td style="color: #2c3e50; font-size: 14px; font-weight: 600;">' . $serviceName . '</td>
                                        </tr>
                                        <tr>
                                            <td style="color: #666; font-size: 14px;">Property Type:</td>
                                            <td style="color: #2c3e50; font-size: 14px; font-weight: 600;">' . $propertyName . '</td>
                                        </tr>
                                        <tr>
                                            <td style="color: #666; font-size: 14px;">Location:</td>
                                            <td style="color: #2c3e50; font-size: 14px; font-weight: 600;">' . $city . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- What\'s Next -->
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #fff8e6; border-radius: 8px; border-left: 4px solid #c9a227; margin-bottom: 25px;">
                            <tr>
                                <td style="padding: 25px;">
                                    <h3 style="color: #2c3e50; margin: 0 0 15px; font-size: 16px;">🚀 What Happens Next?</h3>
                                    <ol style="color: #555; font-size: 14px; line-height: 2; margin: 0; padding-left: 20px;">
                                        <li>Our team will call you to confirm the appointment</li>
                                        <li>A certified technician will visit for free inspection</li>
                                        <li>You\'ll receive a detailed quote with no obligation</li>
                                        <li>Treatment scheduled at your convenience</li>
                                    </ol>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Contact Info -->
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 25px;">
                            <tr>
                                <td style="text-align: center; padding: 20px;">
                                    <p style="color: #666; font-size: 14px; margin: 0 0 15px;">Need immediate assistance? Contact us:</p>
                                    <a href="tel:' . COMPANY_PHONE . '" style="display: inline-block; background: linear-gradient(135deg, #c9a227 0%, #e8c547 100%); color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 25px; font-size: 15px; font-weight: 600; margin: 5px;">📞 ' . COMPANY_PHONE . '</a>
                                    <a href="mailto:' . COMPANY_EMAIL . '" style="display: inline-block; background-color: #2c3e50; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 25px; font-size: 15px; font-weight: 600; margin: 5px;">✉️ Email Us</a>
                                </td>
                            </tr>
                        </table>
                        
                        <p style="color: #555; font-size: 14px; line-height: 1.6; margin: 0;">Best regards,<br><strong>The ' . COMPANY_NAME . ' Team</strong></p>
                    </td>
                </tr>
                
                <!-- Footer -->
                <tr>
                    <td style="background-color: #2c3e50; padding: 30px 40px; text-align: center;">
                        <p style="color: #ffffff; margin: 0 0 10px; font-size: 16px; font-weight: 600;">' . COMPANY_NAME . '</p>
                        <p style="color: rgba(255,255,255,0.7); margin: 0 0 5px; font-size: 13px;">🏠 ' . COMPANY_ADDRESS . '</p>
                        <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 13px;">📞 ' . COMPANY_PHONE . ' | ✉️ ' . COMPANY_EMAIL . '</p>
                        <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.2); margin: 20px 0;">
                        <p style="color: rgba(255,255,255,0.5); margin: 0; font-size: 11px;">© ' . date('Y') . ' ' . COMPANY_NAME . '. All rights reserved.</p>
                    </td>
                </tr>
            </table>
        </body>
        </html>';

        $customerMail->Body = $customerEmailBody;
        $customerMail->AltBody = "Thank You for Your Inquiry!\n\nDear $name,\n\nThank you for choosing " . COMPANY_NAME . "! We have received your request for a free pest inspection.\n\nRequest Summary:\n- Service: $serviceName\n- Property: $propertyName\n- Location: $city\n- Date: $currentDate\n\nOur team will contact you within 24 hours.\n\nBest regards,\n" . COMPANY_NAME . "\n" . COMPANY_PHONE . "\n" . COMPANY_EMAIL;

        $customerMail->send();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your request has been submitted successfully. We will contact you within 24 hours.'
    ]);

} catch (Exception $e) {
    error_log("Contact Form Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sorry, there was an error sending your request. Please try again or call us directly.'
    ]);
}
