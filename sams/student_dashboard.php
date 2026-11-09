
<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit;
}

$username = htmlspecialchars($_SESSION["username"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SAMS | Student Dashboard</title>

    <link rel="stylesheet" href="studentdashboard.css">
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

    <a href="logout.php" class="logout-link">Logout</a>

</header>

<!-- DASHBOARD -->
<main class="dashboard">

    <!-- WELCOME -->
     
    <section class="welcome-section">
        <span class="welcome-label">STUDENT PORTAL</span>

        <h2>Welcome, <?php echo $username; ?>!</h2>

        <p>
            Manage your scholarship applications and student documents
            in one convenient place.
        </p>

    </section>

    <!-- STUDENT MENU -->
    <section class="student-menu">

        <h2 class="section-title">Student Dashboard</h2>

        <p class="section-description">
            Choose a service to get started.
        </p>

        <div class="menu-grid">

            <a href="profile.php" class="menu-card">
                <div class="menu-image">
                    <img src="profile.jpg" alt="My Profile">
                </div>
                <h3>My Profile</h3>
                <p>View and manage your student information.</p>
                <span class="card-link">Open Profile →</span>
            </a>

            <a href="scholarships.php" class="menu-card">
                <div class="menu-image">
                    <img src="cap.jpg" alt="Available Scholarship">
                </div>
                <h3>Available Scholarships</h3>
                <p>Explore scholarship opportunities.</p>
                <span class="card-link">View Scholarships →</span>
            </a>

            <a href="my_applications.php" class="menu-card">
                <div class="menu-image">
                    <img src="file.jpg" alt="My Application">
                </div>
                <h3>My Applications</h3>
                <p>Check your submitted applications.</p>
                <span class="card-link">View Applications →</span>
            </a>

            <a href="upload_documents.php" class="menu-card">
                <div class="menu-image">
                    <img src="uploads.jpg" alt="Upload">
                </div>
                <h3>Upload Documents</h3>
                <p>Submit your required scholarship documents.</p>
                <span class="card-link">Upload Now →</span>
            </a>

        </div>

    </section>

    <!-- MORE INFORMATION -->
    <section class="information">

        <h2 class="section-title">Learn More</h2>
        <p class="section-description">
        Discover more about our scholarship system.
        </p>

        <div class="info-buttons">

            <button class="info-button" onclick="toggleInfo('about')">
                <span class="info-icon">ⓘ</span>
                About Us
            </button>

            <div id="about" class="info-content">
                <h3>About Us</h3>
                <p>
                    The Scholarship Application Management System helps
                    students find and apply for scholarship opportunities.
                </p>
            </div>

            <button class="info-button" onclick="toggleInfo('services')">
                <span class="info-icon">⫶☰</span>
                Our Services
            </button>

            <div id="services" class="info-content">
                <h3>Our Services</h3>
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
                Contact Us
            </button>

            <div id="contact" class="info-content">
                <h3>Contact Us</h3>
                <p>Scholarship Administration Office</p>
                <p>Email: sams@gmail.com</p>
                <p>Office Hours: Monday - Friday</p>
            </div>

        </div>

    </section>

</main>

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
