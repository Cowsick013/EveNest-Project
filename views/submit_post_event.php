<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/* -------------------------------
   FIXED EVENT ID HANDLING
--------------------------------*/
$event_id = $_GET["event_id"] ?? null;

if (!$event_id) {
    echo "Invalid event ID";
    exit;
}

$event_id = intval($event_id);

/* --------------------------------
   FETCH EVENT DETAILS
---------------------------------*/
$eventSql = "SELECT * FROM events WHERE id = ?";
$stmt = $conn->prepare($eventSql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Submit Post Event Summary</title>
    <style>
        .container {
            width: 70%;
            margin: auto;
            padding: 20px;
            background: #fafafa;
            border: 1px solid #999;
            border-radius: 10px;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
        }
        .section-title {
            font-size: 20px;
            margin: 20px 0 10px;
            font-weight: bold;
        }
        .btn {
            padding: 10px 15px;
            background: #0d47a1;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Submit Post Event Summary</h2>

    <p><strong>Event:</strong> <?= $event["title"] ?></p>
    <p><strong>Date:</strong> <?= $event["event_date"] ?></p>

    <!-- THE MOST IMPORTANT FIX: correct controller path -->
    <form action="../controllers/PostEventController.php" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="event_id" value="<?= $event_id ?>">

        <div class="section-title">Event Summary</div>
        <textarea name="summary" required placeholder="Write a brief summary of the event..."></textarea>

        <div class="section-title">Dignitaries Name(s)</div>
        <textarea name="dignitaries" placeholder="List all dignitaries..."></textarea>

        <div class="section-title">Dignitaries' Words (2–3 lines)</div>
        <textarea name="dignitaries_words" placeholder="What did they say?"></textarea>

        <div class="section-title">Upload Event Report (PDF)</div>
        <input type="file" name="report_file" accept="application/pdf">

        <div class="section-title">Upload Photos</div>
        <input type="file" name="photos[]" multiple>

        <button type="submit" class="btn">Submit Summary</button>
    </form>

    <br><br>
    <a class="btn" href="summary_list.php">Back</a>

</div>

</body>
</html>
