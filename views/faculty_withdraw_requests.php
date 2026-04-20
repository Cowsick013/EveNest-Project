<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    die("Unauthorized");
}



$faculty_id = $_SESSION['user_id'];

$sql = "
SELECT 
    r.student_id,
    r.event_id,
    r.full_name,
    r.roll_no,
    r.stream,
    r.withdrawal_reason,
    e.title
FROM event_registrations r
JOIN events e ON e.id = r.event_id
WHERE r.status = 'withdraw_requested'
  AND (
        e.created_by = ?
        OR e.incharge = ?
      )
ORDER BY e.event_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $faculty_id, $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<h2>📄 Withdrawal Requests</h2>

<?php if ($result->num_rows === 0): ?>
    <p>No withdrawal requests.</p>
<?php else: ?>
<table border="1" cellpadding="10">
<tr>
    <th>Event</th>
    <th>Student</th>
    <th>Roll No</th>
    <th>Stream</th>
    <th>Reason</th>
    <th>Faculty Action</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['title']) ?></td>
    <td><?= htmlspecialchars($row['full_name']) ?></td>
    <td><?= htmlspecialchars($row['roll_no']) ?></td>
    <td><?= htmlspecialchars($row['stream']) ?></td>

    <td style="max-width:250px;">
        <?= nl2br(htmlspecialchars($row['withdrawal_reason'])) ?>
    </td>

    <td>
        <!-- APPROVE -->
        <form method="POST" action="../controllers/approve_withdrawal.php" style="margin-bottom:6px;">
            <input type="hidden" name="event_id" value="<?= $row['event_id'] ?>">
            <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>">

            <input type="text" name="response"
                   placeholder="Approval remark (optional)"
                   style="width:200px;">

            <br><br>
            <button type="submit">✅ Approve</button>
        </form>

        <!-- DENY -->
        <form method="POST" action="../controllers/deny_withdrawal.php">
            <input type="hidden" name="event_id" value="<?= $row['event_id'] ?>">
            <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>">

            <input type="text" name="response"
                   placeholder="Reason for denial"
                   style="width:200px;" required>

            <br><br>
            <button type="submit">❌ Deny</button>
        </form>
    </td>
</tr>

<?php endwhile; ?>
</table>
<?php endif; ?>
