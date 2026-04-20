<?php
session_start();
require_once "../db.php";

if (!isset($_GET['id'])) {
    echo "Invalid event.";
    exit;
}

$event_id = (int) $_GET['id'];

/* Fetch event */
$eventStmt = $conn->prepare("SELECT title, event_date FROM events WHERE id = ?");
$eventStmt->bind_param("i", $event_id);
$eventStmt->execute();
$event = $eventStmt->get_result()->fetch_assoc();

if (!$event) {
    echo "Event not found.";
    exit;
}

/* Fetch AI-generated report */
$reportStmt = $conn->prepare("
    SELECT * FROM post_event_reports 
    WHERE event_id = ?
");
$reportStmt->bind_param("i", $event_id);
$reportStmt->execute();
$report = $reportStmt->get_result()->fetch_assoc();

/* Fetch photos */
$photos = [];
if ($report) {
    $photoStmt = $conn->prepare("
        SELECT photo_path 
        FROM post_event_photos 
        WHERE report_id = ?
    ");
    $photoStmt->bind_param("i", $report['id']);
    $photoStmt->execute();
    $photos = $photoStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Report</title>

    <style>
        body {
            font-family: "Times New Roman", serif;
            background: #f4f4f4;
            padding: 20px;
        }

        .report-box {
            width: 70%;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2, h3 {
            text-align: center;
        }

        .meta {
            text-align: center;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 20px;
            line-height: 1.6;
            text-align: justify;
        }

        .gallery img {
            width: 180px;
            margin: 8px;
            border-radius: 6px;
        }

        .actions {
            margin-top: 30px;
            text-align: center;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            margin: 5px;
            background: #1976d2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn.secondary {
            background: #555;
        }

        /* PRINT */
        @media print {
            body {
                background: white;
            }
            .actions {
                display: none;
            }
            .report-box {
                width: 100%;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

<div class="report-box">

    <h2>Post Event Report</h2>

    <div class="meta">
        <b>Event Title:</b> <?= htmlspecialchars($event['title']) ?><br>
        <b>Date:</b> <?= htmlspecialchars($event['event_date']) ?><br>
        <b>Report Source:</b>
        <?= isset($report['report_source']) ? strtoupper($report['report_source']) : 'AI GENERATED' ?>
    </div>

    <hr>

    <?php if (!$report || empty($report['summary'])): ?>

        <p style="text-align:center; color:red;">
            Post-event report has not been generated yet.
        </p>

    <?php else: ?>

        <div class="section">
            <?= nl2br(htmlspecialchars($report['summary'])) ?>
        </div>

        <?php if (!empty($photos)): ?>
            <hr>
            <h3>Event Photographs</h3>
            <div class="gallery">
                <?php foreach ($photos as $img): ?>
                    <img src="../uploads/post_event/<?= $event_id ?>/<?= htmlspecialchars($img['photo_path']) ?>">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <div class="actions">
        <button onclick="window.print()" class="btn">
            Print / Save as PDF
        </button>

        <a href="view_event.php?id=<?= $event_id ?>" class="btn secondary">
            ⬅ Back to Event
        </a>
    </div>

</div>

</body>
</html>
