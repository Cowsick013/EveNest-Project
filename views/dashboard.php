<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
} 



?>
<!DOCTYPE html>
<html>
<body>
    <h2>Welcome, <?php echo $_SESSION['user_name']; ?>!</h2>

    <a href="../controllers/logout.php">Logout</a>
</body>
</html>
