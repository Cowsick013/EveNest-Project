<?php
session_start();
require_once "../db.php";

/* ===============================
   AUTH CHECK
================================ */
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['faculty', 'admin'])) {
    die("Unauthorized");
}

if (!isset($_GET['event_id'])) {
    die("Invalid event");
}

$event_id = intval($_GET['event_id']);
$user_id  = $_SESSION['user_id'];
$role     = $_SESSION['role'];

/* ===============================
   OWNERSHIP CHECK (FACULTY)
================================ */
if ($role === 'faculty') {
    $chk = $conn->prepare("
        SELECT id FROM events
        WHERE id = ? AND created_by = ?
    ");
    $chk->bind_param("ii", $event_id, $user_id);
    $chk->execute();
    $chk->store_result();

    if ($chk->num_rows === 0) {
        die("Unauthorized access");
    }
    $chk->close();
}

/* ===============================
   EVENT TITLE (FOR FILE NAME)
================================ */
$evt = $conn->prepare("
    SELECT title
    FROM events
    WHERE id = ?
");
$evt->bind_param("i", $event_id);
$evt->execute();
$evt->bind_result($event_title);
$evt->fetch();
$evt->close();

$filename = "attendance_" . preg_replace("/[^a-zA-Z0-9]/", "_", $event_title) . ".csv";

/* ===============================
   CSV HEADERS
================================ */
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

$output = fopen("php://output", "w");

/* ===============================
   CSV COLUMN HEADINGS
================================ */
fputcsv($output, [
    "Student Name",
    "Roll No",
    "Stream",
    "Status",
    "Distance (meters)",
    "Marked At"
]);

/* ===============================
   FETCH ATTENDANCE DATA
================================ */
$sql = "
SELECT 
    u.name AS student_name,
    r.roll_no,
    r.stream,
    a.status,
    a.distance_meters,
    a.marked_at
FROM event_attendance a
JOIN users u ON u.id = a.student_id
LEFT JOIN event_registrations r
    ON r.student_id = a.student_id AND r.event_id = a.event_id
WHERE a.event_id = ?
ORDER BY u.name
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

/* ===============================
   WRITE CSV ROWS
================================ */
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['student_name'],
        $row['roll_no'] ?? '',
        $row['stream'] ?? '',
        ucfirst($row['status']),
        round($row['distance_meters'], 2),
        $row['marked_at']
    ]);
}

$stmt->close();
fclose($output);
exit;
