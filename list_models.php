<?php
define('GEMINI_API_KEY', 'AIzaSyCEhJlBxBLx7SAOLQXTDTZdvyYffMWWtZg');

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . GEMINI_API_KEY;

$response = file_get_contents($url);

echo "<pre>";
echo $response;
echo "</pre>";



//AIzaSyCEhJlBxBLx7SAOLQXTDTZdvyYffMWWtZg

