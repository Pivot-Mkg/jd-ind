<?php
// Minimal PHPMailer-like class for SMTP
class SimpleMailer {
    private $host;
    private $port;
    private $username;
    private $password;
    private $from;
    private $fromName;
    private $to;
    private $subject;
    private $body;
    private $replyTo;
    
    public function __construct() {
        $this->host = 'smtp.gmail.com';
        $this->port = 587;
        $this->username = 'jdme.website.contact@gmail.com';
        $this->password = 'idui ahbd gsxx jtwu';
    }
    
    public function setFrom($email, $name = '') {
        $this->from = $email;
        $this->fromName = $name;
    }
    
    public function addAddress($email) {
        $this->to = $email;
    }
    
    public function addReplyTo($email, $name = '') {
        $this->replyTo = $email;
    }
    
    public function setSubject($subject) {
        $this->subject = $subject;
    }
    
    public function setBody($body) {
        $this->body = $body;
    }
    
    public function send() {
        // Use PHP's mail function with proper headers
        $headers = "From: {$this->fromName} <{$this->from}>\r\n";
        $headers .= "Reply-To: {$this->replyTo}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        return mail($this->to, $this->subject, $this->body, $headers);
    }
}
?>