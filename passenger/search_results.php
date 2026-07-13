<?php
session_start();
require_once '../config/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch current user's verification status
$stmt_user = $pdo->prepare("SELECT verification_status FROM users WHERE user_id = ?");
$stmt_user->execute([$_SESSION['user_id']]);
$user = $stmt_user->fetch();

// 1. Grab inputs from Dashboard
$source_raw = $_GET['source_name'] ?? '';
$dest_raw = $_GET['destination_name'] ?? '';
// 🚨 FORCING THE DATE FOR TESTING - Change this back to $_GET['date'] later!
$ride_date = $_GET['date'] ?? '2026-05-20'; 

// Grab the secret GPS coordinates!
$pass_src_lat = $_GET['search_source_lat'] ?? '';
$pass_src_lon = $_GET['search_source_lon'] ?? '';
$pass_dest_lat = $_GET['search_dest_lat'] ?? '';
$pass_dest_lon = $_GET['search_dest_lon'] ?? '';

function clean($address) {
    if (empty($address)) return 'Anywhere';
    if (strlen($address) > 30 && !strpos($address, ' ')) {
        return 'Location Selected'; 
    }
    $parts = explode(',', $address);
    return trim($parts[0]);
}
$source_clean = clean($source_raw);
$dest_clean = clean($dest_raw);

$rides = []; // Default to empty array

// Only run the heavy math if we actually caught the GPS coordinates
if (!empty($pass_src_lat) && !empty($pass_dest_lat)) {
    try {
        // 2. THE SPATIAL SQL QUERY (Method 2 - ST_Distance in Degrees)
        $query = "SELECT r.*, u.name as driver_name, u.rating as driver_rating, 
                  s.location_name as source_name, d.location_name as dest_name 
                  FROM rides r
                  JOIN users u ON r.driver_id = u.user_id
                  JOIN locations s ON r.source_id = s.location_id
                  JOIN locations d ON r.destination_id = d.location_id
                  WHERE r.ride_date = ?
                  AND r.status = 'active' 
                  AND r.available_seats > 0 
                  AND r.route_path IS NOT NULL
                  AND ST_Distance(POINT(?, ?), r.route_path) <= 0.045  
                  AND ST_Distance(POINT(?, ?), r.route_path) <= 0.045  
                  ORDER BY r.ride_time ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $ride_date,
            $pass_src_lon, $pass_src_lat,
            $pass_dest_lon, $pass_dest_lat
        ]);

        $rides = $stmt->fetchAll();

    } catch (PDOException $e) {
        // If MySQL crashes, print the error instead of a blank screen!
        die("<div style='padding: 50px; background: #fee2e2; color: #991b1b; font-family: sans-serif;'>
                <h2>Database Crash!</h2>
                <p>" . $e->getMessage() . "</p>
             </div>");
    }
}

include '../includes/header.php';
?>

<main class="max-w-4xl mx-auto px-6 py-10">

    <div class="mb-10 relative overflow-hidden bg-slate-900 p-10 rounded-[3rem] text-white shadow-2xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
            <div>
                <p class="text-indigo-400 text-[10px] font-black uppercase tracking-[0.2em] mb-3">Active Route</p>
                <div class="flex flex-wrap items-center gap-4 text-3xl md:text-4xl font-black italic tracking-tight">
                    <span><?php echo htmlspecialchars($source_clean); ?></span>
                    <span class="text-indigo-500">→</span>
                    <span class="text-slate-500"><?php echo htmlspecialchars($dest_clean); ?></span>
                </div>
                <div class="mt-4 inline-flex items-center gap-2 bg-white/5 border border-white/10 px-4 py-2 rounded-xl text-xs font-bold text-slate-300">
                    🗓️ <?php echo date('D, M jS', strtotime($ride_date)); ?>
                </div>
            </div>
            <a href="dashboard.php" class="bg-white text-slate-900 px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-400 transition-all text-center">
                Modify
            </a>
        </div>
    </div>

    <div class="mb-8 flex items-center justify-between px-4">
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
            <?php echo count($rides); ?> Rides Found
        </h3>
        <div class="h-px flex-1 bg-slate-100 mx-6"></div>
    </div>

    <?php if(empty($rides)): ?>
        <div class="bg-white border-2 border-dashed border-slate-200 rounded-[3rem] p-20 text-center">
            <span class="text-4xl mb-4 block">🏜️</span>
            <p class="text-slate-800 font-black text-xl mb-2">No WayMates found</p>
            <p class="text-slate-400 text-sm">Try another date or a nearby location.</p>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach($rides as $ride): ?>
                <div class="bg-white p-2 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl transition-all overflow-hidden">
                    <div class="p-6 md:p-8 flex flex-col md:flex-row items-center gap-8">
                        
                        <div class="w-full md:w-1/4 flex flex-row md:flex-col justify-between items-center md:items-start md:border-r border-slate-50 md:pr-8">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Departure</p>
                                <p class="text-4xl font-black text-slate-900 leading-none">
                                    <?php echo date('h:i', strtotime($ride['ride_time'])); ?>
                                    <span class="text-xs uppercase text-indigo-500 block mt-1"><?php echo date('A', strtotime($ride['ride_time'])); ?></span>
                                </p>
                            </div>
                            <div class="mt-0 md:mt-8 text-right md:text-left">
                                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">Price</p>
                                <p class="text-3xl font-black text-slate-900">₹<?php echo number_format($ride['price_per_seat']); ?></p>
                            </div>
                        </div>

                        <div class="w-full md:w-2/4">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-800 font-black text-lg">
                                    <?php echo strtoupper(substr($ride['driver_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-900"><?php echo htmlspecialchars($ride['driver_name']); ?></h4>
                                    <div class="flex items-center gap-1">
                                        <span class="text-amber-500 text-xs">★</span>
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                            Rating • <?php echo number_format($ride['driver_rating'], 1); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="relative pl-6">
                                <div class="absolute left-1 top-1 bottom-1 w-0.5 bg-gradient-to-b from-indigo-500 to-slate-800 rounded-full"></div>
                                <div class="absolute left-[-2px] top-0 w-2.5 h-2.5 rounded-full bg-white border-2 border-indigo-500"></div>
                                <div class="absolute left-[-2px] bottom-0 w-2.5 h-2.5 rounded-full bg-white border-2 border-slate-800"></div>
                                
                                <div class="space-y-4">
                                    <p class="text-sm font-bold text-slate-800 leading-none">
                                        <?php echo htmlspecialchars(clean($ride['source_name'])); ?>
                                    </p>
                                    <p class="text-sm font-bold text-slate-500 leading-none">
                                        <?php echo htmlspecialchars(clean($ride['dest_name'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="w-full md:w-1/4 flex flex-col items-stretch md:items-end gap-4">
                            <div class="bg-amber-50 text-amber-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider text-center">
                                <?php echo $ride['available_seats']; ?> Seats Left
                            </div>

                            <?php if (isset($user['verification_status']) && $user['verification_status'] === 'verified'): ?>
                                <form action="book_ride.php" method="POST" class="w-full">
                                    <input type="hidden" name="trip_id" value="<?php echo $ride['ride_id']; ?>">
                                    <input type="hidden" name="pickup_location" value="<?php echo htmlspecialchars($source_clean); ?>">
                                    <input type="hidden" name="dropoff_location" value="<?php echo htmlspecialchars($dest_clean); ?>">
                                    <button type="submit" class="w-full bg-slate-900 text-white px-6 py-5 rounded-[1.5rem] font-black text-xs uppercase tracking-[0.2em] shadow-xl hover:bg-indigo-600 transition-all">
                                        Join Ride
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="verify_identity.php" class="w-full bg-rose-50 text-rose-600 px-6 py-5 rounded-[1.5rem] font-black text-[10px] uppercase tracking-widest text-center hover:bg-rose-100 transition-all">
                                    Verify ID to Book
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php include '../includes/footer.php'; ?>