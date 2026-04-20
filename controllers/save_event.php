<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['role']) || !in_array(strtolower($_SESSION['role']), ['admin','faculty'])) {
    header("Location: ../views/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Invalid request");
}

/* =========================
   BASIC EVENT DATA
========================= */
$title   = $_POST['event_title'];
$desc    = $_POST['description'];
$date    = $_POST['event_date'];
$from    = $_POST['time_from'];
$to      = $_POST['time_to'];
$venue   = $_POST['venue'];
$notes   = $_POST['notes'] ?? '';
$created_by = $_SESSION['user_id'];

/* =========================
   ORGANIZERS (REQUIRED)
========================= */
$organizers = $_POST['organizers'] ?? [];
$organizer_other = trim($_POST['organizer_other'] ?? '');
if ($organizer_other !== '') {
    $organizers[] = $organizer_other;
}
$organized_by = implode(", ", $organizers);

/* =========================
   EXTRA FIELDS
========================= */
$speaker        = $_POST['speaker'] ?? null;
$incharge       = $_POST['incharge'];
$jr_coordinator = $_POST['jr_coordinator'] ?? null;
$audience       = implode(", ", $_POST['audience'] ?? []);

/* =========================
   PARTICIPATION
========================= */
$is_participatable = isset($_POST['is_participatable']) ? 1 : 0;

/* =========================
   LOCATION
========================= */
$lat    = $_POST['latitude'];
$lng    = $_POST['longitude'];
$radius = $_POST['radius'];

$conn->begin_transaction();

try {

    /* =========================
       INSERT EVENT
    ========================= */
    $stmt = $conn->prepare("
        INSERT INTO events
        (
            title, description, event_date, time_from, time_to,
            venue, organized_by, notes, created_by,
            speaker, incharge, jr_coordinator, audience,
            is_participatable, status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");

    if (!$stmt) {
        throw new Exception("EVENT SQL ERROR: " . $conn->error);
    }

    // 14 placeholders → 14 types
    $stmt->bind_param(
        "ssssssssissssi",
        $title,
        $desc,
        $date,
        $from,
        $to,
        $venue,
        $organized_by,
        $notes,
        $created_by,
        $speaker,
        $incharge,
        $jr_coordinator,
        $audience,
        $is_participatable
    );

    $stmt->execute();
    $event_id = $stmt->insert_id;

    /* =========================
       INSERT EVENT LOCATION
    ========================= */
    $loc = $conn->prepare("
        INSERT INTO event_locations
        (event_id, latitude, longitude, radius_meters)
        VALUES (?, ?, ?, ?)
    ");

    if (!$loc) {
        throw new Exception("LOCATION SQL ERROR: " . $conn->error);
    }

    $loc->bind_param(
        "iddi",
        $event_id,
        $lat,
        $lng,
        $radius
    );

    $loc->execute();

    $conn->commit();

    header("Location: ../views/event_created.php?id=" . $event_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    die($e->getMessage());
}
