<?php
/* =============================================================
   EVENTHUB PRO — About Us
   Premium styled About page using the shared glassmorphic header/footer.
   ============================================================= */
session_start();
include('dbconnect.php');

$page_title = 'About Us — EventHub Pro';
$page_desc = 'Learn about EventHub Pro, our mission to revolutionize college event management, our mentors, and our engineering team.';

include('header.php');
?>

<!-- ===== About Hero ===== -->
<section class="eh-hero" style="padding-bottom: 60px;">
  <div class="container-max">
    <div style="text-align: center; max-width: 800px; margin: 0 auto;">
      <span class="eh-badge"><i class="fas fa-info-circle"></i> Our Story</span>
      <h1 style="font-size: clamp(34px, 5vw, 58px);">Revolutionizing <br><span class="grad">Event Discovery & Management</span></h1>
      <p>EventHub Pro is a college-level, production-ready Event Management platform built to eliminate administrative stress, boost participant engagement, and automate ticketing.</p>
    </div>
  </div>
</section>

<!-- ===== About Detail / Platform Vision ===== -->
<section class="eh-section" style="padding-top: 0;">
  <div class="container-max">
    <div class="row align-items-center" style="gap: 40px 0;">
      <div class="col-md-7" data-aos="fade-right">
        <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #fff; margin-bottom: 20px;">The EventHub Pro Mission</h2>
        <p style="color: var(--eh-muted); font-size: 16px; line-height: 1.8;">
          Organizing events at a university or collegiate scale often demands tremendous manual promotion, scattered participant lists, offline coordination, and complex payment handling. 
        </p>
        <p style="color: var(--eh-muted); font-size: 16px; line-height: 1.8;">
          EventHub Pro acts as a single, centralized hub where students, faculty, and administrators can discover active workshops, technical hackathons, cultural festivals, and sports leagues. With built-in analytical reporting, QR-based check-in tickets, and secure Stripe payment processing, EventHub Pro bridges the gap between organizers and participants.
        </p>
      </div>
      <div class="col-md-5" data-aos="fade-left">
        <div class="eh-card" style="padding: 30px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; backdrop-filter: blur(10px);">
          <h3 style="font-family: 'Outfit', sans-serif; font-weight: 600; color: #fff; margin-bottom: 20px;"><i class="fas fa-bolt" style="color: var(--eh-accent); margin-right: 10px;"></i> Key Capabilities</h3>
          <ul style="list-style: none; padding: 0; margin: 0; color: var(--eh-muted); display: flex; flex-direction: column; gap: 14px;">
            <li><i class="fas fa-check-circle" style="color: var(--eh-accent); margin-right: 8px;"></i> Instant search, category & venue filtering</li>
            <li><i class="fas fa-check-circle" style="color: var(--eh-accent); margin-right: 8px;"></i> Dual mode (Individual vs Team) registrations</li>
            <li><i class="fas fa-check-circle" style="color: var(--eh-accent); margin-right: 8px;"></i> Real-time organizer dashboard & analytics</li>
            <li><i class="fas fa-check-circle" style="color: var(--eh-accent); margin-right: 8px;"></i> Secure payment collections via Stripe</li>
            <li><i class="fas fa-check-circle" style="color: var(--eh-accent); margin-right: 8px;"></i> Automated QR codes for venue validation</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== Mentors section ===== -->
<section class="eh-section" style="background: rgba(255,255,255,0.01); border-top: 1px solid rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.03);">
  <div class="container-max">
    <div class="eh-sec-head" style="text-align: center;" data-aos="fade-up">
      <span class="eh-eyebrow">Guidance</span>
      <h2>Our Mentors</h2>
      <p>Under whose supervision and encouragement this project was brought to fruition.</p>
    </div>
    
    <div class="row justify-content-center" style="gap: 30px 0;">
      <div class="col-md-5 col-lg-4" data-aos="fade-up" data-aos-delay="100">
        <div class="eh-card text-center" style="padding: 40px 20px; height: 100%;">
          <div class="eh-avatar" style="width: 100px; height: 100px; font-size: 40px; margin: 0 auto 20px;">GS</div>
          <h3 style="color: #fff; font-size: 22px; font-weight: 600; margin-bottom: 5px;">Dr. Gunjan Sethi</h3>
          <p style="color: var(--eh-accent); font-size: 14px; margin-bottom: 15px;">Project Mentor / Professor</p>
          <p style="color: var(--eh-muted); font-size: 14px;">Guided the overall system architecture, database planning, and academic standards alignment.</p>
          <div style="margin-top: 20px; display: flex; justify-content: center; gap: 15px;">
            <a href="https://www.linkedin.com/in/gunjan-sethi-23a89711/" target="_blank" rel="noopener" style="color: var(--eh-muted); font-size: 18px;"><i class="fab fa-linkedin"></i></a>
          </div>
        </div>
      </div>
      
      <div class="col-md-5 col-lg-4" data-aos="fade-up" data-aos-delay="200">
        <div class="eh-card text-center" style="padding: 40px 20px; height: 100%;">
          <div class="eh-avatar" style="width: 100px; height: 100px; font-size: 40px; margin: 0 auto 20px;">RS</div>
          <h3 style="color: #fff; font-size: 22px; font-weight: 600; margin-bottom: 5px;">Dr. Rohini Sharma</h3>
          <p style="color: var(--eh-accent); font-size: 14px; margin-bottom: 15px;">Project Mentor / Professor</p>
          <p style="color: var(--eh-muted); font-size: 14px;">Supervised UX validation, testing benchmarks, and general project management strategies.</p>
          <div style="margin-top: 20px; display: flex; justify-content: center; gap: 15px;">
            <a href="https://www.linkedin.com/in/rohini-sharma-7117b827/" target="_blank" rel="noopener" style="color: var(--eh-muted); font-size: 18px;"><i class="fab fa-linkedin"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== Developers section ===== -->
<section class="eh-section">
  <div class="container-max">
    <div class="eh-sec-head" style="text-align: center;" data-aos="fade-up">
      <span class="eh-eyebrow">Engineering</span>
      <h2>Our Engineering Team</h2>
      <p>The developers behind designing, implementing, and securing EventHub Pro.</p>
    </div>
    
    <div class="row justify-content-center" style="gap: 30px 0;">
      <!-- Anshul Sharma -->
      <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="50">
        <div class="eh-card text-center" style="padding: 30px 20px; height: 100%;">
          <div class="eh-avatar" style="width: 80px; height: 80px; font-size: 32px; margin: 0 auto 16px;">AS</div>
          <h3 style="color: #fff; font-size: 20px; font-weight: 600; margin-bottom: 5px;">Anshul Sharma</h3>
          <p style="color: var(--eh-accent); font-size: 13px; margin-bottom: 15px;">Back-End & Business Logic</p>
          <div style="display: flex; justify-content: center; gap: 15px;">
            <a href="https://www.instagram.com/anshul.sharma23/" target="_blank" rel="noopener" style="color: var(--eh-muted); font-size: 18px;"><i class="fab fa-instagram"></i></a>
            <a href="https://www.facebook.com/friendanshul1998" target="_blank" rel="noopener" style="color: var(--eh-muted); font-size: 18px;"><i class="fab fa-facebook"></i></a>
          </div>
        </div>
      </div>

      <!-- Keshav Sharma -->
      <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
        <div class="eh-card text-center" style="padding: 30px 20px; height: 100%;">
          <div class="eh-avatar" style="width: 80px; height: 80px; font-size: 32px; margin: 0 auto 16px;">KS</div>
          <h3 style="color: #fff; font-size: 20px; font-weight: 600; margin-bottom: 5px;">Keshav Sharma</h3>
          <p style="color: var(--eh-accent); font-size: 13px; margin-bottom: 15px;">Database Architecture & Connectivity</p>
          <div style="display: flex; justify-content: center; gap: 15px;">
            <a href="https://www.instagram.com/sharmakeshav0101/" target="_blank" rel="noopener" style="color: var(--eh-muted); font-size: 18px;"><i class="fab fa-instagram"></i></a>
            <a href="https://www.facebook.com/keshav.sharma.0101" target="_blank" rel="noopener" style="color: var(--eh-muted); font-size: 18px;"><i class="fab fa-facebook"></i></a>
          </div>
        </div>
      </div>

      <!-- Prince Parihar -->
      <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="150">
        <div class="eh-card text-center" style="padding: 30px 20px; height: 100%;">
          <div class="eh-avatar" style="width: 80px; height: 80px; font-size: 32px; margin: 0 auto 16px;">PP</div>
          <h3 style="color: #fff; font-size: 20px; font-weight: 600; margin-bottom: 5px;">Prince Parihar</h3>
          <p style="color: var(--eh-accent); font-size: 13px; margin-bottom: 15px;">UI/UX Design & Front-End</p>
          <div style="display: flex; justify-content: center; gap: 15px;">
            <a href="https://www.instagram.com/princepariharr/" target="_blank" rel="noopener" style="color: var(--eh-muted); font-size: 18px;"><i class="fab fa-instagram"></i></a>
            <a href="https://www.facebook.com/princeparihar22" target="_blank" rel="noopener" style="color: var(--eh-muted); font-size: 18px;"><i class="fab fa-facebook"></i></a>
          </div>
        </div>
      </div>

      <!-- Sukrut Patil -->
      <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200" style="margin-top: 30px;">
        <div class="eh-card text-center" style="padding: 30px 20px; height: 100%;">
          <div class="eh-avatar" style="width: 80px; height: 80px; font-size: 32px; margin: 0 auto 16px;">SP</div>
          <h3 style="color: #fff; font-size: 20px; font-weight: 600; margin-bottom: 5px;">Sukrut Patil</h3>
          <p style="color: var(--eh-accent); font-size: 13px; margin-bottom: 15px;">Full Stack Engineering</p>
          <div style="display: flex; justify-content: center; gap: 15px;">
            <a href="https://www.instagram.com/sukrut_patill/" target="_blank" rel="noopener" style="color: var(--eh-muted); font-size: 18px;"><i class="fab fa-instagram"></i></a>
            <a href="https://twitter.com/sukrutpatil77" target="_blank" rel="noopener" style="color: var(--eh-muted); font-size: 18px;"><i class="fab fa-twitter"></i></a>
          </div>
        </div>
      </div>

      <!-- Vaibhav Pahwa -->
      <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="250" style="margin-top: 30px;">
        <div class="eh-card text-center" style="padding: 30px 20px; height: 100%;">
          <div class="eh-avatar" style="width: 80px; height: 80px; font-size: 32px; margin: 0 auto 16px;">VP</div>
          <h3 style="color: #fff; font-size: 20px; font-weight: 600; margin-bottom: 5px;">Vaibhav Pahwa</h3>
          <p style="color: var(--eh-accent); font-size: 13px; margin-bottom: 15px;">Front-End Development</p>
          <div style="display: flex; justify-content: center; gap: 15px;">
            <a href="https://www.instagram.com/vishu_pahwa/" target="_blank" rel="noopener" style="color: var(--eh-muted); font-size: 18px;"><i class="fab fa-instagram"></i></a>
            <a href="https://www.facebook.com/vishu.pahwa" target="_blank" rel="noopener" style="color: var(--eh-muted); font-size: 18px;"><i class="fab fa-facebook"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php 
include('footer.php');
$conn->close();
?>
