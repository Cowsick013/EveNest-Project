<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "faculty") {
    echo "Unauthorized access.";
    exit;
}

if (!isset($_POST['event_id'])) {
    echo "Invalid event.";
    exit;
}

$event_id = $_POST['event_id'];

// Create folder if not exists
$uploadDir = "../uploads/post_event/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ----------------------------
// 1. HANDLE MULTIPLE IMAGES
// ----------------------------
$photoFiles = [];

if (!empty($_FILES["photos"]["name"][0])) {

    for ($i = 0; $i < count($_FILES["photos"]["name"]); $i++) {

        $fileTmp  = $_FILES["photos"]["tmp_name"][$i];
        $fileName = time() . "_" . rand(1000,9999) . "_" . basename($_FILES["photos"]["name"][$i]);

        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmp, $targetFile)) {
            $photoFiles[] = $fileName;   // store name only
        }
    }
}

// Convert to JSON for DB
$photoJson = json_encode($photoFiles);

// ----------------------------
// 2. HANDLE PDF REPORT
// ----------------------------
$reportFile = null;

if (!empty($_FILES["report"]["name"])) {
    $fileName = time() . "_report_" . basename($_FILES["report"]["name"]);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES["report"]["tmp_name"], $targetFile)) {
        $reportFile = $fileName;
    }
}

// ----------------------------
// 3. HANDLE ATTENDANCE CSV
// ----------------------------
$attendanceFile = null;

if (!empty($_FILES["attendance"]["name"])) {
    $fileName = time() . "_attendance_" . basename($_FILES["attendance"]["name"]);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES["attendance"]["tmp_name"], $targetFile)) {
        $attendanceFile = $fileName;
    }
}

// ----------------------------
// 4. INSERT INTO DATABASE
// ----------------------------
$stmt = $conn->prepare("
    INSERT INTO post_event_reports 
    (event_id, photos, report_file, attendance_file, created_at)
    VALUES (?, ?, ?, ?, NOW())
");

$stmt->bind_param("isss", $event_id, $photoJson, $reportFile, $attendanceFile);

if ($stmt->execute()) {
    header("Location: ../views/view_event_summary.php?event_id=$event_id&upload_success=1");
    exit;
} else {
    echo "Database insert failed.";
}
?>
