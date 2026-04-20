<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'principal') {
    header("Location: ../login.php");
    exit;
}
include "../includes/header.php";
?>


<div class="page-container">
    <h2 class="page-title">Principal Dashboard</h2>
    <p class="page-subtitle">Event oversight and approvals</p>

    <div class="dashboard-grid">

        <a href="event_list.php" class="dash-card">
            <div class="dash-icon">📋</div>
            <h3>View Events</h3>
            <p>All institutional events</p>
        </a>

        <a href="event_reports.php" class="dash-card">
            <div class="dash-icon">📄</div>
            <h3>Event Reports</h3>
            <p>Academic reports and outcomes</p>
        </a>

        <a href="cancel_requests.php" class="dash-card">
            <div class="dash-icon">⚠️</div>
            <h3>Cancellation Requests</h3>
            <p>Approve or reject requests</p>
        </a>

        <a href="my_profile.php" class="dash-card">
            <div class="dash-icon">👤</div>
            <h3>My Profile</h3>
            <p>Account information</p>
        </a>

    </div>
</div>

<?php include "../includes/footer.php"; ?>
