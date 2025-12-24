<?php
session_start();
require_once "../db.php";

if ($_SESSION['role'] !== "faculty") {
    echo "Unauthorized";
    exit;
}

$event_id = $_POST['event_id'];

// 1. Upload Photos
if (!empty($_FILES['photos']['name'][0])) {
    foreach ($_FILES['photos']['tmp_name'] as $key => $tmp) {
        $name = time() . "_" . basename($_FILES['photos']['name'][$key]);
        move_uploaded_file($tmp, "../uploads/photos/" . $name);
    }
}

// 2. Upload Report (PDF)
if (!empty($_FILES['report']['name'])) {
    $report_name = time() . "_" . $_FILES['report']['name'];
    move_uploaded_file($_FILES['report']['tmp_name'], "../uploads/reports/" . $report_name);
}

// 3. Upload Attendance (CSV)
if (!empty($_FILES['attendance']['name'])) {
    $attendance_name = time() . "_" . $_FILES['attendance']['name'];
    move_uploaded_file($_FILES['attendance']['tmp_name'], "../uploads/attendance/" . $attendance_name);
}

header("Location: ../views/view_event.php?id=$event_id&uploaded=1");
exit;
?>
