<?php
session_start();
require_once '../config/db.php';

// In a real app, you would add an Admin Security Check here!
// if ($_SESSION['role'] !== 'admin') { exit(); }

// Fetch all reviews, joining the driver, passenger, and custom route data
$query = "SELECT r.*, 
                 d.name as driver_name, 
                 p.name as passenger_name, 
                 COALESCE(b.pickup_location, sl.location_name) as pickup, 
                 COALESCE(b.dropoff_location, dl.location_name) as dropoff
          FROM reviews r
          JOIN users d ON r.driver_id = d.user_id
          JOIN users p ON r.reviewer_id = p.user_id
          JOIN bookings b ON r.booking_id = b.booking_id
          JOIN rides trip ON b.ride_id = trip.ride_id
          JOIN locations sl ON trip.source_id = sl.location_id
          JOIN locations dl ON trip.destination_id = dl.location_id
          ORDER BY r.review_id DESC"; // Assuming review_id is auto-incrementing

$stmt = $pdo->query($query);
$all_reviews = $stmt->fetchAll();

include 'admin_header.php';
?>

<main class="max-w-6xl mx-auto px-6 py-12">
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Admin Vault 🛡️</h1>
            <p class="text-slate-500 font-medium">Confidential Passenger Feedback & Ratings</p>
        </div>
        <div class="bg-indigo-50 text-indigo-600 px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest border border-indigo-100 shadow-sm">
            Total Reviews: <?php echo count($all_reviews); ?>
        </div>
    </div>

    <?php if (empty($all_reviews)): ?>
        <div class="bg-white border-2 border-dashed border-slate-200 rounded-[3rem] p-20 text-center">
            <span class="text-4xl grayscale opacity-50 block mb-4">📭</span>
            <p class="text-slate-800 font-black text-xl">No reviews yet</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($all_reviews as $review): ?>
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 relative overflow-hidden hover:shadow-xl transition-all">
                    
                    <div class="absolute top-0 left-0 right-0 h-1.5 <?php echo $review['rating'] >= 4 ? 'bg-emerald-400' : ($review['rating'] == 3 ? 'bg-amber-400' : 'bg-rose-500'); ?>"></div>
                    
                    <div class="flex justify-between items-start mb-6 mt-2">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 font-black text-xl">
                                <?php echo strtoupper(substr($review['driver_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Driver</p>
                                <h2 class="text-xl font-black text-slate-800 leading-none"><?php echo htmlspecialchars($review['driver_name']); ?></h2>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100 shadow-sm text-sm tracking-widest">
                            <?php echo str_repeat('⭐', $review['rating']); ?>
                        </div>
                    </div>

                    <?php if (!empty($review['comment'])): ?>
                        <div class="bg-slate-50 p-5 rounded-2xl mb-6 border border-slate-100 shadow-inner">
                            <p class="text-slate-700 italic font-medium">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                        </div>
                    <?php else: ?>
                        <div class="mb-6">
                            <p class="text-slate-400 italic text-sm">No written comment provided.</p>
                        </div>
                    <?php endif; ?>

                    <div class="flex items-center justify-between border-t border-dashed border-slate-200 pt-5 mt-auto">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Reviewed By</p>
                            <p class="text-xs font-bold text-slate-800"><?php echo htmlspecialchars($review['passenger_name']); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Trip Route</p>
                            <div class="flex items-center gap-1.5 justify-end">
                                <span class="text-xs font-bold text-slate-600"><?php echo htmlspecialchars($review['pickup']); ?></span>
                                <span class="text-indigo-400 text-xs">→</span>
                                <span class="text-xs font-bold text-slate-600"><?php echo htmlspecialchars($review['dropoff']); ?></span>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>