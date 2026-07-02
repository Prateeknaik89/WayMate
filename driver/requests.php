<?php
require_once '../config/db.php';
include '../includes/header.php';

$driver_id = $_SESSION['user_id'];

// Fetch all PENDING bookings for this driver's rides
$query = "SELECT b.booking_id, b.status, b.booking_date,
                 u.name as passenger_name, u.phone as passenger_phone,
                 sl.location_name as source, dl.location_name as dest, 
                 r.ride_date, r.ride_time
          FROM bookings b
          JOIN rides r ON b.ride_id = r.ride_id
          JOIN users u ON b.passenger_id = u.user_id
          JOIN locations sl ON r.source_id = sl.location_id
          JOIN locations dl ON r.destination_id = dl.location_id
          WHERE r.driver_id = ? AND b.status = 'pending'
          ORDER BY b.booking_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$driver_id]);
$requests = $stmt->fetchAll();
?>

<main class="max-w-3xl mx-auto px-6 py-10">
    <div class="mb-10">
        <h1 class="text-3xl font-black text-slate-800 mb-2">Ride Requests</h1>
        <p class="text-slate-500 font-medium italic text-sm">Review who wants to join your journey.</p>
    </div>

    <?php if(!$requests): ?>
        <div class="bg-white border-2 border-dashed border-slate-200 rounded-[3rem] p-16 text-center">
            <div class="text-4xl mb-4">🧊</div>
            <p class="text-slate-400 font-bold">No pending requests at the moment.</p>
            <a href="dashboard.php" class="text-indigo-600 text-xs font-black uppercase mt-4 inline-block hover:underline">Back to Dashboard</a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach($requests as $req): ?>
                <div id="request-row-<?= $req['booking_id'] ?>" class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 transition-all duration-500">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 font-black">
            <?= strtoupper(substr($req['passenger_name'], 0, 1)) ?>
        </div>
        <div>
            <h3 class="font-black text-slate-800"><?= htmlspecialchars($req['passenger_name']) ?></h3>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                <?= $req['source'] ?> → <?= $req['dest'] ?>
            </p>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button onclick="handleRequest(<?= $req['booking_id'] ?>, 'reject')" 
                class="px-6 py-3 bg-slate-50 text-slate-400 rounded-2xl font-black text-xs hover:bg-rose-50 hover:text-rose-500 transition">
            Decline
        </button>
        
        <button onclick="handleRequest(<?= $req['booking_id'] ?>, 'accept')" 
                class="px-6 py-3 bg-indigo-600 text-white rounded-2xl font-black text-xs shadow-lg shadow-indigo-100 hover:bg-slate-900 transition">
            Accept Passenger
        </button>
    </div>
</div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script>
function handleRequest(bookingId, action) {
    const row = document.getElementById(`request-row-${bookingId}`);
    
    // 1. Give the user immediate feedback (fade the row)
    row.style.opacity = '0.5';
    row.style.pointerEvents = 'none';

    // 2. The AJAX call
    // Change your fetch line to this:
    fetch(`/waymate/ajax/handle_request_ajax.php?id=${bookingId}&action=${action}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 3. Success! Slide the card out of view
            row.style.transform = 'translateX(100px)';
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 500);
        } else {
            alert("Error: " + data.message);
            row.style.opacity = '1';
            row.style.pointerEvents = 'auto';
        }
    })
    .catch(err => {
        console.error("AJAX Error:", err);
        row.style.opacity = '1';
        row.style.pointerEvents = 'auto';
    });
}
</script>

<?php include '../includes/footer.php'; ?>