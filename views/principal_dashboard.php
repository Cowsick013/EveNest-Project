<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'principal') {
    echo "Unauthorized Access!";
    exit;
}

?>

<h1>Welcome, <?= $_SESSION['user_name']; ?>principal</h1>

<ul>
    <li><a href="view_events.php">View Events</a></li>
    <li><a href="../controllers/logout.php">Logout</a></li>
</ul>

