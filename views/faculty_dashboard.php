<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'faculty') {
    echo "Unauthorized Access!";
    exit;
}
?>


<h1>Welcome, <?= $_SESSION['user_name']; ?>Faculty</h1>

<ul>
    <li><a href="../views/calendar.php">Create Event</a></li>
    <li><a href="event_list.php">View My Events</a></li>
    <li><a href="summary_list.php">Summary</a></li>
    <li><a href="../controllers/logout.php">Logout</a></li>
</ul>