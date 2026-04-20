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
   EVENT INFO
================================ */
$evt = $conn->prepare("
    SELECT title, event_date
    FROM events
    WHERE id = ?
");
$evt->bind_param("i", $event_id);
$evt->execute();
$evt->bind_result($event_title, $event_date);
$evt->fetch();
$evt->close();

/* ===============================
   ATTENDANCE DATA (FIXED)
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
if (!$stmt) {
    die("Query failed: " . $conn->error);
}

$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

/* ===============================
   SUMMARY COUNTS
================================ */
$total = $result->num_rows;
$present = 0;
$rejected = 0;
$rows = [];

while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
    if ($row['status'] === 'present') $present++;
    if ($row['status'] === 'rejected') $rejected++;
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance – <?= htmlspecialchars($event_title) ?></title>
    <style>
        body { font-family: Arial; background:#f4f6f8; padding:20px; }
        .summary { display:flex; gap:15px; margin:20px 0; }
        .card {
            background:#fff; padding:15px; border-radius:8px;
            box-shadow:0 0 5px #ccc; min-width:150px; text-align:center;
        }
        table {
            width:100%; border-collapse:collapse; background:#fff;
        }
        th, td {
            padding:10px; border-bottom:1px solid #ddd;
        }
        th { background:#e3f2fd; }
        .present { color:green; font-weight:bold; }
        .rejected { color:red; font-weight:bold; }
        .btn {
            padding:10px 15px; background:#1976d2;
            color:#fff; text-decoration:none; border-radius:5px;
        }
    </style>
</head>
<body>

<h2>📋 Attendance</h2>
<p><b>Event:</b> <?= htmlspecialchars($event_title) ?> (<?= $event_date ?>)</p>

<div class="summary">
    <div class="card">
        <h3><?= $total ?></h3>
        <p>Total Marked</p>
    </div>
    <div class="card">
        <h3 style="color:green"><?= $present ?></h3>
        <p>Present</p>
    </div>
    <div class="card">
        <h3 style="color:red"><?= $rejected ?></h3>
        <p>Rejected</p>
    </div>
</div>

<table>
<tr>
    <th>Student</th>
    <th>Roll No</th>
    <th>Stream</th>
    <th>Status</th>
    <th>Distance (m)</th>
    <th>Marked At</th>
</tr>

<?php if (empty($rows)): ?>
<tr><td colspan="6">No attendance marked yet.</td></tr>
<?php else: ?>
<?php foreach ($rows as $r): ?>
<tr>
    <td><?= htmlspecialchars($r['student_name']) ?></td>
    <td><?= htmlspecialchars($r['roll_no'] ?? '-') ?></td>
    <td><?= htmlspecialchars($r['stream'] ?? '-') ?></td>
    <td class="<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></td>
    <td><?= round($r['distance_meters'], 2) ?></td>
    <td><?= $r['marked_at'] ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>

<br>

<a class="btn" href="../controllers/export_attendance.php?event_id=<?= $event_id ?>">
    ⬇ Export Attendance (CSV)
</a>

</body>
</html>
