<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../views/login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

if (!isset($_POST['event_id'])) {
    exit("Invalid request.");
}

$event_id = intval($_POST['event_id']);

/* ===============================
   PREVENT DUPLICATE REGISTRATION
================================ */
$chk = $conn->prepare("
    SELECT id
    FROM event_registrations
    WHERE event_id = ? AND student_id = ?
");

if (!$chk) {
    die("SQL ERROR (check): " . $conn->error);
}

$chk->bind_param("ii", $event_id, $student_id);
$chk->execute();

if ($chk->get_result()->num_rows > 0) {
    exit("Already registered for this event.");
}

/* ===============================
   INSERT REGISTRATION
================================ */
$stmt = $conn->prepare("
    INSERT INTO event_registrations
    (event_id, student_id, status, registered_at)
    VALUES (?, ?, 'active', NOW())
");

if (!$stmt) {
    die("SQL ERROR (insert): " . $conn->error);
}

$stmt->bind_param("ii", $event_id, $student_id);
$stmt->execute();

header("Location: ../views/student_dashboard.php?registered=1");
exit;
