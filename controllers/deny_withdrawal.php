<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    die("Unauthorized");
}

$faculty_id = $_SESSION['user_id'];
$event_id   = $_POST['event_id'] ?? null;
$student_id = $_POST['student_id'] ?? null;
$response   = trim($_POST['response'] ?? '');

if (!$event_id || !$student_id || $response === '') {
    die("Invalid request data");
}

$sql = "
UPDATE event_registrations r
JOIN events e ON e.id = r.event_id
SET 
    r.status = 'active',
    r.withdrawal_response = ?
WHERE r.event_id = ?
  AND r.student_id = ?
  AND r.status = 'withdraw_requested'
  AND e.created_by = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("siii", $response, $event_id, $student_id, $faculty_id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    die("No record updated (check ownership or status)");
}

header("Location: ../views/faculty_withdraw_requests.php");
exit;
