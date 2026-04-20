<?php
session_start();
require_once "../db.php";
require_once "../includes/gemini_config.php";

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','faculty'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['event_id'])) {
    echo json_encode(['error' => 'Event ID missing']);
    exit;
}

$event_id = intval($_GET['event_id']);

$stmt = $conn->prepare("
    SELECT title, event_date, venue, target_audience, speaker, notes
    FROM events WHERE id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo json_encode(['error' => 'Event not found']);
    exit;
}

$prompt = "
Write a formal post-event summary for a college event.

Rules:
- Formal academic tone
- Do not invent facts
- 2–3 paragraphs max

Event Title: {$event['title']}
Date: {$event['event_date']}
Venue: {$event['venue']}
Target Audience: {$event['target_audience']}
Speaker: {$event['speaker']}
Notes: {$event['notes']}
";

$payload = [
    "contents" => [[
        "parts" => [["text" => $prompt]]
    ]]
];

$ch = curl_init(GEMINI_API_URL . "?key=" . GEMINI_API_KEY);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

if (!$text) {
    echo json_encode(['error' => 'AI generation failed']);
    exit;
}

echo json_encode([
    'success' => true,
    'summary' => $text
]);
