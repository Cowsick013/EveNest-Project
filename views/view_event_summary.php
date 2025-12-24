<?php
session_start();
require_once "../db.php";

if (!isset($_GET['event_id'])) {
    echo "Invalid event.";
    exit;
}

$event_id = intval($_GET['event_id']);

// Get event
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

// Get summary
$stmt2 = $conn->prepare("SELECT * FROM post_event_reports WHERE event_id = ?");
$stmt2->bind_param("i", $event_id);
$stmt2->execute();
$summary = $stmt2->get_result()->fetch_assoc();

if (!$summary) {
    echo "No summary found for this event.";
    exit;
}

// Get photos
$stmt3 = $conn->prepare("SELECT * FROM post_event_photos WHERE report_id = ?");
$stmt3->bind_param("i", $summary['id']);
$stmt3->execute();
$photosResult = $stmt3->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Summary</title>

    <style>
        body { font-family: Arial; padding: 20px; background: #f6f6f6; }

        .summary-box {
            background:white; padding:20px; border-radius:10px;
            box-shadow:0 0 8px rgba(0,0,0,0.1);
            width:85%; margin:auto;
        }

        img.summary-thumb {
            width:200px; margin:10px; cursor:pointer; border-radius:6px;
            transition:0.2s;
        }
        img.summary-thumb:hover {
            transform: scale(1.05);
        }

        /* Carousel Modal */
        .carousel-modal {
            display:none; position:fixed; top:0; left:0;
            width:100%; height:100%; background:rgba(0,0,0,0.9);
            z-index:2000; text-align:center;
        }
        .carousel-modal img {
            max-width:90%; max-height:80vh; margin-top:60px; border-radius:12px;
        }
        .close-btn {
            position:absolute; top:20px; right:30px; font-size:40px;
            color:white; cursor:pointer;
        }
        .arrow {
            position:absolute; top:50%; transform:translateY(-50%);
            font-size:50px; color:white; cursor:pointer;
        }
        .arrow.left { left:20px; }
        .arrow.right { right:20px; }

        .download-btn {
            position:absolute; bottom:40px; left:50%;
            transform:translateX(-50%);
            background:#0275d8; color:white;
            padding:10px 20px;
            border-radius:6px;
            text-decoration:none;
            font-size:18px;
        }
    </style>
</head>

<body>

<div class="summary-box">
    <h2><?= $event['title'] ?></h2>
    <p><b>Date:</b> <?= $event['event_date'] ?></p>

    <h3>Summary</h3>
    <p><?= nl2br($summary['summary']) ?></p>

    <h3>Dignitaries</h3>
    <p><?= nl2br($summary['dignitaries']) ?></p>

    <h3>Dignitaries Words</h3>
    <p><?= nl2br($summary['dignitaries_words']) ?></p>

    <h3>Report PDF</h3>
    <?php if (!empty($summary['report_file'])): ?>
        <a href="../<?= $summary['report_file'] ?>" target="_blank">Download PDF</a>
    <?php else: ?>
        <p>No report uploaded.</p>
    <?php endif; ?>

    <h3>Photos</h3>
    <?php
    $photoArray = [];
    while ($p = $photosResult->fetch_assoc()):
        $photoArray[] = "../" . $p['photo_path'];
    ?>
        <img src="../<?= $p['photo_path'] ?>" 
             class="summary-thumb" 
             onclick="openCarousel(<?= count($photoArray)-1 ?>)">
    <?php endwhile; ?>

    <br><br>
    <a href="summary_list.php">Back</a>
</div>

<!-- CAROUSEL MODAL -->
<div id="carouselModal" class="carousel-modal">
    <span class="close-btn" onclick="closeCarousel()">&times;</span>
    <span class="arrow left" onclick="changeSlide(-1)">&#10094;</span>
    <img id="carouselImage">
    <span class="arrow right" onclick="changeSlide(1)">&#10095;</span>

    <a id="downloadLink" class="download-btn" download>Download</a>
</div>

<script>
let photos = <?php echo json_encode($photoArray); ?>;
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
    const img = photos[currentIndex];
    document.getElementById("carouselImage").src = img;
    document.getElementById("downloadLink").href = img;
}
</script>

</body>
</html>
