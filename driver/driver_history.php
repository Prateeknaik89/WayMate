<?php
session_start();
require_once '../config/db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../index.php");
    exit();
}

$driver_id = $_SESSION['user_id'];

/**
 * THE CLEANER FUNCTION
 */
function clean($address) {
    if (empty($address)) return 'Unknown';
    $parts = explode(',', $address);
    return trim($parts[0]);
}

// Fetch PAST rides with Passenger Counts
// 🚨 ADDED: r.status = 'active' so cancelled trips don't show up in earnings!
$query = "SELECT r.*, s.location_name as source_name, d.location_name as dest_name,
                 (SELECT COUNT(*) FROM bookings WHERE ride_id = r.ride_id AND status = 'confirmed') as passenger_count
          FROM rides r
          JOIN locations s ON r.source_id = s.location_id
          JOIN locations d ON r.destination_id = d.location_id
          WHERE r.driver_id = ? 
          AND r.status = 'active'
          AND (r.ride_date < CURDATE() OR (r.ride_date = CURDATE() AND r.ride_time < CURTIME()))
          ORDER BY r.ride_date DESC, r.ride_time DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$driver_id]);
$past_rides = $stmt->fetchAll();

// Calculate Career Stats
$total_trips = count($past_rides);
$total_earnings = 0;
foreach($past_rides as $ride) {
    $total_earnings += ($ride['passenger_count'] * $ride['price_per_seat']);
}

include '../includes/header.php';
?>

<main class="max-w-4xl mx-auto px-6 py-10">
    
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <a href="dashboard.php" class="inline-flex items-center gap-2 text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] mb-4 group hover:text-indigo-700 transition-colors">
                <span class="group-hover:-translate-x-1 transition-transform">←</span> Back to Dashboard
            </a>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Trip History 🕰️</h1>
            <p class="text-slate-500 font-medium mt-1">Review your completed journeys and performance.</p>
        </div>

        <div class="bg-white border border-slate-100 p-6 rounded-[2rem] shadow-sm flex gap-8">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Trips</p>
                <p class="text-2xl font-black text-slate-800"><?php echo $total_trips; ?></p>
            </div>
            <div class="w-px h-10 bg-slate-100 self-center"></div>
            <div>
                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">Earnings</p>
                <p class="text-2xl font-black text-slate-800">₹<?php echo number_format($total_earnings); ?></p>
            </div>
        </div>
    </div>

    <?php if(empty($past_rides)): ?>
        <div class="bg-white border-2 border-dashed border-slate-200 rounded-[3rem] p-20 text-center">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="text-4xl grayscale opacity-50">🛣️</span>
            </div>
            <p class="text-slate-800 font-black text-xl mb-2">No past trips found</p>
            <p class="text-slate-400 text-sm max-w-xs mx-auto uppercase tracking-widest font-black text-[10px]">When you finish a journey, it will appear here</p>
        </div>
    <?php else: ?>
        
        <div class="space-y-5">
            <?php foreach($past_rides as $ride): 
                $ride_earnings = $ride['passenger_count'] * $ride['price_per_seat'];
                $is_today = ($ride['ride_date'] == date('Y-m-d'));
            ?>
                <div class="bg-white p-6 md:p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all group relative overflow-hidden">
                    
                    <div class="absolute top-6 right-6 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest <?php echo $is_today ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-50 text-slate-500'; ?>">
                        <?php echo $is_today ? 'Recently Finished' : 'Completed'; ?>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center gap-6 mt-8 md:mt-0">
                        
                        <div class="flex flex-col items-center justify-center bg-slate-50 w-20 h-20 rounded-3xl shrink-0">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">
                                <?php echo date('M', strtotime($ride['ride_date'])); ?>
                            </span>
                            <span class="text-2xl font-black text-slate-800 leading-none">
                                <?php echo date('d', strtotime($ride['ride_date'])); ?>
                            </span>
                        </div>

                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-slate-100 text-slate-500 text-[10px] font-black px-2 py-1 rounded-md uppercase">
                                    <?php echo date('h:i A', strtotime($ride['ride_time'])); ?>
                                </span>
                                <span class="text-slate-300">•</span>
                                <span class="text-slate-400 text-[10px] font-black uppercase tracking-widest">
                                    Trip ID #<?php echo $ride['ride_id']; ?>
                                </span>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <p class="text-lg font-black text-slate-800 italic"><?php echo htmlspecialchars(clean($ride['source_name'])); ?></p>
                                <span class="text-indigo-400 font-bold">→</span>
                                <p class="text-lg font-black text-slate-500 italic"><?php echo htmlspecialchars(clean($ride['dest_name'])); ?></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-8 md:text-right pt-4 md:pt-0 border-t md:border-t-0 border-slate-50 md:pl-6 md:border-l">
                            
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 md:text-right">Fill Rate</p>
                                <div class="flex items-center gap-1.5 md:justify-end">
                                    <span class="text-sm font-bold text-slate-700"><?php echo $ride['passenger_count']; ?></span>
                                    <span class="text-xs text-slate-300">/</span>
                                    <span class="text-xs text-slate-400"><?php echo $ride['available_seats']; ?></span>
                                </div>
                            </div>
                            
                            <div class="bg-emerald-50 px-6 py-4 rounded-2xl border border-emerald-100 min-w-[120px] text-center">
                                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-1">Earned</p>
                                <p class="text-2xl font-black text-slate-800 leading-none">
                                    ₹<?php echo number_format($ride_earnings); ?>
                                </p>
                            </div>

                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>

<?php include '../includes/footer.php'; ?>