<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "Invalid event";
    exit;
}

$event_id = intval($_GET['id']);
$student_id = $_SESSION['user_id'];

// FETCH EVENT
$e = $conn->prepare("SELECT * FROM events WHERE id=?");
$e->bind_param("i", $event_id);
$e->execute();
$event = $e->get_result()->fetch_assoc();

if (!$event) {
    echo "Event not found";
    exit;
}

// PARTICIPATION DETAILS
$part = null;
if ($event['is_participatable']) {
    $p = $conn->prepare("SELECT * FROM event_participation_details WHERE event_id=?");
    $p->bind_param("i", $event_id);
    $p->execute();
    $part = $p->get_result()->fetch_assoc();
}

// CHECK IF STUDENT REGISTERED
$reg = $conn->prepare("
    SELECT id, status
    FROM event_registrations
    WHERE event_id=? AND student_id=?
");
$reg->bind_param("ii", $event_id, $student_id);
$reg->execute();
$regResult = $reg->get_result();

$is_registered = false;
$reg_status = null;

if ($row = $regResult->fetch_assoc()) {
    $is_registered = true;
    $reg_status = $row['status'];
}


// DEADLINE CHECK
$deadline_passed = false;
if ($part && !empty($part['registration_deadline'])) {
    $deadline_passed = date("Y-m-d") > $part['registration_deadline'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Details</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; padding:20px; }
        .box {
            background:#fff;
            padding:20px;
            max-width:650px;
            margin:auto;
            border-radius:10px;
            box-shadow:0 0 8px #aaa;
        }
        .btn {
            padding:10px 15px;
            background:#1976d2;
            color:white;
            text-decoration:none;
            border-radius:5px;
            display:inline-block;
            margin-top:10px;
        }
        .disabled {
            background:#aaa;
            cursor:not-allowed;
        }
    </style>
</head>
<body>

<div class="box">
    <h2><?= htmlspecialchars($event['title']) ?></h2>

    <p><b>Date:</b> <?= $event['event_date'] ?></p>
    <p><b>Time:</b> <?= $event['time_from'] ?> - <?= $event['time_to'] ?></p>
    <p><b>Venue:</b> <?= htmlspecialchars($event['venue']) ?></p>
    <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>

    <?php if ($event['is_participatable'] && $part): ?>
        <hr>
        <h3>Participation Details</h3>

        <p><b>Type:</b> <?= $part['participation_type'] ?></p>
        <p><b>Expected Participants:</b> <?= $part['expected_participants'] ?></p>
        <p><b>Instructions:</b><br><?= nl2br(htmlspecialchars($part['rounds_info'])) ?></p>

     <?php if ($is_registered && $reg_status === 'active'): ?>
    <p style="color:green;"><b>You are registered for this event.</b></p>

<?php elseif ($is_registered && $reg_status === 'withdraw_requested'): ?>
    <p style="color:orange;"><b>Withdrawal request pending approval.</b></p>

<?php elseif ($is_registered && $reg_status === 'withdrawn'): ?>
    <p style="color:red;"><b>You have withdrawn from this event.</b></p>

<?php elseif ($deadline_passed): ?>
    <p style="color:red;"><b>Registration closed.</b></p>

<?php else: ?>
    <a class="btn" href="../controllers/register_event.php?id=<?= $event_id ?>">
        Register for Event
    </a>
<?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>
