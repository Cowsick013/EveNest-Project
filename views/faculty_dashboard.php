<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../login.php");
    exit;
}

include "../includes/header.php";
?>

<h2 class="page-title">Faculty Dashboard</h2>
<p class="page-subtitle">
    Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Faculty') ?>
</p>

<div class="dashboard-grid">

    <a href="calendar.php" class="dash-card">
        <div class="dash-icon">📅</div>
        <h3>Event Calendar</h3>
        <p>View, plan and manage upcoming events</p>
    </a>

    <a href="event_list.php" class="dash-card">
        <div class="dash-icon">📋</div>
        <h3>My Events</h3>
        <p>View events created or handled by you</p>
    </a>

    <a href="summary_list.php" class="dash-card">
        <div class="dash-icon">📝</div>
        <h3>Post-Event Summary</h3>
        <p>Add summaries and dignitaries remarks</p>
    </a>

    <a href="event_reports.php" class="dash-card">
        <div class="dash-icon">📄</div>
        <h3>Event Reports</h3>
        <p>View and generate academic reports</p>
    </a>

    <a href="my_profile.php" class="dash-card">
        <div class="dash-icon">👤</div>
        <h3>My Profile</h3>
        <p>View or update your account details</p>
    </a>
<a href="faculty_attendance.php?event_id=<?= $event['id'] ?>">
    📋 View Attendance
</a>
</div>

<?php include "../includes/footer.php"; ?>
