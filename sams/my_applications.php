<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT a.application_id, a.application_date, a.status,
            s.scholarship_name, s.application_deadline
     FROM applications a
     INNER JOIN students st ON a.student_id = st.student_id
     INNER JOIN scholarships s ON a.scholarship_id = s.scholarship_id
     WHERE st.user_id = ?
     ORDER BY a.application_date DESC"
);

mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications</title>
    <link rel="stylesheet" href="my_application.css">
</head>

<body>

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

<main class="main-content">

    <section class="page-intro">
        <div class="intro-text">
            <span class="page-label">STUDENT PORTAL</span>
            <h2>My Applications</h2>
            <p>View and track the status of your scholarship applications.</p>
        </div>
    </section>

    <section class="application-list">

        <?php if (mysqli_num_rows($result) > 0): ?>

            <?php while ($application = mysqli_fetch_assoc($result)): ?>

                <div class="application-card">

                    <div class="application-header">
                        <div class="application-title">
                            <h3>
                                <?php echo htmlspecialchars($application["scholarship_name"]); ?>
                            </h3>
                        </div>

                        <div class="status">
                            <span>
                                <?php echo htmlspecialchars(ucfirst($application["status"])); ?>
                            </span>
                        </div>
                    </div>

                    <div class="application-content">

                        <div class="application-detail">
                            <h4>Application ID</h4>
                            <p>
                                <?php echo (int)$application["application_id"]; ?>
                            </p>
                        </div>

                        <div class="application-detail">
                            <h4>Application Date</h4>
                            <p>
                                <?php echo htmlspecialchars($application["application_date"]); ?>
                            </p>
                        </div>

                        <div class="application-detail">
                            <h4>Application Deadline</h4>
                            <p>
                                <?php echo htmlspecialchars($application["application_deadline"]); ?>
                            </p>
                        </div>
                    </div>
                        
                        <div class="page-actions">
                            <a href="student_dashboard.php" class="back-button">← Back to Dashboard</a>
                        </div>
                </div> 
                
                

            <?php endwhile; ?>

        <?php else: ?>

            <div class="empty-message">
                <h3>No Applications Yet</h3>
                <p>You have not submitted any applications yet.</p>
            </div>

        <?php endif; ?>

    </section>

  

</main>

<footer>
    <p>© 2026 SAMS | Scholarship Application Management System</p>
    <p>A Brighter Future Through Education</p>
</footer>

</body>
</html>
