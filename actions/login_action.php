<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    try {
        // 1. Find the user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();

        if ($user) {
            // 2. Check if password is correct
            if (password_verify($password, $user['password_hash'])) {
                
                // 3. Set Session Data
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role']; 

                // 4. Dynamic Redirect
                if ($user['role'] === 'driver') {
                    header("Location: ../driver/dashboard.php");
                } 
                elseif ($user['role'] === 'passenger'){
                    header("Location: ../passenger/dashboard.php");
                }
                else {
                    header("Location: ../admin/dashboard.php");
                }
                exit();
                
            } else {
                // Password incorrect
                $_SESSION['login_error'] = "❌ Wrong password! Please try again.";
                header("Location: ../index.php");
                exit();
            }
        } else {
            // User not found
            $_SESSION['login_error'] = "❌ No account found with this phone number!";
            header("Location: ../index.php");
            exit();
        }

    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['login_error'] = "❌ Something went wrong. Please try again.";
        header("Location: ../index.php");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>

