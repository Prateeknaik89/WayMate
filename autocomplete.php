<?php
// autocomplete.php - The Secure Proxy for the Search Bar

// 1. Open the vault and read the .env file
$envFilePath = __DIR__ . '/.env'; 

if (file_exists($envFilePath)) {
    $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip any comments in the .env file
        if (strpos(trim($line), '#') === 0) continue; 
        
        // Grab the variable name and the value
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value); 
    }
}

// 2. Securely load your API Key from memory
$apiKey = isset($_ENV['GEOAPIFY_API_KEY']) ? $_ENV['GEOAPIFY_API_KEY'] : '';

if (empty($apiKey)) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "API key is missing or not named GEOAPIFY_API_KEY in .env"]);
    exit;
}

// 3. Get the text the user is typing from the JavaScript
$text = isset($_GET['text']) ? urlencode($_GET['text']) : '';

if (empty($text)) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Missing search text"]);
    exit;
}

// 4. Securely ask Geoapify 
// Notice we are using format=json here, which perfectly matches our data.results JS code!
$url = "https://api.geoapify.com/v1/geocode/autocomplete?text={$text}&filter=countrycode:in&format=json&apiKey={$apiKey}";

// 5. Fetch the answer and send it back to the browser
$response = file_get_contents($url);

header('Content-Type: application/json');
echo $response;
?>