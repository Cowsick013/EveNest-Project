<?php
define('GEMINI_API_KEY', 'AIzaSyCEhJlBxBLx7SAOLQXTDTZdvyYffMWWtZg');

function generate_gemini_report(array $event)
{
    $title = $event['title'] ?? '';
    $date = $event['event_date'] ?? '';
    $venue = $event['venue'] ?? '';
    $target = $event['target_audience'] ?? 'Students and Faculty';
    $speaker = $event['speaker'] ?? 'Resource Person';
    $incharge = $event['incharge'] ?? 'Faculty Coordinator';
    $remarks = $event['dignitaries_words'] ?? '';

    $prompt = <<<PROMPT
Generate a formal college post-event report based on the following event details.


Event:
Title: $title
Date: $date
Venue: $venue
Audience: $target
Speaker: $speaker
In-charge: $incharge

STRICT FORMAT:
 
INTRODUCTION
OBJECTIVES OF THE EVENT
EVENT PLANNING AND EXECUTION
PARTICIPATION AND ATTENDANCE
KEY HIGHLIGHTS
OUTCOME OF THE EVENT
CONCLUSION

Rules:
- Follow standard academic report writing style.
- Paragraphs only
- No bullets
- Minimum 3–4 lines per section
PROMPT;

    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ]
    ];

    $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . GEMINI_API_KEY);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        return "CURL ERROR: " . curl_error($ch);
    }

    curl_close($ch);

    $data = json_decode($response, true);

    return $data['candidates'][0]['content']['parts'][0]['text']
        ?? "AI report generation failed. Please regenerate.";
}


//AIzaSyCx1UQYzx_j7epse3eADKeOeClAVb3hFIY