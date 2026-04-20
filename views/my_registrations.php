<?php
session_start();
require_once "../db.php";
include "../includes/header.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Unauthorized access");
}

$student_id = $_SESSION['user_id'];

$sql = "
SELECT 
    e.id,
    e.title,
    e.event_date,
    e.time_from,
    e.time_to,
    e.venue,
    e.status AS event_status,
    r.status AS reg_status,
    r.withdrawal_response,
    a.status AS attendance_status
FROM event_registrations r
JOIN events e ON e.id = r.event_id
LEFT JOIN event_attendance a 
    ON a.event_id = r.event_id AND a.student_id = r.student_id
WHERE r.student_id = ?
ORDER BY e.event_date DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query failed: " . $conn->error);
}

$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="page-container">
    <h2 class="page-title">My Registered Events</h2>
    <p class="page-subtitle">Events you have enrolled in</p>

    <div class="card">

<?php if ($result->num_rows === 0): ?>
    <p class="center text-muted">You have not registered for any events.</p>
<?php else: ?>

<table class="event-table">
    <thead>
        <tr>
            <th>Event</th>
            <th>Date</th>
            <th>Time</th>
            <th>Venue</th>
            <th>Registration</th>
            <th>Attendance</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>

<?php while ($row = $result->fetch_assoc()): ?>
<?php
    $now = time();
    $eventEnd = strtotime($row['event_date'] . ' ' . $row['time_to']);
    $attendanceClose = $eventEnd + (30 * 60);

    // Registration status
    if ($row['event_status'] === 'cancelled') {
        $regStatus = "❌ Cancelled";
    } elseif ($row['reg_status'] === 'withdrawn') {
        $regStatus = "🚫 Withdrawn";
    } elseif ($row['reg_status'] === 'withdraw_requested') {
        $regStatus = "🟠 Pending";
    } elseif ($eventEnd < $now) {
        $regStatus = "✔ Ended";
    } else {
        $regStatus = "🟢 Active";
    }

    // Attendance status
    if ($row['reg_status'] === 'withdrawn') {
        $attStatus = "—";
    } elseif ($row['attendance_status'] === 'present') {
        $attStatus = "✅ Present";
    } elseif ($now < $eventEnd) {
        $attStatus = "⏳ Not Open";
    } elseif ($now > $attendanceClose) {
        $attStatus = "❌ Absent";
    } else {
        $attStatus = "🕒 Window Open";
    }
?>

<tr>
    <td class="event-title"><?= htmlspecialchars($row['title']) ?></td>
    <td class="event-date-cell"><?= date("d M Y", strtotime($row['event_date'])) ?></td>
    <td><?= $row['time_from'] ?> – <?= $row['time_to'] ?></td>
    <td><?= htmlspecialchars($row['venue']) ?></td>
    <td><?= $regStatus ?></td>
    <td><?= $attStatus ?></td>
    <td class="action-cell">
        <a class="table-link" href="registration_success.php?event_id=<?= $row['id'] ?>">View</a>
    </td>
</tr>

<?php if (!empty($row['withdrawal_response'])): ?>
<tr>
    <td colspan="7" class="text-muted">
        <b>Faculty Response:</b><br>
        <?= nl2br(htmlspecialchars($row['withdrawal_response'])) ?>
    </td>
</tr>
<?php endif; ?>

<?php endwhile; ?>

    </tbody>
</table>
<?php endif; ?>

    </div>
</div>

<?php include "../includes/footer.php"; ?>
