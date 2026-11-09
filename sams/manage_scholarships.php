
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit;
}

$username = $_SESSION["username"] ?? "Administrator";
$message = "";
$messageType = "success";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add" || $action === "update") {
        $name = trim($_POST["scholarship_name"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $eligibility = trim($_POST["eligibility"] ?? "");
        $benefits = trim($_POST["benefits"] ?? "");
        $deadline = $_POST["application_deadline"] ?? "";
        $status = $_POST["status"] ?? "";

        if (!in_array($status, ["active", "inactive"], true)) {
            $message = "Invalid scholarship status.";
            $messageType = "error";
        } elseif ($name === "" || $description === "" || $eligibility === "" || $benefits === "" || $deadline === "") {
            $message = "Please complete all required fields.";
            $messageType = "error";
        } else {
            if ($action === "add") {
                $stmt = mysqli_prepare($conn,
                    "INSERT INTO scholarships
                    (scholarship_name, description, eligibility, benefits, application_deadline, status)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param($stmt, "ssssss",
                    $name, $description, $eligibility, $benefits, $deadline, $status
                );

                $message = mysqli_stmt_execute($stmt)
                    ? "Scholarship added successfully!"
                    : "Failed to add scholarship.";
            } else {
                $id = (int)($_POST["scholarship_id"] ?? 0);

                $stmt = mysqli_prepare($conn,
                    "UPDATE scholarships
                    SET scholarship_name=?, description=?, eligibility=?,
                        benefits=?, application_deadline=?, status=?
                    WHERE scholarship_id=?"
                );

                mysqli_stmt_bind_param($stmt, "ssssssi",
                    $name, $description, $eligibility, $benefits, $deadline, $status, $id
                );

                $message = mysqli_stmt_execute($stmt)
                    ? "Scholarship updated successfully!"
                    : "Failed to update scholarship.";
            }

            if (isset($stmt)) {
                if (strpos($message, "Failed") === 0) {
                    $messageType = "error";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    if ($action === "delete") {
        $id = (int)($_POST["scholarship_id"] ?? 0);

        $stmt = mysqli_prepare($conn,
            "DELETE FROM scholarships WHERE scholarship_id=?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        $message = mysqli_stmt_execute($stmt)
            ? "Scholarship deleted successfully!"
            : "Failed to delete scholarship.";

        if (strpos($message, "Failed") === 0) {
            $messageType = "error";
        }

        mysqli_stmt_close($stmt);
    }
}

$edit_scholarship = null;

if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = mysqli_prepare($conn,
        "SELECT * FROM scholarships WHERE scholarship_id=?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $edit_scholarship = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    mysqli_stmt_close($stmt);
}

$result = mysqli_query($conn,
    "SELECT * FROM scholarships ORDER BY scholarship_id DESC"
);

$totalScholarships = mysqli_num_rows($result);
$activeScholarships = 0;
$inactiveScholarships = 0;

mysqli_data_seek($result, 0);
while ($countRow = mysqli_fetch_assoc($result)) {
    if ($countRow["status"] === "active") {
        $activeScholarships++;
    } else {
        $inactiveScholarships++;
    }
}
mysqli_data_seek($result, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMS | Manage Scholarships</title>
    <link rel="stylesheet" href="manage_scholarships.css">
</head>

<body>

<div class="dashboard-wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar closed" id="sidebar">

        <div class="logo">
            <h2>S<span>A</span>MS</h2>
            <p>Scholarship Management System</p>
        </div>

        <nav>
            <p class="nav-title">Main Menu</p>

            <a href="admin_dashboard.php" >Dashboard</a>

            <a href="manage_scholarships.php" class="active">Scholarships</a>

            <a href="manage_requirements.php">Requirements</a>

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

    <!-- MAIN CONTENT -->
    <main class="main-area expanded" id="mainArea">

        <div class="topbar">
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation">☰</button>

            <div>
                <h1>Manage Scholarships</h1>
                <p>Create, update, and manage scholarship programs.</p>
            </div>
        </div>

        <!-- PAGE INTRO -->
        <section class="page-banner">
            <div>
                <h2>Scholarship Management</h2>
                <p>Manage scholarship opportunities available to students.</p>
            </div>
        </section>

        <!-- MESSAGE -->
        <?php if ($message !== ""): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- STATISTICS -->
        <section class="statistics">

            <div class="stat-card">
                <p>Total Scholarships</p>
                <h2><?php echo $totalScholarships; ?></h2>
            </div>

            <div class="stat-card">
                <p>Active Scholarships</p>
                <h2 class="green"><?php echo $activeScholarships; ?></h2>
            </div>

            <div class="stat-card">
                <p>Inactive Scholarships</p>
                <h2 class="gold"><?php echo $inactiveScholarships; ?></h2>
            </div>

        </section>

        <!-- ADD / EDIT FORM -->
        <section class="panel form-panel">

            <div class="panel-header">
                <div>
                    <h3><?php echo $edit_scholarship ? "Edit Scholarship" : "Add New Scholarship"; ?></h3>
                    <p><?php echo $edit_scholarship ? "Update scholarship information." : "Fill in the details to create a scholarship."; ?></p>
                </div>

                <span class="panel-icon">✎</span>
            </div>

            <form method="POST" class="scholarship-form">

                <input type="hidden" name="action"
                    value="<?php echo $edit_scholarship ? "update" : "add"; ?>">

                <?php if ($edit_scholarship): ?>
                    <input type="hidden" name="scholarship_id"
                        value="<?php echo (int)$edit_scholarship["scholarship_id"]; ?>">
                <?php endif; ?>

                <div class="form-grid">

                    <div class="form-group full-width">
                        <label for="scholarship_name">Scholarship Name</label>
                        <input type="text" id="scholarship_name" name="scholarship_name"
                            placeholder="Enter scholarship name"
                            value="<?php echo htmlspecialchars($edit_scholarship["scholarship_name"] ?? ""); ?>"
                            required>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"
                            placeholder="Describe the scholarship program"
                            required><?php echo htmlspecialchars($edit_scholarship["description"] ?? ""); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="eligibility">Eligibility</label>
                        <textarea id="eligibility" name="eligibility"
                            placeholder="Enter eligibility requirements"
                            required><?php echo htmlspecialchars($edit_scholarship["eligibility"] ?? ""); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="benefits">Benefits</label>
                        <textarea id="benefits" name="benefits"
                            placeholder="Enter scholarship benefits"
                            required><?php echo htmlspecialchars($edit_scholarship["benefits"] ?? ""); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="application_deadline">Application Deadline</label>
                        <input type="date" id="application_deadline"
                            name="application_deadline"
                            value="<?php echo htmlspecialchars($edit_scholarship["application_deadline"] ?? ""); ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="status">Scholarship Status</label>
                        <select id="status" name="status" required>
                            <option value="active"
                                <?php echo ($edit_scholarship["status"] ?? "active") === "active" ? "selected" : ""; ?>>
                                Active
                            </option>
                            <option value="inactive"
                                <?php echo ($edit_scholarship["status"] ?? "") === "inactive" ? "selected" : ""; ?>>
                                Inactive
                            </option>
                        </select>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="primary-button">
                        <?php echo $edit_scholarship ? "Update Scholarship" : "+ Add Scholarship"; ?>
                    </button>

                    <?php if ($edit_scholarship): ?>
                        <a href="manage_scholarships.php" class="secondary-button">Cancel</a>
                    <?php endif; ?>
                </div>

            </form>
        </section>

        <!-- EXISTING SCHOLARSHIPS -->
        <section class="panel scholarship-panel">

            <div class="panel-header">
                <div>
                    <h3>Existing Scholarships</h3>
                    <p>All scholarship programs in the system.</p>
                </div>

                <span class="count-badge"><?php echo $totalScholarships; ?> Total</span>
            </div>

            <?php if ($totalScholarships > 0): ?>

                <div class="scholarship-list">

                    <?php while ($row = mysqli_fetch_assoc($result)): ?>

                        <article class="scholarship-card">

                            <div class="scholarship-card-header">
                                <div>
                                    <span class="scholarship-id">
                                        SCH-<?php echo str_pad($row["scholarship_id"], 4, "0", STR_PAD_LEFT); ?>
                                    </span>

                                    <h4><?php echo htmlspecialchars($row["scholarship_name"]); ?></h4>
                                </div>

                                <span class="status-badge <?php echo strtolower($row["status"]); ?>">
                                    <?php echo htmlspecialchars($row["status"]); ?>
                                </span>
                            </div>

                            <div class="scholarship-details">

                                <div class="detail-block">
                                    <strong>Description</strong>
                                    <p><?php echo nl2br(htmlspecialchars($row["description"])); ?></p>
                                </div>

                                <div class="detail-block">
                                    <strong>Eligibility</strong>
                                    <p><?php echo nl2br(htmlspecialchars($row["eligibility"])); ?></p>
                                </div>

                                <div class="detail-block">
                                    <strong>Benefits</strong>
                                    <p><?php echo nl2br(htmlspecialchars($row["benefits"])); ?></p>
                                </div>

                            </div>

                            <div class="scholarship-footer">
                                <div class="deadline">
                                    <span>Application Deadline</span>
                                    <strong><?php echo htmlspecialchars($row["application_deadline"]); ?></strong>
                                </div>

                                <div class="card-actions">
                                    <a href="manage_scholarships.php?edit=<?php echo (int)$row["scholarship_id"]; ?>"
                                       class="edit-button">✎ Edit</a>

                                    <form method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this scholarship? Related applications and records may also be deleted.');">

                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="scholarship_id"
                                            value="<?php echo (int)$row["scholarship_id"]; ?>">

                                        <button type="submit" class="delete-button">✕ Delete</button>
                                    </form>
                                </div>
                            </div>

                        </article>

                    <?php endwhile; ?>

                </div>

            <?php else: ?>

                <div class="empty-state">
                    <div class="empty-icon">🎓</div>
                    <h3>No Scholarships Yet</h3>
                    <p>Add your first scholarship using the form above.</p>
                </div>

            <?php endif; ?>

        </section>

        <footer>
            © <?php echo date("Y"); ?> SAMS | Scholarship Application Management System
        </footer>

    </main>

</div>

<!-- MOBILE OVERLAY -->
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

menuToggle.addEventListener("click", function() {
    if (sidebar.classList.contains("closed")) {
        openSidebar();
    } else {
        closeSidebar();
    }
});

overlay.addEventListener("click", closeSidebar);

document.querySelectorAll(".sidebar nav a").forEach(function(link) {
    link.addEventListener("click", function() {
        if (window.innerWidth <= 768) {
            closeSidebar();
        }
    });
});

window.addEventListener("resize", function() {
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