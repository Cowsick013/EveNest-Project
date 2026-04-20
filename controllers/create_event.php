<?php
session_start();
include("../db.php");

// Faculty only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../views/login.php");
    exit;
}

$faculty_id = $_SESSION['user_id'];

$title       = $_POST['title'];
$description = $_POST['description'];
$event_date  = $_POST['event_date'];
$event_time  = $_POST['event_time'];
$venue       = $_POST['venue'];

$today = date('Y-m-d');
if ($event_date < $today) {
    echo "<script>
        alert('You cannot create an event on a past date!');
        window.location.href='../views/create_event.php';
    </script>";
    exit;
}

/* ✅ TARGET AUDIENCE FIX */
$target_audience = isset($_POST['target_audience'])
    ? implode(', ', $_POST['target_audience'])
    : '';

$stmt = $conn->prepare("
    INSERT INTO events (
    faculty_id, title, description, event_date, event_time,
    venue, target_stream, target_year, target_gender, max_participants
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "issssss",
    $faculty_id,
    $title,
    $description,
    $event_date,
    $event_time,
    $venue,
    $target_audience
);

if ($stmt->execute()) {
    echo "<script>
        alert('Event Created Successfully! Pending Admin Approval');
        window.location.href='../views/faculty_dashboard.php';
    </script>";
} else {
    echo 'Error: ' . $stmt->error;
}

$stmt->close();
$conn->close();
$target_stream = isset($_POST['target_stream'])
    ? implode(', ', $_POST['target_stream'])
    : '';

$target_year = isset($_POST['target_year'])
    ? implode(', ', $_POST['target_year'])
    : '';

$target_gender = $_POST['target_gender'] ?? 'All';

$max_participants = $_POST['max_participants'] ?? null;

?>
