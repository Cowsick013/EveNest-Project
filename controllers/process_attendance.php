<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../db.php";

header("Content-Type: application/json");
date_default_timezone_set("Asia/Kolkata");

/* ===============================
   1️⃣ AUTH CHECK
================================ */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access"
    ]);
    exit;
}

$student_id = $_SESSION['user_id']; // users.id

/* ===============================
   2️⃣ READ JSON INPUT
================================ */
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request format"
    ]);
    exit;
}

$event_id = intval($input['event_id'] ?? 0);
$lat1     = floatval($input['latitude'] ?? 0);
$lon1     = floatval($input['longitude'] ?? 0);

if (!$event_id || !$lat1 || !$lon1) {
    echo json_encode([
        "success" => false,
        "message" => "Missing attendance data"
    ]);
    exit;
}

/* ===============================
   3️⃣ ATTENDANCE TIME WINDOW
================================ */
$timeStmt = $conn->prepare("
    SELECT event_date, time_to
    FROM events
    WHERE id = ?
");
if (!$timeStmt) {
    echo json_encode([
        "success" => false,
        "message" => "SQL error: " . $conn->error
    ]);
    exit;
}

$timeStmt->bind_param("i", $event_id);
$timeStmt->execute();
$eventTime = $timeStmt->get_result()->fetch_assoc();

if (!$eventTime) {
    echo json_encode([
        "success" => false,
        "message" => "Event not found"
    ]);
    exit;
}

$eventEnd = strtotime($eventTime['event_date'] . ' ' . $eventTime['time_to']);
$attendanceClose = $eventEnd + (15 * 60);

if (time() > $attendanceClose) {
    echo json_encode([
        "success" => false,
        "message" => "Attendance window has closed"
    ]);
    exit;
}

/* ===============================
   4️⃣ DUPLICATE ATTENDANCE CHECK
================================ */
$chk = $conn->prepare("
    SELECT id
    FROM event_attendance
    WHERE event_id = ? AND student_id = ?
");
if (!$chk) {
    echo json_encode([
        "success" => false,
        "message" => "SQL error: " . $conn->error
    ]);
    exit;
}

$chk->bind_param("ii", $event_id, $student_id);
$chk->execute();

if ($chk->get_result()->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Attendance already marked"
    ]);
    exit;
}

/* ===============================
   5️⃣ FETCH EVENT LOCATION
================================ */
$loc = $conn->prepare("
    SELECT latitude, longitude, radius
    FROM event_locations
    WHERE event_id = ?
");
if (!$loc) {
    echo json_encode([
        "success" => false,
        "message" => "SQL error: " . $conn->error
    ]);
    exit;
}

$loc->bind_param("i", $event_id);
$loc->execute();
$locData = $loc->get_result()->fetch_assoc();

if (!$locData) {
    echo json_encode([
        "success" => false,
        "message" => "Event location not configured"
    ]);
    exit;
}

/* ===============================
   6️⃣ DISTANCE CALCULATION
================================ */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earth = 6371000; // meters
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) ** 2 +
         cos(deg2rad($lat1)) *
         cos(deg2rad($lat2)) *
         sin($dLon / 2) ** 2;

    return 2 * $earth * asin(sqrt($a));
}

$distance = calculateDistance(
    $lat1,
    $lon1,
    $locData['latitude'],
    $locData['longitude']
);

/* ===============================
   7️⃣ HITBOX CHECK (WITH BUFFER)
================================ */
$buffer = 50; // meters GPS tolerance
$allowedRadius = $locData['radius'] + $buffer;

if ($distance > $allowedRadius) {
    echo json_encode([
        "success" => false,
        "message" => "You are outside the event location"
    ]);
    exit;
}

/* ===============================
   8️⃣ MARK ATTENDANCE
================================ */
$ins = $conn->prepare("
    INSERT INTO event_attendance
    (event_id, student_id, latitude, longitude, distance_meters, status)
    VALUES (?, ?, ?, ?, ?, 'present')
");
if (!$ins) {
    echo json_encode([
        "success" => false,
        "message" => "SQL error: " . $conn->error
    ]);
    exit;
}

$ins->bind_param(
    "iiddi",
    $event_id,
    $student_id,
    $lat1,
    $lon1,
    $distance
);
$ins->execute();

echo json_encode([
    "success" => true,
    "message" => "Attendance marked successfully!"
]);
exit;