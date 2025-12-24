<?php
session_start();
require_once "../db.php";

// Only admin & faculty can save events
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'faculty'])) {
    header("Location: ../views/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // BASIC FIELDS
    $title       = $_POST['event_title'];
    $desc        = $_POST['description'];
    $date        = $_POST['event_date'];
    $time_from   = $_POST['time_from'];
    $time_to     = $_POST['time_to'];
    $venue       = $_POST['venue'];
    $notes       = $_POST['notes'] ?? "";
    $created_by  = $_SESSION['user_id'];

    // NEW FIELDS
    // Organizers (checkbox)
    $organizers = isset($_POST['organizers']) 
                  ? implode(", ", $_POST['organizers']) 
                  : "";

    // Target Audience (checkbox)
    $audience = isset($_POST['audience'])
                ? implode(", ", $_POST['audience'])
                : "";

    // Speaker / Guest
    $speaker = $_POST['speaker'] ?? "";

    // Event Incharge / Coordinator
    $incharge = $_POST['incharge'] ?? "";

    // Junior Coordinator (optional)
    $jr_coordinator = $_POST['jr_coordinator'] ?? "";

    // Prepare SQL
    $stmt = $conn->prepare("
        INSERT INTO events 
        (title, description, event_date, time_from, time_to, venue, organized_by, notes, created_by, 
         speaker, incharge, jr_coordinator, audience, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
    ");

    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    // BIND PARAMETERS
    // s = string, i = integer
    $stmt->bind_param(
        "ssssssssissss",
        $title,
        $desc,
        $date,
        $time_from,
        $time_to,
        $venue,
        $organizers,
        $notes,
        $created_by,      // INT
        $speaker,
        $incharge,
        $jr_coordinator,
        $audience
    );

    // EXECUTE
    if ($stmt->execute()) {
        $event_id = $stmt->insert_id;
        header("Location: ../views/event_created.php?id=" . $event_id);
        exit();
    } else {
        echo "Error saving event: " . $stmt->error;
    }
}
?>
