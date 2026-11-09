<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$username = $_SESSION["username"] ?? "Administrator";

$sql = "SELECT d.document_id, d.file_name, d.upload_date,
               r.requirement_name,
               s.scholarship_name,
               st.first_name, st.last_name
        FROM documents d
        INNER JOIN applications a ON d.application_id = a.application_id
        INNER JOIN students st ON a.student_id = st.student_id
        INNER JOIN requirements r ON d.requirement_id = r.requirement_id
        INNER JOIN scholarships s ON a.scholarship_id = s.scholarship_id
        ORDER BY d.upload_date DESC";

$documents = mysqli_query($conn, $sql);

if (!$documents) {
    die("Database error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMS | Manage Documents</title>
    <link rel="stylesheet" href="manage_documents.css">
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

            <a href="manage_students.php">
                Students
            </a>

            <a href="manage_documents.php" class="active">
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
                <h1>Manage Documents</h1>

                <p>
                    View and manage uploaded scholarship documents.
                </p>
            </div>

        </div>

        <section class="hero">

            <h2>Uploaded Documents</h2>

            <p>
                Review documents submitted by students for their scholarship
                applications and download files when necessary.
            </p>

        </section>

        <section class="documents-panel">

            <div class="panel-header">

                <div>
                    <h3>Student Documents</h3>

                    <p>
                        All documents uploaded for scholarship applications.
                    </p>
                </div>
            </div>

            <?php if (mysqli_num_rows($documents) > 0): ?>

                <div class="table-container">

                    <table>

                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Scholarship</th>
                                <th>Requirement</th>
                                <th>File Name</th>
                                <th>Upload Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php while ($row = mysqli_fetch_assoc($documents)): ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $row["first_name"] . " " . $row["last_name"]
                                        );
                                        ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["scholarship_name"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["requirement_name"]); ?>
                                </td>

                                <td class="file-name">
                                    <?php echo htmlspecialchars($row["file_name"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["upload_date"]); ?>
                                </td>

                                <td>
                                    <a
                                        class="download-btn"
                                        href="download_document.php?id=<?php echo (int)$row["document_id"]; ?>">
                                        Download
                                    </a>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty">

                    <h3>No Documents Found</h3>

                    <p>
                        No uploaded documents are available yet.
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
