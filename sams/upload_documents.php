<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit;
}

$message = "";

$stmt = mysqli_prepare(
    $conn,
    "SELECT a.application_id, s.scholarship_name
     FROM applications a
     INNER JOIN students st ON a.student_id = st.student_id
     INNER JOIN scholarships s ON a.scholarship_id = s.scholarship_id
     WHERE st.user_id = ?
     ORDER BY a.application_id DESC"
);

mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$applications = mysqli_stmt_get_result($stmt);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $application_id = (int)$_POST["application_id"];
    $requirement_id = (int)$_POST["requirement_id"];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT r.requirement_id
         FROM requirements r
         INNER JOIN applications a ON a.scholarship_id = r.scholarship_id
         INNER JOIN students st ON st.student_id = a.student_id
         WHERE a.application_id = ?
         AND r.requirement_id = ?
         AND st.user_id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "iii",
        $application_id,
        $requirement_id,
        $_SESSION["user_id"]
    );

    mysqli_stmt_execute($stmt);
    $valid_requirement = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    if (mysqli_num_rows($valid_requirement) === 0) {
        $message = "Invalid application or requirement selected.";
    } elseif (!isset($_FILES["document"]) || $_FILES["document"]["error"] !== UPLOAD_ERR_OK) {
        $message = "Please select a valid file to upload.";
    } elseif ($_FILES["document"]["size"] > 5 * 1024 * 1024) {
        $message = "File size must not exceed 5 MB.";
    } else {
        $original_name = $_FILES["document"]["name"];
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed = ["pdf", "jpg", "jpeg", "png"];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES["document"]["tmp_name"]);
        finfo_close($finfo);

        $allowed_types = [
            "pdf" => "application/pdf",
            "jpg" => "image/jpeg",
            "jpeg" => "image/jpeg",
            "png" => "image/png"
        ];

        if (!isset($allowed_types[$extension]) || $mime !== $allowed_types[$extension]) {
            $message = "Only PDF, JPG, JPEG, and PNG files are allowed.";
        } else {
            $upload_directory = __DIR__ . "/uploads/";

            if (!is_dir($upload_directory)) {
                mkdir($upload_directory, 0755, true);
            }

            $file_name = bin2hex(random_bytes(16)) . "." . $extension;
            $destination = $upload_directory . $file_name;

            if (move_uploaded_file($_FILES["document"]["tmp_name"], $destination)) {
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO documents (application_id, requirement_id, file_name)
                     VALUES (?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "iis",
                    $application_id,
                    $requirement_id,
                    $file_name
                );

                if (mysqli_stmt_execute($stmt)) {
                    $message = "Document uploaded successfully!";
                } else {
                    unlink($destination);
                    $message = "Failed to save document information.";
                }

                mysqli_stmt_close($stmt);
            } else {
                $message = "File upload failed.";
            }
        }
    }
}

$requirements = mysqli_prepare(
    $conn,
    "SELECT r.requirement_id, r.requirement_name, r.scholarship_id,
            s.scholarship_name
     FROM requirements r
     INNER JOIN scholarships s ON r.scholarship_id = s.scholarship_id
     INNER JOIN applications a ON a.scholarship_id = s.scholarship_id
     INNER JOIN students st ON st.student_id = a.student_id
     WHERE st.user_id = ?
     ORDER BY s.scholarship_name, r.requirement_name"
);

mysqli_stmt_bind_param($requirements, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($requirements);
$requirement_result = mysqli_stmt_get_result($requirements);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Documents</title>
    <link rel="stylesheet" href="upload_documents.css">
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

    <div class="page-intro">

        <div class="intro-text">
            <span class="page-label">STUDENT PORTAL</span>

            <h2>Upload Documents</h2>

            <p>
                Upload the required documents for your scholarship application.
            </p>
        </div>

    </div>

    <div class="upload-card">

        <div class="upload-header">
            <h3>Scholarship Documents</h3>

            <p>
                Please select your application and the corresponding requirement.
            </p>
        </div>

        <?php if (!empty($message)): ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="upload-form">

            <div class="form-group">

                <label for="application_id">
                    Application
                </label>

                <select name="application_id" id="application_id" required>

                    <option value="">
                        Select Application
                    </option>

                    <?php while ($application = mysqli_fetch_assoc($applications)): ?>

                        <option value="<?php echo (int)$application["application_id"]; ?>">
                            <?php echo htmlspecialchars($application["scholarship_name"]); ?>
                        </option>

                    <?php endwhile; ?>

                </select>

            </div>

            <div class="form-group">

                <label for="requirement_id">
                    Requirement
                </label>

                <select name="requirement_id" id="requirement_id" required>

                    <option value="">
                        Select Requirement
                    </option>

                    <?php while ($requirement = mysqli_fetch_assoc($requirement_result)): ?>

                        <option value="<?php echo (int)$requirement["requirement_id"]; ?>">
                            <?php echo htmlspecialchars($requirement["scholarship_name"] . " - " . $requirement["requirement_name"]); ?>
                        </option>

                    <?php endwhile; ?>

                </select>

            </div>

            <div class="form-group">

                <label for="document">
                    Select Document
                </label>

                <div class="file-box">
                    <input
                        type="file"
                        name="document"
                        id="document"
                        accept=".pdf,.jpg,.jpeg,.png"
                        required
                    >
                </div>

                <p class="file-info">
                    Allowed formats: PDF, JPG, JPEG, PNG. Maximum size: 5 MB.
                </p>

            </div>

            <div class="form-actions">

                <a href="student_dashboard.php" class="back-button">
                    ← Back to Dashboard
                </a>

                <button type="submit" class="upload-button">
                    Upload Document
                </button>

            </div>

        </form>

    </div>

</main>

<footer>

    <div class="footer-content">
        <p>© 2026 SAMS | Scholarship Application Management System</p>
        <p>A Brighter Future Through Education</p>
    </div>

</footer>

</body>
</html>