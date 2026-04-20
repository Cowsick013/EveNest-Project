<?php
define('GEMINI_API_KEY', 'AIzaSyCEhJlBxBLx7SAOLQXTDTZdvyYffMWWtZg');

$payload = [
    "contents" => [
        [
            "parts" => [
                [
                    "text" => "Write a formal academic introduction paragraph for a college event report."
                ]
            ]
        ]
    ]
];

$ch = curl_init(
    "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . GEMINI_API_KEY
);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);

if ($response === false) {
    die("CURL ERROR: " . curl_error($ch));
}

curl_close($ch);

echo "<pre>";
echo htmlspecialchars($response);
echo "</pre>";


//AIzaSyCEhJlBxBLx7SAOLQXTDTZdvyYffMWWtZg
?>