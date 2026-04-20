<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!in_array($_SESSION['role'], ['admin','faculty'])) {
    die("Unauthorized access");
}

if (!isset($_GET['event_id'])) {
    die("Invalid request");
}

$event_id = (int) $_GET['event_id'];

// Fetch event
$stmt = $conn->prepare("SELECT title, event_date, venue FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event not found");
}

// Fetch summary
$stmt2 = $conn->prepare("SELECT * FROM post_event_reports WHERE event_id = ?");
$stmt2->bind_param("i", $event_id);
$stmt2->execute();
$summary = $stmt2->get_result()->fetch_assoc();

if (!$summary) {
    die("Summary not found");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Event Summary</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background: #f4f6f9;
            padding: 30px;
        }

        .form-card {
            max-width: 850px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        }

        h2 {
            margin-bottom: 6px;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 24px;
        }

        label {
            font-size: 14px;
            color: #475569;
            display: block;
            margin-top: 20px;
        }

        textarea {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            margin-top: 6px;
            font-size: 14px;
            resize: vertical;
            min-height: 120px;
        }

        button {
            margin-top: 30px;
            width: 100%;
            padding: 14px;
            font-size: 15px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 14px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .note {
            font-size: 12px;
            color: #64748b;
            margin-top: 6px;
        }

        .event-info {
            font-size: 14px;
            color: #334155;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="form-card">
    <h2>Edit Post-Event Summary</h2>
    <p class="subtitle">You may revise the report to reflect actual execution</p>

    <div class="event-info">
        <b>Event:</b> <?= htmlspecialchars($event['title']) ?><br>
        <b>Date:</b> <?= date("d M Y", strtotime($event['event_date'])) ?><br>
        <b>Venue:</b> <?= htmlspecialchars($event['venue']) ?>
    </div>

    <form action="../controllers/update_event_summary.php" method="POST">

        <input type="hidden" name="event_id" value="<?= $event_id ?>">

        <label>Event Summary</label>
        <textarea name="summary" required><?= htmlspecialchars($summary['summary']) ?></textarea>

        <label>Dignitaries</label>
        <textarea name="dignitaries"><?= htmlspecialchars($summary['dignitaries']) ?></textarea>

        <label>Dignitaries’ Remarks</label>
        <textarea name="dignitaries_words"><?= htmlspecialchars($summary['dignitaries_words']) ?></textarea>

        <p class="note">
            This section allows factual correction only.  
            Original event details and audience settings remain unchanged.
        </p>

        <button type="submit">Update Summary</button>
    </form>
</div>

</body>
</html>