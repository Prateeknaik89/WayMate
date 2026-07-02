<?php
session_start();
require_once '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $driver_id = $_SESSION['user_id'];
    $source_name = $_POST['source_text']; // Name from API
    $dest_name = $_POST['dest_text'];     // Name from API
    $date = $_POST['date'];
    $time = $_POST['time'];
    $seats = $_POST['seats'];
    $price = $_POST['price'];

    try {
        $pdo->beginTransaction();

        // 1. Check if source exists in locations table, if not, add it
        $stmt = $pdo->prepare("INSERT IGNORE INTO locations (location_name) VALUES (?)");
        $stmt->execute([$source_name]);
        $source_id = $pdo->lastInsertId() ?: $pdo->query("SELECT location_id FROM locations WHERE location_name = '$source_name'")->fetchColumn();

        // 2. Check if destination exists
        $stmt->execute([$dest_name]);
        $dest_id = $pdo->lastInsertId() ?: $pdo->query("SELECT location_id FROM locations WHERE location_name = '$dest_name'")->fetchColumn();

        // 3. Insert the ride
        $sql = "INSERT INTO rides (driver_id, source_id, destination_id, ride_date, ride_time, available_seats, price_per_seat, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'available')";
        $stmt_ride = $pdo->prepare($sql);
        
        if ($stmt_ride->execute([$driver_id, $source_id, $dest_id, $date, $time, $seats, $price])) {
            $pdo->commit();
            $message = "success";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offer a Ride | WayMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <div class="max-w-xl mx-auto p-6 py-12">
        
        <div class="mb-10 flex items-center justify-between">
            <a href="dashboard.php" class="text-slate-400 hover:text-slate-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
            <h1 class="text-xl font-black text-slate-800 text-center">Post a Ride</h1>
            <div class="w-6"></div>
        </div>

        <?php if($message == "success"): ?>
            <div class="bg-emerald-50 border border-emerald-100 p-6 rounded-[2rem] text-center mb-8">
                <p class="text-emerald-600 font-bold">Trip posted successfully! 🎉</p>
                <a href="dashboard.php" class="text-xs font-black text-emerald-500 uppercase mt-2 inline-block underline">Back to Dashboard</a>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-8">
            
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">1. The Route</h3>
                <div class="space-y-4 relative">
                    <div class="relative">
                        <input type="text" id="source-input" placeholder="From where?" autocomplete="off" required
                            class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700">
                        <input type="hidden" name="source_text" id="source-text">
                        <div id="source-results" class="absolute w-full mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 hidden overflow-hidden"></div>
                    </div>

                    <div class="relative">
                        <input type="text" id="dest-input" placeholder="To where?" autocomplete="off" required
                            class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700">
                        <input type="hidden" name="dest_text" id="dest-text">
                        <div id="dest-results" class="absolute w-full mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 hidden overflow-hidden"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">2. Date & Time</h3>
                <div class="grid grid-cols-2 gap-4">
                    <input type="date" name="date" required min="<?= date('Y-m-d') ?>" 
                        class="p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700">
                    <input type="time" name="time" required 
                        class="p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700">
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">3. Seats & Pricing</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="relative">
                        <span class="absolute left-4 top-4 text-slate-400 font-bold">💺</span>
                        <input type="number" name="seats" placeholder="Seats" min="1" max="6" required 
                            class="w-full p-4 pl-12 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700">
                    </div>
                    <div class="relative">
                        <span class="absolute left-4 top-4 text-slate-400 font-bold">₹</span>
                        <input type="number" name="price" placeholder="Price" min="0" required 
                            class="w-full p-4 pl-10 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700">
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-[2rem] font-extrabold text-lg shadow-xl shadow-slate-200 hover:bg-indigo-600 transition transform active:scale-95">
                Publish Ride
            </button>
        </form>

    </div>

    <script>
    const GEOAPIFY_API_KEY = 'db1860f6b2bd44a29e66ce1c8bf445fd';

    const setupAutocomplete = (inputId, resultsId, hiddenId) => {
        const input = document.getElementById(inputId);
        const results = document.getElementById(resultsId);
        const hidden = document.getElementById(hiddenId);
        let debounceTimer;

        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value;

            if (query.length > 2) {
                debounceTimer = setTimeout(() => {
                    fetch(`https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(query)}&filter=countrycode:in&format=json&apiKey=${GEOAPIFY_API_KEY}`)
                        .then(res => res.json())
                        .then(data => {
                            results.innerHTML = '';
                            if (data.results && data.results.length > 0) {
                                results.classList.remove('hidden');
                                data.results.forEach(loc => {
                                    const item = document.createElement('div');
                                    item.className = "p-4 hover:bg-indigo-50 cursor-pointer border-b border-slate-50 last:border-0";
                                    const city = loc.city || loc.name || loc.county;
                                    item.innerHTML = `<div class="font-bold text-sm text-slate-700">📍 ${city}</div><div class="text-[10px] text-slate-400">${loc.formatted}</div>`;
                                    
                                    item.onclick = () => {
                                        input.value = city;
                                        hidden.value = city;
                                        results.classList.add('hidden');
                                    };
                                    results.appendChild(item);
                                });
                            }
                        });
                }, 300);
            } else {
                results.classList.add('hidden');
            }
        });
    };

    setupAutocomplete('source-input', 'source-results', 'source-text');
    setupAutocomplete('dest-input', 'dest-results', 'dest-text');
    </script>
</body>
</html>