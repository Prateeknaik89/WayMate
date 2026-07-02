<?php
// --- THE LINK: Read the .env file ---
$envFilePath = __DIR__ . '/.env'; // Looks for .env in the same folder

if (file_exists($envFilePath)) {
    // Read the file line by line
    $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip any comments in the .env file
        if (strpos(trim($line), '#') === 0) continue; 
        
        // Split the line into Name and Value
        list($name, $value) = explode('=', $line, 2);
        
        // Save it into PHP's secret $_ENV vault
        $_ENV[trim($name)] = trim($value); 
    }
}

// --- NOW WE CAN USE THE API KEY ---
$apiKey = $_ENV['GEOAPIFY_API_KEY'];

// Get the coordinates sent from JavaScript
$lat = isset($_GET['lat']) ? $_GET['lat'] : '';
$lng = isset($_GET['lng']) ? $_GET['lng'] : '';

if (empty($lat) || empty($lng)) {
    echo json_encode(["error" => "Missing coordinates"]);
    exit;
}

// The server talks to Geoapify using the hidden key
$url = "https://api.geoapify.com/v1/geocode/reverse?lat={$lat}&lon={$lng}&apiKey={$apiKey}";

$response = file_get_contents($url);

header('Content-Type: application/json');
echo $response;
?>