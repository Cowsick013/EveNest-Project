<?php
require_once "../db.php";
require_once "includes/faculty_auth.php";

$event_id = $_GET['event_id'] ?? null;

if(!$event_id){
    die("Event ID missing");
}

// Fetch event
$event = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM events WHERE id='$event_id'
"));

// Fetch attendance
$result = mysqli_query($conn, "
    SELECT u.name, u.email, u.roll_no
    FROM event_attendance ea
    JOIN users u ON ea.student_id = u.id
    WHERE ea.event_id = '$event_id'
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<h2>Attendance - <?= $event['title'] ?></h2>

<a href="export_attendance.php?event_id=<?= $event_id ?>">
    ⬇ Export CSV
</a>

<table border="1" cellpadding="10">
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Roll No</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)): ?>
<tr>
    <td><?= $row['name'] ?></td>
    <td><?= $row['email'] ?></td>
    <td><?= $row['roll_no'] ?></td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>