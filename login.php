<?php
include 'SQLConnection.php';
session_start();

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Debug: See what values we're working with (remove in production)
    // echo "Email: " . $email . "<br>";
    
    $sql = "SELECT * FROM php_users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        $error_message = "Database error: " . mysqli_error($conn);
    } else {
        $user = mysqli_fetch_assoc($result);
        
        if ($user) {
            // Debug: Check stored password (remove in production)
            // echo "Stored hashed password: " . $user['password'] . "<br>";
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: home.php");
                exit();
            } else {
                $error_message = "Password is incorrect.";
            }
        } else {
            $error_message = "No user found with that email.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Login</h2>
    
    <?php if (!empty($error_message)) { ?>
        <div class="message error"><?php echo $error_message; ?></div>
    <?php } ?>
    
    <form action="" method="post">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</div>
</body>
</html>