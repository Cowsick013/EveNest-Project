<?php
require_once "../db.php";
include "../includes/header.php";

/* Auth check */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

/* --------------------------------
   FETCH EVENT DATES (FOR CALENDAR)
---------------------------------*/
$eventDates = [];

$resDates = $conn->query("
    SELECT DISTINCT event_date
    FROM events
    WHERE status = 'active'
");

if ($resDates) {
    while ($r = $resDates->fetch_assoc()) {
        $eventDates[] = $r['event_date'];
    }
}

/* --------------------------------
   FETCH EVENTS FOR SIDE TAB
---------------------------------*/
$sideTabEvents = [];

$res = $conn->query("
    SELECT id, title, event_date, time_from, time_to
    FROM events
    WHERE status = 'active'
    ORDER BY event_date, time_from
");

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $sideTabEvents[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Calendar</title>

    <style>
        .calendar-wrapper {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            max-width: 850px;
            margin: auto;
        }

        .calendar {
            width: 100%;
        }

        .month {
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .month button {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            padding: 0 10px;
        }

        .days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }

        .day-name {
            padding: 12px;
            text-align: center;
            background: #f1f1f1;
            font-weight: 600;
            border: 1px solid #ddd;
        }

        .day {
            padding: 14px;
            text-align: center;
            border: 1px solid #ddd;
            cursor: pointer;
            min-height: 55px;
        }

        .day:hover {
            background: #f0f7ff;
        }

        .today {
            background: #ffebee !important;
            font-weight: bold;
        }

        .past-event {
            background: #c8e6c9 !important;
            font-weight: bold;
        }

        .disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* SIDE TAB */
        .burger-side-tab {
            position: fixed;
            top: 64px; /* navbar height */
            left: -280px;
            width: 260px;
            height: calc(100% - 64px);
            background: #0f172a;
            color: white;
            padding: 15px;
            transition: left 0.3s ease;
            z-index: 1900;
            overflow-y: auto;
        }

        .burger-side-tab h3 {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #334155;
            padding-bottom: 8px;
        }

        .side-item {
            background: #1e293b;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
            cursor: pointer;
            font-size: 14px;
        }

        .side-item:hover {
            background: #334155;
        }
    </style>
</head>

<body>

<div class="calendar-toolbar">
    <select onchange="handleCalendarAction(this.value)">
        <option value="">Quick Actions</option>
        <option value="today">Go to Today</option>
        <option value="past">View Past Events</option>
    </select>
</div>

<div class="page-container">

    <h2 class="page-title center">Event Calendar</h2>

    <div style="text-align:center; margin-bottom:15px; font-size:14px;">
        <span style="background:#ffebee; padding:4px 8px; border-radius:5px;">Today</span>
        &nbsp;
        <span style="background:#c8e6c9; padding:4px 8px; border-radius:5px;">Past Event</span>
    </div>

    <div class="calendar-wrapper">
        <div class="calendar">
            <div class="month" id="month-title"></div>
            <div class="days" id="day-names"></div>
            <div class="days" id="calendar-days"></div>
        </div>
    </div>
</div>

<!-- SIDE TAB -->
<div id="burgerSideTab" class="burger-side-tab">
    <h3>Events</h3>

    <?php if (empty($sideTabEvents)): ?>
        <p style="text-align:center; font-size:13px;">No events</p>
    <?php endif; ?>

    <?php foreach ($sideTabEvents as $e): ?>
        <div class="side-item"
             onclick="window.location.href='view_event.php?id=<?= $e['id'] ?>'">
            <b><?= htmlspecialchars($e['title']) ?></b><br>
            <small><?= date("d M Y", strtotime($e['event_date'])) ?></small>
        </div>
    <?php endforeach; ?>
</div>

<script>
let eventDates = <?= json_encode($eventDates); ?>;
let date = new Date();

function renderCalendar() {
    const monthTitle = document.getElementById("month-title");
    const calendarDays = document.getElementById("calendar-days");
    const dayNames = document.getElementById("day-names");

    dayNames.innerHTML = `
        <div class='day-name'>Sun</div>
        <div class='day-name'>Mon</div>
        <div class='day-name'>Tue</div>
        <div class='day-name'>Wed</div>
        <div class='day-name'>Thu</div>
        <div class='day-name'>Fri</div>
        <div class='day-name'>Sat</div>
    `;

    let year = date.getFullYear();
    let month = date.getMonth();

    monthTitle.innerHTML = `
        <button onclick="prevMonth()">◀</button>
        ${date.toLocaleString('default', { month: 'long' })} ${year}
        <button onclick="nextMonth()">▶</button>
    `;

    let firstDay = new Date(year, month, 1).getDay();
    let lastDate = new Date(year, month + 1, 0).getDate();

    calendarDays.innerHTML = "";

    for (let i = 0; i < firstDay; i++) {
        calendarDays.innerHTML += `<div></div>`;
    }

    let today = getToday();

    for (let d = 1; d <= lastDate; d++) {
        let fullDate = `${year}-${String(month+1).padStart(2,"0")}-${String(d).padStart(2,"0")}`;
        let classes = "day";
        let hasEvent = eventDates.includes(fullDate);

        if (fullDate === today) classes += " today";

        if (fullDate < today && hasEvent) {
            classes += " past-event";
            calendarDays.innerHTML += `<div class="${classes}" onclick="openView('${fullDate}')">${d}</div>`;
        } else if (fullDate < today && !hasEvent) {
            calendarDays.innerHTML += `<div class="${classes} disabled">${d}</div>`;
        } else {
            calendarDays.innerHTML += `<div class="${classes}" onclick="openCreate('${fullDate}')">${d}</div>`;
        }
    }
}

function getToday() {
    let t = new Date();
    return `${t.getFullYear()}-${String(t.getMonth()+1).padStart(2,"0")}-${String(t.getDate()).padStart(2,"0")}`;
}

function prevMonth(){ date.setMonth(date.getMonth()-1); renderCalendar(); }
function nextMonth(){ date.setMonth(date.getMonth()+1); renderCalendar(); }

function openView(date) {
    window.location.href = `view_events_by_date.php?date=${date}`;
}

function openCreate(date){
<?php if (in_array($_SESSION['role'], ['admin','faculty'])): ?>
    window.location.href = `create_event_form.php?date=${date}`;
<?php else: ?>
    window.location.href = `view_events_by_date.php?date=${date}`;
<?php endif; ?>
}

function toggleSideTab() {
    const tab = document.getElementById("burgerSideTab");
    tab.style.left = (tab.style.left === "0px") ? "-280px" : "0px";
}

renderCalendar();

function handleCalendarAction(action) {
    if (action === "today") {
        date = new Date();
        renderCalendar();
    }
    if (action === "past") {
        window.location.href = "view_events_by_date.php?type=past";
    }
}

</script>

<?php include "../includes/footer.php"; ?>
</body>
</html>
