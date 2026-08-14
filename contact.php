<?php
/* =============================================================
   EVENTHUB PRO — Contact Us
   Premium contact page using the shared glassmorphic header/footer.
   Saves queries in the feedback database table with event_id = 0.
   ============================================================= */
session_start();
include('dbconnect.php');

$page_title = 'Contact Us — EventHub Pro';
$page_desc = 'Get in touch with the EventHub Pro team. Send us your feedback, bug reports, or general inquiries.';

$success_msg = '';
$error_msg = '';

if (isset($_POST['submit'])) {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name !== '' && $email !== '' && $message !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Format the feedback message text to include email and subject
        $formatted_message = "Email: " . $email . "\nSubject: " . $subject . "\nMessage: " . $message;
        $event_id = 0;
        $stars = 0;

        $stmt = $conn->prepare("INSERT INTO feedback (event_id, name, feedback, stars) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('issi', $event_id, $name, $formatted_message, $stars);
        
        if ($stmt->execute()) {
            $success_msg = 'Your message has been sent successfully! We will get back to you shortly.';
        } else {
            $error_msg = 'There was an error saving your message. Please try again.';
        }
        $stmt->close();
    } else {
        $error_msg = 'Please fill out all required fields with a valid email address.';
    }
}

include('header.php');
?>

<!-- ===== Contact Hero ===== -->
<section class="eh-hero" style="padding-bottom: 60px;">
  <div class="container-max">
    <div style="text-align: center; max-width: 800px; margin: 0 auto;">
      <span class="eh-badge"><i class="fas fa-envelope"></i> Contact Us</span>
      <h1 style="font-size: clamp(34px, 5vw, 58px);">Get in <span class="grad">Touch</span></h1>
      <p>Have a question, feedback, or need help with hosting an event? We'd love to hear from you.</p>
    </div>
  </div>
</section>

<!-- ===== Contact Content ===== -->
<section class="eh-section" style="padding-top: 0;">
  <div class="container-max">
    <div class="row" style="gap: 40px 0;">
      <!-- Contact details -->
      <div class="col-md-5" data-aos="fade-right">
        <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #fff; margin-bottom: 24px;">Contact Information</h2>
        <p style="color: var(--eh-muted); font-size: 16px; margin-bottom: 30px;">
          Reach out to us directly or fill out the form. Our support team typically responds within 24 business hours.
        </p>

        <div style="display: flex; flex-direction: column; gap: 24px;">
          <div style="display: flex; gap: 16px; align-items: flex-start;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(124, 58, 237, 0.1); display: flex; align-items: center; justify-content: center; color: var(--eh-accent); font-size: 18px; flex-shrink: 0;">
              <i class="fas fa-map-marker-alt"></i>
            </div>
            <div>
              <h4 style="color: #fff; font-size: 16px; font-weight: 600; margin: 0 0 4px;">Address</h4>
              <p style="color: var(--eh-muted); font-size: 14px; margin: 0;">Dr. Virendra Swaroop Institute of Professional Studies, Kidwai Nagar, Kanpur, Uttar Pradesh, India</p>
            </div>
          </div>

          <div style="display: flex; gap: 16px; align-items: flex-start;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(124, 58, 237, 0.1); display: flex; align-items: center; justify-content: center; color: var(--eh-accent); font-size: 18px; flex-shrink: 0;">
              <i class="fas fa-envelope"></i>
            </div>
            <div>
              <h4 style="color: #fff; font-size: 16px; font-weight: 600; margin: 0 0 4px;">Email</h4>
              <p style="color: var(--eh-muted); font-size: 14px; margin: 0;"><a href="mailto:hello@eventhubpro.com" style="color: var(--eh-muted); text-decoration: none;">hello@eventhubpro.com</a></p>
            </div>
          </div>

          <div style="display: flex; gap: 16px; align-items: flex-start;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(124, 58, 237, 0.1); display: flex; align-items: center; justify-content: center; color: var(--eh-accent); font-size: 18px; flex-shrink: 0;">
              <i class="fas fa-phone-alt"></i>
            </div>
            <div>
              <h4 style="color: #fff; font-size: 16px; font-weight: 600; margin: 0 0 4px;">Support Hours</h4>
              <p style="color: var(--eh-muted); font-size: 14px; margin: 0;">Monday — Friday: 9:00 AM — 5:00 PM</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact form -->
      <div class="col-md-7" data-aos="fade-left">
        <div class="eh-card" style="padding: 40px; background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.05); border-radius: 24px; backdrop-filter: blur(10px);">
          <h3 style="color: #fff; font-family: 'Outfit', sans-serif; font-weight: 600; margin-bottom: 24px;">Send Message</h3>
          
          <?php if ($success_msg !== ''): ?>
            <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #34d399; border-radius: 12px; padding: 15px; margin-bottom: 24px;">
              <i class="fas fa-check-circle" style="margin-right: 8px;"></i> <?php echo htmlspecialchars($success_msg); ?>
            </div>
          <?php endif; ?>

          <?php if ($error_msg !== ''): ?>
            <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171; border-radius: 12px; padding: 15px; margin-bottom: 24px;">
              <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> <?php echo htmlspecialchars($error_msg); ?>
            </div>
          <?php endif; ?>

          <form action="contact.php" method="post" class="eh-reg-form" style="display: flex; flex-direction: column; gap: 20px;">
            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label for="contact-name" style="color: var(--eh-text); font-size: 14px; font-weight: 500;">Your Name <span style="color: var(--eh-accent);">*</span></label>
              <input type="text" id="contact-name" name="name" placeholder="John Doe" required style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff; outline: none; transition: border-color 0.2s;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label for="contact-email" style="color: var(--eh-text); font-size: 14px; font-weight: 500;">Your Email <span style="color: var(--eh-accent);">*</span></label>
              <input type="email" id="contact-email" name="email" placeholder="john@example.com" required style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff; outline: none; transition: border-color 0.2s;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label for="contact-subject" style="color: var(--eh-text); font-size: 14px; font-weight: 500;">Subject</label>
              <input type="text" id="contact-subject" name="subject" placeholder="General Inquiry" style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff; outline: none; transition: border-color 0.2s;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label for="contact-message" style="color: var(--eh-text); font-size: 14px; font-weight: 500;">Message <span style="color: var(--eh-accent);">*</span></label>
              <textarea id="contact-message" name="message" placeholder="Type your message here..." required style="width: 100%; height: 150px; padding: 12px 16px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff; outline: none; transition: border-color 0.2s; resize: vertical;"></textarea>
            </div>

            <button type="submit" name="submit" class="eh-btn eh-btn-primary" style="justify-content: center; padding: 14px; border-radius: 12px; font-size: 16px; font-weight: 600; margin-top: 10px;">
              <i class="fas fa-paper-plane" style="margin-right: 8px;"></i> Send Message
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php 
include('footer.php');
$conn->close();
?>
