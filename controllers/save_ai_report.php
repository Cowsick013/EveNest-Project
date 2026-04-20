<?php
session_start();
require_once "../db.php";
require_once "../includes/flash.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

$event_id = (int) $_POST['event_id'];
$summary = trim($_POST['summary']);
$dignitaries = trim($_POST['dignitaries']);
$remarks = trim($_POST['remarks']);
$user_id = $_SESSION['user_id'];

// Check if report exists
$check = $conn->query("
    SELECT id FROM post_event_reports WHERE event_id = $event_id
")->fetch_assoc();

if ($check) {
    // Update
    $stmt = $conn->prepare("
        UPDATE post_event_reports
        SET summary=?, dignitaries=?, dignitaries_words=?
        WHERE event_id=?
    ");
    $stmt->bind_param("sssi", $summary, $dignitaries, $remarks, $event_id);
} else {
    // Insert
    $stmt = $conn->prepare("
        INSERT INTO post_event_reports
        (event_id, summary, dignitaries, dignitaries_words, created_by, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("isssi", $event_id, $summary, $dignitaries, $remarks, $user_id);
}

$stmt->execute();
$stmt->close();

flash("success", "Report saved successfully.");
header("Location: ../views/event_report_print.php?id=" . $event_id);
exit;
