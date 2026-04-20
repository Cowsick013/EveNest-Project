<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','faculty'])) {
    echo "Unauthorized access.";
    exit;
}

if (!isset($_GET['event_id'])) {
    echo "Invalid event.";
    exit;
}

$event_id = (int) $_GET['event_id'];

/* Fetch event */
$stmt = $conn->prepare("SELECT title, event_date FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo "Event not found.";
    exit;
}

/* Check if summary already exists */
$checkStmt = $conn->prepare("
    SELECT id FROM post_event_reports WHERE event_id = ?
");
$checkStmt->bind_param("i", $event_id);
$checkStmt->execute();
$existing = $checkStmt->get_result()->fetch_assoc();

if ($existing) {
    header("Location: view_event_summary.php?event_id=".$event_id);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Post-Event Summary</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }

        .box {
            width: 65%;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }

        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .btn {
            padding: 10px 15px;
            background: #1976d2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn.secondary {
            background: #555;
        }

        .note {
            background: #e3f2fd;
            border-left: 5px solid #1976d2;
            padding: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="box">

    <h2>Add Post-Event Summary</h2>
    <p><b>Event:</b> <?= htmlspecialchars($event['title']) ?></p>
    <p><b>Date:</b> <?= htmlspecialchars($event['event_date']) ?></p>

    <div class="note">
        <b>Note:</b> This summary will be used to generate the final academic report.
        Please enter factual and accurate information.
    </div>

    <form method="post" action="../controllers/PostEventController.php">

        <input type="hidden" name="event_id" value="<?= $event_id ?>">

        <label><b>Event Summary</b></label>
        <textarea name="summary" rows="6" required></textarea>

        <br><br>

        <label><b>Dignitaries (Name & Designation)</b></label>
        <textarea name="dignitaries" rows="4" required></textarea>

        <br><br>

        <label><b>Dignitaries’ Words / Remarks</b></label>
        <textarea name="dignitaries_words" rows="4" required></textarea>

        <br><br>

        <button type="submit" class="btn">
            Save Summary
        </button>

        <a href="view_event.php?id=<?= $event_id ?>" class="btn secondary">
            Cancel
        </a>

    </form>

</div>

</body>
</html>
