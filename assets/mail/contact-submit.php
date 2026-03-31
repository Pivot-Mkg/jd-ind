<?php
// assets/mail/contact-submit.php
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

function send_response(int $statusCode, string $status, string $message): void
{
    http_response_code($statusCode);
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(405, 'error', 'Method not allowed.');
}

$assist = trim($_POST['assist'] ?? '');
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$organization = trim($_POST['organization'] ?? '');
$designation = trim($_POST['designation'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$city = trim($_POST['city'] ?? '');
$country = trim($_POST['country'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];
if ($assist === '') {
    $errors[] = 'Please select how we can assist you.';
}
if ($firstName === '') {
    $errors[] = 'First name is required.';
}
if ($lastName === '') {
    $errors[] = 'Last name is required.';
}
if ($organization === '') {
    $errors[] = 'Company is required.';
}
if ($designation === '') {
    $errors[] = 'Designation is required.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email address is required.';
}
if ($phone === '') {
    $errors[] = 'Phone number is required.';
}
if ($city === '') {
    $errors[] = 'City is required.';
}
if ($country === '') {
    $errors[] = 'Country is required.';
}
if ($message === '') {
    $errors[] = 'Message is required.';
}

if (!empty($errors)) {
    send_response(400, 'error', implode(' ', $errors));
}

$to = 'contactus@jamesdouglas.co.in, aakash@pivotmkg.com, ritu.sanghavi@jamesdouglas.co.in';
$subject = 'default testing website mail';

$body = "New contact form submission:\n\n";
$body .= "How can we assist: {$assist}\n";
$body .= "Name: {$firstName} {$lastName}\n";
$body .= "Company: {$organization}\n";
$body .= "Designation: {$designation}\n";
$body .= "Email: {$email}\n";
$body .= "Phone: {$phone}\n";
$body .= "City: {$city}\n";
$body .= "Country: {$country}\n\n";
$body .= "Message:\n{$message}\n";

$headers = "From: no-reply@jamesdouglas.co.in\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$sent = false;
if (function_exists('mail')) {
    $sent = @mail($to, $subject, $body, $headers);
}

if ($sent) {
    send_response(200, 'success', 'Thank you for your message! We will get back to you soon.');
}

error_log('Contact form failed: mail() returned false. Remote IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
send_response(500, 'error', 'Unable to send your message at this time. Please try again later.');
