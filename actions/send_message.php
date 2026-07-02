<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Unauthorized');
}

$sender_id = $_SESSION['user_id'];
$booking_id = $_POST['booking_id'];
$receiver_id = $_POST['receiver_id'];
$message = trim($_POST['message']);

if (!empty($message)) {
    $stmt = $pdo->prepare("INSERT INTO messages (booking_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$booking_id, $sender_id, $receiver_id, $message]);
}

// 🚀 BULLETPROOF AJAX CHECK: Look for the hidden flag we sent from JS
if(isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    echo "success";
    exit();
}

// Fallback just in case
header("Location: ../shared/chat.php?booking_id=" . $booking_id);
exit();
?>