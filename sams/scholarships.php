
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit;
}

$sql = "SELECT * FROM scholarships WHERE status = 'active' ORDER BY application_deadline ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Scholarships</title>
    <link rel="stylesheet" href="scholarships.css">
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

                <h2>Available Scholarships</h2>

                <p>
                    Explore scholarship opportunities available for you.
                </p>
            </div>

        </section>


        <section class="scholarship-list">

            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php while ($scholarship = mysqli_fetch_assoc($result)): ?>

                    <div class="scholarship-card">

                        <div class="scholarship-header">

                            <div class="scholarship-title">
                                <h3>
                                    <?php echo htmlspecialchars($scholarship["scholarship_name"]); ?>
                                </h3>
                            </div>

                            <div class="status">
                                <span>
                                    <?php echo htmlspecialchars($scholarship["status"]); ?>
                                </span>
                            </div>

                        </div>


                        <div class="scholarship-content">

                            <div class="scholarship-detail">
                                <h4>Description</h4>

                                <p>
                                    <?php
                                    echo nl2br(
                                        htmlspecialchars($scholarship["description"])
                                    );
                                    ?>
                                </p>
                            </div>


                            <div class="scholarship-detail">
                                <h4>Eligibility</h4>

                                <p>
                                    <?php
                                    echo nl2br(
                                        htmlspecialchars($scholarship["eligibility"])
                                    );
                                    ?>
                                </p>
                            </div>


                            <div class="scholarship-detail">
                                <h4>Benefits</h4>

                                <p>
                                    <?php
                                    echo nl2br(
                                        htmlspecialchars($scholarship["benefits"])
                                    );
                                    ?>
                                </p>
                            </div>


                            <div class="scholarship-deadline">

                                <strong>
                                    Application Deadline:
                                </strong>

                                <span>
                                    <?php
                                    echo htmlspecialchars(
                                        $scholarship["application_deadline"]
                                    );
                                    ?>
                                </span>

                            </div>

                        </div>


                        <div class="scholarship-actions">
                            <a href="student_dashboard.php" class="back-button">← Back to Dashboard</a>
                            <a href="apply.php?id=<?php echo (int)$scholarship["scholarship_id"]; ?> 
                            "class="apply-button"> Apply Now </a>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="empty-message">

                    <h3>No Available Scholarships</h3>

                    <p>
                        No available scholarships at the moment.
                    </p>

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
