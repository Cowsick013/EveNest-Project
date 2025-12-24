<?php
session_start();
require_once "../db.php";

// Must be logged in
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid event!");
}

$event_id = $_GET['id'];

// Fetch event details
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Event not found!");
}

$event = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Created</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            padding: 30px;
        }
        .box {
            background: white;
            padding: 25px;
            border-radius: 8px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #28a745;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            margin-top: 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        .btn-secondary {
            background: #6c757d;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Event Created Successfully!</h2>

    <p>Your event has been saved.</p>

    <h3><?php echo htmlspecialchars($event['title']); ?></h3>

    <p><b>Date:</b> <?php echo htmlspecialchars($event['event_date']); ?></p>
    <p><b>Time:</b> <?php echo htmlspecialchars($event['time_from'] . " - " . $event['time_to']); ?></p>
    <p><b>Venue:</b> <?php echo htmlspecialchars($event['venue']); ?></p>

    <br>

    <a class="btn" href="view_event.php?id=<?php echo $event['id']; ?>">View Event</a>

    <?php if ($_SESSION['role'] == 'faculty') { ?>
        <a class="btn-secondary btn" href="faculty_dashboard.php">Back to Dashboard</a>
    <?php } else { ?>
        <a class="btn-secondary btn" href="admin_dashboard.php">Back to Dashboard</a>
    <?php } ?>
</div>

</body>
</html>
