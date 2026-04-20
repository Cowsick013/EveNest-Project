<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Unauthorized");
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
    r.team_name,
    r.status AS reg_status,
    e.title,
    e.event_date,
    e.time_to,
    e.status AS event_status
FROM event_registrations r
JOIN events e ON e.id = r.event_id
WHERE r.student_id = ? AND r.event_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $student_id, $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Registration not found");
}

$data = $result->fetch_assoc();

// Time lock
$eventEnd = strtotime($data['event_date'] . ' ' . $data['time_to']);
if (
    $data['reg_status'] !== 'active' ||
    $data['event_status'] === 'cancelled' ||
    time() > $eventEnd
) {
    die("Editing not allowed");
}
?>

<h2>Edit Registration – <?= htmlspecialchars($data['title']) ?></h2>

<form method="POST" action="../controllers/update_registration.php">
    <input type="hidden" name="event_id" value="<?= $event_id ?>">

    <p><strong>Name:</strong> <?= htmlspecialchars($data['full_name']) ?></p>
    <p><strong>Roll No:</strong> <?= htmlspecialchars($data['roll_no']) ?></p>
    <p><strong>Stream:</strong> <?= $data['stream'] ?> (Year <?= $data['year'] ?>)</p>

    <label>Contact Number</label><br>
    <input type="text" name="contact_no" value="<?= htmlspecialchars($data['contact_no']) ?>" required><br><br>

    <label>Age</label><br>
    <input type="number" name="age" value="<?= htmlspecialchars($data['age']) ?>"><br><br>

    <label>Team Name (optional)</label><br>
    <input type="text" name="team_name" value="<?= htmlspecialchars($data['team_name']) ?>"><br><br>

    <button type="submit">Update Registration</button>
</form>
