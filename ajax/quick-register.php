<?php
/**
 * =====================================================
 * WayMate - Quick Registration (AJAX Handler)
 * =====================================================
 * Handles modal popup registration
 * =====================================================
 */

require_once '../includes/config.php';

// Set header for JSON response
header('Content-Type: application/json');

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and sanitize input
$name = clean_input($_POST['name']);
$phone = clean_input($_POST['phone']);
$password = $_POST['password'];

// Validation
if (empty($name) || empty($phone) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required!']);
    exit;
}

if (strlen($phone) != 10 || !is_numeric($phone)) {
    echo json_encode(['success' => false, 'message' => 'Phone number must be exactly 10 digits!']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters!']);
    exit;
}

// Check if phone already exists
$check_sql = "SELECT user_id FROM users WHERE phone = ?";
$stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($stmt, "s", $phone);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    echo json_encode(['success' => false, 'message' => 'Phone number already registered! Please login.']);
    exit;
}

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$insert_sql = "INSERT INTO users (name, phone, password_hash, phone_verified, role) 
              VALUES (?, ?, ?, 0, 'passenger')";
$stmt = mysqli_prepare($conn, $insert_sql);
mysqli_stmt_bind_param($stmt, "sss", $name, $phone, $password_hash);

if (mysqli_stmt_execute($stmt)) {
    // Auto-login
    $user_id = mysqli_insert_id($conn);
    $_SESSION['user_id'] = $user_id;
    $_SESSION['name'] = $name;
    $_SESSION['role'] = 'passenger';
    $_SESSION['phone'] = $phone;
    
    echo json_encode([
        'success' => true, 
        'message' => '🎉 Account created! Redirecting to dashboard...'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Registration failed! Please try again.'
    ]);
}
?>