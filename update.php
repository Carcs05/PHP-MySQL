<?php
session_start();
include("SQLConnection.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$error_message = "";
$success_message = "";
$user = null;

// Get user ID from URL
if (isset($_GET["id"])) {
    $id = $_GET["id"];
    
    // Fetch current user data
    $sql = "SELECT * FROM php_users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        $error_message = "User not found.";
    }
} else {
    header("Location: home.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $user) {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $id = $_POST["id"];
    
    // Check if email already exists (excluding current user)
    $check_sql = "SELECT * FROM php_users WHERE email = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $email, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $error_message = "Email already exists. Please use a different email.";
    } else {
        // Update user (password only if provided)
        if (!empty($_POST["password"])) {
            $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
            $update_sql = "UPDATE php_users SET username = ?, email = ?, password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssi", $username, $email, $password, $id);
        } else {
            $update_sql = "UPDATE php_users SET username = ?, email = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssi", $username, $email, $id);
        }

        if ($update_stmt->execute()) {
            $success_message = "User updated successfully!";
            // Refresh user data
            $sql = "SELECT * FROM php_users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error_message = "Error updating user: " . $update_stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Update User</h2>
    
    <?php if (!empty($error_message)) { ?>
        <div class="message error"><?php echo $error_message; ?></div>
    <?php } ?>
    
    <?php if (!empty($success_message)) { ?>
        <div class="message success"><?php echo $success_message; ?></div>
    <?php } ?>
    
    <?php if ($user) { ?>
        <form method="post">
            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
            <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br>
            <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>
            <input type="password" name="password" placeholder="New Password (leave blank to keep current)"><br>
            <button type="submit">Update User</button>
        </form>
        <br>
        <a href="home.php" class="btn">Back to Home</a>
    <?php } else { ?>
        <a href="home.php" class="btn">Back to Home</a>
    <?php } ?>
</div>
</body>
</html>

