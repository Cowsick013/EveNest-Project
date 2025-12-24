<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit;
}

$event_id = $_GET['id'];
$user_role = $_SESSION['role'];
$user_id   = $_SESSION['user_id'];

// Fetch event
$stmt = $conn->prepare("SELECT created_by, cancel_request FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo "Event not found.";
    exit;
}

// Rule 1: Event MUST be admin-approved for cancellation
if ($event['cancel_request'] !== "approved") {
    echo "This event cannot be deleted because cancellation is not approved.";
    exit;
}

// Rule 2: Faculty can only delete their own event
if ($user_role === "faculty" && $event['created_by'] != $user_id) {
    echo "Not allowed.";
    exit;
}

// Rule 3: Admin can delete ONLY after approval (already checked)
$stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);

if ($stmt->execute()) {
    header("Location: ../views/event_list.php?deleted=1");
    exit();
} else {
    echo "Error deleting event.";
}
?>
