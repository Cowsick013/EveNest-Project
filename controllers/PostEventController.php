<?php
session_start();
require_once "../db.php";
require_once "../includes/flash.php";

/* ===============================
   AUTH CHECK
================================ */
if (!isset($_SESSION['user_id'])) {
    flash("error", "Unauthorized access.");
    header("Location: ../views/login.php");
    exit;
}

/* ===============================
   VALIDATE INPUT
================================ */
if (!isset($_POST['event_id'])) {
    flash("error", "Invalid event submission.");
    header("Location: ../views/summary_list.php");
    exit;
}

$event_id            = intval($_POST['event_id']);
$summary             = trim($_POST['summary'] ?? '');
$dignitaries         = trim($_POST['dignitaries'] ?? '');
$dignitaries_words   = trim($_POST['dignitaries_words'] ?? '');
$source              = ($_POST['source'] ?? 'manual') === 'ai' ? 'ai' : 'manual';

/* ===============================
   INSERT POST-EVENT REPORT
================================ */
$stmt = $conn->prepare("
    INSERT INTO post_event_reports
    (event_id, summary, dignitaries, dignitaries_words, report_source, created_at)
    VALUES (?, ?, ?, ?, ?, NOW())
");

if (!$stmt) {
    die("SQL ERROR (post_event_reports): " . $conn->error);
}

$stmt->bind_param(
    "issss",
    $event_id,
    $summary,
    $dignitaries,
    $dignitaries_words,
    $source
);

$stmt->execute();
$report_id = $stmt->insert_id;
$stmt->close();

/* ===============================
   OPTIONAL PDF UPLOAD
================================ */
if (isset($_FILES['report_file']) && $_FILES['report_file']['error'] === 0) {

    $year  = date("Y");
    $month = date("m");
    $day   = date("d");

    $dir = "../uploads/reports/$year/$month/$day/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $filename   = "report_" . time() . ".pdf";
    $full_path  = $dir . $filename;
    $db_path    = "uploads/reports/$year/$month/$day/$filename";

    move_uploaded_file($_FILES['report_file']['tmp_name'], $full_path);

    $stmt2 = $conn->prepare("
        UPDATE post_event_reports
        SET report_file = ?
        WHERE id = ?
    ");

    if (!$stmt2) {
        die("SQL ERROR (update report_file): " . $conn->error);
    }

    $stmt2->bind_param("si", $db_path, $report_id);
    $stmt2->execute();
    $stmt2->close();
}

/* ===============================
   MULTIPLE PHOTO UPLOAD
================================ */
$photo_dir = "../uploads/post_event/$event_id/";
if (!is_dir($photo_dir)) mkdir($photo_dir, 0777, true);

if (!empty($_FILES['photos']['name'][0])) {

    foreach ($_FILES['photos']['tmp_name'] as $i => $tmpName) {

        if (!is_uploaded_file($tmpName)) continue;

        $filename   = time() . "_" . basename($_FILES['photos']['name'][$i]);
        $full_path  = $photo_dir . $filename;
        $db_path    = "uploads/post_event/$event_id/$filename";

        move_uploaded_file($tmpName, $full_path);

        $stmt3 = $conn->prepare("
            INSERT INTO post_event_photos
            (report_id, photo_path, uploaded_at)
            VALUES (?, ?, NOW())
        ");

        if (!$stmt3) {
            die("SQL ERROR (post_event_photos): " . $conn->error);
        }

        $stmt3->bind_param("is", $report_id, $db_path);
        $stmt3->execute();
        $stmt3->close();
    }
}

/* ===============================
   REDIRECT
================================ */
flash("success", "Summary added successfully!");
header("Location: ../views/event_report_print.php?id=" . $event_id);
exit;
