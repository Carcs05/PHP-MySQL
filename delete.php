<?php
session_start();
include("SQLConnection.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$error_message = "";

if (isset($_GET["id"])) {
    $id = $_GET["id"];

    // If the user deletes their own account, log them out after deletion
    $isSelf = $_SESSION["user_id"] == $id;

    $sql = "DELETE FROM php_users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($isSelf) {
            session_destroy();
            header("Location: register.php");
        } else {
            header("Location: home.php");
        }
        exit();
    } else {
        $error_message = "Error deleting user: " . $stmt->error;
    }
} else {
    $error_message = "No user ID specified.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Delete User</h2>
    
    <?php if (!empty($error_message)) { ?>
        <div class="message error"><?php echo $error_message; ?></div>
        <br>
        <a href="home.php" class="btn">Back to Home</a>
    <?php } ?>
</div>
</body>
</html>