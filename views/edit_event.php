<?php
session_start();
require_once "../db.php";
require_once "../includes/flash.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Invalid event ID");
}

$event_id = (int) $_GET['id'];

// Fetch event
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event not found");
}

$user_role = $_SESSION['role'];
if (!in_array($user_role, ['admin','faculty'])) {
    die("Permission denied");
}

/* ---------- AUDIENCE PREFILL ---------- */
$allowed = [
    'streams' => [],
    'years'   => [],
    'gender'  => 'All'
];

if (!empty($event['allowed_audience'])) {
    $decoded = json_decode($event['allowed_audience'], true);
    if (is_array($decoded)) {
        $allowed = array_merge($allowed, $decoded);
    }
}

$allStreams = ['BCA','BBA','BCOM'];
$allChecked = empty(array_diff($allStreams, $allowed['streams']));
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit Event</title>

<style>
body {
    font-family: "Segoe UI", sans-serif;
    background:#f4f6f9;
    padding:30px;
}

.form-card {
    max-width:850px;
    margin:auto;
    background:#fff;
    padding:30px;
    border-radius:16px;
    box-shadow:0 15px 35px rgba(0,0,0,0.08);
}

h2 {
    margin-bottom:6px;
}

.subtitle {
    color:#64748b;
    font-size:14px;
    margin-bottom:24px;
}

label {
    font-size:13px;
    color:#475569;
    display:block;
    margin-top:16px;
}

input, textarea, select {
    width:100%;
    padding:12px 14px;
    border-radius:10px;
    border:1px solid #cbd5e1;
    margin-top:6px;
    font-size:14px;
}

textarea { resize:vertical; min-height:90px; }

hr {
    margin:30px 0;
    border:none;
    border-top:1px solid #e5e7eb;
}

.section-title {
    font-size:16px;
    margin-bottom:10px;
}

.checkbox-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(140px,1fr));
    gap:12px;
}

.checkbox-grid label {
    display:flex;
    align-items:center;
    gap:8px;
    background:#f8fafc;
    padding:10px 12px;
    border-radius:10px;
    border:1px solid #e2e8f0;
    cursor:pointer;
}

button {
    margin-top:30px;
    width:100%;
    padding:14px;
    font-size:15px;
    background:#2563eb;
    color:#fff;
    border:none;
    border-radius:14px;
    cursor:pointer;
}

button:hover { background:#1d4ed8; }

.note {
    font-size:12px;
    color:#64748b;
    margin-top:6px;
}
</style>
</head>

<body>

<div class="form-card">
<h2>Edit Event</h2>
<p class="subtitle">You may expand audience eligibility if required</p>

<form action="../controllers/update_event.php" method="POST">

<input type="hidden" name="event_id" value="<?= $event_id ?>">

<label>Event Title</label>
<input type="text" name="event_title" value="<?= htmlspecialchars($event['title']) ?>" required>

<label>Description</label>
<textarea name="description" required><?= htmlspecialchars($event['description']) ?></textarea>

<label>Date</label>
<input type="date" name="event_date" value="<?= $event['event_date'] ?>" required>

<label>Time From</label>
<input type="time" name="time_from" value="<?= $event['time_from'] ?>" required>

<label>Time To</label>
<input type="time" name="time_to" value="<?= $event['time_to'] ?>" required>

<label>Venue</label>
<input type="text" name="venue" value="<?= htmlspecialchars($event['venue']) ?>" required>

<label>Organized By</label>
<input type="text" name="organized_by" value="<?= htmlspecialchars($event['organized_by']) ?>">

<label>Notes</label>
<textarea name="notes"><?= htmlspecialchars($event['notes']) ?></textarea>

<hr>

<h3 class="section-title">Target Audience</h3>

<div class="checkbox-grid">
<label><input type="checkbox" name="audience_streams[]" value="BCA" <?= in_array('BCA',$allowed['streams'])?'checked':'' ?>> BCA</label>
<label><input type="checkbox" name="audience_streams[]" value="BBA" <?= in_array('BBA',$allowed['streams'])?'checked':'' ?>> BBA</label>
<label><input type="checkbox" name="audience_streams[]" value="BCOM" <?= in_array('BCOM',$allowed['streams'])?'checked':'' ?>> BCOM</label>
<label><input type="checkbox" id="audienceAll" <?= $allChecked?'checked':'' ?>> All Students</label>
</div>

<p class="note">
Audience may be expanded if participation is low. Original intent remains unchanged.
</p>

<hr>

<h3 class="section-title">Target Year</h3>
<div class="checkbox-grid">
<label><input type="checkbox" name="audience_year[]" value="FY" <?= in_array('FY',$allowed['years'])?'checked':'' ?>> FY</label>
<label><input type="checkbox" name="audience_year[]" value="SY" <?= in_array('SY',$allowed['years'])?'checked':'' ?>> SY</label>
<label><input type="checkbox" name="audience_year[]" value="TY" <?= in_array('TY',$allowed['years'])?'checked':'' ?>> TY</label>
</div>

<hr>

<h3 class="section-title">Gender Eligibility</h3>
<select name="audience_gender">
<option value="All" <?= $allowed['gender']==='All'?'selected':'' ?>>All</option>
<option value="Male" <?= $allowed['gender']==='Male'?'selected':'' ?>>Male</option>
<option value="Female" <?= $allowed['gender']==='Female'?'selected':'' ?>>Female</option>
</select>

<button type="submit">Update Event</button>

</form>
</div>

<script>
const allBox = document.getElementById("audienceAll");
const streamBoxes = document.querySelectorAll("input[name='audience_streams[]']");

allBox.addEventListener("change", () => {
    streamBoxes.forEach(cb => cb.checked = allBox.checked);
});

streamBoxes.forEach(cb => {
    cb.addEventListener("change", () => {
        allBox.checked = [...streamBoxes].every(c => c.checked);
    });
});
</script>

</body>
</html>
