<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$event_id = $_GET["event_id"] ?? null;
if (!$event_id) {
    die("Invalid event ID");
}

$event_id = (int)$event_id;

/* Fetch event */
$stmt = $conn->prepare("SELECT title, event_date FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event not found");
}

include "../includes/header.php";
?>

<div class="page-container">
    <div class="card form-card">

        <h2 class="page-title">Submit Post Event Summary</h2>

        <p class="form-meta">
            <strong>Event:</strong> <?= htmlspecialchars($event['title']) ?><br>
            <strong>Date:</strong> <?= date("d M Y", strtotime($event['event_date'])) ?>
        </p>

        <form action="../controllers/PostEventController.php"
              method="POST"
              enctype="multipart/form-data">

            <input type="hidden" name="event_id" value="<?= $event_id ?>">

            <div class="form-group">
                <label>Event Summary</label>
                <textarea name="summary"
                          required
                          placeholder="Write a brief academic summary of the event..."></textarea>
            </div>

            <div class="form-group">
                <label>Guest(s)</label>
                <textarea name="dignitaries"
                          placeholder="List all dignitaries involved in the event..."></textarea>
            </div>

            <div class="form-group">
                <label>Words </label>
                <textarea name="dignitaries_words"
                          placeholder="Brief remarks or message delivered by dignitaries..."></textarea>
            </div>

            <div class="form-group">
                <label>Upload Event Photographs</label>
                <input type="file" name="photos[]" multiple>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">
                    Submit Summary
                </button>

                <a href="summary_list.php" class="btn back">
                    Back
                </a>
            </div>

        </form>

    </div>
</div>
<a href="summary_list.php" class="btn back">Back</a>

<?php include "../includes/footer.php"; ?>
