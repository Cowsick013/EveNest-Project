<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    die("Invalid Request");
}

$event_id  = (int) $_POST['event_id'];
$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

/* BASIC FIELDS */
$title      = $_POST['event_title'];
$desc       = $_POST['description'];
$date       = $_POST['event_date'];
$time_from  = $_POST['time_from'];
$time_to    = $_POST['time_to'];
$venue      = $_POST['venue'];
$organized  = $_POST['organized_by'];
$notes      = $_POST['notes'];

/* AUDIENCE LOGIC */
$audience = [
    'streams' => $_POST['audience_streams'] ?? [],
    'years'   => $_POST['audience_year'] ?? [],
    'gender'  => $_POST['audience_gender'] ?? 'All'
];
$audience_json = json_encode($audience);

/* PERMISSION CHECK */
$stmt = $conn->prepare("SELECT created_by FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    die("Event not found");
}

if ($user_role !== "admin" && $event['created_by'] != $user_id) {
    die("Unauthorized");
}

/* UPDATE EVENT */
$stmt = $conn->prepare("
    UPDATE events SET
        title = ?,
        description = ?,
        event_date = ?,
        time_from = ?,
        time_to = ?,
        venue = ?,
        organized_by = ?,
        notes = ?,
        allowed_audience = ?
    WHERE id = ?
");

$stmt->bind_param(
    "sssssssssi",
    $title,
    $desc,
    $date,
    $time_from,
    $time_to,
    $venue,
    $organized,
    $notes,
    $audience_json,
    $event_id
);

if ($stmt->execute()) {
    header("Location: ../views/view_event.php?id=$event_id&updated=1");
    exit;
}

die("Update failed: " . $conn->error);
