<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Us - James Douglas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main>
        <div class="hero-section">
            <div class="container">
                <h1>Contact Us</h1>
                <p class="lead">Get in touch with our executive search experts</p>
            </div>
        </div>
        
        <div class="container content-section">
            <div class="row">
                <div class="col-lg-8">
                    <div class="form-container">
                        <h2>Send us a Message</h2>
                        
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">Thank you for your message. We'll get back to you soon!</div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">There was an error sending your message. Please try again.</div>
                        <?php endif; ?>
                        
                        <form action="forms/contact.php" method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" required>
                                    <div class="invalid-feedback">Please provide your first name.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" required>
                                    <div class="invalid-feedback">Please provide your last name.</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Please provide a valid email address.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="company" class="form-label">Company</label>
                                <input type="text" class="form-control" id="company" name="company">
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <select class="form-select" id="subject" name="subject" required>
                                    <option value="">Select a subject</option>
                                    <option value="executive-search">Executive Search</option>
                                    <option value="leadership-consulting">Leadership Consulting</option>
                                    <option value="career-opportunities">Career Opportunities</option>
                                    <option value="partnership">Partnership Inquiry</option>
                                    <option value="other">Other</option>
                                </select>
                                <div class="invalid-feedback">Please select a subject.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                <div class="invalid-feedback">Please provide your message.</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Dubai Office</h5>
                            <p class="card-text">
                                <strong>Address:</strong><br>
                                DIFC, Dubai, UAE<br><br>
                                <strong>Phone:</strong> +971 4 XXX XXXX<br>
                                <strong>Email:</strong> dubai@jamesdouglas.com
                            </p>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Mumbai Office</h5>
                            <p class="card-text">
                                <strong>Address:</strong><br>
                                BKC, Mumbai, India<br><br>
                                <strong>Phone:</strong> +91 22 XXXX XXXX<br>
                                <strong>Email:</strong> mumbai@jamesdouglas.com
                            </p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Business Hours</h5>
                            <p class="card-text">
                                <strong>Sunday - Thursday:</strong><br>
                                9:00 AM - 6:00 PM GST<br><br>
                                <strong>Friday - Saturday:</strong><br>
                                Closed
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>