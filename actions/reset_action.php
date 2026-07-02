<?php
session_start();

// Step out of the 'actions' folder and into the 'config' folder
require_once '../config/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];

    try {
        // 1. Check if a user exists with this exact phone AND email combination
        // Using $pdo instead of $dsn
        $stmt = $pdo->prepare("SELECT user_id FROM USERS WHERE phone_number = ? AND email = ?");
        
        // PDO executes the variables directly in an array
        $stmt->execute([$phone, $email]); 
        $user = $stmt->fetch(); // Fetch the result

        if ($user) {
            // User verified! Now hash the new password for security
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // 2. Update the password in the database
            $update_stmt = $pdo->prepare("UPDATE USERS SET password_hash = ? WHERE phone_number = ? AND email = ?");
            
            // Execute the update securely
            if ($update_stmt->execute([$hashed_password, $phone, $email])) {
                // Password updated successfully
                echo "<script>
                        alert('Password successfully reset! You can now login.');
                        window.location.href = '../login.php';
                      </script>";
            } else {
                // Database update failed
                echo "<script>
                        alert('System Error: Could not update password. Please try again.');
                        window.history.back();
                      </script>";
            }
        } else {
            // Verification failed (wrong phone or email)
            echo "<script>
                    alert('Verification Failed: Phone number and email do not match our records.');
                    window.history.back();
                  </script>";
        }
        
    } catch (PDOException $e) {
        // This will catch any PDO database errors and show them safely
        echo "<script>
                alert('Database Error: " . addslashes($e->getMessage()) . "');
                window.history.back();
              </script>";
    }
    
    // Close the connection
    $pdo = null; 
}
?>