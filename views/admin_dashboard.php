<?php
session_start();
require_once "../db.php";
require_once "../includes/flash.php";

// Block if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Only allow admin
if ($_SESSION['role'] !== 'admin') {
    echo "Unauthorized Access!";
    exit;
}

// Count pending cancellation requests
$count_req = $conn->query("
    SELECT COUNT(*) AS total 
    FROM events 
    WHERE cancel_request = 'requested'
")->fetch_assoc()['total'];

// Fetch pending requests
$pending = $conn->query("
    SELECT e.*, u.name AS faculty_name
    FROM events e
    LEFT JOIN users u ON u.id = e.created_by
    WHERE e.cancel_request = 'requested'
");
?>

<?php include "../includes/header.php"; ?>

<style>
.admin-wrapper {
    padding: 30px;
}

.admin-header {
    margin-bottom: 25px;
}

.admin-header h1 {
    font-size: 26px;
    margin-bottom: 6px;
}

.admin-header span {
    color: #64748b;
    font-size: 14px;
}

/* DASHBOARD GRID */
.admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 35px;
}

.admin-card {
    background: #fff;
    padding: 22px;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    text-decoration: none;
    color: #0f172a;
    position: relative;
}

.admin-card h3 {
    margin: 0;
    font-size: 18px;
}

.admin-card p {
    font-size: 13px;
    color: #64748b;
    margin-top: 6px;
}

.badge {
    position: absolute;
    top: 14px;
    right: 14px;
    background: #ef4444;
    color: white;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: bold;
}

/* TABLE */
.table-card {
    background: #fff;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
}

.table-card h2 {
    margin-bottom: 15px;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}

th {
    background: #f1f5f9;
}

.action-links a {
    margin-right: 10px;
    font-weight: bold;
    text-decoration: none;
}

.approve { color: #16a34a; }
.reject  { color: #dc2626; }
</style>

<div class="admin-wrapper">

    <div class="admin-header">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']); ?></h1>
        <span>Administrator Dashboard</span>
    </div>

    <!-- DASHBOARD ACTIONS -->
    <div class="admin-grid">

        <a href="../views/calendar.php" class="admin-card">
            <h3>📅 Create Event</h3>
            <p>Schedule new college events</p>
        </a>

        <a href="event_list.php" class="admin-card">
            <h3>📋 View All Events</h3>
            <p>Manage upcoming & past events</p>
        </a>

        <a href="../views/summary_list.php" class="admin-card">
            <h3>📄 Event Reports</h3>
            <p>Post-event academic summaries</p>
        </a>
        <div class="dash-card">
    <h3>Admin Control Panel</h3>
    <p>Manage users, monitor events, and control system access</p>
    <a href="../admin_panel/dashboard.php" class="btn primary">
        Open Admin Panel
    </a>
</div>
        <a href="../views/cancel_requests.php" class="admin-card">
            <h3>⚠ Cancellation Requests</h3>
            <p>Approve or reject cancellations</p>

            <?php if ($count_req > 0): ?>
                <span class="badge"><?= $count_req ?></span>
            <?php endif; ?>
        </a>

    </div>

    <!-- CANCELLATION REQUESTS -->
    <div class="table-card">
        <h2>⚠ Pending Event Cancellation Requests</h2>

        <?php if ($pending->num_rows > 0): ?>

        <table>
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Requested By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php while ($row = $pending->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= date("d M Y", strtotime($row['event_date'])) ?></td>
                    <td><?= htmlspecialchars($row['faculty_name']) ?></td>
                    <td class="action-links">
                        <a class="approve" href="../controllers/approve_cancel.php?id=<?= $row['id'] ?>">Approve</a>
                        <a class="reject" href="../controllers/reject_cancel.php?id=<?= $row['id'] ?>">Reject</a>
                    </td>
                </tr>
            <?php endwhile; ?>

            </tbody>
        </table>

        <?php else: ?>
            <p class="text-muted">No cancellation requests at the moment.</p>
        <?php endif; ?>
    </div>

</div>

<?php include "../includes/footer.php"; ?>
