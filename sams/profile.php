
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$message = "";
$messageType = "";

// LOAD PROFILE
function loadStudentProfile($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($result) ?? [];

    mysqli_stmt_close($stmt);
    return $student;
}

$student = loadStudentProfile($conn, $user_id);

// SAVE PROFILE
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $first_name = trim($_POST["first_name"] ?? "");
    $last_name = trim($_POST["last_name"] ?? "");
    $middle_name = trim($_POST["middle_name"] ?? "");
    $birth_date = $_POST["birth_date"] ?? "";
    $gender = $_POST["gender"] ?? "";
    $address = trim($_POST["address"] ?? "");
    $contact_number = trim($_POST["contact_number"] ?? "");
    $school = trim($_POST["school"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $year_level = trim($_POST["year_level"] ?? "");
    $gpa = $_POST["gpa"] ?? 0;

    if (!empty($student)) {

        $stmt = mysqli_prepare($conn,
            "UPDATE students SET
            first_name=?, last_name=?, middle_name=?, birth_date=?,
            gender=?, address=?, contact_number=?, school=?,
            course=?, year_level=?, GPA=?
            WHERE user_id=?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssssssssssdi",
            $first_name, $last_name, $middle_name, $birth_date,
            $gender, $address, $contact_number, $school,
            $course, $year_level, $gpa, $user_id
        );

    } else {

        $stmt = mysqli_prepare($conn,
            "INSERT INTO students
            (user_id, first_name, last_name, middle_name, birth_date,
            gender, address, contact_number, school, course, year_level, GPA)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "issssssssssd",
            $user_id, $first_name, $last_name, $middle_name,
            $birth_date, $gender, $address, $contact_number,
            $school, $course, $year_level, $gpa
        );
    }

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        // Reload and return to profile card
        header("Location: profile.php?saved=1");
        exit;

    } else {
        $message = "Error saving profile: " . mysqli_stmt_error($stmt);
        $messageType = "error";
        mysqli_stmt_close($stmt);
    }
}

// RELOAD PROFILE
$student = loadStudentProfile($conn, $user_id);

if (isset($_GET["saved"])) {
    $message = "Student profile saved successfully!";
    $messageType = "success";
}

// SAFE DISPLAY
function profileValue($field) {
    global $student;
    return htmlspecialchars($student[$field] ?? "", ENT_QUOTES, "UTF-8");
}

function displayValue($field) {
    global $student;
    $value = $student[$field] ?? "";
    return htmlspecialchars($value !== "" ? $value : "Not provided", ENT_QUOTES, "UTF-8");
}

// EDIT MODE
$editMode = isset($_GET["edit"]) || empty($student);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMS | Student Profile</title>
    <link rel="stylesheet" href="prof.css">
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

<main class="profile-container">

    <div class="page-heading">
        <span class="heading-label">STUDENT PORTAL</span>
        <h2>My Profile</h2>
        <p>View and manage your personal and academic information.</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="message <?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($editMode): ?>

    <!-- EDIT PROFILE FORM -->
    <form method="POST" class="profile-form">

        <section class="form-section">
            <div class="section-heading">
                <div>
                    <h3>Personal Information</h3>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>First Name <span>*</span></label>
                    <input type="text" name="first_name" value="<?= profileValue('first_name') ?>" required>
                </div>

                <div class="form-group">
                    <label>Last Name <span>*</span></label>
                    <input type="text" name="last_name" value="<?= profileValue('last_name') ?>" required>
                </div>

                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" value="<?= profileValue('middle_name') ?>">
                </div>

                <div class="form-group">
                    <label>Birth Date <span>*</span></label>
                    <input type="date" name="birth_date" value="<?= profileValue('birth_date') ?>" required>
                </div>

                <div class="form-group">
                    <label>Gender <span>*</span></label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <?php foreach (["Male","Female","Other"] as $option): ?>
                            <option value="<?= $option ?>" <?= ($student['gender'] ?? '') == $option ? 'selected' : '' ?>>
                                <?= $option ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </section>

        <section class="form-section">
            <div class="section-heading">
                <div>
                    <h3>Contact Information</h3>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Complete Address <span>*</span></label>
                    <input type="text" name="address" value="<?= profileValue('address') ?>" required>
                </div>

                <div class="form-group">
                    <label>Contact Number <span>*</span></label>
                    <input type="tel" name="contact_number" value="<?= profileValue('contact_number') ?>" required>
                </div>
            </div>
        </section>

        <section class="form-section">
            <div class="section-heading">
                <div>
                    <h3>Academic Information</h3>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label>School / University <span>*</span></label>
                    <input type="text" name="school" value="<?= profileValue('school') ?>" required>
                </div>

                <div class="form-group">
                    <label>Course / Program <span>*</span></label>
                    <input type="text" name="course" value="<?= profileValue('course') ?>" required>
                </div>

                <div class="form-group">
                    <label>Year Level <span>*</span></label>
                    <select name="year_level" required>
                        <option value="">Select Year Level</option>
                        <?php foreach (["1st Year","2nd Year","3rd Year","4th Year"] as $option): ?>
                            <option value="<?= $option ?>" <?= ($student['year_level'] ?? '') == $option ? 'selected' : '' ?>>
                                <?= $option ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>GPA <span>*</span></label>
                    <input type="number" name="gpa" value="<?= profileValue('GPA') ?>" step="0.01" min="0" max="4" required>
                </div>
            </div>
        </section>

        <div class="form-actions">
            <a href="profile.php" class="back-button">Cancel</a>
            <button type="submit" class="save-button">Save Changes</button>
        </div>

    </form>

    <?php else: ?>

    <!-- PROFILE CARD -->
    <div class="profile-card">

        <div class="profile-card-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($student['first_name'] ?? 'S', 0, 1)) ?>
            </div>

            <div class="profile-identity">
                <span class="profile-label">STUDENT PROFILE</span>
                <h2><?= displayValue('first_name') ?> <?= displayValue('middle_name') !== 'Not provided' ? displayValue('middle_name').' ' : '' ?><?= displayValue('last_name') ?></h2>
                <p><?= displayValue('course') ?> · <?= displayValue('year_level') ?></p>
            </div>

            <span class="profile-status">Profile Saved</span>
        </div>

        <section class="info-section">
            <div class="info-heading">
                <h3>Personal Information</h3>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <span>First Name</span>
                    <strong><?= displayValue('first_name') ?></strong>
                </div>
                <div class="info-item">
                    <span>Last Name</span>
                    <strong><?= displayValue('last_name') ?></strong>
                </div>
                <div class="info-item">
                    <span>Middle Name</span>
                    <strong><?= displayValue('middle_name') ?></strong>
                </div>
                <div class="info-item">
                    <span>Birth Date</span>
                    <strong><?= displayValue('birth_date') ?></strong>
                </div>
                <div class="info-item">
                    <span>Gender</span>
                    <strong><?= displayValue('gender') ?></strong>
                </div>
            </div>
        </section>

        <section class="info-section">
            <div class="info-heading">
                <h3>Contact Information</h3>
            </div>

            <div class="info-grid">
                <div class="info-item full-width">
                    <span>Complete Address</span>
                    <strong><?= displayValue('address') ?></strong>
                </div>
                <div class="info-item">
                    <span>Contact Number</span>
                    <strong><?= displayValue('contact_number') ?></strong>
                </div>
            </div>
        </section>

        <section class="info-section">
            <div class="info-heading">
                <h3>Academic Information</h3>
            </div>

            <div class="info-grid">
                <div class="info-item full-width">
                    <span>School / University</span>
                    <strong><?= displayValue('school') ?></strong>
                </div>
                <div class="info-item">
                    <span>Course / Program</span>
                    <strong><?= displayValue('course') ?></strong>
                </div>
                <div class="info-item">
                    <span>Year Level</span>
                    <strong><?= displayValue('year_level') ?></strong>
                </div>
                <div class="info-item">
                    <span>GPA</span>
                    <strong><?= displayValue('GPA') ?></strong>
                </div>
            </div>
        </section>

        <div class="profile-card-actions">
            <a href="student_dashboard.php" class="back-button">← Back to Dashboard</a>
            <a href="profile.php?edit=1" class="save-button edit-button">✎ Edit Profile</a>
        </div>

    </div>

    <?php endif; ?>

</main>

<footer>
    <p>© 2026 SAMS | Scholarship Application Management System</p>
    <p>A Brighter Future Through Education</p>
</footer>

</body>
</html>
