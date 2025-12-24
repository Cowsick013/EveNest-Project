<?php
session_start();
require_once "../db.php";

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash'] = ["type" => "error", "message" => "Unauthorized access"];
    header("Location: ../views/event_list.php");
    exit;
}

// Must have a valid event ID
if (!isset($_GET['id'])) {
    $_SESSION['flash'] = ["type" => "error", "message" => "Invalid event selected"];
    header("Location: ../views/event_list.php");
    exit;
}

$event_id  = $_GET['id'];
$user_name = $_SESSION['user_name'];

// Fetch event
$eventQuery = $conn->prepare("SELECT title, cancel_request FROM events WHERE id=?");
$eventQuery->bind_param("i", $event_id);
$eventQuery->execute();
$event = $eventQuery->get_result()->fetch_assoc();

if (!$event) {
    $_SESSION['flash'] = ["type" => "error", "message" => "Event not found"];
    header("Location: ../views/event_list.php");
    exit;
}

// Prevent duplicate cancellation requests
if ($event['cancel_request'] === "requested") {
    $_SESSION['flash'] = ["type" => "error", "message" => "Cancellation already requested"];
    header("Location: ../views/view_event.php?id=$event_id");
    exit;
}

// Update event status
$stmt = $conn->prepare("UPDATE events SET cancel_request='requested' WHERE id=?");
$stmt->bind_param("i", $event_id);

if ($stmt->execute()) {

    // Email notification to admin
    $admin_email = "admin@example.com"; // change to real admin email
    $event_title = $event['title'];

    $subject = "New Event Cancellation Request";
    $message = "A cancellation request was submitted by $user_name for the event: $event_title.";
    $headers = "From: noreply@yourdomain.com";

    // Send email (optional)
    @mail($admin_email, $subject, $message, $headers);

    $_SESSION['flash'] = [
        "type" => "success",
        "message" => "Cancellation request submitted successfully!"
    ];
} else {
    $_SESSION['flash'] = [
        "type" => "error",
        "message" => "Failed to submit cancellation request"
    ];
}

// Redirect back to event page
header("Location: ../views/view_event.php?id=$event_id");
exit;
?>
