<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['event_id'])) {
    die("Invalid event.");
}

$event_id = (int) $_GET['event_id'];

// Fetch event
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event not found.");
}

// Decode allowed audience (if any)
$allowedAudience = null;
if (!empty($event['allowed_audience'])) {
    $allowedAudience = json_decode($event['allowed_audience'], true);
}

// Fetch report
$stmt2 = $conn->prepare("SELECT * FROM post_event_reports WHERE event_id = ?");
$stmt2->bind_param("i", $event_id);
$stmt2->execute();
$summary = $stmt2->get_result()->fetch_assoc();

if (!$summary) {
    die("No summary found for this event.");
}

// Fetch photos
$stmt3 = $conn->prepare("SELECT * FROM post_event_photos WHERE report_id = ?");
$stmt3->bind_param("i", $summary['id']);
$stmt3->execute();
$photosResult = $stmt3->get_result();

include "../includes/header.php";
?>

<div class="page-container">

    <h2 class="page-title">Post Event Report</h2>
    <p class="page-subtitle">
        <?= htmlspecialchars($event['title']) ?> |
        <?= date("d M Y", strtotime($event['event_date'])) ?> |
        <?= htmlspecialchars($event['venue']) ?>
    </p>

    <!-- ACTIONS (TOP) -->
    <?php if (in_array($_SESSION['role'], ['admin','faculty'])): ?>
        <div class="profile-actions" style="margin-bottom:20px;">
            <a href="edit_event_summary.php?event_id=<?= $event_id ?>" class="button">
                ✏️ Edit Summary
            </a>
        </div>
    <?php endif; ?>

    <!-- AUDIENCE INFO -->
    <div class="card">
        <h3>Audience Information</h3>

        <p>
            <b>Planned Target Audience:</b><br>
            <?= htmlspecialchars($event['audience']) ?>
        </p>

        <?php if ($allowedAudience): ?>
            <p>
                <b>Additional Audience Allowed:</b><br>

                <?php if (!empty($allowedAudience['streams'])): ?>
                    <?= implode(", ", $allowedAudience['streams']) ?><br>
                <?php endif; ?>

                <?php if (!empty($allowedAudience['years'])): ?>
                    Years: <?= implode(", ", $allowedAudience['years']) ?><br>
                <?php endif; ?>

                Gender: <?= $allowedAudience['gender'] ?? 'All' ?>
            </p>

            <small class="text-muted">
                Audience eligibility was expanded after event creation due to participation requirements.
            </small>
        <?php endif; ?>
    </div>

    <!-- REPORT CONTENT -->
    <div class="card report-content">

        <p class="text-muted">
            <strong>Report Source:</strong>
            <?= strtoupper($summary['report_source'] ?? 'MANUAL') ?>
        </p>

        <hr>

        <h3>Event Summary</h3>
        <p><?= nl2br(htmlspecialchars($summary['summary'])) ?></p>

        <h3>Dignitaries</h3>
        <p><?= nl2br(htmlspecialchars($summary['dignitaries'])) ?></p>

        <h3>Dignitaries’ Remarks</h3>
        <p><?= nl2br(htmlspecialchars($summary['dignitaries_words'])) ?></p>

    </div>

    <!-- EVENT PHOTOS -->
    <?php
    $photoArray = [];
    if ($photosResult->num_rows > 0):
    ?>
    <div class="card">
        <h3>Event Photographs</h3>

        <div class="photo-grid">
            <?php while ($p = $photosResult->fetch_assoc()):
                $photoArray[] = "../" . $p['photo_path'];
            ?>
                <img src="../<?= $p['photo_path'] ?>"
                     class="summary-thumb"
                     onclick="openCarousel(<?= count($photoArray)-1 ?>)">
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ACTIONS (BOTTOM) -->
    <div class="profile-actions">
        <a style="margin: 6px;" href="event_report_print.php?id=<?= $event_id ?>" class="button">
            🖨️ Print Report
        </a>
        <a style="margin: 6px;" href="view_event.php?id=<?= $event_id ?>" class="button">
            Back to Event
        </a>
        <a href="event_reports.php" class="button">
            Back to Reports
        </a>
    </div>

</div>

<!-- IMAGE CAROUSEL -->
<div id="carouselModal" class="carousel-modal">
    <span class="close-btn" onclick="closeCarousel()">&times;</span>
    <span class="arrow left" onclick="changeSlide(-1)">&#10094;</span>
    <img id="carouselImage">
    <span class="arrow right" onclick="changeSlide(1)">&#10095;</span>
    <a id="downloadLink" class="download-btn" download>Download</a>
</div>

<script>
let photos = <?= json_encode($photoArray); ?>;
let currentIndex = 0;

function openCarousel(index) {
    currentIndex = index;
    updateCarousel();
    document.getElementById("carouselModal").style.display = "block";
}
function closeCarousel() {
    document.getElementById("carouselModal").style.display = "none";
}
function changeSlide(step) {
    currentIndex = (currentIndex + step + photos.length) % photos.length;
    updateCarousel();
}
function updateCarousel() {
    document.getElementById("carouselImage").src = photos[currentIndex];
    document.getElementById("downloadLink").href = photos[currentIndex];
}
</script>

<?php include "../includes/footer.php"; ?>
