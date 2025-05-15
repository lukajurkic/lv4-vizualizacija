<?php
include("db.php");
include('header.php');

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $repeat_password = $_POST["repeat_password"];

    if (empty($username) || empty($email) || empty($password)) {
        $message = "All fields required.";
    } elseif ($password !== $repeat_password) {
        $message = "Passwords don't match.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($con, "SELECT id FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $message = "Username or e-mail already exists.";
        } else {
            $stmt = mysqli_prepare($con, "INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password_hash);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION["username"] = $username;
                $_SESSION["email"] = $email;
                $_SESSION["role"] = $role;
                $_SESSION["user_id"] = mysqli_insert_id($con);

                header("Location: lv4.php");
                exit();
            } else {
                $message = "Database error: " . mysqli_error($con);
            }
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
<div class="auth-container">
    <h2>Register</h2>
    <form method="post" action="">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div class="form-group">
            <label for="repeat_password">Repeat Password:</label>
            <input type="password" name="repeat_password" id="repeat_password" required>
        </div>

        <input type="submit" value="Register" class="btn">
    </form>
    <p class="error"><?php echo $message; ?></p>
</div>
</body>
</html>

