<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Invalid Event");
}

$event_id = (int)$_GET['id'];

/* ===============================
   FETCH EVENT
================================ */
$event = $conn->query("
    SELECT id, title, event_date, venue, audience, allowed_audience
    FROM events
    WHERE id = $event_id
")->fetch_assoc();

if (!$event) {
    die("Event not found");
}

/* ===============================
   DECODE ALLOWED AUDIENCE
================================ */
$allowedAudience = null;
if (!empty($event['allowed_audience'])) {
    $allowedAudience = json_decode($event['allowed_audience'], true);
}

/* ===============================
   FETCH POST-EVENT REPORT
================================ */
$report = $conn->query("
    SELECT *
    FROM post_event_reports
    WHERE event_id = $event_id
")->fetch_assoc();

if (!$report) {
    die("Post-event report not available");
}

/* ===============================
   PARTICIPATION & ATTENDANCE
================================ */

// Registered participants
$registeredCountRow = $conn->query("
    SELECT COUNT(*) AS total
    FROM event_registrations
    WHERE event_id = $event_id
    AND status = 'active'
");

$registeredCount = $registeredCountRow
    ? (int)$registeredCountRow->fetch_assoc()['total']
    : 0;

// Actual attendance (QR verified)
$attendanceCountRow = $conn->query("
    SELECT COUNT(*) AS total
    FROM event_attendance
    WHERE event_id = $event_id
    AND status = 'present'
");

$attendanceCount = $attendanceCountRow
    ? (int)$attendanceCountRow->fetch_assoc()['total']
    : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Report</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            margin: 0;
            color: #000;
        }

        .print-actions {
            margin: 20px;
            display: flex;
            gap: 10px;
        }

        .report-container {
            width: 210mm;
            min-height: 297mm;
            padding: 25mm;
            margin: auto;
            background: #fff;
        }

        h1 {
            text-align: center;
            font-size: 18pt;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .meta {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 30px;
        }

        .section {
            margin-bottom: 25px;
        }

        .section h3 {
            font-size: 13pt;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }

        .section p, .section li {
            text-align: justify;
            line-height: 1.7;
        }

        ul {
            margin-left: 20px;
        }

        .footer {
            margin-top: 40px;
            font-size: 11pt;
        }

        @media print {
            .print-actions {
                display: none !important;
            }

            @page {
                size: A4;
                margin: 20mm;
            }
        }
    </style>
</head>
<body>

<div class="print-actions">
    <form action="../controllers/generate_ai_report.php" method="post">
        <input type="hidden" name="event_id" value="<?= $event_id ?>">
        <button type="submit">Generate AI Report</button>
    </form>

    <button onclick="window.print()">Print / Save as PDF</button>
</div>

<div class="report-container">

    <h1><?= strtoupper(htmlspecialchars($event['title'])) ?></h1>

    <div class="meta">
        <strong>Date:</strong> <?= date("d F Y", strtotime($event['event_date'])) ?>
        &nbsp; | &nbsp;
        <strong>Venue:</strong> <?= htmlspecialchars($event['venue']) ?>
    </div>

    <!-- AUDIENCE INFORMATION -->
    <div class="section">
        <h3>Audience Information</h3>

        <p>
            <b>Planned Target Audience:</b><br>
            <?= !empty($event['audience'])
                ? htmlspecialchars($event['audience'])
                : 'As per event approval' ?>
        </p>

        <?php if ($allowedAudience): ?>
            <p>
                <b>Additional Audience Allowed:</b><br>

                <?php if (!empty($allowedAudience['streams'])): ?>
                    Streams: <?= htmlspecialchars(implode(", ", $allowedAudience['streams'])) ?><br>
                <?php endif; ?>

                <?php if (!empty($allowedAudience['years'])): ?>
                    Years: <?= htmlspecialchars(implode(", ", $allowedAudience['years'])) ?><br>
                <?php endif; ?>

                Gender: <?= htmlspecialchars($allowedAudience['gender'] ?? 'All') ?>
            </p>

            <p style="font-size:11pt;">
                <i>
                    Audience eligibility was expanded post approval
                    due to participation requirements.
                </i>
            </p>
        <?php endif; ?>
    </div>

    <!-- PARTICIPATION & ATTENDANCE -->
    <div class="section">
        <h3>Participation & Attendance Summary</h3>

        <p>
            <b>Event Nature:</b>
            <?= $registeredCount > 0 ? 'Participatory' : 'Audience-based' ?>
        </p>

        <?php if ($registeredCount > 0): ?>
            <p>
                <b>Total Registered Participants:</b>
                <?= $registeredCount ?>
            </p>
        <?php endif; ?>

        <p>
            <b>Total Students Present:</b>
            <?= $attendanceCount ?>
        </p>

        <p style="font-size:11pt;">
            <i>
                Attendance was recorded using QR code scanning with
                location verification during the permitted time window.
            </i>
        </p>
    </div>

    <!-- EVENT SUMMARY -->
    <div class="section">
        <h3>Event Summary</h3>

        <?php
        $cleanReport = $report['summary'];

        $cleanReport = str_replace("**", "", $cleanReport);
        $cleanReport = preg_replace('/^\d+\.\s*/m', '', $cleanReport);

        $cleanReport = preg_replace(
            '/^(INTRODUCTION|OBJECTIVES OF THE EVENT|EVENT PLANNING AND EXECUTION|OUTCOME OF THE EVENT|CONCLUSION)/m',
            '<h3>$1</h3>',
            $cleanReport
        );

        echo $cleanReport;
        ?>
    </div>

    <!-- DIGNITARIES -->
    <?php if (!empty(trim($report['dignitaries']))): ?>
        <div class="section">
            <h3>Dignitaries</h3>
            <ul>
                <?php foreach (explode("\n", $report['dignitaries']) as $d): ?>
                    <?php if (trim($d)): ?>
                        <li><?= htmlspecialchars(trim($d)) ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- DIGNITARIES REMARKS -->
    <?php if (!empty(trim($report['dignitaries_words']))): ?>
        <div class="section">
            <h3>Dignitaries’ Remarks</h3>
            <p><?= nl2br(htmlspecialchars($report['dignitaries_words'])) ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($report['updated_at'])): ?>
        <p>
            <b>Summary Last Updated On:</b>
            <?= date("d-m-Y", strtotime($report['updated_at'])) ?>
        </p>
    <?php endif; ?>

    <div class="footer">
        <p><b>Organized By:</b> Department / Committee</p>
        <p><b>Report Generated On:</b> <?= date("d-m-Y") ?></p>
    </div>

</div>

</body>
</html>
