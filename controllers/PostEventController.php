<?php
session_start();
require_once "../db.php";
require_once "../includes/flash.php";

// Validate login
if (!isset($_SESSION['user_id'])) {
    flash("error", "Unauthorized access.");
    header("Location: ../views/login.php");
    exit;
}

// Validate event ID
if (!isset($_POST['event_id'])) {
    flash("error", "Invalid event submission.");
    header("Location: ../views/summary_list.php");
    exit;
}

$event_id = intval($_POST['event_id']);
$summary = trim($_POST['summary']);
$dignitaries = trim($_POST['dignitaries']);
$dignitaries_words = trim($_POST['dignitaries_words']);
$user_id = $_SESSION['user_id'];


// Insert record
$stmt = $conn->prepare("
    INSERT INTO post_event_reports 
    (event_id, summary, dignitaries, dignitaries_words, report_file, created_by, created_at)
    VALUES (?, ?, ?, ?, '', ?, NOW())
");
$stmt->bind_param("isssi", $event_id, $summary, $dignitaries, $dignitaries_words, $user_id);
$stmt->execute();

$report_id = $stmt->insert_id;
$stmt->close();


// Upload PDF (optional)
$report_path_db = "";

if (isset($_FILES['report_file']) && $_FILES['report_file']['error'] === 0) {

    $year = date("Y");
    $month = date("m");
    $day = date("d");

    $dir = "../uploads/reports/$year/$month/$day/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $filename = "report_" . time() . ".pdf";
    $full_path = $dir . $filename;

    move_uploaded_file($_FILES['report_file']['tmp_name'], $full_path);

    $report_path_db = "uploads/reports/$year/$month/$day/$filename";

    $stmt2 = $conn->prepare("
        UPDATE post_event_reports SET report_file = ? WHERE id = ?
    ");
    $stmt2->bind_param("si", $report_path_db, $report_id);
    $stmt2->execute();
    $stmt2->close();
}


// Upload multiple photos
$photo_dir = "../uploads/post_event/$event_id/";
if (!is_dir($photo_dir)) mkdir($photo_dir, 0777, true);

if (!empty($_FILES['photos']['name'][0])) {

    foreach ($_FILES['photos']['tmp_name'] as $i => $tmpName) {

        if (!is_uploaded_file($tmpName)) continue;

        $filename = time() . "_" . basename($_FILES['photos']['name'][$i]);
        $full_path = $photo_dir . $filename;

        move_uploaded_file($tmpName, $full_path);

        $photo_db_path = "uploads/post_event/$event_id/$filename";

        $stmt3 = $conn->prepare("
            INSERT INTO post_event_photos (report_id, photo_path, uploaded_at)
            VALUES (?, ?, NOW())
        ");
        $stmt3->bind_param("is", $report_id, $photo_db_path);
        $stmt3->execute();
        $stmt3->close();
    }
}


// Redirect after success
flash("success", "Summary added successfully!");
header("Location: ../views/view_event_summary.php?event_id=" . $event_id);
exit;
