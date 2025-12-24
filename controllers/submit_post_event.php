<?php
require_once "../includes/flash.php";
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    flash("error", "Unauthorized access.");
    header("Location: ../views/login.php");
    exit;
}

/* ------------------------------------------
   VALIDATE EVENT ID
-------------------------------------------*/
if (!isset($_POST['event_id']) || empty($_POST['event_id'])) {
    flash("error", "Invalid event submission.");
    header("Location: ../views/summary_list.php");
    exit;
}

$event_id = intval($_POST['event_id']);
$summary = $_POST['summary'];
$dignitaries = $_POST['dignitaries'];
$dignitaries_words = $_POST['dignitaries_words'];
$user_id = $_SESSION['user_id'];

/* ------------------------------------------
   1) INSERT SUMMARY FIRST
-------------------------------------------*/
$stmt = $conn->prepare("
    INSERT INTO post_event_reports 
    (event_id, summary, dignitaries, dignitaries_words, report_file, created_by, created_at)
    VALUES (?, ?, ?, ?, '', ?, NOW())
");

if (!$stmt) {
    flash("error", "Database error: " . $conn->error);
    header("Location: ../views/summary_list.php");
    exit;
}

$stmt->bind_param("isssi", $event_id, $summary, $dignitaries, $dignitaries_words, $user_id);
$stmt->execute();
$report_id = $stmt->insert_id;
$stmt->close();

/* ------------------------------------------
   2) PDF UPLOAD (YEAR/MONTH/DAY STRUCTURE)
-------------------------------------------*/
$report_path_db = "";

if (isset($_FILES['report_file']) && $_FILES['report_file']['error'] === 0) {

    $year = date("Y");
    $month = date("m");
    $day = date("d");

    $base_dir = "../uploads/reports/$year/$month/$day/";

    if (!is_dir($base_dir)) {
        mkdir($base_dir, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['report_file']['name'], PATHINFO_EXTENSION));
    if ($ext !== "pdf") {
        flash("error", "Only PDF files are allowed.");
        header("Location: ../views/submit_post_event.php?event_id=" . $event_id);
        exit;
    }

    $filename = "report_" . time() . ".pdf";
    $full_path = $base_dir . $filename;

    if (move_uploaded_file($_FILES['report_file']['tmp_name'], $full_path)) {

        $report_path_db = "uploads/reports/$year/$month/$day/$filename";

        $stmt2 = $conn->prepare("
            UPDATE post_event_reports 
            SET report_file = ?
            WHERE id = ?
        ");

        $stmt2->bind_param("si", $report_path_db, $report_id);
        $stmt2->execute();
        $stmt2->close();
    }
}

/* ------------------------------------------
   3) PHOTO UPLOADS WITH CORRECT PATH
-------------------------------------------*/
$photo_dir = "../uploads/post_event/$event_id/";

if (!is_dir($photo_dir)) {
    mkdir($photo_dir, 0777, true);
}

if (!empty($_FILES['photos']['name'][0])) {

    foreach ($_FILES['photos']['tmp_name'] as $key => $tmp) {

        if (!is_uploaded_file($tmp)) continue;

        $filename = time() . "_" . basename($_FILES['photos']['name'][$key]);
        $destination = $photo_dir . $filename;

        if (move_uploaded_file($tmp, $destination)) {

            // FIX: Correct photo path
            $photo_path_db = "uploads/post_event/$event_id/$filename";

            $stmt3 = $conn->prepare("
                INSERT INTO post_event_photos (report_id, photo_path, uploaded_at)
                VALUES (?, ?, NOW())
            ");

            $stmt3->bind_param("is", $report_id, $photo_path_db);
            $stmt3->execute();
            $stmt3->close();
        }
    }
}

/* ------------------------------------------
   SUCCESS
-------------------------------------------*/
flash("success", "Post-event summary submitted successfully!");
// redirect straight to view summary so you see the result immediately
header("Location: ../views/view_event_summary.php?event_id=" . $event_id);
exit;

?>
