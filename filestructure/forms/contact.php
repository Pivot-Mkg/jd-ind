<?php
// Contact form handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Basic validation
    $errors = [];
    
    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email address is required';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    if (empty($errors)) {
        // Prepare email content
        $to = 'info@jamesdouglas.com'; // Replace with actual email
        $emailSubject = 'Contact Form Submission: ' . $subject;
        
        $emailBody = "New contact form submission:\n\n";
        $emailBody .= "Name: {$firstName} {$lastName}\n";
        $emailBody .= "Email: {$email}\n";
        $emailBody .= "Company: {$company}\n";
        $emailBody .= "Phone: {$phone}\n";
        $emailBody .= "Subject: {$subject}\n\n";
        $emailBody .= "Message:\n{$message}\n";
        
        $headers = "From: {$email}\r\n";
        $headers .= "Reply-To: {$email}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // Send email (in production, use a proper mail service)
        if (mail($to, $emailSubject, $emailBody, $headers)) {
            // Success - redirect with success message
            header('Location: /contact?success=1');
            exit;
        } else {
            // Error sending email
            header('Location: /contact?error=1');
            exit;
        }
    } else {
        // Validation errors - redirect with error message
        header('Location: /contact?error=1');
        exit;
    }
} else {
    // Not a POST request - redirect to contact page
    header('Location: /contact');
    exit;
}
?>