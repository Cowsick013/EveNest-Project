<?php
session_start();
require_once "../db.php";
require_once "../includes/gemini.php";

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

if (!isset($_POST['event_id'])) {
    die("Invalid request");
}

$event_id = (int)$_POST['event_id'];
$user_id  = $_SESSION['user_id'];

/* Fetch event (ONLY REAL COLUMNS) */
$result = $conn->query("
    SELECT title, event_date, venue
    FROM events
    WHERE id = $event_id
");

if (!$result) {
    die("Event query failed: " . $conn->error);
}

$event = $result->fetch_assoc();

if (!$event) {
    die("Event not found");
}

/* Generate AI summary */
$ai_summary = generate_gemini_report($event);

/* Check if report already exists */
$check = $conn->query("
    SELECT id FROM post_event_reports WHERE event_id = $event_id
");

if (!$check) {
    die("Check query failed: " . $conn->error);
}

$existing = $check->fetch_assoc();

if ($existing) {

    /* UPDATE */
    $stmt = $conn->prepare("
        UPDATE post_event_reports
        SET summary = ?
        WHERE event_id = ?
    ");

    if (!$stmt) {
        die("Prepare failed (UPDATE): " . $conn->error);
    }

    $stmt->bind_param("si", $ai_summary, $event_id);

} else {

    /* INSERT */
    $stmt = $conn->prepare("
    INSERT INTO post_event_reports
    (event_id, summary, dignitaries, dignitaries_words, created_by, created_at)
    VALUES (?, ?, '', '', ?, NOW())

    ");

    if (!$stmt) {
        die("Prepare failed (INSERT): " . $conn->error);
    }

    $stmt->bind_param("isi", $event_id, $ai_summary, $user_id);
}

$stmt->execute();

if ($stmt->error) {
    die("Execution failed: " . $stmt->error);
}

$stmt->close();

/* Redirect back to print page */
header("Location: ../views/event_report_print.php?id=" . $event_id);
exit;
