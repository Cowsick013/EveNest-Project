<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

include "../includes/header.php";
?>

<h2 class="page-title">Student Dashboard</h2>
<p class="page-subtitle">
    Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Student') ?>
</p>

<div class="dashboard-grid">

    <a href="calendar.php" class="dash-card">
        <div class="dash-icon">📅</div>
        <h3>Event Calendar</h3>
        <p>View upcoming and past events</p>
    </a>

    <a href="my_registrations.php" class="dash-card">
        <div class="dash-icon">🎟️</div>
        <h3>My Registrations</h3>
        <p>Events you have registered for</p>
    </a>

    <a href="event_reports.php" class="dash-card">
        <div class="dash-icon">📄</div>
        <h3>Event Reports</h3>
        <p>View post-event academic reports</p>
    </a>

    <a href="my_profile.php" class="dash-card">
        <div class="dash-icon">👤</div>
        <h3>My Profile</h3>
        <p>View or update your details</p>
    </a>

</div>

<?php include "../includes/footer.php"; ?>
