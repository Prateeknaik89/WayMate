<?php
/**
 * =====================================================
 * WayMate - Database Configuration & Session Management
 * =====================================================
 * This file:
 * 1. Connects to MySQL database
 * 2. Starts PHP session for login system
 * 3. Sets timezone
 * =====================================================
 */

// Database credentials
define('DB_HOST', 'localhost');        // MySQL server (usually localhost)
define('DB_USER', 'root');             // MySQL username (default: root)
define('DB_PASS', '');                 // MySQL password (default: empty for XAMPP)
define('DB_NAME', 'waymate');          // Database name we created

// Create connection to MySQL
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check if connection successful
if (!$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error());
}

// Set character encoding to UTF-8 (supports all languages & special characters)
mysqli_set_charset($conn, "utf8mb4");

// Start PHP session (needed to remember logged-in users across pages)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone to Indian Standard Time
date_default_timezone_set('Asia/Kolkata');

// Optional: Display errors during development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Helper function to sanitize user input (prevent XSS attacks)
 * Usage: $clean_input = clean_input($_POST['username']);
 */
function clean_input($data) {
    $data = trim($data);                    // Remove extra spaces
    $data = stripslashes($data);            // Remove backslashes
    $data = htmlspecialchars($data);        // Convert special chars to HTML entities
    return $data;
}

/**
 * Helper function to check if user is logged in
 * Usage: if (is_logged_in()) { ... }
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Helper function to redirect to another page
 * Usage: redirect('login.php');
 */
function redirect($page) {
    header("Location: $page");
    exit();
}

?>