<?php
// ajax/search_locations.php
require_once '../config/db.php';
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$taluku = $_GET['taluku'] ?? '';

if (strlen($q) > 0) {
    if (!empty($taluku)) {
        // Smart Search: Filter by the user's specific Taluku
        $stmt = $pdo->prepare("SELECT location_id, location_name FROM locations WHERE location_name LIKE ? AND taluku = ? LIMIT 5");
        $stmt->execute(["%$q%", $taluku]);
    } else {
        // Global Search: Fallback if they haven't set their profile yet
        $stmt = $pdo->prepare("SELECT location_id, location_name FROM locations WHERE location_name LIKE ? LIMIT 5");
        $stmt->execute(["%$q%"]);
    }
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
} else {
    echo json_encode([]); // Return empty if they typed nothing
}