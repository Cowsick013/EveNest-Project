<?php
require_once "includes/admin_auth.php";
require_once "../db.php";
require_once "controllers/AdminEventController.php";

$stats = getDashboardStats($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - EVENEST</title>
    <link rel="stylesheet" href="./style.css">

    <!-- INLINE DASHBOARD POLISH -->
    <style>

    .dashboard-header {
        margin-bottom: 20px;
    }

    .dashboard-header h1 {
        font-size: 28px;
        margin-bottom: 5px;
    }

    .dashboard-header p {
        color: #6b7280;
        font-size: 14px;
    }

    /* GRID LAYOUT */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
    }

    /* CARD OVERRIDE (keeps your base .card but enhances it) */
    .dashboard-card {
        background: white;
        padding: 22px;
        border-radius: 14px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        transition: 0.2s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-4px);
    }

    .dashboard-card h3 {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .dashboard-card p {
        font-size: 28px;
        font-weight: 600;
        color: #111827;
    }

    /* QUICK ACTIONS */
    .quick-actions a {
        display: block;
        margin-top: 10px;
        text-decoration: none;
        color: #1976d2;
        font-weight: 500;
    }

    .quick-actions a:hover {
        text-decoration: underline;
    }

    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>

<div class="main">

    <div class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <p>System overview and quick management controls</p>
    </div>

    <div class="dashboard-grid">

        <div class="dashboard-card">
            <h3>Total Students</h3>
            <p><?= $stats['students'] ?></p>
        </div>

        <div class="dashboard-card">
            <h3>Total Faculty</h3>
            <p><?= $stats['faculty'] ?></p>
        </div>

        <div class="dashboard-card">
            <h3>Active Events</h3>
            <p><?= $stats['events'] ?></p>
        </div>

        <div class="dashboard-card">
            <h3>Pending Cancellations</h3>
            <p><?= $stats['cancellations'] ?></p>
        </div>

        <div class="dashboard-card">
            <h3>Reports Generated</h3>
            <p><?= $stats['reports'] ?></p>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="dashboard-card">
            <h3>Quick Actions</h3>

            <div class="quick-actions">
                <a href="manage_users.php">👥 Manage Users</a>
                <a href="create_student.php">➕ Add Student</a>
                <a href="bulk_upload_students.php">📂 Bulk Upload</a>
                <a href="event_monitor.php">📊 Event Monitoring</a>
                <a href="system_logs.php">🧾 Audit Logs</a>
            </div>
        </div>

    </div>

</div>

</body>
</html>