<?php
session_start();
include 'config/db.php'; // Ensure $conn = PDO

// Fetch approved testimonials
$testimonialStmt = $conn->prepare("SELECT name, message FROM testimonials WHERE approved = 1 ORDER BY created_at DESC");
$testimonialStmt->execute();
$approvedTestimonials = $testimonialStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AfyaCall - Book Your Doctor Appointment</title>
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <!-- JS Libraries (defer to avoid blocking) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js" defer></script>
</head>
<body>

<!-- Navbar -->
<header class="navbar">
    <div class="container navbar-container">
        <a href="#home" class="logo-link">
            <img src="assets/images/logo.png" alt="AfyaCall Logo" class="logo">
        </a>

        <!-- Hamburger Menu -->
        <div class="menu-toggle" id="mobile-menu" aria-label="Toggle navigation">
            &#9776;
        </div>

        <nav class="nav-links">
            <a href="#home" class="nav-btn">Home</a>
            <a href="#how-it-works" class="nav-btn">Overview</a>
             <a href="#about-us" class="nav-btn">About Us</a>
             <a href="#services-section" class="nav-btn">Services</a>
            <a href="#testimonials" class="nav-btn">Testimonials</a>
             <a href="login.php" class="btn-primary">Book An Appointment</a>
        </nav>
    </div>
    
</header>

<!-- Hero Section -->
<section id="home" class="hero-slider">
    <div class="hero-slides">
        <div class="slide active">
            <img src="assets/images/hero1.jpg" alt="Hero 1">
        </div>
        <div class="slide">
            <img src="assets/images/hero2.jpg" alt="Hero 2">
        </div>
        <div class="slide">
            <img src="assets/images/hero3.jpg" alt="Hero 3">
        </div>
    </div>
    <div class="hero-overlay">
        <div class="hero-content">
            <h1>Enhance Your Health with <span class="highlight">AfyaCall</span></h1>
            <p>Daktari Kiganjani. Book and manage your doctor appointments online.</p>
            <div class="hero-buttons">
                <a href="register.php" class="btn-primary">Get Started</a>
                <a href="tel:0900011111" class="btn-secondary">Call Now</a>
            </div>
        </div>
    </div>
</section>
<!-- How It Works Section -->
<section class="how-it-works" id="how-it-works">
    <h2>System At a Glance</h2>
    <div class="steps">
        <div class="step-card">
            <div class="step-number">1</div>
            <img src="assets/images/step1.png" alt="Step 1 Icon" loading="lazy">
            <p>Create your profile by signing up on AfyaCall</p>
        </div>
        <div class="step-card">
            <div class="step-number">2</div>
            <img src="assets/images/step2.png" alt="Step 2 Icon" loading="lazy">
            <p>Choose the service plan that suits your needs</p>
        </div>
        <div class="step-card">
            <div class="step-number">3</div>
            <img src="assets/images/step3.png" alt="Step 3 Icon" loading="lazy">
            <p>Book an appointment or receive health tips directly</p>
        </div>
        <div class="step-card">
            <div class="step-number">4</div>
            <img src="assets/images/step4.png" alt="Step 4 Icon" loading="lazy">
            <p>Enjoy personalized healthcare support anytime</p>
        </div>
    </div>
    <div class="how-it-works-cta">
        <a href="login.php" class="mini-cta">Book Now</a>
    </div>
</section>

<!-- About Us Section -->
<section id="about-us" class="about-section">
  <div class="container about-container">
    
    <!-- Image Column -->
    <div class="about-image">
    <div class="image-fade">
        <img src="assets\images\about-healthcare.webp" alt="Afyacall Healthcare">
    </div>
</div>

    
    <!-- Text Column -->
    <div class="about-text">
      <h2>About Afyacall</h2>
      <p>
        Afyacall is Tanzania's leading digital healthcare platform, dedicated to connecting patients with professional medical care anytime, anywhere. Our innovative technology simplifies access to healthcare and empowers doctors to serve communities efficiently.
      </p>
      <p>
        By combining telemedicine with expert guidance, we ensure reliable, inclusive, and accessible health services for everyone, whether using a feature phone or a smartphone.
      </p>

      <!-- Mission & Vision Cards -->
      <div class="grid-cards">
        <div class="card">
          <h3>Our Mission</h3>
          <p>Enhancing healthcare access through technology-driven, innovative solutions across Tanzania.</p>
        </div>
        <div class="card">
          <h3>Our Vision</h3>
          <p>A healthier, happier Tanzania empowered by accessible, reliable, and modern digital healthcare.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Services Section -->
<section class="services-section" id="services-section">
    <h2>Our Services</h2>
    <p>Discover the future of healthcare with our digital health service.</p>
    <div class="service-cards">
        <div class="service-card">
            <img src="assets/images/sms.png" alt="SMS Service">
            <h3>TEXT Service (SMS)</h3>
            <p>Receive two daily health tips via SMS (morning and evening).</p>
            <p><strong>Cost:</strong> TZS 150/day</p>
            <p><strong>How to Join:</strong> Send "<b>AFYASMS</b>" to 15723</p>
        </div>
        <div class="service-card">
            <img src="assets/images/ivr.png" alt="IVR Service">
            <h3>VOICE Service (IVR)</h3>
            <p>Listen to your choice of health information from Afyacall's wide selection of topics (up to 2 episodes daily).</p>
            <p><strong>Cost:</strong> TZS 300/day</p>
            <p><strong>How to Join:</strong> Send "<b>AFYAIVR</b>" to 15723</p>
        </div>
        <div class="service-card">
            <img src="assets\images\doctor.jpeg" alt="Doctor's Call Service">
            <h3>DOCTOR'S CALL Service</h3>
            <p>Speak directly with a medical doctor.</p>
            <p><strong>Cost:</strong> Various plans available</p>
            <p><strong>How to Join:</strong> Send "<b>AFYADOC</b>" to 15723</p>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="services-section" id="about-us">
    <h2>Why Choose Us</h2>
    <div class="service-cards">
        <div class="card">
            <div class="icon"><img src="assets/icons/doctor.png" alt="Expert Doctors"></div>
            <h3>Expert Doctors</h3>
            <p>Our team includes top specialists across various medical fields.</p>
        </div>
        <div class="card">
            <div class="icon"><img src="assets/icons/24.7.png" alt="24/7 Support"></div>
            <h3>24/7 Support</h3>
            <p>We are always here to provide timely healthcare support.</p>
        </div>
        <div class="card">
            <div class="icon"><img src="assets/icons/personalized.png" alt="Personalized Care"></div>
            <h3>Personalized Care</h3>
            <p>Treatment plans tailored to your needs for better recovery and well-being.</p>
        </div>
    </div>
</section>

<!-- Feedback Section -->
<section id="feedback" class="feedback-section">
    <h2>Send Us Your Feedback</h2>
    <div class="feedback-container">
        <form id="feedback-form" class="feedback-form" method="POST" action="submit_feedback.php" enctype="multipart/form-data">
            
            <div class="form-group">
                <input type="text" name="name" placeholder="Your Name" required>
            </div>

            <div class="form-group">
                <input type="email" name="email" placeholder="Your Email" required>
            </div>

            <div class="form-group">
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>

            <div class="form-group">
                <textarea name="message" placeholder="Your Message" required></textarea>
            <section class="form-group profile-group">
                <label for="profile_pic">Profile Picture (optional)</label>
                <img id="profile-preview" src="assets/images/profile-placeholder.png" alt="   " class="profile-preview">
                <input type="file" name="profile_pic" accept="image/*" id="profile_pic">
            </div>

            <div class="form-group">
                <label for="rating">Rating</label>
                <select name="rating" required>
                    <option value="5">★★★★★</option>
                    <option value="4">★★★★</option>
                    <option value="3">★★★</option>
                    <option value="2">★★</option>
                    <option value="1">★</option>
                </select>
            </div>

            <div class="form-group">
                <button type="submit" class="btn-submit">Send Message</button>
            </div>
        </form>
    </div>
</section>

<!-- Testimonials Section -->
<section id="testimonials" class="testimonial-section">
    <h2>What Our Users Say</h2>
    <div class="container">
        <?php
        try {
            $stmt = $conn->prepare("SELECT name, message, profile_pic, rating FROM feedback WHERE status='approved' ORDER BY created_at DESC");
            $stmt->execute();
            $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Testimonial fetch error: ' . $e->getMessage());
            $testimonials = [];
        }
        ?>
        <?php if (!empty($testimonials)): ?>
        <div class="swiper testimonial-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($testimonials as $t): ?>
                <div class="swiper-slide">
                    <div class="testimonial-card">
                        <div class="profile">
                            <img src="<?php echo htmlspecialchars($t['profile_pic']); ?>" alt="<?php echo htmlspecialchars($t['name']); ?>">
                        </div>
                        <p class="message">"<?php echo htmlspecialchars($t['message']); ?>"</p>
                        <h4 class="author">- <?php echo htmlspecialchars($t['name']); ?></h4>
                        <div class="rating">
                            <?php
                            $fullStars = intval($t['rating']);
                            for ($i = 0; $i < $fullStars; $i++) echo '<span class="star">&#9733;</span>';
                            $emptyStars = 5 - $fullStars;
                            for ($i = 0; $i < $emptyStars; $i++) echo '<span class="star">&#9734;</span>';
                            ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
        <?php else: ?>
        <p>No testimonials available yet.</p>
        <?php endif; ?>
    </div>
</section>

<!-- FAQ Section -->
<section id="faq" class="faq-section">
    <h2>Frequently Asked Questions</h2>
    <div class="container">
        <?php
        try {
            $stmt = $conn->prepare("SELECT question, answer FROM faqs ORDER BY created_at DESC");
            $stmt->execute();
            $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('FAQ fetch error: ' . $e->getMessage());
            $faqs = [];
        }

        if (!empty($faqs)):
            foreach ($faqs as $faq):
        ?>
        <div class="faq-item">
            <button class="faq-question"><?php echo htmlspecialchars($faq['question']); ?></button>
            <div class="faq-answer">
                <p><?php echo htmlspecialchars($faq['answer']); ?></p>
            </div>
        </div>
        <?php 
            endforeach;
        else: 
        ?>
        <p>No FAQs available yet.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Footer -->
<footer id="contact" class="footer">
    <div class="footer-container">
        <div class="footer-about">
            <h3>AFYACALL</h3>
 <p>The technology dubbed Afyacall or doctor in your palm is a mobile health service that allows people using any type of mobile phone to speak to the doctor, gain access to medical services and consultation.</p>        </div>
        <div class="footer-links">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#services-section">Services</a></li>
                <li><a href="login.php">Book Appointment</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </div>
        <div class="footer-contact">
            <h4>Contact</h4>
           <p><a href="mailto:support@afyacall.com">info@afyacall.com</a></p>
<p><a href="tel:+255123456789">+2557-4423-456789</a></p>
<p><a href="https://www.google.com/maps/place/Dar+es+Salaam,+Tanzania" target="_blank" rel="noopener">Dar es Salaam, Tanzania</a></p>

        </div>
    </div>
    <div class="footer-social">
        <a href="https://web.facebook.com/afyacall/" target="_blank"><img src="assets/icons/facebook.png" alt="Facebook"></a>
        <a href="https://twitter.com/AfyaCall" target="_blank"><img src="assets/icons/x.png" alt="Twitter"></a>
        <a href="https://instagram.com/afyacall_tz" target="_blank"><img src="assets/icons/instagram.png" alt="Instagram"></a>
        <a href="https://www.linkedin.com/in/afyacall-vodacom-58b36b244" target="_blank"><img src="assets/icons/linkedin.png" alt="LinkedIn"></a>
    </div>
    <div class="footer-bar">
        <p>&copy; 2025 AfyaCall. All rights reserved.</p>
    </div>
    
</footer>
<!-- Back to Top Button -->
<a href="#home" id="back-to-top" title="Back to Top">&#8679;</a>



<!-- Custom JS -->
<script>
// Back-to-top functionality
const backToTop = document.getElementById('back-to-top');

window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
        backToTop.classList.add('show');
    } else {
        backToTop.classList.remove('show');
    }
});

backToTop.addEventListener('click', e => {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

    <!-- Hero Slider Script -->
    
 const slides = document.querySelectorAll('.hero-slides .slide');
let currentSlide = 0;

function showSlide(index) {
    slides.forEach((slide, i) => {
        slide.classList.toggle('active', i === index);
    });
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
}

// Start slider with 8 seconds per slide
showSlide(currentSlide);
setInterval(nextSlide, 8000);

document.addEventListener('DOMContentLoaded', () => {

    // MOBILE MENU TOGGLE
    const mobileMenu = document.getElementById('mobile-menu');
    const navLinks = document.querySelector('.nav-links');
    if (mobileMenu && navLinks) {
        mobileMenu.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            mobileMenu.classList.toggle('active');
        });
        const navItems = document.querySelectorAll('.nav-links a');
        navItems.forEach(item => {
            item.addEventListener('click', () => {
                navLinks.classList.remove('active');
                mobileMenu.classList.remove('active');
            });
        });
    }

    // SHADOW ON SCROLL
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 10) navbar.classList.add('scrolled');
            else navbar.classList.remove('scrolled');
        });
    }

    // FEEDBACK FORM SUBMISSION
    const feedbackForm = document.getElementById('feedback-form');
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(feedbackForm);
            try {
                const response = await fetch('submit_feedback.php', { method: 'POST', body: formData, credentials: 'same-origin' });
                let data;
                try { data = await response.json(); } 
                catch { data = { status: 'error', message: 'Invalid server response' }; }
                if (data.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Thank You!', text: data.message, timer: 2500, showConfirmButton: false });
                    feedbackForm.reset();
                } else { Swal.fire({ icon: 'error', title: 'Oops!', text: data.message }); }
            } catch(e) { Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not reach server.' }); }
        });
    }

    // PROFILE PICTURE PREVIEW
    const profileInput = document.getElementById('profile_pic');
    const previewImg = document.getElementById('profile-preview');
    if (profileInput && previewImg) {
        profileInput.addEventListener('change', e => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = event => previewImg.src = event.target.result;
                reader.readAsDataURL(file);
            } else previewImg.src = 'assets/images/profile-placeholder.png';
        });
    }

    // TESTIMONIAL SWIPER
    if (typeof Swiper !== 'undefined' && document.querySelector('.testimonial-swiper')) {
        new Swiper('.testimonial-swiper', {
            loop: true,
            autoplay: { delay: 5000, disableOnInteraction: false },
            speed: 800,
            slidesPerView: 1,
            spaceBetween: 30,
            pagination: { el: '.swiper-pagination', clickable: true },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            breakpoints: { 768: { slidesPerView: 2 }, 1024: { slidesPerView: 3 } }
        });
    }

    // FAQ TOGGLE
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        question.addEventListener('click', () => {
            item.classList.toggle('active');
            faqItems.forEach(other => { if(other !== item) other.classList.remove('active'); });
        });
    });

});
</script>


</body>
</html>
