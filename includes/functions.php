<?php
// Common utility functions

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateOTP() {
    return sprintf("%06d", mt_rand(1, 999999));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: pages/login.php');
        exit();
    }
}

function getUserData($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function calculateCostShare($total_cost, $total_passengers) {
    return round($total_cost / ($total_passengers + 1), 2); // +1 for driver
}

function formatTime($time) {
    return date('g:i A', strtotime($time));
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function showAlert($message, $type = 'info') {
    return "<div class='alert alert-{$type} mb-4 p-4 rounded-lg'>{$message}</div>";
}
?>