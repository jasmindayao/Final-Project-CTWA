<?php
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } else {
        $check = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $message = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = "student";

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $username,
                $email,
                $hashed_password,
                $role
            );

            if (mysqli_stmt_execute($stmt)) {
                $message = "Registration successful!";
            } else {
                $message = "Registration failed.";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($check);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Registration</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

    <div class="register-container">

        <div class="register-header">
            <h2>Student Registration</h2>

            <div class="register-divider">
                <span></span>
                <i class="fa-solid fa-graduation-cap"></i>
                <span></span>
            </div>
        </div>

        <div class="register-message">
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>

        <div class="register-form">
            <form method="POST">

                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-user"></i>
                        Username
                    </label>

                    <div class="input-box">
                        <input type="text" name="username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-envelope"></i>
                        Email
                    </label>

                    <div class="input-box">
                        <input type="email" name="email" required>
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

                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-lock"></i>
                        Confirm Password
                    </label>

                    <div class="input-box">
    <input type="password" name="confirm_password" id="confirm_password" required>
    <i class="fa-regular fa-eye password-icon" onclick="togglePassword('confirm_password', this)"></i>
</div>
                </div>

                <div class="form-button">
                    <button type="submit">
                        Register
                    
                    </button>
                </div>

            </form>
        </div>

        <div class="login-link">
            <p>
                Already have an account?
                <a href="login.php">Back to Login</a>
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
