<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_role = $_SESSION['role'];

if ($user_role === "admin") {
    $sql = "SELECT * FROM events ORDER BY event_date DESC";
    $stmt = $conn->prepare($sql);

} elseif ($user_role === "faculty") {
    // Faculty can see ALL events
    $sql = "SELECT * FROM events ORDER BY event_date DESC";
    $stmt = $conn->prepare($sql);

} elseif ($user_role === "student") {
    // Students only see ACTIVE events
    $sql = "SELECT * FROM events WHERE status = 'active' ORDER BY event_date DESC";
    $stmt = $conn->prepare($sql);

} else {
    echo json_encode([]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode($events);
?>
