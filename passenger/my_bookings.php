<?php
require_once '../config/db.php';
include '../includes/header.php';

$passenger_id = $_SESSION['user_id'];

// 🚨 UPGRADED QUERY: Use COALESCE to show the passenger's custom route for every booking!
$query = "SELECT b.booking_id, b.status as booking_status, 
                 r.ride_id, r.ride_date, r.ride_time, r.price_per_seat,
                 u.name as driver_name,
                 COALESCE(b.pickup_location, sl.location_name) as source_name,
                 COALESCE(b.dropoff_location, dl.location_name) as dest_name
          FROM bookings b
          INNER JOIN rides r ON b.ride_id = r.ride_id
          INNER JOIN locations sl ON r.source_id = sl.location_id
          INNER JOIN locations dl ON r.destination_id = dl.location_id
          LEFT JOIN users u ON r.driver_id = u.user_id
          WHERE b.passenger_id = ?
          ORDER BY r.ride_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$passenger_id]);
$bookings = $stmt->fetchAll();
?>
<main class="max-w-2xl mx-auto px-6 py-10">
    
    <div class="mb-10">
        <h1 class="text-3xl font-black text-slate-800 mb-2">My Bookings 🎒</h1>
        <p class="text-slate-500 font-medium">All your confirmed seats in one place.</p>
    </div>

    <?php if(empty($bookings)): ?>
        <div class="bg-white/50 backdrop-blur-sm border-2 border-dashed border-slate-200 rounded-[2.5rem] p-16 text-center">
            <div class="text-4xl mb-4">🎫</div>
            <p class="text-slate-400 font-bold mb-4">No bookings found yet.</p>
            <a href="dashboard.php" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-indigo-100 inline-block">Find a Ride</a>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach($bookings as $book): ?>
                <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all group relative overflow-hidden">
                    
                    <div class="absolute top-6 right-6 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest">
                        <?php echo $book['booking_status']; ?>
                    </div>

                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 font-bold text-xl uppercase">
                            <?php echo substr($book['driver_name'], 0, 1); ?>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Driver</p>
                            <h3 class="font-bold text-slate-800"><?php echo htmlspecialchars($book['driver_name']); ?></h3>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-6 rounded-3xl mb-6">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-slate-400 uppercase mb-1">From</span>
                                <span class="font-extrabold text-slate-700"><?php echo $book['source_name']; ?></span>
                            </div>
                            <div class="h-[1px] flex-1 mx-4 bg-slate-200 relative">
                                <div class="absolute -top-1 right-0 w-2 h-2 rounded-full bg-indigo-400"></div>
                            </div>
                            <div class="flex flex-col text-right">
                                <span class="text-[10px] font-black text-slate-400 uppercase mb-1">To</span>
                                <span class="font-extrabold text-slate-700"><?php echo $book['dest_name']; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between px-2">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-black text-slate-400 uppercase">Departure</span>
                            <span class="text-sm font-black text-indigo-600">
                                <?php echo date('M d', strtotime($book['ride_date'])); ?> @ <?php echo date('h:i A', strtotime($book['ride_time'])); ?>
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter block mb-1">Total Paid</span>
                            <span class="text-xl font-black text-slate-800">₹<?php echo number_format($book['price_per_seat']); ?></span>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php include '../includes/footer.php'; ?>