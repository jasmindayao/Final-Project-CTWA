
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMS | Scholarship Application Management System</title>
    <link rel="stylesheet" href="landing.css">
</head>
<body>

<!-- HEADER -->
<header class="header">
    <div class="branding">
        <img src="SAMS LOGO.png" alt="SAMS Logo">
        <div class="brand-text">
            <h1>SAMS</h1>
            <p>Scholarship Application Management System</p>
        </div>
    </div>
    <a href="index.php" class="home-link">🏠︎ Home</a>
</header>

<!-- HERO SECTION -->
<section class="hero">

    <div class="hero-content">
        <span class="welcome-label">WELCOME TO SAMS</span>

        <h2>Scholarship Application<br>
        Management System</h2>

        <p>Apply for available scholarships and manage your applications.</p>

        <div class="hero-buttons">
            <a href="login.php" class="btn-login">Log In</a>
            <a href="register.php" class="btn-register">Register</a>
        </div>
    </div>

    <div class="hero-visual">
        <img src="logo.png" alt="SAMS Logo">
        <p>A Brighter Future Through Education</p>
    </div>

</section>

<!-- INFORMATION SECTION -->
<section class="information">
    <h2 class="section-title">Learn More</h2>
    <p class="section-description">
        Discover more about our scholarship system.
    </p>

    <div class="info-buttons">

        <button class="info-button" onclick="toggleInfo('about')">
            <span class="info-icon">ⓘ</span>
            <span>About Us</span>
        </button>

        <div id="about" class="info-content">
            <h2>About Us</h2>
            <p>
                The Scholarship Application Management System helps students
                find and apply for scholarship opportunities.
            </p>
        </div>

        <button class="info-button" onclick="toggleInfo('services')">
            <span class="info-icon">⫶☰</span>
            <span>Our Services</span>
        </button>

        <div id="services" class="info-content">
            <h2>Our Services</h2>
            <ul>
                <li>View Available Scholarships</li>
                <li>Submit Scholarship Applications</li>
                <li>Upload Required Documents</li>
                <li>Track Application Status</li>
                <li>Manage Student Profiles</li>
            </ul>
        </div>

        <button class="info-button" onclick="toggleInfo('contact')">
            <span class="info-icon">✉</span>
            <span>Contact Us</span>
        </button>

        <div id="contact" class="info-content">
            <h2>Contact Us</h2>
            <p>Scholarship Administration Office</p>
            <p>Email: sams@gmail.com</p>
            <p>Office Hours: Monday - Friday</p>
        </div>

    </div>
</section>

<!-- FOOTER -->
<footer>
    <p>© 2026 SAMS | Scholarship Application Management System</p>
    <p>A Brighter Future Through Education</p>
</footer>

<!-- JAVASCRIPT -->
<script>
function toggleInfo(id) {
    const section = document.getElementById(id);

    if (section.style.display === "block") {
        section.style.display = "none";
    } else {
        section.style.display = "block";
    }
}
</script>

</body>
</html>