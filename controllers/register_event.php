<?php
session_start();
require_once "../db.php";

// Only logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../views/login.php");
    exit;
}

// Validate event ID
if (!isset($_GET['id'])) {
    $_SESSION['flash'] = ["type"=>"error", "message"=>"Invalid event"];
    header("Location: ../views/student_dashboard.php");
    exit;
}

$event_id   = intval($_GET['id']);
$student_id = $_SESSION['user_id'];

// Check if event exists and is participatable
$e = $conn->prepare("
    SELECT e.id, p.registration_deadline
    FROM events e
    JOIN event_participation_details p ON e.id = p.event_id
    WHERE e.id = ? AND e.is_participatable = 1
");
$e->bind_param("i", $event_id);
$e->execute();
$event = $e->get_result()->fetch_assoc();

if (!$event) {
    $_SESSION['flash'] = ["type"=>"error", "message"=>"This event is not open for participation"];
    header("Location: ../views/student_dashboard.php");
    exit;
}

// Deadline check
if (!empty($event['registration_deadline']) && date("Y-m-d") > $event['registration_deadline']) {
    $_SESSION['flash'] = ["type"=>"error", "message"=>"Registration deadline has passed"];
    header("Location: ../views/view_event_student.php?id=".$event_id);
    exit;
}

// Prevent duplicate registration
$check = $conn->prepare("
    SELECT id FROM event_participants
    WHERE event_id = ? AND student_id = ?
");
$check->bind_param("ii", $event_id, $student_id);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    $_SESSION['flash'] = ["type"=>"info", "message"=>"You are already registered"];
    header("Location: ../views/view_event_student.php?id=".$event_id);
    exit;
}

// Register student
$insert = $conn->prepare("
    INSERT INTO event_participants (event_id, student_id, registered_at, status)
    VALUES (?, ?, NOW(), 'registered')
");
$insert->bind_param("ii", $event_id, $student_id);

if ($insert->execute()) {
    $_SESSION['flash'] = ["type"=>"success", "message"=>"Successfully registered for the event"];
} else {
    $_SESSION['flash'] = ["type"=>"error", "message"=>"Registration failed"];
}

// Redirect back to event page
header("Location: ../views/registration_success.php?event_id=$event_id");
exit;
