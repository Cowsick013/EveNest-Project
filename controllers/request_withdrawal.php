<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Unauthorized");
}

$student_id = $_SESSION['user_id'];
$event_id = $_POST['event_id'] ?? null;
$reason = trim($_POST['withdrawal_reason'] ?? '');

if (!$event_id || $reason === '') {
    die("Invalid request");
}

$sql = "
UPDATE event_registrations
SET status = 'withdraw_requested',
    withdrawal_reason = ?
WHERE student_id = ?
  AND event_id = ?
  AND status = 'active'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $reason, $student_id, $event_id);
$stmt->execute();

header("Location: ../views/my_registrations.php");
exit;
