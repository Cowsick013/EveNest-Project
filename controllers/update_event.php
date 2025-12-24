<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo "Invalid Request";
    exit;
}

$event_id    = $_POST['event_id'];
$title       = $_POST['event_title'];
$desc        = $_POST['description'];
$date        = $_POST['event_date'];
$time_from   = $_POST['time_from'];
$time_to     = $_POST['time_to'];
$venue       = $_POST['venue'];
$organized   = $_POST['organized_by'];
$notes       = $_POST['notes'];

$user_role   = $_SESSION['role'];
$user_id     = $_SESSION['user_id'];

// Check permission
$stmt = $conn->prepare("SELECT created_by FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    echo "Event not found.";
    exit;
}

if ($user_role !== "admin" && $event['created_by'] != $user_id) {
    echo "You are not allowed to update this event.";
    exit;
}

// Update query
$stmt = $conn->prepare("
    UPDATE events 
    SET title=?, description=?, event_date=?, time_from=?, time_to=?, venue=?, organized_by=?, notes=?
    WHERE id=?
");

$stmt->bind_param("ssssssssi", 
    $title,
    $desc,
    $date,
    $time_from,
    $time_to,
    $venue,
    $organized,
    $notes,
    $event_id
);

if ($stmt->execute()) {
    header("Location: ../views/view_event.php?id=$event_id&updated=1");
    exit;
} else {
    echo "Error updating event: " . $conn->error;
}
?>
