# Product Requirement Document (PRD) — EventHub Pro

---

## 1. Project Overview & Objective
**EventHub Pro** is a premium, college-level, real-world Event Management platform. The system is designed to allow college/university students, administrators, and external organizers to discover, register for, and manage events. It is built as a highly secure, fast, and modern web application with a unified glassmorphic UI/UX theme.

---

## 2. Technology Stack & Design System

### 2.1 Backend Architecture
* **Language:** PHP 8.0+ (Procedural and OOP helper patterns)
* **Database:** MySQL / MariaDB (utilizing `mysqli` extension with parameterized prepared statements)
* **API Integrations:** Stripe Payment Gateway (V2 Token Integration)

### 2.2 Frontend Architecture
* **Markup/Styles:** HTML5, CSS3 (Custom Glassmorphism styling sheets), Bootstrap 4 (Grid & Utility components)
* **Libraries & Interactive Scripts:**
  * **jQuery** — DOM traversal and event handling
  * **AJAX (Fetch API)** — Page filtering, dynamic event loading, and gallery filters
  * **GSAP (GreenSock)** & **AOS (Animate On Scroll)** — Modern micro-animations and reveal transitions
  * **Swiper.js** — Premium slider carousels
  * **Chart.js** — Interactive graphs on the organizer dashboard

### 2.3 Visual Language & Brand Identity
* **Theme:** Strictly dark-mode aesthetics (using deep charcoal backgrounds like `#070b14`).
* **Glassmorphism:** Styled cards, panels, and sidebars featuring semi-transparent backgrounds, backdrop blurs, and soft borders.
* **Typography:** Premium modern font families (`Outfit`, `Inter`, `Poppins`) loaded via Google Fonts.

---

## 3. Database Schema Specification
The application relies on six core relational database tables inside the `id13212736_event` schema:

### 3.1 `sign_up` (Account Credentials)
Tracks registered event organizers.
* `id` (INT, Primary Key, Auto Increment)
* `first_name` (VARCHAR, First name)
* `last_name` (VARCHAR, Last name)
* `email` (VARCHAR, Email address used for notifications)
* `password` (VARCHAR, Secure BCRYPT hash string)
* `username` (VARCHAR, Generated Virtual Column mirroring `email`)
* `full_name` (VARCHAR, Generated Virtual Column concatenating `first_name` and `last_name`)
* `created_at` (TIMESTAMP, Account creation time)

### 3.2 `create_event` (Events Repository)
Tracks event catalog metadata.
* `Event_ID` (INT, Primary Key, Auto Increment)
* `organizer_name` (VARCHAR, Matches organizer's `full_name`)
* `event_title` (VARCHAR, Title of the event)
* `event_desc` (TEXT, Full event overview description)
* `category` (VARCHAR, Event category)
* `eventtype` (VARCHAR, e.g., "Single Participant" or "Team Event")
* `min_team` (INT, Minimum team size, defaults to `0` for individual events)
* `max_team` (INT, Maximum team size, defaults to `0` for individual events)
* `event_rules` (TEXT, Rules list)
* `startdate` (DATE, Event launch date)
* `enddate` (DATE, Event completion date)
* `event_venue` (VARCHAR, Location details)
* `time` (VARCHAR, Hour/Minute duration metadata)
* `event_price` (INT, Entry ticket fee, defaults to `0` for free events)
* `event_thumbnail` (VARCHAR, Image path references under `/images`)
* `event_sponsors` (TEXT, Partner/sponsor names)
* `event_prizes` (TEXT, Rewards breakdown)
* `publish_event` (ENUM('yes','no'), Publication state)
* `open_closed` (VARCHAR, Registration availability state, e.g., "open" or "closed")

### 3.3 `singleevent_registration` (Individual Sign-ups)
* `id` (INT, Primary Key, Auto Increment)
* `event_id` (INT, Foreign Key mapping to `create_event.Event_ID`)
* `name` (VARCHAR, Full name of participant)
* `roll_no` (VARCHAR, Participant roll number)
* `college_name` (VARCHAR, College affiliation)
* `dept_name` (VARCHAR, Academic department)
* `email` (VARCHAR, Participant email)
* `mobile_no` (VARCHAR, Contact phone number)
* `paid_amount` (VARCHAR, Total amount paid via Stripe)
* `paid_amount_currency` (VARCHAR, Payment currency)
* `txn_id` (VARCHAR, Stripe Transaction ID)
* `payment_status` (VARCHAR, e.g., "succeeded")
* `created_at` (TIMESTAMP, Time of registration)

### 3.4 `teamevent_registration` (Team Sign-ups)
* `id` (INT, Primary Key, Auto Increment)
* `event_id` (INT, Foreign Key mapping to `create_event.Event_ID`)
* `team_name` (VARCHAR, Name of the team)
* `college_name` (VARCHAR, College affiliation)
* `student_name` (TEXT, Team members/leader name)
* `emails` (TEXT, Comma-separated or group emails)
* `mobile_no` (TEXT, Contact number)
* `paid_amount` (VARCHAR, Entry fee paid)
* `paid_amount_currency` (VARCHAR, Currency)
* `txn_id` (VARCHAR, Stripe Transaction ID)
* `payment_status` (VARCHAR, Stripe confirmation)
* `created_at` (TIMESTAMP, Time of registration)

### 3.5 `gallery` (Highlights & Photos)
* `id` (INT, Primary Key, Auto Increment)
* `event_name` (VARCHAR, Associated event)
* `organizer_name` (VARCHAR, Associated organizer)
* `image` (VARCHAR, Filename referenced under `/gallery`)
* `date` (DATE, Timeline mapping)
* `category` (VARCHAR, Associated category tag)

### 3.6 `feedback` (Ratings & Reviews)
* `id` (INT, Primary Key, Auto Increment)
* `event_id` (INT, Foreign Key matching `create_event.Event_ID`)
* `name` (VARCHAR, Feedback submitter name)
* `feedback` (TEXT, Verbatim comment review)
* `stars` (INT, Rating between 1 and 5)
* `created_at` (TIMESTAMP)

---

## 4. Completed Feature Log (Phase 1 — Phase 5)

### Phase 1: Foundation & Navigation Refinement
* **Unified Global Layout:** Transformed `header.php` and `footer.php` into modern glassmorphic headers.
* **Authentication Toggles:** Nav links dynamically adapt depending on session values:
  * Guest users see a "Login" button.
  * Logged-in organizers see "Dashboard" and "Logout" controls.
* **Backward-Compatible Redirects:** Implemented HTTP 301 headers inside `aboutus.php` to seamlessly guide users to the new `about.php` page.
* **New Pages:** Created static `about.php` (mentors/developers card grid layout) and `contact.php` (integrated AJAX support queries).

### Phase 2: Event Marketplace
* **AJAX Filtering Interface:** Built a responsive marketplace grid inside `events.php` connected to an AJAX `event_search.php` backend.
* **Advanced Multi-Filters:** Supports combined searches by Category, Event Type (Team/Individual), Ticket Price (Free/Paid), Registration Availability (Open/Closed), Date, Venue, and Organizer.
* **Offsets Pagination:** Renders a clean "Load More" pagination widget that increments offset queries in safe sets of 6.
* **Debounced Event Handling:** Integrated a custom Javascript debouncer to prevent redundant API queries while typing search keywords.

### Phase 3: Event Details & Gallery
* **Dynamic Event details (`eventpage.php`):** Shows countdown clocks, details tables, descriptions, and dynamic registration buttons.
* **Timeline Countdown:** Calculates timings:
  * Shows days, hours, minutes, and seconds countdown cells for upcoming events.
  * Shows "Event is Live" for ongoing events.
  * Shows "Event Completed" for past events.
* **Adaptive CTA Buttons:** If an event registration is closed, unpublished, or completed, the button updates its text and gets disabled.
* **Safe 404 Pages:** If a non-existent or invalid Event ID is queried, a premium "Event Not Found" screen renders inline.
* **Dynamic Gallery (`gallery.php`):** Implemented masonry media filters querying `gallery_media.php`. Added Year, Category, Event, and Organizer select options alongside a keyboard-friendly responsive lightbox.

### Phase 4: Organizer Dashboard & Event Management
* **Grid Overview:** Shows calculated total events, drafts, published events, and total registrants.
* **Chart.js Visualizations:** Renders interactive graphs showing "Event Status Distributions" and "Registration Counts per Event".
* **Unified Registrations Grids:** Organizers see a consolidated list of individual and team registrants for their own events.
* **Wizard Form Creators:** Step-by-step wizard guides creators through event setup (Basics, Schedule, Rules/Prizes, Media Uploads).
* **Upload Sanitization:** Enforces safe file uploads (limits file size, validates images via `getimagesize()`, and generates random file hashes).
* **State Updates:** Integrated CSRF-validated toggles for publishing, unpublishing, and re-opening events.

### Phase 5: Secure Authentication Refactor
* **Hashed Passwords Storage:** Encrypts user credentials during registration using BCRYPT (`password_hash(..., PASSWORD_DEFAULT)`).
* **Auto-Upgrade Logic:** If an older user logs in using a plaintext password, `log_in.php` checks it, hashes it, updates their database record, and verifies all future logins using `password_verify()`.
* **Fixation Protections:** Triggers `session_regenerate_id(true)` upon login.
* **Session Integrity:** Prefixed all session initializations with `session_status() === PHP_SESSION_NONE`.

### Homepage Enhancements & Feature Upgrades
* **Dynamic Core Categories:** Merges real-time database counts with primary categories (Technical, Cultural, Sports, Workshops, Others) on the homepage.
* **Self-Healing Database Seeders:** Automatically populates `create_event` and `gallery` tables with high-quality, realistic college events (Hackathon, Sports Meet, Music Fest, AI Bootcamp) when empty.
* **Bento Grid Modal & Links:** 
  * "Certificates" option removed from bento feature list.
  * Analytics links directly to `dashboard.php`.
  * Event Calendar links directly to `calendar.php`.
  * AI Recommendations, QR Tickets, Payments, and Email Reminders trigger rich, glassmorphic modal popups explaining features or showing live QR codes.
* **Dynamic Event Calendar (`calendar.php`):** Renders dynamic monthly calendar layouts pulling event dates from database, complete with links to detail pages.
* **Interactive AI Chatbot:** Floating widget on index page that dynamically parses user requests in real-time and responds with event descriptions, venues, rules, or checkout guidance.
* **Centered Empty-States:** Corrected grid layout of moments gallery empty warning blocks to render at full 100% centered width instead of 33% columns.
* **Dynamic QR Ticket Generator:** Embedded live online QR code API image requests inside individual (`successfullyregistered.php`) and team (`successfullyregistered1.php`) registration success views.

---

## 5. Security & Validation Controls
* **Prepared SQL queries:** Converted all dynamic SQL queries across search endpoints, page lookups, and state controllers to parameterized statements (`bind_param`) to block SQL injection.
* **CSRF Token Validation:** Validates `csrf_token` session variables on all POST actions (event creation, edits, deletion, publishing).
* **Ownership Checks:** Server-side scripts verify that the organizer matches the database creator row for the target event ID.
* **Generic Error Warnings:** Replaced descriptive login warnings with generic error states to prevent user account enumeration.
