<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "Invalid event ID";
    exit;
}

$event_id = $_GET['id'];
$user_role = $_SESSION['role'];
$user_id   = $_SESSION['user_id'];

require_once "../includes/flash.php";

// Fetch event data
$stmt = $conn->prepare("
    SELECT events.*, users.name AS creator_name
    FROM events
    LEFT JOIN users ON events.created_by = users.id
    WHERE events.id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    echo "Event not found";
    exit;
}

// Current date & time
$today = date("Y-m-d");
$current_time = date("H:i");

// EVENT HAS ENDED IF:
// 1) Today's date is after event date
// OR
// 2) Same date but current time is past time_to
$event_has_ended = (
    $today > $event['event_date'] ||
    ($today == $event['event_date'] && $current_time > $event['time_to'])
);

?>
<!DOCTYPE html>
<html>
<head>
    <title>View Event</title>
    <style>
        .box {
            width: 60%;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            background: #fafafa;
            border-radius: 10px;
        }
        .actions a {
            padding: 8px 12px;
            margin-right: 10px;
            background: #ddd;
            display: inline-block;
            text-decoration: none;
            border-radius: 5px;
        }
        .cancel-box {
            padding: 10px;
            margin: 15px 0;
            background: #ffcc80;
            border-left: 6px solid #ff9800;
        }
        .cancelled {
            padding: 10px;
            background: #ef5350;
            border-left: 6px solid #c62828;
            color: white;
            margin: 15px 0;
        }
        .post-event-btn {
            display:inline-block;
            margin-top:10px;
            padding:10px 15px;
            background:#0d47a1;
            color:white;
            border-radius:5px;
            text-decoration:none;
        }
    </style>
</head>
<body>

<div class="box">
    <h2><?php echo $event['title']; ?></h2>

    <?php if ($event['status'] === "cancelled"): ?>
        <div class="cancelled">
            <b>This event has been cancelled.</b>
        </div>
    <?php endif; ?>

    <?php if ($event['cancel_request'] === "requested" && $event['status'] === "active"): ?>
        <div class="cancel-box">
            <b>Cancellation Request Pending</b><br>
            Requested by: <b><?php echo $event['creator_name']; ?></b>
        </div>
    <?php endif; ?>

    <p><b>Date:</b> <?= $event['event_date']; ?></p>
    <p><b>Time:</b> <?= $event['time_from'] . " - " . $event['time_to']; ?></p>
    <p><b>Venue:</b> <?= $event['venue']; ?></p>
    <p><b>Description:</b> <?= $event['description']; ?></p>
    <p><b>Organized By:</b> <?= $event['organized_by']; ?></p>
    <p><b>Status:</b> <?= $event['status']; ?></p>
    <p><b>Notes:</b> <?= $event['notes']; ?></p>

    <hr>

    <div class="actions">

        <!-- EDIT (admin + faculty) -->
        <?php if (in_array($user_role, ["admin", "faculty"])): ?>
            <a href="edit_event.php?id=<?php echo $event_id; ?>">Edit</a>
        <?php endif; ?>

        <!-- DELETE BUTTON (only if cancel_request = approved) -->
         <?php if ($event['cancel_request'] === "approved" && in_array($user_role, ["admin", "faculty"])): ?>
            <a href="../controllers/delete_event.php?id=<?= $event_id ?>"
            onclick="return confirm('Are you sure you want to delete this event?')">
            Delete Event
        </a>
        <?php endif; ?>


        <p><b>Speaker:</b> <?= $event['speaker']; ?></p>
        <p><b>Event Incharge:</b> <?= $event['incharge']; ?></p>
        <p><b>Jr. Coordinator:</b> <?= $event['jr_coordinator']; ?></p>
        <p><b>Target Audience:</b> <?= $event['audience']; ?></p>

        <!-- REQUEST CANCEL (admin + faculty) -->
        <?php if (
    $user_role === "faculty" &&
    $event['status'] === "active" && //THIS LOGIC HELPS TO KEEP THE CANCELLATION EVEN AFTER ADMIN DENIES IT
    in_array($event['cancel_request'],  ["none", "denied"])
): ?>
    <a href="../controllers/request_cancel.php?id=<?= $event_id; ?>">
        Request Cancellation
    </a>
<?php endif; ?>

        <!-- REQUEST GOT DENIED BY ADMIN-->
         <?php if ($event['cancel_request'] === "denied"): ?>
    <div class="cancel-box" style="background:#ffcdd2; border-left: 6px solid #d32f2f;">
        <b>Previous cancellation request was denied.</b>
        <br>You may request again.
    </div>
<?php endif; ?>

    </div>

    <!-- POST-EVENT SUMMARY BUTTON (ONLY show if event ended) -->
    <?php if ($event_has_ended && in_array($user_role, ['admin', 'faculty'])): ?>
        <div style='margin-top:15px; padding:10px; background:#e3f2fd; border-left:5px solid #1976d2;'>
            <b>This event has ended.</b><br>
            You may now submit the Post-Event Summary.
        </div>

        <a href="submit_post_event.php?id=<?php echo $event_id; ?>" class="post-event-btn">
            Submit Post-Event Summary
        </a>
    <?php endif; ?>

    <?php if (isset($_GET['msg'])): ?>
    <div style="padding:10px; background:#d1ecf1; color:#0c5460; border:1px solid #bee5eb; margin-bottom:15px;">
        <?php 
            if ($_GET['msg'] === "request_sent") echo "Cancellation request submitted.";
            if ($_GET['msg'] === "already_requested") echo "Cancellation request is already pending.";
            if ($_GET['msg'] === "denied") echo "Your cancellation request was denied by admin.";
        ?>
    </div>
<?php endif; ?>

<?php if ($_SESSION['role'] === 'student'): ?>
    <a href="mark_attendance.php?event_id=<?= $event_id ?>">
        Test Attendance Page
    </a>
<?php endif; ?>


</div>

</body>
</html>
