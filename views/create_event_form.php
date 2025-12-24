<?php
session_start();

require_once "../includes/flash.php";

// Only admin & faculty can create events
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'faculty'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['date'])) {
    die("No date selected!");
}

$selectedDate = $_GET['date'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Event</title>
</head>
<body>

<h2>Create Event - <?php echo htmlspecialchars($selectedDate); ?></h2>

<form action="../controllers/save_event.php" method="POST">

    <input type="hidden" name="event_date" value="<?php echo $selectedDate; ?>">

    <label>Event Title:</label><br>
    <input type="text" name="event_title" required><br><br>

    <label>Description:</label><br>
    <textarea name="description" required></textarea><br><br>

    <label>Start Time:</label><br>
    <input type="time" name="time_from" required><br><br>

    <label>End Time:</label><br>
    <input type="time" name="time_to" required><br><br>

    <label>Venue:</label><br>
    <input type="text" name="venue" required><br><br>

    <!-- ORGANIZERS -->
    <label>Organizers:</label><br>
    <?php
        $orgs = ["BCA","BCOM","BBA","GMFC","LIBRARY","Sports","NSS","Student Council","IQAC"];
        foreach ($orgs as $o):
    ?>
        <input type="checkbox" name="organizers[]" value="<?= $o ?>"> <?= $o ?><br>
    <?php endforeach; ?>

    <label>Other Organizer (optional):</label><br>
    <input type="text" name="organizer_other"><br><br>

    <hr>

    <!-- TARGET AUDIENCE -->
    <label>Target Audience:</label><br>
    <input type="checkbox" name="audience[]" value="BCA"> BCA<br>
    <input type="checkbox" name="audience[]" value="BCOM"> BCOM<br>
    <input type="checkbox" name="audience[]" value="BBA"> BBA<br>

    <label>Other Audience (optional):</label><br>
    <input type="text" name="audience_other"><br><br>

    <hr>

    <!-- GUEST/SPEAKER -->
    <label>Guest / Speaker (optional):</label><br>
    <input type="text" name="speaker"><br><br>

    <!-- EVENT INCHARGE -->
    <label>Event Incharge / Coordinator:</label><br>
    <input type="text" name="incharge" required><br><br>

    <!-- JUNIOR COORDINATOR -->
    <label>Jr. Coordinator (optional):</label><br>
    <input type="text" name="jr_coordinator"><br><br>

    <!-- NOTES -->
    <label>Notes / Instructions:</label><br>
    <textarea name="notes"></textarea><br><br>

    <button type="submit">Save Event</button>

</form>


</body>
</html>
