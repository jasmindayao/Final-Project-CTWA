<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["scholarship_name"]);
    $description = trim($_POST["description"]);
    $eligibility = trim($_POST["eligibility"]);
    $benefits = trim($_POST["benefits"]);
    $deadline = $_POST["application_deadline"];
    $status = "active";

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO scholarships
        (scholarship_name, description, eligibility, benefits, application_deadline, status)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssssss",
        $name,
        $description,
        $eligibility,
        $benefits,
        $deadline,
        $status
    );

    if (mysqli_stmt_execute($stmt)) {
        $message = "Scholarship added successfully!";
    } else {
        $message = "Failed to add scholarship.";
    }

    mysqli_stmt_close($stmt);
}

$result = mysqli_query($conn, "SELECT * FROM scholarships ORDER BY scholarship_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Scholarships</title>
</head>
<body>

<h1>Manage Scholarships</h1>

<a href="admin_dashboard.php">Back to Dashboard</a>

<p><?php echo htmlspecialchars($message); ?></p>

<h2>Add Scholarship</h2>

<form method="POST">

    <label>Scholarship Name:</label><br>
    <input type="text" name="scholarship_name" required><br><br>

    <label>Description:</label><br>
    <textarea name="description" required></textarea><br><br>

    <label>Eligibility:</label><br>
    <textarea name="eligibility" required></textarea><br><br>

    <label>Benefits:</label><br>
    <textarea name="benefits" required></textarea><br><br>

    <label>Application Deadline:</label><br>
    <input type="date" name="application_deadline" required><br><br>

    <button type="submit">Add Scholarship</button>

</form>

<hr>

<h2>Existing Scholarships</h2>

<?php if (mysqli_num_rows($result) > 0): ?>

    <?php while ($row = mysqli_fetch_assoc($result)): ?>

        <h3><?php echo htmlspecialchars($row["scholarship_name"]); ?></h3>

        <p>Description: <?php echo nl2br(htmlspecialchars($row["description"])); ?></p>

        <p>Eligibility: <?php echo nl2br(htmlspecialchars($row["eligibility"])); ?></p>

        <p>Benefits: <?php echo nl2br(htmlspecialchars($row["benefits"])); ?></p>

        <p>Deadline: <?php echo htmlspecialchars($row["application_deadline"]); ?></p>

        <p>Status: <?php echo htmlspecialchars($row["status"]); ?></p>

        <hr>

    <?php endwhile; ?>

<?php else: ?>

    <p>No scholarships have been added yet.</p>

<?php endif; ?>

</body>
</html>