<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "Invalid event ID";
    exit;
}

$event_id  = (int) $_GET['id'];
$user_role = $_SESSION['role'];
$user_id   = $_SESSION['user_id'];

// Fetch event data
$stmt = $conn->prepare("
    SELECT events.*, users.name AS creator_name
    FROM events
    LEFT JOIN users ON events.created_by = users.id
    WHERE events.id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo "Event not found";
    exit;
}

$allowedAudience = null;

if (!empty($event['allowed_audience'])) {
    $allowedAudience = json_decode($event['allowed_audience'], true);
}

// Date logic
$today = date("Y-m-d");
$current_time = date("H:i");

$event_has_ended = (
    $today > $event['event_date'] ||
    ($today == $event['event_date'] && $current_time > $event['time_to'])
);

include "../includes/header.php";
?>
<style>

.event-page {
    max-width: 1000px;
    margin: 30px auto;
    padding: 10px;
}

/* HEADER */
.event-header h2 {
    font-size: 26px;
    margin-bottom: 6px;
}

.event-date {
    color: #6b7280;
    font-size: 14px;
}

/* STATUS BANNERS */
.status-banner {
    padding: 12px 16px;
    border-radius: 8px;
    margin: 15px 0;
    font-size: 14px;
}

.status-banner.ended {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-banner.cancelled {
    background: #fdecea;
    color: #b71c1c;
}

.status-banner.pending {
    background: #fff3cd;
    color: #856404;
}

/* MAIN CARD */
.event-card {
    background: white;
    padding: 24px;
    border-radius: 14px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    margin-top: 20px;
}

/* GRID */
.event-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

/* EACH FIELD */
.event-info-grid div {
    background: #f9fafb;
    padding: 12px 14px;
    border-radius: 10px;
}

.event-info-grid b {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 4px;
}

/* DESCRIPTION */
.event-card p {
    margin-bottom: 14px;
    line-height: 1.6;
}

/* ACTION BAR */
.action-bar {
    margin-top: 20px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

/* BUTTON COLORS */
.btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    text-decoration: none;
}

.btn.primary {
    background: #1f2937;
    color: white;
}

.btn.secondary {
    background: white;
    border: 1px solid #ccc;
    color: #333;
}

.btn.danger {
    background: #ef5350;
    color: white;
}

/* BUTTON INTERACTION */
.btn:hover {
    transform: translateY(-2px);
    transition: 0.2s ease;
}

/* SMALL TEXT */
small {
    display: block;
    margin-top: 6px;
    font-size: 12px;
}

</style>
<div class="event-page">

    <!-- HEADER -->
    <div class="event-header">
        <h2><?= htmlspecialchars($event['title']) ?></h2>
        <span class="event-date">
            <?= $event['event_date'] ?> • <?= $event['time_from'] ?> – <?= $event['time_to'] ?>
        </span>
    </div>

    <!-- STATUS BANNERS -->
    <?php if ($event['status'] === 'cancelled'): ?>
        <div class="status-banner cancelled">
            This event has been cancelled.
        </div>
    <?php elseif ($event_has_ended): ?>
        <div class="status-banner ended">
            This event has been completed.
        </div>
    <?php endif; ?>

    <?php if ($event['cancel_request'] === "requested" && $event['status'] === "active"): ?>
        <div class="status-banner pending">
            Cancellation request pending (requested by <?= htmlspecialchars($event['creator_name']) ?>)
        </div>
    <?php endif; ?>

    <!-- EVENT DETAILS -->
    <div class="event-card">

        <div class="event-info-grid">
            <div><b>Venue</b><br><?= htmlspecialchars($event['venue']) ?></div>
            <div><b>Organized By</b><br><?= htmlspecialchars($event['organized_by']) ?></div>
            <div><b>Speaker</b><br><?= htmlspecialchars($event['speaker']) ?></div>
            <div><b>Event In-Charge</b><br><?= htmlspecialchars($event['incharge']) ?></div>
            <div><b>Coordinator</b><br><?= htmlspecialchars($event['jr_coordinator']) ?></div>
            <div>
    <b>Target Audience</b><br>
    <?= htmlspecialchars($event['audience']) ?>

    <?php if ($allowedAudience): ?>
        <br><br>
        <b>Allowed Audience (Updated)</b><br>

        <?php if (!empty($allowedAudience['streams'])): ?>
            <?= implode(", ", $allowedAudience['streams']) ?><br>
        <?php endif; ?>

        <?php if (!empty($allowedAudience['years'])): ?>
            Years: <?= implode(", ", $allowedAudience['years']) ?><br>
        <?php endif; ?>

        Gender: <?= $allowedAudience['gender'] ?? 'All' ?>

        <br>
        <small style="color:#64748b;">
            Audience eligibility expanded after event creation
        </small>
    <?php endif; ?>
</div>

        </div>

        <hr>

        <p><b>Description</b></p>
        <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>

        <?php if (!empty($event['notes'])): ?>
            <p><b>Notes</b></p>
            <p><?= nl2br(htmlspecialchars($event['notes'])) ?></p>
        <?php endif; ?>

    </div>

    
    <!-- ACTION BAR -->
    <div class="action-bar">

        <?php if (in_array($user_role, ['admin','faculty'])): ?>
            <a href="edit_event.php?id=<?= $event_id ?>" class="btn secondary">Edit Event</a>
        <?php endif; ?>
<!-- ACTION BAR 
        <?php if ($event_has_ended && in_array($user_role, ['admin','faculty'])): ?>
            <a href="view_event_summary.php?id=<?= $event_id ?>" class="btn primary">
                Post-Event Summary
            </a>
        <?php endif; ?>
-->
        <?php if (
            $user_role === 'faculty' &&
            $event['status'] === 'active' &&
            in_array($event['cancel_request'], ['none','denied'])
        ): ?>
            <a href="../controllers/request_cancel.php?id=<?= $event_id ?>" class="btn danger">
                Request Cancellation
            </a>
        <?php endif; ?>

    </div>

    <!-- STUDENT ACTION -->
    <?php if ($user_role === 'student' && !$event_has_ended): ?>
        <a href="mark_attendance.php?event_id=<?= $event_id ?>" class="btn primary">
            Mark Attendance
        </a>
    <?php endif; ?>

    <!-- FACULTY CONTROLS -->
    <?php if ($user_role === 'faculty' && $event['status'] === 'active'): ?>
        <hr>
        <h3>Faculty Controls</h3>

        <a href="registered_students.php?event_id=<?= $event_id ?>" class="btn secondary">
            View Registered Students
        </a>

        <a href="attendance_qr.php?event_id=<?= $event_id ?>" class="btn secondary">
            Open Attendance QR
        </a>
    <?php endif; ?>
    <a href="event_list.php" class="btn secondary">← Back to Events</a>

</div>

<?php include "../includes/footer.php"; ?>
