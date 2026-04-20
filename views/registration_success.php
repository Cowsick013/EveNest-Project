<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Unauthorized access");
}

$student_id = $_SESSION['user_id'];
$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    die("Invalid request");
}

$sql = "
SELECT 
    r.full_name,
    r.roll_no,
    r.stream,
    r.year,
    r.age,
    r.contact_no,
    r.email,
    r.team_name,
    r.registered_at,
    e.title,
    e.event_date,
    e.time_from,
    e.time_to,
    e.venue,
    e.status
FROM event_registrations r
JOIN events e ON e.id = r.event_id
WHERE r.event_id = ? AND r.student_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $event_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No registration record found");
}

$data = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registration Successful</title>
</head>
<body>

<h2>✅ Registration Successful</h2>

<div class="page-container">

<h3><?= htmlspecialchars($data['title']) ?></h3>

<p><strong>Date:</strong> <?= $data['event_date'] ?></p>
<p><strong>Time:</strong> <?= $data['time_from'] ?> – <?= $data['time_to'] ?></p>
<p><strong>Venue:</strong> <?= htmlspecialchars($data['venue']) ?></p>

<hr>

<h4>Student Details</h4>
<p><strong>Name:</strong> <?= htmlspecialchars($data['full_name']) ?></p>
<p><strong>Roll No:</strong> <?= htmlspecialchars($data['roll_no']) ?></p>
<p><strong>Stream:</strong> <?= $data['stream'] ?></p>
<p><strong>Year:</strong> <?= $data['year'] ?></p>

<?php if (!empty($data['age'])): ?>
<p><strong>Age:</strong> <?= $data['age'] ?></p>
<?php endif; ?>

<p><strong>Email:</strong> <?= htmlspecialchars($data['email']) ?></p>
<p><strong>Contact:</strong> <?= htmlspecialchars($data['contact_no']) ?></p>

<?php if (!empty($data['team_name'])): ?>
<p><strong>Team Name:</strong> <?= htmlspecialchars($data['team_name']) ?></p>
<?php endif; ?>

<p><strong>Registered On:</strong> <?= $data['registered_at'] ?></p>

<hr>

<p><strong>⚠️ Attendance Notice:</strong><br>
Attendance will be marked on the event day using <b>QR Code + GPS verification</b>.
</p>

<br>

<a href="my_registrations.php">📋 My Registered Events</a> |
<a href="view_event_student.php?id=<?= $event_id ?>">🔍 View Event</a>

</body>
</html>
