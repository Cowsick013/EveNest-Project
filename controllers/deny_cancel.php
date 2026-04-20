<?php
session_start();
require_once "../db.php";

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash'] = [
        "type" => "error",
        "message" => "Unauthorized access"
    ];
    header("Location: ../views/cancel_requests.php");
    exit;
}

// Only Admin or Principal can deny
if (!in_array($_SESSION['role'], ['admin', 'principal'])) {
    $_SESSION['flash'] = [
        "type" => "error",
        "message" => "Only Admin or Principal can deny cancellations"
    ];
    header("Location: ../views/cancel_requests.php");
    exit;
}

// Must have event ID
if (!isset($_GET['id'])) {
    $_SESSION['flash'] = [
        "type" => "error",
        "message" => "Invalid event selected"
    ];
    header("Location: ../views/cancel_requests.php");
    exit;
}

$event_id = (int) $_GET['id'];

// Fetch event + creator info
$eventQuery = $conn->prepare("
    SELECT title, created_by 
    FROM events 
    WHERE id = ?
");
$eventQuery->bind_param("i", $event_id);
$eventQuery->execute();
$event = $eventQuery->get_result()->fetch_assoc();

if (!$event) {
    $_SESSION['flash'] = [
        "type" => "error",
        "message" => "Event not found"
    ];
    header("Location: ../views/cancel_requests.php");
    exit;
}

$event_title = $event['title'];
$creator_id  = $event['created_by'];

// Fetch creator email (optional)
$userQuery = $conn->prepare("SELECT email FROM users WHERE id = ?");
$userQuery->bind_param("i", $creator_id);
$userQuery->execute();
$user = $userQuery->get_result()->fetch_assoc();

$creator_email = $user ? $user['email'] : null;

// Deny cancellation (reset request)
$stmt = $conn->prepare("
    UPDATE events 
    SET cancel_request = 'denied'
    WHERE id = ?
");
$stmt->bind_param("i", $event_id);

if ($stmt->execute()) {

    // Optional email notification
    if ($creator_email) {
        $subject = "Event Cancellation Denied";
        $message = "Your cancellation request for the event \"$event_title\" has been denied.";
        $headers = "From: noreply@evenest.edu";

        @mail($creator_email, $subject, $message, $headers);
    }

    $_SESSION['flash'] = [
        "type" => "success",
        "message" => "Cancellation request denied successfully"
    ];

} else {

    $_SESSION['flash'] = [
        "type" => "error",
        "message" => "Failed to deny cancellation request"
    ];
}

header("Location: ../views/cancel_requests.php");
exit;
