<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/autoload.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mailConfig = [
    'host'               => 'email-smtp.us-east-1.amazonaws.com',
    'port'               => 587,
    'encryption'         => PHPMailer::ENCRYPTION_STARTTLS,
    'username'           => 'AKIA5ND33FW4GI4ZIXZL',
    'password'           => 'BK91vk6GIk+gK6akNq1+eYpUl3koup2Ty2dr/iqRLhNk',
    'from_email'         => 'enquiry@jamesdouglas.ae',
    'from_name'          => 'James Douglas Website',
    'notification_email' => 'enquiry@jamesdouglas.ae',
    //'cc_email' => 'aakash@pivotmkg.com',
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $fullName = trim($_POST['fullName'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $service  = trim($_POST['service'] ?? '');
    $message  = trim($_POST['message'] ?? '');

    // Validate inputs
    $errors = [];

    if (empty($fullName)) {
        $errors[] = 'Full name is required';
    }

    if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }

    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    }

    if (empty($message)) {
        $errors[] = 'Message is required';
    }

    // If no errors, process the form
    if (empty($errors)) {
        $subject = 'New Contact Form Submission from ' . $fullName;

        // Email body
        $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        $emailBody   = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #23235b; color: white; padding: 15px; border-radius: 5px 5px 0 0; }
                .content { padding: 20px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; }
                .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='margin: 0;'>New Contact Form Submission</h2>
                </div>
                <div class='content'>
                    <p><strong>Name:</strong> " . htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') . "</p>
                    <p><strong>Email:</strong> " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "</p>
                    <p><strong>Phone:</strong> " . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . "</p>
                    <p><strong>Service Interested In:</strong> " . (! empty($service) ? htmlspecialchars($service, ENT_QUOTES, 'UTF-8') : 'Not specified') . "</p>
                    <p><strong>Message:</strong></p>
                    <p>{$safeMessage}</p>
                </div>
                <div class='footer'>
                    <p>This email was sent from the contact form on James Douglas website.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $altBody = "New Contact Form Submission\n"
            . "Name: {$fullName}\n"
            . "Email: {$email}\n"
            . "Phone: {$phone}\n"
            . "Service Interested In: " . ($service ?: 'Not specified') . "\n\n"
            . "Message:\n{$message}";

        try {
            $mailer = new PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host       = $mailConfig['host'];
            $mailer->Port       = $mailConfig['port'];
            $mailer->SMTPAuth   = true;
            $mailer->SMTPSecure = $mailConfig['encryption'];
            $mailer->Username   = $mailConfig['username'];
            $mailer->Password   = $mailConfig['password'];
            $mailer->CharSet    = 'UTF-8';
            $mailer->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
            $mailer->addAddress($mailConfig['notification_email']);
            //$mailer->addCC($mailConfig['cc_email']);
            $mailer->addReplyTo($email, $fullName);
            $mailer->Subject = $subject;
            $mailer->isHTML(true);
            $mailer->Body    = $emailBody;
            $mailer->AltBody = $altBody;
            $mailer->addCustomHeader('X-Mailer', 'PHPMailer');

            if (! empty($_SERVER['REMOTE_ADDR'])) {
                $mailer->addCustomHeader('X-Originating-IP', $_SERVER['REMOTE_ADDR']);
            }

            $mailer->send();

            echo json_encode([
                'success'  => true,
                'message'  => 'Thank you for your message! We will get back to you soon.',
                'redirect' => '../thank-you.html',
            ]);
        } catch (Exception $e) {
            $errorDetail = isset($mailer) ? $mailer->ErrorInfo : $e->getMessage();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to send email. Please try again later.',
                'error'   => 'Mailer Error: ' . $errorDetail,
            ]);
        }
    } else {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all required fields.',
            'errors'  => $errors,
        ]);
    }
} else {
    // If someone tries to access this file directly, redirect to contact page
    header('Location: ../contact-us.html');
    exit();
}
