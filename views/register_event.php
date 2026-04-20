<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    exit("Invalid event");
}

$event_id = intval($_GET['id']);

// Fetch event
$eventStmt = $conn->prepare("
    SELECT title, is_participatable 
    FROM events 
    WHERE id=? AND status='active'
");
$eventStmt->bind_param("i", $event_id);
$eventStmt->execute();
$event = $eventStmt->get_result()->fetch_assoc();

if (!$event || !$event['is_participatable']) {
    exit("Registration not allowed for this event.");
}

// Fetch student info
// Fetch student info
$userStmt = $conn->prepare("
    SELECT name, email 
    FROM users 
    WHERE id=?
");

if (!$userStmt) {
    die("USER SQL ERROR: " . $conn->error);
}

$userStmt->bind_param("i", $student_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Registration</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; padding:30px; }
        .box {
            background:#fff;
            max-width:600px;
            margin:auto;
            padding:25px;
            border-radius:10px;
            box-shadow:0 0 8px #aaa;
        }
        input, select {
            width:100%;
            padding:8px;
            margin-top:6px;
            margin-bottom:12px;
        }
        button {
            padding:10px 18px;
            background:#2e7d32;
            color:#fff;
            border:none;
            border-radius:6px;
            cursor:pointer;
            font-size:16px;
        }
    </style>
</head>
<body>

<div class="box">
    <h2><?= htmlspecialchars($event['title']) ?></h2>

    <form action="../controllers/submit_registration.php" method="POST">
        <input type="hidden" name="event_id" value="<?= $event_id ?>">

        <label>Full Name</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($user['name']) ?>" readonly>

        <label>Roll No / Reg No</label>
        <input type="text" name="roll_no" required>

        <label>Stream</label>
        <select name="stream" required>
            <option value="">Select</option>
            <option>BCA</option>
            <option>BCOM</option>
            <option>BBA</option>
        </select>

        <label>Year</label>
        <input type="number" name="year" min="1" max="4" required>

        <label>Age</label>
        <input type="number" name="age" min="16" max="30">

        <label>Contact Number</label>
        <input type="text" name="contact_no" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>

        <label>Team Name (optional)</label>
        <input type="text" name="team_name">

        <button type="submit">Confirm Registration</button>
    </form>
</div>

</body>
</html>
