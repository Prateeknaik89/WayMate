<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $role = $_POST['role']; // This is 'driver' or 'passenger'

    try {
        // Check if phone exists
        $check = $pdo->prepare("SELECT user_id FROM users WHERE phone = ?");
        $check->execute([$phone]);
        if ($check->rowCount() > 0) {
            header("Location: ../index.php?error=phone_taken");
            exit();
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert including the ROLE
        $sql = "INSERT INTO users (name, phone, password_hash, role, status) VALUES (?, ?, ?, ?, 'active')";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$name, $phone, $hashed_password, $role])) {
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['role'] = $role; // Save role in session

            // Redirect based on role
            if ($role === 'driver') {
                header("Location: ../driver/dashboard.php");
            } else {
                header("Location: ../passenger/dashboard.php");
            }
            exit();
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}