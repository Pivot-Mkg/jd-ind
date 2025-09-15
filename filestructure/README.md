# James Douglas Website

A complete website structure for James Douglas executive search firm.

## Project Structure

```
filestructure/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   ├── js/
│   │   └── main.js            # Main JavaScript file
│   └── images/
│       └── JD-logo.png        # Company logo (placeholder)
├── includes/
│   ├── navbar.php             # Navigation bar component
│   └── footer.php             # Footer component
├── practices/                 # Practice area pages
│   ├── bfsi.html
│   ├── technology-digital.html
│   ├── industrial-logistics.html
│   ├── family-business-private-enterprise.html
│   └── nationalization.html
├── insights/                  # Insights hub pages
│   ├── blog.html
│   ├── whitepapers.html
│   └── gcc-talent-trends.html
├── region/                    # Regional pages
│   ├── uae-middle-east.html
│   └── india.html
├── forms/                     # PHP form handlers
│   ├── contact.php
│   └── newsletter.php
├── index.html                 # Home page
├── for-companies.html         # For Companies page
├── for-leaders.html           # For Leaders page
├── about-us.html              # About Us page
├── careers.html               # Careers page
├── contact.html               # Contact Us page
├── navbar.html                # Original navbar file
├── .htaccess                  # URL rewriting and security
└── README.md                  # This file
```

## Features

- **Responsive Design**: Bootstrap 5 framework for mobile-first responsive design
- **Clean URLs**: .htaccess configuration for SEO-friendly URLs
- **Form Handling**: PHP forms for contact and newsletter subscription
- **Smooth Navigation**: Enhanced dropdown menus with hover effects
- **Modular Structure**: Reusable PHP includes for navbar and footer
- **Security**: Basic security headers and file protection

## Technology Stack

- **HTML5**: Semantic markup
- **CSS3**: Custom styles with Bootstrap 5
- **JavaScript**: Vanilla JS for interactions
- **PHP**: Server-side form processing
- **Apache**: .htaccess for URL rewriting

## Setup Instructions

1. **Web Server**: Deploy to a web server with PHP support (Apache/Nginx)
2. **Logo**: Replace `assets/images/JD-logo.png` with actual company logo
3. **Email Configuration**: Update email addresses in PHP form handlers
4. **Database**: Set up database for newsletter subscriptions (optional)
5. **SSL Certificate**: Configure HTTPS for production

## Navigation Structure

Based on the navbar, the website includes:

### Main Pages
- Home (/)
- For Companies (/for-companies)
- For Leaders (/for-leaders)
- About Us (/about-us)
- Careers (/careers)
- Contact Us (/contact)

### Practices (Dropdown)
- Banking & Financial Services (/practices/bfsi)
- Technology & Digital (/practices/technology-digital)
- Industrial & Logistics (/practices/industrial-logistics)
- Family Business & Private Enterprise (/practices/family-business-private-enterprise)
- Nationalization (/practices/nationalization)

### Insights Hub (Dropdown)
- Blog (/insights/blog)
- Whitepapers (/insights/whitepapers)
- GCC Talent Trends Reports (/insights/gcc-talent-trends)

### Regional Pages (Dropdown)
- UAE & Middle East (/region/uae-middle-east)
- India (/region/india)

## Form Functionality

### Contact Form
- Located at `/contact`
- Handles: name, email, company, phone, subject, message
- PHP processing at `/forms/contact.php`
- Email notification to company

### Newsletter Subscription
- Located at `/insights/blog`
- Handles: email subscription
- PHP processing at `/forms/newsletter.php`

## Customization

1. **Colors**: Modify CSS variables in `assets/css/style.css`
2. **Content**: Update HTML content in respective pages
3. **Images**: Add images to `assets/images/` directory
4. **Forms**: Customize form fields and validation in PHP handlers
5. **Navigation**: Modify navbar structure in `includes/navbar.php`

## Production Checklist

- [ ] Replace placeholder logo with actual logo
- [ ] Update contact information and addresses
- [ ] Configure email settings in PHP forms
- [ ] Set up SSL certificate
- [ ] Configure database for newsletter (if needed)
- [ ] Test all forms and navigation
- [ ] Optimize images for web
- [ ] Set up analytics tracking
- [ ] Configure backup system

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- IE 11+ (limited support)

## License

Proprietary - James Douglas Executive Search