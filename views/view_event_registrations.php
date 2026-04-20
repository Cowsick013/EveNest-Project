<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    die("Unauthorized");
}

$faculty_id = $_SESSION['user_id'];
$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    die("Invalid event");
}

// Verify faculty owns event
$eventCheck = $conn->prepare("
    SELECT id, title, event_date, time_from, time_to
    FROM events
    WHERE id = ? AND created_by = ?
");
$eventCheck->bind_param("ii", $event_id, $faculty_id);
$eventCheck->execute();
$event = $eventCheck->get_result()->fetch_assoc();

if (!$event) {
    die("Access denied");
}

// Fetch registrations
$sql = "
SELECT 
    r.student_id,
    r.full_name,
    r.roll_no,
    r.stream,
    r.year,
    r.contact_no,
    r.status
FROM event_registrations r
WHERE r.event_id = ?
  AND r.status != 'withdrawn'
ORDER BY r.full_name
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Registered Students – <?= htmlspecialchars($event['title']) ?></h2>
<p>
    📅 <?= $event['event_date'] ?> |
    ⏰ <?= $event['time_from'] ?> – <?= $event['time_to'] ?>
</p>

<a href="attendance_qr.php?event_id=<?= $event_id ?>">📸 Open Attendance QR</a>

<br><br>

<?php if ($result->num_rows === 0): ?>
<p>No registered students.</p>
<?php else: ?>
<table border="1" cellpadding="8">
<tr>
    <th>Name</th>
    <th>Roll No</th>
    <th>Stream</th>
    <th>Year</th>
    <th>Contact</th>
    <th>Status</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['full_name']) ?></td>
    <td><?= htmlspecialchars($row['roll_no']) ?></td>
    <td><?= $row['stream'] ?></td>
    <td><?= $row['year'] ?></td>
    <td><?= htmlspecialchars($row['contact_no']) ?></td>
    <td>
<?php
if ($row['status'] === 'withdrawn') {
    echo "<span style='color:red;'>❌ Withdrawn (Blocked)</span>";
} elseif ($row['status'] === 'withdraw_requested') {
    echo "<span style='color:orange;'>🟠 Withdrawal Pending (Blocked)</span>";
} else {
    echo "<span style='color:green;'>🟢 Active</span>";
}
?>
</td>

</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>
