<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$username = $_SESSION["username"] ?? "Administrator";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $application_id = (int)$_POST["application_id"];
    $status = $_POST["status"];

    if (in_array($status, ["pending", "approved", "rejected"], true)) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE applications SET status = ? WHERE application_id = ?"
        );

        mysqli_stmt_bind_param($stmt, "si", $status, $application_id);

        if (mysqli_stmt_execute($stmt)) {

            $history = mysqli_prepare(
                $conn,
                "INSERT INTO application_status (application_id, status, remarks)
                 VALUES (?, ?, ?)"
            );

            $remarks = "Status updated by administrator.";

            mysqli_stmt_bind_param(
                $history,
                "iss",
                $application_id,
                $status,
                $remarks
            );

            mysqli_stmt_execute($history);
            mysqli_stmt_close($history);

            $message = "Application status updated successfully.";

        } else {
            $message = "Failed to update application status.";
        }

        mysqli_stmt_close($stmt);

    } else {
        $message = "Invalid status selected.";
    }
}

$sql = "SELECT a.application_id, a.application_date, a.status,
               st.first_name, st.last_name, st.school, st.course,
               u.email, s.scholarship_name
        FROM applications a
        INNER JOIN students st ON a.student_id = st.student_id
        INNER JOIN users u ON st.user_id = u.user_id
        INNER JOIN scholarships s ON a.scholarship_id = s.scholarship_id
        ORDER BY a.application_date DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMS | Manage Applications</title>
    <link rel="stylesheet" href="manage_applications.css">
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

            <a href="admin_dashboard.php">
                Dashboard
            </a>

            <a href="manage_scholarships.php">
                Scholarships
            </a>

            <a href="manage_requirements.php">
                Requirements
            </a>

            <a href="manage_applications.php" class="active">
                Applications
            </a>

            <a href="manage_students.php">
                Students
            </a>

            <a href="manage_documents.php">
                Documents
            </a>
        </nav>

        <div class="admin-profile">
            <p>Logged in as</p>

            <strong>
                <?php echo htmlspecialchars($username); ?>
            </strong>

            <a href="logout.php" class="logout">
                Logout
            </a>
        </div>

    </aside>

    <main class="main-area expanded" id="mainArea">

        <div class="topbar">

            <button
                class="menu-toggle"
                id="menuToggle"
                aria-label="Toggle navigation">
                ☰
            </button>

            <div>
                <h1>Manage Applications</h1>

                <p>
                    Review and manage student scholarship applications.
                </p>
            </div>

        </div>

        <section class="hero">

            <h2>Scholarship Applications</h2>

            <p>
                Review submitted applications, check student information,
                and update application status.
            </p>

        </section>

        <?php if ($message !== ""): ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>

        <section class="applications-panel">

            <div class="panel-header">

                <div>
                    <h3>Submitted Applications</h3>

                    <p>
                        Review the details of each scholarship application.
                    </p>
                </div>

                <a href="admin_dashboard.php">
                    ← Dashboard
                </a>

            </div>

            <?php if ($result && mysqli_num_rows($result) > 0): ?>

                <div class="applications-list">

                    <?php while ($row = mysqli_fetch_assoc($result)): ?>

                        <div class="application-card">

                            <div class="application-header">

                                <div>
                                    <span class="application-id">
                                        Application #<?php echo (int)$row["application_id"]; ?>
                                    </span>

                                    <h2>
                                        <?php echo htmlspecialchars($row["scholarship_name"]); ?>
                                    </h2>
                                </div>

                                <span class="status <?php echo strtolower($row["status"]); ?>">
                                    <?php echo htmlspecialchars(ucfirst($row["status"])); ?>
                                </span>

                            </div>

                            <div class="application-details">

                                <div class="detail-item">
                                    <span>Student</span>

                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $row["first_name"] . " " . $row["last_name"]
                                        );
                                        ?>
                                    </strong>
                                </div>

                                <div class="detail-item">
                                    <span>Email</span>

                                    <strong>
                                        <?php echo htmlspecialchars($row["email"]); ?>
                                    </strong>
                                </div>

                                <div class="detail-item">
                                    <span>School</span>

                                    <strong>
                                        <?php echo htmlspecialchars($row["school"]); ?>
                                    </strong>
                                </div>

                                <div class="detail-item">
                                    <span>Course</span>

                                    <strong>
                                        <?php echo htmlspecialchars($row["course"]); ?>
                                    </strong>
                                </div>

                                <div class="detail-item">
                                    <span>Application Date</span>

                                    <strong>
                                        <?php echo htmlspecialchars($row["application_date"]); ?>
                                    </strong>
                                </div>

                                <div class="detail-item">
                                    <span>Current Status</span>

                                    <strong>
                                        <?php echo htmlspecialchars(ucfirst($row["status"])); ?>
                                    </strong>
                                </div>

                            </div>

                            <div class="application-footer">

                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="application_id"
                                        value="<?php echo (int)$row["application_id"]; ?>">

                                    <div class="status-control">

                                        <label>
                                            Update Status
                                        </label>

                                        <select name="status" required>

                                            <option
                                                value="pending"
                                                <?php echo $row["status"] === "pending" ? "selected" : ""; ?>>
                                                Pending
                                            </option>

                                            <option
                                                value="approved"
                                                <?php echo $row["status"] === "approved" ? "selected" : ""; ?>>
                                                Approved
                                            </option>

                                            <option
                                                value="rejected"
                                                <?php echo $row["status"] === "rejected" ? "selected" : ""; ?>>
                                                Rejected
                                            </option>

                                        </select>

                                        <button type="submit">
                                            Update Status
                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            <?php else: ?>

                <div class="empty">
                    <h3>No Applications Found</h3>

                    <p>
                        No scholarship applications have been submitted yet.
                    </p>
                </div>

            <?php endif; ?>

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