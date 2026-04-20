<?php
require_once "../db.php";

$event_id = $_GET['event_id'] ?? null;

if(!$event_id){
    die("Event ID missing");
}

// Set headers for download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance.csv"');

$output = fopen("php://output", "w");

// Column headings
fputcsv($output, ['Name', 'Email', 'Roll No']);

// Fetch data
$result = mysqli_query($conn, "
    SELECT u.name, u.email, u.roll_no
    FROM event_attendance ea
    JOIN users u ON ea.student_id = u.id
    WHERE ea.event_id = '$event_id'
");

while($row = mysqli_fetch_assoc($result)){
    fputcsv($output, $row);
}

fclose($output);
exit;