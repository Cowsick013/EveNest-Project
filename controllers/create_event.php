<?php
session_start();
include("../db.php");

// Faculty only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../views/login.php");
    exit;
}

$faculty_id = $_SESSION['user_id'];
$title = $_POST['title'];
$description = $_POST['description'];
$event_date = $_POST['event_date'];
$today = date('Y-m-d');

if ($event_date < $today) {
    echo "<script>alert('You cannot create an event on a past date!'); 
    window.location.href='../views/create_event.php';</script>";
    exit;
}

$event_time = $_POST['event_time'];
$venue = $_POST['venue'];

$stmt = $conn->prepare("INSERT INTO events (faculty_id, title, description, event_date, event_time, venue) 
                        VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $faculty_id, $title, $description, $event_date, $event_time, $venue);

if ($stmt->execute()) {
    echo "<script>alert('Event Created Successfully! Pending Admin Approval'); 
    window.location.href='../views/faculty_dashboard.php';</script>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
