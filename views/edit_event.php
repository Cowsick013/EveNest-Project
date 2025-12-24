<?php
session_start();
require_once "../db.php";
require_once "../includes/flash.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "Invalid event ID";
    exit;
}

$event_id = $_GET['id'];

// Fetch event
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    echo "Event not found";
    exit;
}

$user_role = $_SESSION['role'];

// Allow only admin and faculty to edit ANY event
if (!in_array($user_role, ['admin', 'faculty'])) {
    echo "You do not have permission to edit this event.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
    <style>
        form {
            width: 60%;
            margin: auto;
            padding: 20px;
            background: #f5f5f5;
            border: 1px solid #ccc;
        }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>

<h2 style="text-align:center;">Edit Event</h2>

<form action="../controllers/update_event.php" method="POST">

    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">

    <label>Event Title:</label>
    <input type="text" name="event_title" value="<?php echo $event['title']; ?>" required>

    <label>Description:</label>
    <textarea name="description" required><?php echo $event['description']; ?></textarea>

    <label>Date:</label>
    <input type="date" name="event_date" value="<?php echo $event['event_date']; ?>" required>

    <label>Time From:</label>
    <input type="time" name="time_from" value="<?php echo $event['time_from']; ?>" required>

    <label>Time To:</label>
    <input type="time" name="time_to" value="<?php echo $event['time_to']; ?>" required>

    <label>Venue:</label>
    <input type="text" name="venue" value="<?php echo $event['venue']; ?>" required>

    <label>Organized By:</label>
    <input type="text" name="organized_by" value="<?php echo $event['organized_by']; ?>" required>

    <label>Notes:</label>
    <textarea name="notes"><?php echo $event['notes']; ?></textarea>

    <button type="submit">Update Event</button>

</form>

</body>
</html>
