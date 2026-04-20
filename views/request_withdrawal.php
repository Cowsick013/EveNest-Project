<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Unauthorized");
}

$student_id = $_SESSION['user_id'];
$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    die("Invalid request");
}

$sql = "
SELECT e.title, r.status
FROM event_registrations r
JOIN events e ON e.id = r.event_id
WHERE r.student_id = ? AND r.event_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $student_id, $event_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data || $data['status'] !== 'active') {
    die("Withdrawal not allowed");
}
?>

<h2>Withdraw from Event</h2>

<p>Are you sure you want to withdraw from:</p>
<p><strong><?= htmlspecialchars($data['title']) ?></strong></p>

<form method="POST" action="../controllers/request_withdrawal.php">
    <input type="hidden" name="event_id" value="<?= $event_id ?>">

    <label>Reason for withdrawal (required)</label><br>
    <textarea name="withdrawal_reason" required style="width:100%;height:80px;"></textarea><br><br>

    <button type="submit">Submit Withdrawal Request</button>
    <a href="my_registrations.php">Cancel</a>
</form>
