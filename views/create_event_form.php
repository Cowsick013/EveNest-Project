<?php
session_start();
require_once "../includes/flash.php";

if (!isset($_SESSION['role']) || !in_array(strtolower($_SESSION['role']), ['admin', 'faculty'])) {
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

<style>
:root {
  --bg: #96bade;
  --card: #ffffff;
  --primary: #2563eb;
  --primary-dark: #1e40af;
  --text: #0f172a;
  --muted: #64748b;
  --border: #e2e8f0;
}

* { box-sizing: border-box; }

body {
  font-family: "Segoe UI", system-ui, sans-serif;
  background: var(--bg);
  margin: 0;
  padding: 40px 20px;
  color: var(--text);
}

.form-wrapper {
  display: flex;
  justify-content: center;
}

.form-card {
  width: 100%;
  max-width: 900px;
  background: var(--card);
  border-radius: 18px;
  padding: 36px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}

.form-card h2 {
  font-size: 26px;
  margin-bottom: 6px;
}

.subtitle {
  font-size: 14px;
  color: var(--muted);
  margin-bottom: 32px;
}

label {
  font-size: 13px;
  color: var(--muted);
  margin-top: 16px;
  display: block;
}

input, textarea, select {
  width: 100%;
  padding: 12px 14px;
  border-radius: 10px;
  border: 1px solid var(--border);
  font-size: 14px;
  margin-top: 6px;
}

input:focus, textarea:focus, select:focus {
  outline: none;
  border-color: var(--primary);
}

textarea {
  resize: vertical;
  min-height: 90px;
}

hr {
  margin: 36px 0;
  border: none;
  border-top: 1px solid var(--border);
}

/* SECTIONS */
.form-section {
  margin-top: 32px;
  padding-top: 14px;
  border-top: 1px solid var(--border);
}

.form-section h3 {
  font-size: 16px;
  margin-bottom: 14px;
}

.required { color: red; }

/* CHECKBOX GRID */
.checkbox-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 12px;
}

.checkbox-grid label {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f8fafc;
  border: 1px solid var(--border);
  padding: 10px 12px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 13px;
}

.checkbox-grid input {
  margin: 0;
}

/* PARTICIPATION BOX */
#participationBox {
  margin-top: 20px;
  background: #f8fafc;
  border: 1px solid var(--border);
  padding: 16px;
  border-radius: 12px;
}

/* BUTTONS */
button[type="button"] {
  margin-top: 12px;
  background: #e0e7ff;
  color: var(--primary);
  border: none;
  padding: 10px;
  border-radius: 10px;
  cursor: pointer;
}

button[type="submit"] {
  margin-top: 40px;
  width: 100%;
  background: var(--primary);
  color: white;
  padding: 15px;
  font-size: 15px;
  border-radius: 14px;
  border: none;
  cursor: pointer;
}

button[type="submit"]:hover {
  background: var(--primary-dark);
}

.text-muted {
  font-size: 12px;
  color: var(--muted);
  margin-top: 8px;
}

.hidden { display: none; }
</style>
</head>

<body>

<div class="form-wrapper">
<div class="form-card">

<h2>Create Event – <?= htmlspecialchars($selectedDate); ?></h2>
<p class="subtitle">Fill in the event details carefully</p>

<form action="../controllers/save_event.php" method="POST">

<input type="hidden" name="event_date" value="<?= $selectedDate ?>">

<label>Event Title</label>
<input type="text" name="event_title" required>

<label>Description</label>
<textarea name="description" required></textarea>

<label>Start Time</label>
<input type="time" name="time_from" required>

<label>End Time</label>
<input type="time" name="time_to" required>

<label>Venue</label>
<input type="text" name="venue" required>

<hr>

<div class="form-section">
<h3>Organizers</h3>
<div class="checkbox-grid">
<?php foreach (["BCA","BCOM","BBA","GMFC","LIBRARY","Sports","NSS","Student Council","IQAC"] as $o): ?>
<label><input type="checkbox" name="organizers[]" value="<?= $o ?>"> <?= $o ?></label>
<?php endforeach; ?>
</div>

<label>Other Organizer (optional)</label>
<input type="text" name="organizer_other">
</div>

<div class="form-section">
<h3>Target Audience <span class="required">*</span></h3>

<div class="checkbox-grid">
<label><input type="checkbox" class="audience" value="BCA"> BCA</label>
<label><input type="checkbox" class="audience" value="BBA"> BBA</label>
<label><input type="checkbox" class="audience" value="BCOM"> BCOM</label>
<label><input type="checkbox" id="audienceAll"> All Students</label>
<label><input type="checkbox" id="audienceFaculty"> Faculty</label>
</div>

<p class="text-muted">Select who the event is intended for (not attendance)</p>

<label>Other Audience (optional)</label>
<input type="text" name="audience_other">
</div>

<div class="form-section hidden" id="yearSection">
<h3>Target Year <span class="required">*</span></h3>
<div class="checkbox-grid">
<label><input type="checkbox" name="target_year[]" value="FY"> FY</label>
<label><input type="checkbox" name="target_year[]" value="SY"> SY</label>
<label><input type="checkbox" name="target_year[]" value="TY"> TY</label>
</div>
</div>

<div class="form-section hidden" id="genderSection">
<h3>Gender Eligibility</h3>
<select name="target_gender">
<option value="All">All</option>
<option value="Male">Male</option>
<option value="Female">Female</option>
</select>
</div>

<div class="form-section">
<label>
<input type="checkbox" id="is_participatable" name="is_participatable" value="1">
<b>This event requires student participation</b>
</label>

<div id="participationBox" class="hidden">
<label>Expected Participants</label>
<input type="number" name="expected_participants">

<label>Participation Type</label>
<select name="participation_type">
<option value="Individual">Individual</option>
<option value="Team">Team</option>
</select>

<label>Rounds / Instructions</label>
<textarea name="rounds_info"></textarea>

<label>Registration Deadline</label>
<input type="date" name="registration_deadline">

<label>Participant Limit (Optional)</label>
<input type="number" name="max_participants">
</div>
</div>

<hr>

<label>Guest / Speaker (optional)</label>
<input type="text" name="speaker">

<label>Event Incharge / Coordinator</label>
<input type="text" name="incharge" required>

<label>Jr. Coordinator (optional)</label>
<input type="text" name="jr_coordinator">

<label>Notes / Instructions</label>
<textarea name="notes"></textarea>

<hr>

<h3>📍 Event Location (Mandatory)</h3>

<label>Latitude</label>
<input type="text" id="latitude" name="latitude" required>

<label>Longitude</label>
<input type="text" id="longitude" name="longitude" required>

<label>Radius (meters)</label>
<input type="number" name="radius" value="150" min="50" max="500" required>

<button type="button" onclick="getLocation()">Use My Current Location</button>

<button type="submit">Save Event</button>

</form>
</div>
</div>

<script>
function getLocation() {
  navigator.geolocation.getCurrentPosition(pos => {
    latitude.value = pos.coords.latitude;
    longitude.value = pos.coords.longitude;
  });
}

document.getElementById("is_participatable").addEventListener("change", e => {
  participationBox.classList.toggle("hidden", !e.target.checked);
});

const audienceBoxes = document.querySelectorAll(".audience");
const allBox = document.getElementById("audienceAll");
const facultyBox = document.getElementById("audienceFaculty");

function toggleStudentSections() {
  const anyStudent = [...audienceBoxes].some(cb => cb.checked);
  yearSection.classList.toggle("hidden", !anyStudent || facultyBox.checked);
  genderSection.classList.toggle("hidden", !anyStudent || facultyBox.checked);
}

allBox.addEventListener("change", () => {
  audienceBoxes.forEach(cb => cb.checked = allBox.checked);
  toggleStudentSections();
});

audienceBoxes.forEach(cb => cb.addEventListener("change", toggleStudentSections));

facultyBox.addEventListener("change", () => {
  if (facultyBox.checked) {
    audienceBoxes.forEach(cb => cb.checked = false);
    allBox.checked = false;
  }
  toggleStudentSections();
});
</script>

</body>
</html>
