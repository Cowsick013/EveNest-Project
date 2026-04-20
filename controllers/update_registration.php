<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Unauthorized");
}

$student_id = $_SESSION['user_id'];

$event_id    = $_POST['event_id'] ?? null;
$contact_no  = $_POST['contact_no'] ?? null;
$age         = $_POST['age'] ?? null;
$team_name  = $_POST['team_name'] ?? null;

if (!$event_id || !$contact_no) {
    die("Invalid data");
}

// Re-validate (never trust frontend)
$sql = "
SELECT r.status, e.event_date, e.time_to, e.status AS event_status
FROM event_registrations r
JOIN events e ON e.id = r.event_id
WHERE r.student_id = ? AND r.event_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $student_id, $event_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$eventEnd = strtotime($data['event_date'] . ' ' . $data['time_to']);

if (
    $data['status'] !== 'active' ||
    $data['event_status'] === 'cancelled' ||
    time() > $eventEnd
) {
    die("Update not allowed");
}

// Update
$updateSql = "
UPDATE event_registrations
SET contact_no = ?, age = ?, team_name = ?
WHERE student_id = ? AND event_id = ?
";

$stmt = $conn->prepare($updateSql);
$stmt->bind_param("sisii", $contact_no, $age, $team_name, $student_id, $event_id);
$stmt->execute();

header("Location: ../views/registration_success.php?event_id=$event_id");
exit;
