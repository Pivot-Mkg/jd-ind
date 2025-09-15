<?php
// Newsletter subscription handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $email = trim($_POST['email'] ?? '');
    
    // Basic validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: /insights/blog?error=1');
        exit;
    }
    
    // Here you would typically:
    // 1. Add email to your newsletter database
    // 2. Send confirmation email
    // 3. Integrate with email marketing service (MailChimp, SendGrid, etc.)
    
    // For now, we'll just simulate success
    // In production, implement proper newsletter subscription logic
    
    // Example database insertion (uncomment and modify as needed):
    /*
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=jamesdouglas', $username, $password);
        $stmt = $pdo->prepare('INSERT INTO newsletter_subscribers (email, subscribed_at) VALUES (?, NOW())');
        $stmt->execute([$email]);
        
        // Send confirmation email
        $subject = 'Newsletter Subscription Confirmation';
        $message = 'Thank you for subscribing to our newsletter!';
        $headers = 'From: newsletter@jamesdouglas.com';
        
        mail($email, $subject, $message, $headers);
        
        header('Location: /insights/blog?subscribed=1');
        exit;
    } catch (PDOException $e) {
        header('Location: /insights/blog?error=1');
        exit;
    }
    */
    
    // Temporary success redirect
    header('Location: /insights/blog?subscribed=1');
    exit;
    
} else {
    // Not a POST request - redirect to blog page
    header('Location: /insights/blog');
    exit;
}
?>