
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    die("Access denied. Your account is not an administrator.");
}

$username = $_SESSION["username"] ?? "Administrator";

function getCount($conn, $query) {
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row["total"] ?? 0;
    }
    return 0;
}

$totalScholarships = getCount($conn,
    "SELECT COUNT(*) AS total FROM scholarships");

$totalApplications = getCount($conn,
    "SELECT COUNT(*) AS total FROM applications");

$pendingApplications = getCount($conn,
    "SELECT COUNT(*) AS total FROM applications WHERE status='pending'");

$totalStudents = getCount($conn,
    "SELECT COUNT(*) AS total FROM students");

$recentApplications = mysqli_query($conn, "
    SELECT a.application_id, a.status, u.username, sch.scholarship_name
    FROM applications a
    JOIN students s ON a.student_id = s.student_id
    JOIN users u ON s.user_id = u.user_id
    JOIN scholarships sch ON a.scholarship_id = sch.scholarship_id
    ORDER BY a.application_id DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMS | Admin Dashboard</title>
    <link rel="stylesheet" href="admin_dashboard.css">
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

            <a href="admin_dashboard.php" class="active">Dashboard</a>

            <a href="manage_scholarships.php">Scholarships</a>

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

        <!-- TOPBAR -->
        <div class="topbar">

            <button class="menu-toggle" id="menuToggle"
                    aria-label="Toggle navigation">
                ☰
            </button>

            <div>
                <h1>Dashboard Overview</h1>
                <p>Welcome back, <?php echo htmlspecialchars($username); ?>!</p>
            </div>

        </div>

        <!-- HERO -->
        <section class="hero">
            
            <h2>Welcome to SAMS Admin Portal</h2>

            <p>
                Manage scholarship programs, monitor student applications,
                and organize scholarship requirements in one place.
            </p>

            <a href="manage_applications.php">Review Applications →</a>
        </section>

        <!-- STATISTICS -->
        <section class="statistics">

            <div class="stat-card">
                <p>Total Scholarships</p>
                <h2><?php echo $totalScholarships; ?></h2>
            </div>

            <div class="stat-card">
                <p>Total Applications</p>
                <h2 class="accent"><?php echo $totalApplications; ?></h2>
            </div>

            <div class="stat-card">
                <p>Pending Applications</p>
                <h2><?php echo $pendingApplications; ?></h2>
            </div>

            <div class="stat-card">
                <p>Registered Students</p>
                <h2 class="accent"><?php echo $totalStudents; ?></h2>
            </div>

        </section>

        <!-- DASHBOARD CONTENT -->
        <section class="dashboard-grid">

            <!-- RECENT APPLICATIONS -->
            <div class="panel">

                <div class="panel-header">
                    <h3>Recent Applications</h3>
                    <a href="manage_applications.php">View All →</a>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Scholarship</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if ($recentApplications && mysqli_num_rows($recentApplications) > 0): ?>

                            <?php while ($row = mysqli_fetch_assoc($recentApplications)): ?>
                                <tr>
                                    <td>#<?php echo $row["application_id"]; ?></td>

                                    <td>
                                        <?php echo htmlspecialchars($row["username"]); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($row["scholarship_name"]); ?>
                                    </td>

                                    <td>
                                        <span class="status <?php echo strtolower($row["status"]); ?>">
                                            <?php echo htmlspecialchars($row["status"]); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="4" class="empty">
                                    No applications found.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>
                    </table>
                </div>

            </div>

            <!-- QUICK ACCESS -->
            <div class="panel">

                <div class="panel-header">
                    <h3>Quick Access</h3>
                </div>

                <div class="quick-links">

                    <a href="manage_scholarships.php">
                        Manage Scholarships <span>→</span>
                    </a>

                    <a href="manage_applications.php">
                        Review Applications <span>→</span>
                    </a>

                    <a href="manage_students.php">
                        Student Records <span>→</span>
                    </a>

                    <a href="manage_documents.php">
                        Uploaded Documents <span>→</span>
                    </a>

                </div>

            </div>

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

// Close sidebar when clicking outside
overlay.addEventListener("click", closeSidebar);

// Close sidebar when clicking a navigation link on mobile
document.querySelectorAll(".sidebar nav a").forEach(function(link) {
    link.addEventListener("click", function() {
        if (window.innerWidth <= 768) {
            closeSidebar();
        }
    });
});

// Responsive sidebar behavior
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