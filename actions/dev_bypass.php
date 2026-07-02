<?php
// waymate/actions/dev_bypass.php
session_start();
require_once '../config/db.php';

// 🚨 DEV ONLY: DELETE BEFORE PRODUCTION 🚨

$role = $_GET['role'] ?? 'passenger';

// Check if they want to log in as the Boss (Admin)
if ($role === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE is_admin = 1 LIMIT 1");
} else {
    // Otherwise, grab a normal passenger or driver
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = ? LIMIT 1");
}

$stmt->execute($role === 'admin' ? [] : [$role]);
$user = $stmt->fetch();

if ($user) {
    // Set the session variables (This IS the login process)
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['user_name'] = $user['name'];

    // Teleport to the correct dashboard
    if ($role === 'driver') {
        header("Location: ../driver/dashboard.php");
    } elseif ($role === 'passenger') {
        header("Location: ../passenger/dashboard.php");
    } else {
        header("Location: ../admin/dashboard.php"); // For admin
    }
    exit();
} else {
    die("Bro, there is no user with the role '{$role}' in your database! Register one normally first.");
}
?>