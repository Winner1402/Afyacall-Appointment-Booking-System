<?php
// index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AfyaCall - Book Your Doctor Appointment</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

</head>
<body>
    <!-- Navbar -->
<header class="navbar">
    <div class="container">
        <img src="assets\images\logo.jpeg" alt="AfyaCall Logo" class="logo">
        <nav class="nav-links">
            <a href="#home" class="nav-btn">Home</a>
            <a href="#find-doctor" class="nav-btn">Find a Doctor</a>
            <a href="#services-flow" class="nav-btn">Services</a>
            <a href="#about-us" class="nav-btn">About Us</a>
            <a href="#contact" class="nav-btn">Contact</a>
            <a href="login.php" class="btn-primary">Book An Appointment</a>
        </nav>
    </div>
</header>

<!-- Hero Section -->
<section id="home" class="hero">
    <div class="hero-content">
        <h1>Enhance Your Health with <span class="highlight">AfyaCall</span></h1>
        <p>Daktari Kiganjani. Book and manage your doctor appointments online.</p>
        <div class="hero-buttons">
            <a href="register.php" class="btn-primary">Get started</a>
            <a href="tel:0900011111" class="btn-secondary">Call Now</a>
        </div>
    </div>
    <div class="hero-image">
        <img src="assets\images\doctor_app.png" alt="Doctor">
    </div>
</section>

<!-- Search Section -->
<section id="find-doctor" class="search-box">
 <form method="GET" action="<?php echo isset($_SESSION['user_id']) ? 'search_results.php' : 'login.php'; ?>">
    <input type="text" id="appointment-date" name="appointment_date" placeholder="Select Date & Time" required>
    <input type="text" name="search_query" placeholder="Search doctors, name, specialist" required>
    <button type="submit" class="btn-primary">Search</button>
</form>
</section>

<!-- Operational Flow Section -->
<section id="services-flow" class="services">
  <h2>Operational Flow</h2>
  <div class="service-cards">
    <div class="card">
      <div class="icon">
        <img src="assets\icons\health.png" alt="Health Icon">
      </div>
      <h3>Explore Your Health</h3>
      <p>Discover and book skilled doctors by specialization and location.</p>
    </div>
    <div class="card">
      <div class="icon">
        <img src="assets\icons\booking.png" alt="Appointment Icon">
      </div>
      <h3>Book Appointment</h3>
      <p>Effortlessly book appointments at your convenience.</p>
    </div>
    <div class="card">
      <div class="icon">
        <img src="assets\icons\service.png" alt="Services Icon">
      </div>
      <h3>Get Services</h3>
      <p>Receive personalized healthcare services tailored to your needs.</p>
    </div>
  </div>
</section>

<!-- Why Choose Us Section -->
<section id="about-us" class="services">
  <h2>Why Choose Us</h2>
  <div class="service-cards">
    <div class="card">
      <div class="icon">
        <img src="assets/icons/doctor.png" alt="Expert Doctors">
      </div>
      <h3>Expert Doctors</h3>
      <p>Our team includes top specialists across various medical fields.</p>
    </div>
    <div class="card">
      <div class="icon">
        <img src="assets/icons/24.7.png" alt="24/7 Support">
      </div>
      <h3>24/7 Support</h3>
      <p>We are always here to provide timely healthcare support.</p>
    </div>
    <div class="card">
      <div class="icon">
        <img src="assets/icons/personalized.png" alt="Personalized Care">
      </div>
      <h3>Personalized Care</h3>
      <p>Treatment plans tailored to your needs for better recovery and well-being.</p>
    </div>
  </div>
</section>

<!-- Footer Section -->
<footer id="contact" class="footer">
  <div class="footer-container">
    <div class="footer-about">
      <h3>AFYACALL</h3>
      <p class="footer-overview">
        The technology dubbed Afyacall or doctor in your palm is a mobile health service that allows people using any type of mobile phone to speak to the doctor, gain access to medical services and consultation.
      </p>
    </div>
    <div class="footer-links">
      <h4>Quick Links</h4>
      <ul>
        <li><a href="#home">Home</a></li>
        <li><a href="#services-flow">Services</a></li>
        <li><a href="booking.php">Book Appointment</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>
    </div>
    <div class="footer-contact">
      <h4>Contact</h4>
      <p>Email: support@afyacall.com</p>
      <p>Phone: +255 123 456 789</p>
      <p>Address: Dar es Salaam, Tanzania</p>
    </div>
  </div>

  <div class="footer-social">
    <a href="https://web.facebook.com/afyacall/" target="_blank" rel="noopener noreferrer">
      <img src="assets/icons/facebook.png" alt="Facebook">
    </a>
    <a href="https://twitter.com/AfyaCall" target="_blank" rel="noopener noreferrer">
      <img src="assets/icons/twitter.png" alt="Twitter">
    </a>
    <a href="https://instagram.com/afyacall_tz" target="_blank" rel="noopener noreferrer">
      <img src="assets/icons/instagram.png" alt="Instagram">
    </a>
    <a href="https://www.linkedin.com/in/afyacall-vodacom-58b36b244" target="_blank" rel="noopener noreferrer">
      <img src="assets/icons/linkedin.png" alt="LinkedIn">
    </a>
  </div>

  <div class="footer-bar">
    <p>&copy; 2025 AfyaCall. All rights reserved.</p>
  </div>
</footer>

</body>
<script>
  // Date and Time picker
  flatpickr("#appointment-date", {
    enableTime: true,         
    dateFormat: "Y-m-d H:i",  
    minDate: "today",        
    defaultHour: 9,          
    defaultMinute: 0,
    locale: {
      firstDayOfWeek: 1      
    }
  }); 

  // Optional: smooth scroll behavior
  document.documentElement.style.scrollBehavior = "smooth";
</script>
</html>
