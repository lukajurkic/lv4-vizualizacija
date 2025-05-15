<?php
include("db.php");
include('header.php');

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        $message = "Please fill in both fields.";
    } else {
        $stmt = mysqli_prepare($con, "SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 1) {
            mysqli_stmt_bind_result($stmt, $user_id, $db_username, $db_email, $db_password, $db_role);
            mysqli_stmt_fetch($stmt);

            if (password_verify($password, $db_password)) {
                $_SESSION["user_id"] = $user_id;
                $_SESSION["username"] = $db_username;
                $_SESSION["email"] = $db_email;
                $_SESSION["role"] = $db_role;

                header("Location: lv4.php");
                exit();
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $message = "User not found.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
<div class="auth-container">
    <h2>Login</h2>
    <form method="post" action="">
        <div class="form-group">
            <label for="username">Username or Email:</label>
            <input type="text" name="username" id="username" required>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
        </div>

        <input type="submit" value="Login" class="btn">
    </form>

    <p class="error"><?php echo $message; ?></p>
</div>
</body>
</html>
