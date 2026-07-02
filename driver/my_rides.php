<?php
session_start();
require_once '../config/db.php';
include '../includes/header.php';


// Security: Drivers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../login.php");
    exit();
}

$driver_id = $_SESSION['user_id'];

// Fetch rides with Location Names
$query = "SELECT r.*, s.location_name as source_name, d.location_name as dest_name 
          FROM rides r
          JOIN locations s ON r.source_id = s.location_id
          JOIN locations d ON r.destination_id = d.location_id
          WHERE r.driver_id = ? 
          ORDER BY r.ride_date DESC, r.ride_time DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$driver_id]);
$my_rides = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Offered Rides | WayMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-slate-50 min-h-screen pb-20">


    <div class="max-w-2xl mx-auto p-6">
        <div class="flex items-center justify-between mb-10">
            <a href="dashboard.php" class="text-slate-400 hover:text-slate-900 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-xl font-black text-slate-800">My Posted Trips</h1>
            <a href="../passenger/offer_ride.php" class="bg-indigo-600 text-white p-2 rounded-xl shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </a>
        </div>

        <?php if(empty($my_rides)): ?>
            <div class="text-center py-20">
                <p class="text-slate-400 font-bold">You haven't offered any rides yet.</p>
                <p class="text-xs text-slate-300 uppercase tracking-widest mt-2">Time to earn some gas money!</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach($my_rides as $ride): ?>
                <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm relative overflow-hidden">
                    
                    <div class="absolute top-6 right-6">
                        <span class="text-[10px] font-black uppercase px-3 py-1 rounded-full 
                            <?= $ride['status'] == 'available' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' ?>">
                            <?= $ride['status'] ?>
                        </span>
                    </div>

                    <div class="mb-4">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Route</p>
                        <h2 class="text-lg font-extrabold text-slate-800">
                            <?= $ride['source_name'] ?> 
                            <span class="text-indigo-400 mx-1">→</span> 
                            <?= $ride['dest_name'] ?>
                        </h2>
                    </div>

                    <div class="grid grid-cols-3 gap-4 border-t border-slate-50 pt-4 mt-4">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase">Date</p>
                            <p class="text-sm font-bold text-slate-700"><?= date('D, M d', strtotime($ride['ride_date'])) ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase">Seats</p>
                            <p class="text-sm font-bold text-indigo-600"><?= $ride['available_seats'] ?> Left</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase">Price</p>
                            <p class="text-sm font-bold text-slate-700">₹<?= number_format($ride['price_per_seat']) ?></p>
                        </div>
                    </div>

                    <?php if($ride['status'] == 'available'): ?>
                    <div class="mt-6 flex gap-2">
                        <button class="flex-1 bg-slate-50 text-slate-400 py-3 rounded-xl font-bold text-xs hover:bg-red-50 hover:text-red-500 transition">
                            Cancel Trip
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
 <?php include '../includes/footer.php'; ?>
</body>
</html>