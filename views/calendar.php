<?php
// fetch events from database (returns an array of dates)
require_once "../db.php";

$eventDates = [];

$sql = "SELECT event_date FROM events";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $eventDates[] = $row["event_date"];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Calendar</title>
    <style>
        body { font-family: Arial; }
        .calendar {
            width: 90%;
            max-width: 800px;
            margin: auto;
        }
        .month {
            text-align: center;
            padding: 20px 0;
            font-size: 28px;
            font-weight: bold;
        }
        .days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }
        .day-name, .day {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .day-name {
            background: #f5f5f5;
            font-weight: bold;
        }
        .day:hover {
            background: #e8e8e8;
            cursor: pointer;
        }

        .today {
            background: #ffb3b3 !important;
        }
        .has-event {
            background: #a3d8ff !important;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="calendar">
    <div class="month" id="month-title"></div>
    <div class="days" id="day-names"></div>
    <div class="days" id="calendar-days"></div>
</div>

<script>
let eventDates = <?php echo json_encode($eventDates); ?>;

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

    // Empty boxes before first day
    for (let i = 0; i < firstDay; i++) {
        calendarDays.innerHTML += `<div></div>`;
    }

    // Days rendering
    for (let day = 1; day <= lastDate; day++) {
        let fullDate = `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;

        let classes = "day";

        let today = getToday();

        let isPast = fullDate < today;
        let hasEvent = eventDates.includes(fullDate);
        let isToday = fullDate === today;

        if (isToday) classes += " today";
        if (hasEvent) classes += " has-event";

        // CASE 1: Past + has event → CLICKABLE (view summary)
        if (isPast && hasEvent) {
            calendarDays.innerHTML += `
                <div class="${classes}" onclick="openSummary('${fullDate}')">
                    ${day}
                </div>
            `;
            continue;
        }

        // CASE 2: Past + no event → not clickable
        if (isPast && !hasEvent) {
            calendarDays.innerHTML += `
                <div class="${classes}" style="opacity:0.4; cursor:not-allowed;">
                    ${day}
                </div>
            `;
            continue;
        }

        // CASE 3: Future + today → create event
        calendarDays.innerHTML += `
            <div class="${classes}" onclick="openEventForm('${fullDate}')">
                ${day}
            </div>
        `;
    }
}

function getToday() {
    let t = new Date();
    return `${t.getFullYear()}-${String(t.getMonth() + 1).padStart(2,"0")}-${String(t.getDate()).padStart(2,"0")}`;
}

function prevMonth() {
    date.setMonth(date.getMonth() - 1);
    renderCalendar();
}

function nextMonth() {
    date.setMonth(date.getMonth() + 1);
    renderCalendar();
}

function openEventForm(date) {
    window.location.href = `create_event_form.php?date=${date}`;
}

function openSummary(date) {
    window.location.href = `view_event_summary.php?date=${date}`;
}

renderCalendar();
</script>

</body>
</html>
