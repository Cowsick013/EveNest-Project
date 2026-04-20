<?php
session_start();

require_once '../includes/db.php';
require_once '../includes/gemini_config.php';

header('Content-Type: application/json');

// Role check
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'faculty'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!GEMINI_ENABLED) {
    echo json_encode(['error' => 'AI disabled']);
    exit;
}

if (!isset($_GET['event_id'])) {
    echo json_encode(['error' => 'Event ID missing']);
    exit;
}

$event_id = (int) $_GET['event_id'];

// Fetch event
$stmt = $conn->prepare("
    SELECT title, event_date, time_from, time_to, venue,
           target_audience, speaker, incharge, notes, created_by
    FROM events
    WHERE id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Event not found']);
    exit;
}

$event = $result->fetch_assoc();

// Faculty ownership check
if ($_SESSION['role'] === 'faculty' && $_SESSION['user_id'] != $event['created_by']) {
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Attendance count
$countStmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM event_attendance
    WHERE event_id = ? AND status = 'present'
");
$countStmt->bind_param("i", $event_id);
$countStmt->execute();
$attendance = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;

// Prompt
$prompt = "
Write a formal college Event Report using the format below.

1. Event Details
2. Objective of the Event
3. Description of the Event
4. Resource Person / Speaker
5. Participation Details
6. Outcome of the Event
7. Conclusion

Rules:
- Formal academic tone
- Do not invent facts
- No emojis

Event Data:
Title: {$event['title']}
Date: {$event['event_date']}
Time: {$event['time_from']} - {$event['time_to']}
Venue: {$event['venue']}
Target Audience: {$event['target_audience']}
Speaker: {$event['speaker']}
Faculty In-Charge: {$event['incharge']}
Attendance Count: {$attendance}
Notes: {$event['notes']}
";

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ]
];

$ch = curl_init(GEMINI_API_URL . '?key=' . GEMINI_API_KEY);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => GEMINI_TIMEOUT
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

if (!$text) {
    echo json_encode(['error' => 'Generation failed']);
    exit;
}

echo json_encode([
    'success' => true,
    'report' => $text
]);
