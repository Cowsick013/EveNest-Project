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
   FETCH REGISTERED STUDENTS
================================ */

$stmt = $conn->prepare("
    SELECT
        u.name AS student_name,
        s.roll_no,
        s.stream,
        r.status,
        r.registered_at
    FROM event_registrations r
    JOIN users u ON u.id = r.student_id
    LEFT JOIN students s ON s.user_id = u.id
    WHERE r.event_id = ?
    ORDER BY u.name
");

if (!$stmt) {
    die("SQL ERROR (registered_students): " . $conn->error);
}

$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

$stmt->close();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Registered Students – <?= htmlspecialchars($event_title) ?></title>
    <style>
        body { font-family: Arial; background:#f4f6f8; padding:20px; }
        h2 { margin-bottom:5px; }
        table {
            width:100%;
            border-collapse:collapse;
            background:white;
            margin-top:20px;
        }
        th, td {
            padding:10px;
            border-bottom:1px solid #ddd;
            text-align:left;
        }
        th { background:#e3f2fd; }
        .active { color:green; font-weight:bold; }
        .withdraw_requested { color:orange; font-weight:bold; }
        .withdrawn { color:red; font-weight:bold; }
        .badge {
            padding:4px 8px;
            border-radius:4px;
            font-size:13px;
            display:inline-block;
        }
    </style>
</head>
<body>

<h2>👥 Registered Students</h2>
<p><b>Event:</b> <?= htmlspecialchars($event_title) ?> (<?= $event_date ?>)</p>

<table>
<tr>
    <th>Student Name</th>
    <th>Roll No</th>
    <th>Stream</th>
    <th>Status</th>
    <th>Registered At</th>
</tr>

<?php if (empty($rows)): ?>
<tr><td colspan="5">No students registered yet.</td></tr>
<?php else: ?>
<?php foreach ($rows as $r): ?>
<tr>
    <td><?= htmlspecialchars($r['student_name']) ?></td>
    <td><?= htmlspecialchars($r['roll_no'] ?? '-') ?></td>
    <td><?= htmlspecialchars($r['stream'] ?? '-') ?></td>
    <td class="<?= $r['status'] ?>">
        <?= ucfirst(str_replace('_', ' ', $r['status'])) ?>
    </td>
    <td><?= $r['registered_at'] ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>

<br>
<a href="view_event.php?id=<?= $event_id ?>">⬅ Back to Event</a>

</body>
</html>
