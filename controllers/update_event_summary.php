<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!in_array($_SESSION['role'], ['admin','faculty'])) {
    die("Unauthorized access");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

if (!isset($_POST['event_id'])) {
    die("Event ID missing");
}

$event_id = (int) $_POST['event_id'];

$summary_text       = trim($_POST['summary']);
$dignitaries        = trim($_POST['dignitaries']);
$dignitaries_words  = trim($_POST['dignitaries_words']);

// Basic validation
if ($summary_text === "") {
    die("Summary cannot be empty");
}

// Update summary
$stmt = $conn->prepare("
    UPDATE post_event_reports
    SET 
        summary = ?,
        dignitaries = ?,
        dignitaries_words = ?,
        updated_at = NOW()
    WHERE event_id = ?
");

$stmt->bind_param(
    "sssi",
    $summary_text,
    $dignitaries,
    $dignitaries_words,
    $event_id
);

if ($stmt->execute()) {
    header("Location: ../views/view_event_summary.php?event_id=".$event_id."&updated=1");
    exit;
}

die("Failed to update summary: " . $conn->error);
