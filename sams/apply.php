
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit;
}

$message = "";
$messageType = "";

$scholarship_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

/* GET STUDENT */
$stmt = mysqli_prepare($conn, "SELECT student_id FROM students WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$student_result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($student_result);
mysqli_stmt_close($stmt);

if (!$student) {
    header("Location: profile.php");
    exit;
}

$student_id = $student["student_id"];

/* GET SCHOLARSHIP */
$stmt = mysqli_prepare(
    $conn,
    "SELECT scholarship_name FROM scholarships
     WHERE scholarship_id = ?
     AND status = 'active'
     AND application_deadline >= CURDATE()"
);

mysqli_stmt_bind_param($stmt, "i", $scholarship_id);
mysqli_stmt_execute($stmt);
$scholarship_result = mysqli_stmt_get_result($stmt);
$scholarship = mysqli_fetch_assoc($scholarship_result);
mysqli_stmt_close($stmt);

if (!$scholarship) {
    die("Scholarship is unavailable or the deadline has passed.
    <a href='scholarships.php'>Back to Scholarships</a>");
}

/* SUBMIT APPLICATION */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT application_id FROM applications
         WHERE student_id = ? AND scholarship_id = ?"
    );

    mysqli_stmt_bind_param($stmt, "ii", $student_id, $scholarship_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {

        $message = "You have already applied for this scholarship.";
        $messageType = "error";
        mysqli_stmt_close($stmt);

    } else {

        mysqli_stmt_close($stmt);

        $status = "pending";

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO applications (student_id, scholarship_id, status)
             VALUES (?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "iis",
            $student_id,
            $scholarship_id,
            $status
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "Your scholarship application was submitted successfully!";
            $messageType = "success";
        } else {
            $message = "Application failed. Please try again.";
            $messageType = "error";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Apply for Scholarship | SAMS</title>

    <link rel="stylesheet" href="apply.css">
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

<!-- MAIN CONTENT -->
<main class="main-content">

    <!-- PAGE INTRO -->
    <section class="page-intro">
        <span class="page-label">STUDENT PORTAL</span>
        <h2>Apply for Scholarship</h2>
        <p>Review and confirm your scholarship application.</p>
    </section>

    <!-- APPLICATION CARD -->
    <section class="application-card">

        <!-- APPLICATION HEADER -->
        <div class="application-header">

            <div class="application-title">
                <span class="application-label">SELECTED SCHOLARSHIP</span>

                <h3>
                    <?php echo htmlspecialchars($scholarship["scholarship_name"]); ?>
                </h3>
            </div>

            <div class="application-status">
                <span>APPLICATION</span>
            </div>

        </div>

        <!-- APPLICATION CONTENT -->
        <div class="application-content">

            <?php if ($message !== ""): ?>

                <div class="message-box <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php else: ?>

                <div class="confirmation-section">
                    <h4>Application Confirmation</h4>

                    <p>
                        Are you sure you want to apply for this scholarship?
                        Please make sure your student profile information
                        is complete and accurate before submitting.
                    </p>
                </div>

                <div class="application-notice">
                    <strong>Reminder:</strong>
                    Your application will be marked as pending
                    after submission and will undergo review.
                </div>

            <?php endif; ?>

        </div>

        <!-- BUTTON ROW -->
        <div class="application-actions">

            <a href="scholarships.php" class="back-button">
                &larr; Back to Scholarships
            </a>

            <?php if ($message === ""): ?>

                <form method="POST" class="application-form">
                    <button type="submit" class="apply-button">
                        Submit Application
                    </button>
                </form>

            <?php endif; ?>

        </div>

    </section>

</main>

<footer>
    <p>© 2026 SAMS | Scholarship Application Management System</p>
    <p>A Brighter Future Through Education</p>
</footer>

</body>
</html>
