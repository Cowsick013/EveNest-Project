<?php
session_start();
require_once "../db.php";

if (!isset($_GET['id'])) {
    echo "Invalid event";
    exit;
}

$event_id = $_GET['id'];

// Main report
$report = $conn->query("
    SELECT * FROM post_event_reports 
    WHERE event_id = $event_id
")->fetch_assoc();

// Photos
$photos = $conn->query("
    SELECT * FROM post_event_photos 
    WHERE report_id = {$report['id']}
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Report</title>
    <style>
        .report-box {
            width: 60%;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        .gallery img {
            width: 180px;
            margin: 8px;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<div class="report-box">

    <?php if (!$report): ?>
        <h3>No Post-Event Report Found</h3>
    <?php else: ?>

        <h2>Post Event Summary</h2>
        <p><b>Summary:</b> <?= nl2br($report['summary']) ?></p>
        <p><b>Dignitaries:</b> <?= $report['dignitaries'] ?></p>

        <hr>

        <h3>Photos</h3>
        <div class="gallery">
            <?php while ($img = $photos->fetch_assoc()): ?>
                <img src="../uploads/post_event/<?= $event_id ?>/<?= $img['photo_path'] ?>" />
            <?php endwhile; ?>
        </div>

    <?php endif; ?>
</div>

</body>
</html>
