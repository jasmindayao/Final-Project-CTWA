
<?php
session_start();
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT user_id, username, password, role FROM users WHERE email = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user["password"])) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = trim($user["role"]);

            mysqli_stmt_close($stmt);

            if ($_SESSION["role"] === "admin") {
                header("Location: admin_dashboard.php");
                exit;
            }

            if ($_SESSION["role"] === "student") {
                header("Location: student_dashboard.php");
                exit;
            }

            session_destroy();
            die("Invalid account role.");
        } else {
            $message = "Incorrect email or password.";
        }
    } else {
        $message = "Incorrect email or password.";
    }

    mysqli_stmt_close($stmt);
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Scholarship System Login</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

    <div class="login-container">

        <div class="login-header">
            <h2>Scholarship System Login</h2>

            <div class="login-divider">
                <span></span>
                <i class="fa-solid fa-graduation-cap"></i>
                <span></span>
            </div>
        </div>

        <div class="login-message">
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>

        <div class="login-form">
            <form method="POST">

                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-user"></i>
                        Username or Email Address
                    </label>

                    <div class="input-box">
                        <input type="text" name="email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-lock"></i>
                        Password
                    </label>

                    <div class="input-box">
    <input type="password" name="password" id="password" required>
    <i class="fa-regular fa-eye password-icon" onclick="togglePassword('password', this)"></i>
</div>
                </div>


                <div class="form-button">
                    <button type="submit">
                        Log In
                    </button>
                </div>

            </form>
        </div>

        <div class="login-link">
            <p>
                Don't have an account?
                <a href="register.php">Register Now</a>
            </p>
        </div>

    </div>

<script>
    function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>
</body>
</html>
