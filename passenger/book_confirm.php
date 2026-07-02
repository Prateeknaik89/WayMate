<?php
require_once '../config/db.php';
include '../includes/header.php';

if (!isset($_GET['ride_id'])) {
    header("Location: dashboard.php");
    exit();
}

$ride_id = $_GET['ride_id'];
$user_id = $_SESSION['user_id'];

// Fetch ride details for the confirmation screen
$query = "SELECT r.*, u.name as driver_name, sl.location_name as source, dl.location_name as dest 
          FROM rides r 
          JOIN users u ON r.driver_id = u.user_id 
          JOIN locations sl ON r.source_id = sl.location_id
          JOIN locations dl ON r.destination_id = dl.location_id
          WHERE r.ride_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$ride_id]);
$ride = $stmt->fetch();

if (!$ride) { die("Ride not found."); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .btn-confirm { transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    </style>
</head>
<body class="bg-gray-950 text-slate-200 min-h-screen">

<main class="max-w-md mx-auto px-6 py-12">
    <a href="search_results.php" class="text-xs font-black text-slate-500 uppercase tracking-widest hover:text-indigo-400 transition mb-10 inline-block">
        ← Change Selection
    </a>

    <div class="glass-card rounded-[2.5rem] p-8 shadow-2xl">
        <div class="text-center mb-8">
            <div class="inline-block p-4 bg-indigo-500/10 rounded-3xl mb-4">
                <span class="text-3xl text-indigo-400">🎫</span>
            </div>
            <h1 class="text-2xl font-extrabold text-white">Confirm Booking</h1>
            <p class="text-slate-400 text-sm mt-1">Requesting a seat with <?php echo htmlspecialchars($ride['driver_name']); ?></p>
        </div>

        <div class="space-y-6 mb-10">
            <div class="flex justify-between items-center border-b border-white/5 pb-4">
                <span class="text-xs font-bold text-slate-500 uppercase">Route</span>
                <span class="font-bold text-white"><?php echo $ride['source']; ?> → <?php echo $ride['dest']; ?></span>
            </div>
            <div class="flex justify-between items-center border-b border-white/5 pb-4">
                <span class="text-xs font-bold text-slate-500 uppercase">Date & Time</span>
                <span class="font-bold text-white"><?php echo date('M d', strtotime($ride['ride_date'])); ?> @ <?php echo date('h:i A', strtotime($ride['ride_time'])); ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs font-bold text-slate-500 uppercase">Fare</span>
                <span class="text-xl font-black text-indigo-400">₹<?php echo $ride['price_per_seat']; ?></span>
            </div>
        </div>

        <button id="confirm-btn" onclick="processBooking(<?php echo $ride_id; ?>)" 
                class="btn-confirm w-full bg-indigo-600 hover:bg-indigo-500 text-white py-5 rounded-2xl font-black text-lg shadow-xl shadow-indigo-500/20 active:scale-95">
            Confirm & Request
        </button>
        
        <p class="text-[10px] text-center text-slate-500 mt-6 uppercase tracking-widest leading-relaxed">
            By clicking confirm, a request will be sent to the driver for approval.
        </p>
    </div>
</main>

<script>
function processBooking(rideId) {
    const btn = document.getElementById('confirm-btn');
    
    // 1. Initial Scale Down (Click feedback)
    btn.classList.add('scale-95');
    btn.innerHTML = 'Processing...';
    btn.disabled = true;

    // 2. Fire the AJAX call to our existing folder
    fetch(`../ajax/book_ride_ajax.php?ride_id=${rideId}`)
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // 3. Success Animation (Pulse & Pop)
            setTimeout(() => {
                btn.classList.remove('bg-indigo-600', 'scale-95');
                btn.classList.add('bg-emerald-500', 'scale-105', 'shadow-emerald-500/40');
                btn.innerHTML = 'Request Sent! 🚀';
                
                // Redirect to dashboard after a short delay
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1500);
            }, 600);
        } else {
            alert("Error: " + data.message);
            btn.classList.remove('scale-95');
            btn.innerHTML = 'Confirm & Request';
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error("Booking Error:", err);
        btn.disabled = false;
    });
}
</script>

</body>
</html>