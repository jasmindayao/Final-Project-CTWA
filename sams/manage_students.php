<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$username = $_SESSION["username"] ?? "Administrator";

$sql = "SELECT st.student_id, st.first_name, st.last_name,
               st.middle_name, st.birth_date, st.gender,
               st.address, st.contact_number, st.school,
               st.course, st.year_level, st.GPA, u.email
        FROM students st
        INNER JOIN users u ON st.user_id = u.user_id
        ORDER BY st.student_id DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMS | Manage Students</title>
    <link rel="stylesheet" href="manage_students.css">
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

            <a href="manage_applications.php">
                Applications
            </a>

            <a href="manage_students.php" class="active">
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
                <h1>Manage Students</h1>

                <p>
                    View and manage registered student profiles.
                </p>
            </div>

        </div>

        <section class="hero">

            <h2>Student Records</h2>

            <p>
                View student information, academic details,
                contact information, and scholarship records.
            </p>

        </section>

        <section class="students-panel">

            <div class="panel-header">

                <div>
                    <h3>Registered Students</h3>

                    <p>
                        Student profiles registered in the scholarship system.
                    </p>
                </div>
            </div>

            <?php if ($result && mysqli_num_rows($result) > 0): ?>

                <div class="students-list">

                    <?php while ($student = mysqli_fetch_assoc($result)): ?>

                        <div class="student-card">

                            <div class="student-header">

                                <div>

                                    <span class="student-id">
                                        Student ID #<?php echo (int)$student["student_id"]; ?>
                                    </span>

                                    <h2>
                                        <?php
                                        echo htmlspecialchars(
                                            $student["first_name"] . " " .
                                            $student["last_name"]
                                        );
                                        ?>
                                    </h2>

                                </div>

                            </div>

                            <div class="student-details">

                                <div class="detail-item">
                                    <span>Email</span>

                                    <strong>
                                        <?php echo htmlspecialchars($student["email"]); ?>
                                    </strong>
                                </div>

                                <div class="detail-item">
                                    <span>Middle Name</span>

                                    <strong>
                                        <?php echo htmlspecialchars($student["middle_name"] ?? ""); ?>
                                    </strong>
                                </div>

                                <div class="detail-item">
                                    <span>Birth Date</span>

                                    <strong>
                                        <?php echo htmlspecialchars($student["birth_date"]); ?>
                                    </strong>
                                </div>

                                <div class="detail-item">
                                    <span>Gender</span>

                                    <strong>
                                        <?php echo htmlspecialchars($student["gender"]); ?>
                                    </strong>
                                </div>

                                <div class="detail-item full">
                                    <span>Address</span>

                                    <strong>
                                        <?php echo htmlspecialchars($student["address"]); ?>
                                    </strong>
                                </div>

                                <div class="detail-item">
                                    <span>Contact Number</span>

                                    <strong>
                                        <?php echo htmlspecialchars($student["contact_number"]); ?>
                                    </strong>
                                </div>

                                <div class="detail-item">
                                    <span>School</span>

                                    <strong>
                                        <?php echo htmlspecialchars($student["school"]); ?>
                                    </strong>
                                </div>

                                <div class="detail-item">
                                    <span>Course</span>

                                    <strong>
                                        <?php echo htmlspecialchars($student["course"]); ?>
                                    </strong>
                                </div>

                                <div class="detail-item">
                                    <span>Year Level</span>

                                    <strong>
                                        <?php echo htmlspecialchars($student["year_level"]); ?>
                                    </strong>
                                </div>

                                <div class="detail-item">
                                    <span>GPA</span>

                                    <strong>
                                        <?php echo htmlspecialchars($student["GPA"]); ?>
                                    </strong>
                                </div>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            <?php else: ?>

                <div class="empty">

                    <h3>No Students Found</h3>

                    <p>
                        No student profiles have been registered yet.
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
