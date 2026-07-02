<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// STEP 1: If user clicks "Send OTP"
if (isset($_POST['send_otp'])) {
    $otp = rand(100000, 999999);
    $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    $stmt = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE user_id = ?");
    $stmt->execute([$otp, $expires, $user_id]);
    
    // For demo purposes, we "simulate" the SMS by putting it in a session to show an alert
    $_SESSION['demo_otp'] = $otp; 
    $success = "OTP sent successfully!";
}

// STEP 2: If user submits the OTP to verify
if (isset($_POST['verify_now'])) {
    $entered_otp = $_POST['otp_input'];

    $stmt = $pdo->prepare("SELECT otp_code, otp_expires_at FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();

    if ($row && $row['otp_code'] === $entered_otp && strtotime($row['otp_expires_at']) > time()) {
        // Success! Mark profile as completed
        $update = $pdo->prepare("UPDATE users SET phone_verified = 1, profile_completed = 1, profile_completion_percentage = 100 WHERE user_id = ?");
        $update->execute([$user_id]);
        
        header("Location: dashboard.php?verified=true");
        exit();
    } else {
        $error = "Invalid or expired OTP. Try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Profile | WayMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">

    <div class="bg-white w-full max-w-md rounded-[2.5rem] p-10 shadow-xl border border-slate-100">
        <h2 class="text-2xl font-black text-slate-800 mb-2">Verify Phone</h2>
        <p class="text-slate-500 text-sm mb-8">We've sent a 6-digit code to your registered mobile number.</p>

        <?php if($error): ?>
            <div class="mb-4 p-3 bg-red-50 text-red-600 rounded-xl text-xs font-bold text-center border border-red-100"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <script>alert("DEMO MODE: Your OTP is <?php echo $_SESSION['demo_otp']; ?>");</script>
            <div class="mb-4 p-3 bg-emerald-50 text-emerald-600 rounded-xl text-xs font-bold text-center border border-emerald-100"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <?php if(!isset($_SESSION['demo_otp'])): ?>
                <button type="submit" name="send_otp" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold shadow-lg hover:bg-indigo-700 transition">
                    Send Verification Code
                </button>
            <?php else: ?>
                <input type="text" name="otp_input" maxlength="6" placeholder="0 0 0 0 0 0" required 
                    class="w-full text-center text-2xl tracking-[0.5em] font-black p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none transition">
                
                <button type="submit" name="verify_now" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold shadow-lg hover:bg-indigo-700 transition">
                    Verify & Complete Profile
                </button>
                
                <button type="submit" name="send_otp" class="w-full text-slate-400 text-xs font-bold hover:text-indigo-600 transition">
                    Didn't get it? Resend Code
                </button>
            <?php endif; ?>
        </form>
    </div>

</body>
</html>