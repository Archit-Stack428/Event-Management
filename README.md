# EVENTHUB PRO — Event Management & Discovery Platform

EVENTHUB PRO is a modern college-level, production-ready Event Management and Event Discovery platform built with PHP 8 and MySQL. It provides a visual, secure, and user-friendly marketplace for users to browse, search, and register for various institutional events, alongside a robust dashboard for organizers to create events, manage tickets, track analytics, and handle check-ins.

---

## Key Features

### Participant Experience
1. **Marketplace Discovery:** Access the full catalog of events categorized by type (Technical, Cultural, Sports, Workshops).
2. **Advanced Search & Filtering:** Perform instant AJAX searches and refine listings by date, price (Free vs. Paid), category, availability, venue, and organizer.
3. **Dual Registration System:** Support for both individual and team registrations with dynamic form fields based on capacity settings.
4. **QR Validation Tickets:** Automates digital QR codes for registration verification.
5. **Secure Payments:** Direct Stripe payment integration for paid events.
6. **Platform Feedback:** Star ratings and experience reviews for attended events.

### Organizer Experience
1. **Analytics Dashboard:** Graphical KPIs displaying total events, registrations, draft states, and charts representing ticket statistics.
2. **Multi-Section Creation Wizard:** Clean step-by-step form to declare event schedules, custom rules, ticket prices, and thumbnail uploads.
3. **Check-In Validation:** Instant scanning of participant tickets at the event venue.
4. **Media Management:** Gallery upload wizard with drag-and-drop file support.

---

## Technology Stack

- **Backend:** PHP 8+, MySQL (PDO / MySQLi)
- **Frontend:** HTML5, Vanilla CSS3 (Custom Glassmorphism styling), Bootstrap 4 (utility classes), jQuery (AJAX support)
- **Libraries:** Chart.js, GSAP (Animations), AOS (Scroll Reveals), Lenis (Smooth Scroll), tsParticles (Backdrops), QR Code Generator

---

## Installation & Setup

### 1. Prerequisites
- **Web Server:** Apache (e.g., XAMPP, Laragon, or MAMP)
- **PHP Version:** 8.0 or higher
- **Database Server:** MySQL 5.7 or higher

### 2. Database Initialization
1. Start MySQL on your local web server.
2. Create a new database named `id13212736_event`.
3. Import the database schema from the [database_schema.sql](database_schema.sql) file:
   ```bash
   mysql -u root -p id13212736_event < database_schema.sql
   ```

### 3. Configuration Setup
Create a new file named `config.php` in the root directory (based on `config.php` templates):
```php
<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_mysql_username');
define('DB_PASS', 'your_mysql_password');
define('DB_NAME', 'id13212736_event');

// Stripe Credentials (Optional)
define('STRIPE_SECRET_KEY', 'your_stripe_secret_key');
define('STRIPE_PUBLISHABLE_KEY', 'your_stripe_publishable_key');

// Debug Mode
define('APP_DEBUG', false);
```

### 4. Setting Permissions
Ensure that the web server has write permissions on the following directories for image uploads:
- `/images` (Event thumbnails)
- `/gallery` (Gallery uploads)

---

## Directory Structure Overview

```
EventManagementWebsite/
│
├── assets/                # Design elements (Bootstrap, custom stylesheets, icons)
│   ├── css/               # Core stylesheets (eventhub-pro.css, dashboard-pro.css)
│   └── js/                # Scripts (eventhub-pro.js, dashboard-pro.js)
│
├── images/                # Dynamic uploaded event thumbnail files
├── gallery/               # Gallery uploads
│
├── config.php             # System secrets and configurations (ignored in Git)
├── dbconnect.php          # Central database initialization logic
│
├── index.php              # Premium homepage
├── events.php             # Event marketplace list page
├── eventpage.php          # Detailed single-event details page
├── gallery.php            # Photo and media gallery interface
├── about.php              # Institution and engineering team introduction
├── contact.php            # Contact form and message center
│
├── login.php / signup.php # User forms
├── log_in.php / sign_up.php # Auth logic handlers
├── dashboard.php          # Organizer control panel
│
└── database_schema.sql    # Full SQL database structure
```

---

## Security Practices Followed

- **Secure Session Management:** Login verification prevents unauthorized route access to sensitive dashboards.
- **SQL Injection Prevention:** Every dynamic database query uses prepared queries with parameter bindings (`bind_param`).
- **Encrypted Password Storage:** Passwords are hashed using standard `password_hash()` (BCRYPT). Existing plaintext entries are migrated automatically on their next successful login.
- **Input Sanitization:** User entries are trimmed and sanitized before being processed.
- **Output Escaping:** Direct outputs from user submissions are escaped (`htmlspecialchars`) to block Cross-Site Scripting (XSS).
- **Environment Isolation:** Configuration values and credentials are separated from Git tracking to avoid disclosure.
