<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$event_id = intval($data['event_id']);
$lat1 = $data['latitude'];
$lon1 = $data['longitude'];
$student_id = $_SESSION['user_id'];

/* Check event location */
$stmt = $conn->prepare("
    SELECT latitude, longitude, radius_meters
    FROM event_locations
    WHERE event_id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$loc = $stmt->get_result()->fetch_assoc();

if (!$loc) {
    echo json_encode(["message" => "Event location not set."]);
    exit;
}

/* Haversine formula */
function distance($lat1, $lon1, $lat2, $lon2) {
    $earth = 6371000;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2)**2 +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2)**2;
    return $earth * (2 * atan2(sqrt($a), sqrt(1 - $a)));
}

$dist = distance($lat1, $lon1, $loc['latitude'], $loc['longitude']);

/* Check radius */
if ($dist > $loc['radius_meters']) {
    echo json_encode([
        "message" => "Outside event area. Distance: " . round($dist,2) . " m"
    ]);
    exit;
}

/* Insert attendance */
$stmt = $conn->prepare("
    INSERT INTO event_attendance
    (event_id, student_id, latitude, longitude, distance_meters)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param("iiddi", $event_id, $student_id, $lat1, $lon1, $dist);

if ($stmt->execute()) {
    echo json_encode(["message" => "Attendance marked successfully ✅"]);
} else {
    echo json_encode(["message" => "Attendance already marked ❌"]);
}
