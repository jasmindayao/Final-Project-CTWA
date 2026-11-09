
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit;
}

$username = $_SESSION["username"] ?? "Administrator";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        $scholarship_id = (int)($_POST["scholarship_id"] ?? 0);
        $name = trim($_POST["requirement_name"] ?? "");
        $description = trim($_POST["description"] ?? "");

        $stmt = mysqli_prepare($conn,
            "INSERT INTO requirements (scholarship_id, requirement_name, description)
             SELECT scholarship_id, ?, ? FROM scholarships WHERE scholarship_id = ?"
        );

        mysqli_stmt_bind_param($stmt, "ssi", $name, $description, $scholarship_id);

        $message = mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) === 1
            ? "Requirement added successfully!"
            : "Failed to add requirement.";

        mysqli_stmt_close($stmt);
    }

    if ($action === "update") {
        $id = (int)($_POST["requirement_id"] ?? 0);
        $name = trim($_POST["requirement_name"] ?? "");
        $description = trim($_POST["description"] ?? "");

        $stmt = mysqli_prepare($conn,
            "UPDATE requirements
             SET requirement_name = ?, description = ?
             WHERE requirement_id = ?"
        );

        mysqli_stmt_bind_param($stmt, "ssi", $name, $description, $id);

        $message = mysqli_stmt_execute($stmt)
            ? "Requirement updated successfully!"
            : "Failed to update requirement.";

        mysqli_stmt_close($stmt);
    }

    if ($action === "delete") {
        $id = (int)($_POST["requirement_id"] ?? 0);

        $stmt = mysqli_prepare($conn,
            "DELETE FROM requirements WHERE requirement_id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        $message = mysqli_stmt_execute($stmt)
            ? "Requirement deleted successfully!"
            : "Failed to delete requirement.";

        mysqli_stmt_close($stmt);
    }
}

$edit_requirement = null;

if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = mysqli_prepare($conn,
        "SELECT * FROM requirements WHERE requirement_id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $edit_requirement = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

$scholarships = mysqli_query($conn,
    "SELECT scholarship_id, scholarship_name
     FROM scholarships
     ORDER BY scholarship_name"
);

$requirements = mysqli_query($conn,
    "SELECT r.*, s.scholarship_name
     FROM requirements r
     INNER JOIN scholarships s ON r.scholarship_id = s.scholarship_id
     ORDER BY r.requirement_id DESC"
);

if (!$requirements) {
    die("Database error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SAMS | Manage Requirements</title>
<link rel="stylesheet" href="requirements.css">
</head>
<body>

<div class="dashboard-wrapper">

<aside class="sidebar closed" id="sidebar">
    <div class="logo">
        <h2>S<span>A</span>MS</h2>
        <p>Scholarship Management System</p>
    </div>

    <nav>
        <p class="nav-title">Main Menu</p>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_scholarships.php">Scholarships</a>
        <a href="manage_requirements.php" class="active">Requirements</a>
        <a href="manage_applications.php">Applications</a>
        <a href="manage_students.php">Students</a>
        <a href="manage_documents.php">Documents</a>
    </nav>

    <div class="admin-profile">
        <p>Logged in as</p>
        <strong><?php echo htmlspecialchars($username); ?></strong>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</aside>

<main class="main-area expanded" id="mainArea">

<div class="topbar">
    <button class="menu-toggle" id="menuToggle">☰</button>
    <div>
        <h1>Manage Requirements</h1>
        <p>Add, update, and organize scholarship requirements.</p>
    </div>
</div>

<section class="hero">
    <h2>Scholarship Requirements</h2>
    <p>Manage the documents and requirements needed by students for each scholarship program.</p>
</section>

<?php if ($message): ?>
<div class="message"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<section class="content-grid">

<div class="panel form-panel">
    <div class="panel-header">
        <h3><?php echo $edit_requirement ? "Edit Requirement" : "Add Requirement"; ?></h3>
    </div>

    <form method="POST" class="requirement-form">
        <input type="hidden" name="action" value="<?php echo $edit_requirement ? "update" : "add"; ?>">

        <?php if ($edit_requirement): ?>
        <input type="hidden" name="requirement_id" value="<?php echo (int)$edit_requirement["requirement_id"]; ?>">
        <?php endif; ?>

        <?php if (!$edit_requirement): ?>
        <div class="form-group">
            <label for="scholarship_id">Scholarship</label>
            <select name="scholarship_id" id="scholarship_id" required>
                <option value="">Select Scholarship</option>
                <?php while ($scholarship = mysqli_fetch_assoc($scholarships)): ?>
                <option value="<?php echo (int)$scholarship["scholarship_id"]; ?>">
                    <?php echo htmlspecialchars($scholarship["scholarship_name"]); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="requirement_name">Requirement Name</label>
            <input type="text" id="requirement_name" name="requirement_name"
                value="<?php echo htmlspecialchars($edit_requirement["requirement_name"] ?? ""); ?>"
                placeholder="Enter requirement name" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"
                placeholder="Enter requirement description"><?php echo htmlspecialchars($edit_requirement["description"] ?? ""); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <?php echo $edit_requirement ? "Update Requirement" : "Add Requirement"; ?>
            </button>

            <?php if ($edit_requirement): ?>
            <a href="manage_requirements.php" class="btn-cancel">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="panel logo-panel">
    <img src="logo.png" alt="SAMS Logo" class="sams-logo">
    <div class="logo-text">
        <h2>Scholarship Application</h2>
        <h3>Management System</h3>
        <p>Providing accessible and organized scholarship opportunities for deserving students.</p>
        <span class="logo-badge">SAMS ADMINISTRATION</span>
    </div>
</div>

</section>

<section class="panel requirements-panel">
    <div class="panel-header">
        <h3>Existing Requirements</h3>
        <span class="requirement-count">
            <?php echo mysqli_num_rows($requirements); ?> Requirements
        </span>
    </div>

    <div class="requirements-list">
        <?php if (mysqli_num_rows($requirements) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($requirements)): ?>
            <div class="requirement-card">
                <div class="requirement-content">
                    <h3><?php echo htmlspecialchars($row["requirement_name"]); ?></h3>
                    <p class="scholarship-name"><?php echo htmlspecialchars($row["scholarship_name"]); ?></p>
                    <p class="description"><?php echo nl2br(htmlspecialchars($row["description"] ?? "")); ?></p>
                </div>

                <div class="requirement-actions">
                    <a href="manage_requirements.php?edit=<?php echo (int)$row["requirement_id"]; ?>" class="btn-edit">✎ Edit</a>

                    <form method="POST" onsubmit="return confirm('Delete this requirement?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="requirement_id" value="<?php echo (int)$row["requirement_id"]; ?>">
                        <button type="submit" class="btn-delete">✕ Delete</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
        <div class="empty">
            <h3>No Requirements Found</h3>
            <p>No scholarship requirements have been added yet.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<footer>
    © <?php echo date("Y"); ?> SAMS | Scholarship Application Management System
</footer>

</main>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
const sidebar = document.getElementById("sidebar");
const mainArea = document.getElementById("mainArea");
const menuToggle = document.getElementById("menuToggle");
const overlay = document.getElementById("sidebarOverlay");

function openSidebar() {
    sidebar.classList.remove("closed");
    mainArea.classList.remove("expanded");

    if (window.innerWidth <= 768) {
        overlay.classList.add("show");
    }
}

function closeSidebar() {
    sidebar.classList.add("closed");
    mainArea.classList.add("expanded");
    overlay.classList.remove("show");
}

menuToggle.addEventListener("click", function () {
    if (sidebar.classList.contains("closed")) {
        openSidebar();
    } else {
        closeSidebar();
    }
});

overlay.addEventListener("click", closeSidebar);

window.addEventListener("resize", function () {
    overlay.classList.remove("show");

    if (window.innerWidth > 768) {
        sidebar.classList.remove("closed");
        mainArea.classList.remove("expanded");
    } else {
        sidebar.classList.add("closed");
        mainArea.classList.add("expanded");
    }
});
</script>

</body>
</html>