<?php
session_start();
require_once '../config/db.php';
include '../includes/header.php'; // Using our new header

// Kick back if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$driver_id = $_SESSION['user_id'];

// --- NEW: HANDLE "END TRIP" BUTTON CLICK ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'end_trip') {
    $ride_id = $_POST['ride_id'];
    
    // 1. Mark the main ride as completed
    $stmt_end_ride = $pdo->prepare("UPDATE rides SET status = 'completed' WHERE ride_id = ? AND driver_id = ?");
    if ($stmt_end_ride->execute([$ride_id, $driver_id])) {
        // 2. Mark all confirmed bookings for this ride as completed so passengers can pay/review
        $stmt_end_bookings = $pdo->prepare("UPDATE bookings SET status = 'completed' WHERE ride_id = ? AND status = 'confirmed'");
        $stmt_end_bookings->execute([$ride_id]);
        
        header("Location: dashboard.php?success=trip_ended");
        exit();
    }
}
// -------------------------------------------

// 1. Fetch user data including new location fields
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt_user->execute([$driver_id]);
$user = $stmt_user->fetch();

// Check if they have set their locality
$has_locality = !empty($user['district']) && !empty($user['taluku']);

// 🧹 Auto-expire rides that are in the past (Updated to look for 'active')
$pdo->prepare("UPDATE rides SET status = 'expired' WHERE ride_date < CURDATE() AND status = 'active'")->execute();

// 🚀 Fetch only rides for TODAY or FUTURE, newest first (Updated to 'active')
$query = "SELECT r.*, s.location_name as source_name, d.location_name as dest_name 
          FROM rides r
          JOIN locations s ON r.source_id = s.location_id
          JOIN locations d ON r.destination_id = d.location_id
          WHERE r.driver_id = ? 
          AND r.status = 'active' /* 🚀 THIS WAS THE CULPRIT! */
          AND r.ride_date >= CURDATE() 
          ORDER BY r.ride_date ASC, r.ride_time ASC"; 

$stmt = $pdo->prepare($query);
$stmt->execute([$driver_id]); 
$active_rides = $stmt->fetchAll();

// 3. Fetch PENDING passenger requests for this driver
$req_query = "SELECT b.booking_id, r.ride_date, r.ride_time, 
                     s.location_name as source_name, d.location_name as dest_name,
                     u.name as passenger_name
              FROM bookings b
              JOIN rides r ON b.ride_id = r.ride_id
              JOIN locations s ON r.source_id = s.location_id
              JOIN locations d ON r.destination_id = d.location_id
              JOIN users u ON b.passenger_id = u.user_id
              WHERE r.driver_id = ? AND b.status = 'pending'
              ORDER BY b.created_at ASC";

$stmt_req = $pdo->prepare($req_query);
$stmt_req->execute([$driver_id]);
$pending_requests = $stmt_req->fetchAll();

// Check if they have set their locality
$has_locality = !empty($user['district']) && !empty($user['taluku']);

// 💰 NEW: Calculate Total Earnings from Completed Trips
$stmt_earnings = $pdo->prepare("
    SELECT SUM(r.price_per_seat) as total_earnings 
    FROM bookings b
    JOIN rides r ON b.ride_id = r.ride_id
    WHERE r.driver_id = ? AND b.status = 'completed'
");
$stmt_earnings->execute([$driver_id]);
$earnings_result = $stmt_earnings->fetch();
$total_earnings = $earnings_result['total_earnings'] ? $earnings_result['total_earnings'] : 0;
?>



<main class="max-w-4xl mx-auto px-6 py-10">
    <?php if(isset($_GET['success'])): ?>
        <div id="toast" class="mb-8 bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-4 rounded-2xl flex items-center justify-between shadow-sm animate-bounce-short">
            <div class="flex items-center gap-3 font-bold">
                <span class="text-xl">✅</span>
                <?php 
                    if($_GET['success'] == 'trip_posted') echo "Trip launched successfully! Passengers can now book seats.";
                    elseif($_GET['success'] == 'accept') echo "Passenger accepted! Seat count updated.";
                    elseif($_GET['success'] == 'reject') echo "Passenger request declined.";
                    elseif($_GET['success'] == 'trip_ended') echo "Trip completed successfully! Passengers can now review you.";
                ?>
            </div>
            <button onclick="document.getElementById('toast').style.display='none'" class="text-emerald-400 hover:text-emerald-600 font-bold">✕</button>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div id="toast" class="mb-8 bg-rose-50 border border-rose-200 text-rose-700 px-6 py-4 rounded-2xl flex items-center justify-between shadow-sm animate-bounce-short">
            <div class="flex items-center gap-3 font-bold">
                <span class="text-xl">⚠️</span>
                <?php 
                    if($_GET['error'] == 'empty_fields') echo "Please fill in all the details.";
                    elseif($_GET['error'] == 'db_error') echo "Database connection failed. Try again.";
                    elseif($_GET['error'] == 'ride_full') echo "Cannot accept! The ride is already full.";
                    else echo "Something went wrong.";
                ?>
            </div>
            <button onclick="document.getElementById('toast').style.display='none'" class="text-rose-400 hover:text-rose-600 font-bold">✕</button>
        </div>
    <?php endif; ?>

<script>
    setTimeout(() => {
        const toast = document.getElementById('toast');
        if (toast) {
            toast.style.transition = "opacity 0.5s ease";
            toast.style.opacity = "0";
            setTimeout(() => toast.remove(), 500);
        }
    }, 4000);
</script>
    
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-800 mb-1">Hey, <?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?>! 🏎️</h1>
            <p class="text-slate-500 font-medium">Manage your trips and track your earnings.</p>
        </div>
        <div class="bg-emerald-50 border border-emerald-100 px-6 py-4 rounded-[1.5rem] flex items-center gap-4 hover:bg-emerald-100 transition-colors cursor-default shadow-sm">
            <div class="text-2xl drop-shadow-sm">💰</div>
            <div>
                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-0.5">Total Earnings</p>
                <p class="text-xl font-black text-slate-800">₹<?php echo number_format($total_earnings); ?></p>
            </div>
        </div>
    </div>

    <?php if (!$has_locality): ?>
        <div class="mb-10 bg-amber-500 rounded-[2.5rem] p-8 text-white shadow-xl shadow-amber-200/50 flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
            <div class="relative z-10 text-center md:text-left">
                <h3 class="text-xl font-black mb-1">Get local ride requests 📍</h3>
                <p class="text-amber-100 text-sm font-medium">Add your District and Taluk so local passengers can find your rides.</p>
            </div>
            <a href="../passenger/complete_profile.php" class="relative z-10 bg-white text-amber-600 px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-amber-50 transition-all whitespace-nowrap">
                Update Locality
            </a>
            <div class="absolute right-0 top-0 w-64 h-64 bg-white/10 rounded-full -mr-20 -mt-20"></div>
        </div>
    <?php endif; ?>

    <a href="offer_ride.php" class="block w-full bg-indigo-600 p-8 rounded-[2.5rem] shadow-xl shadow-indigo-100 group transition hover:-translate-y-1 mb-12">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-white text-2xl font-black mb-1">Post a New Trip</h2>
                <p class="text-indigo-100 text-sm font-medium">Found a spare seat? Turn it into cash.</p>
            </div>
            <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-3xl group-hover:rotate-12 transition-transform">➕</div>
        </div>
    </a>

    <?php if(!empty($pending_requests)): ?>
        <div class="mb-12">
            <div class="flex items-center justify-between px-2 mb-4">
                <h3 class="text-sm font-black text-amber-500 uppercase tracking-widest flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> Action Required
                </h3>
            </div>
            <div class="grid grid-cols-1 gap-4">
                <?php foreach($pending_requests as $req): ?>
                    <div class="bg-amber-50 border border-amber-200 p-6 rounded-[2rem] flex flex-col md:flex-row items-center justify-between gap-6">
                        <div>
                            <p class="text-xs font-black text-amber-600 uppercase tracking-widest mb-1">New Request from <?php echo htmlspecialchars($req['passenger_name']); ?></p>
                            <h4 class="font-extrabold text-slate-800 flex items-center gap-2 text-lg">
                                <?php echo $req['source_name']; ?> 
                                <span class="text-amber-300">→</span> 
                                <?php echo $req['dest_name']; ?>
                            </h4>
                            <p class="text-[10px] font-black text-slate-500 uppercase mt-2">
                                🗓️ <?php echo date('M d', strtotime($req['ride_date'])); ?> @ <?php echo date('h:i A', strtotime($req['ride_time'])); ?>
                            </p>
                        </div>
                        <div class="flex gap-3 w-full md:w-auto">
                            <form action="process_request.php" method="POST" class="flex-1 md:flex-none">
                                <input type="hidden" name="booking_id" value="<?php echo $req['booking_id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="w-full bg-white text-rose-500 border border-rose-100 px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-rose-50 transition">Decline</button>
                            </form>
                            <form action="process_request.php" method="POST" class="flex-1 md:flex-none">
                                <input type="hidden" name="booking_id" value="<?php echo $req['booking_id']; ?>">
                                <input type="hidden" name="action" value="accept">
                                <button type="submit" class="w-full bg-amber-500 text-white px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg shadow-amber-200 hover:bg-amber-600 transition">Accept</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="mb-6 flex items-center justify-between px-2">
        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Your Active Trips</h3>
        <a href="driver_history.php" class="text-xs font-bold text-indigo-600 ">View History →</a>
    </div>

<?php if(empty($active_rides)): ?>
        <div class="bg-white border-2 border-dashed border-slate-200 rounded-[2.5rem] p-12 text-center mb-10">
            <p class="text-slate-400 font-bold mb-1">No active trips posted.</p>
            <p class="text-xs text-slate-300 font-medium uppercase tracking-tighter">Click the indigo card above to start</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
            <?php foreach($active_rides as $ride): ?>
                
                <div class="relative bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all flex flex-col">
                    
                    <button onclick="toggleTripMenu(event, <?php echo $ride['ride_id']; ?>)" class="absolute top-6 right-6 z-20 w-8 h-8 bg-slate-50 hover:bg-slate-100 rounded-full flex flex-col items-center justify-center gap-[3px] transition-colors shadow-sm">
                        <div class="w-1 h-1 bg-slate-400 rounded-full pointer-events-none"></div>
                        <div class="w-1 h-1 bg-slate-400 rounded-full pointer-events-none"></div>
                        <div class="w-1 h-1 bg-slate-400 rounded-full pointer-events-none"></div>
                    </button>

                    <div id="trip-menu-<?php echo $ride['ride_id']; ?>" class="trip-dropdown hidden absolute top-14 right-6 z-30 w-48 bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden origin-top-right transition-all">
                        <form action="../actions/cancel_trip.php" method="POST" onsubmit="return confirm('WARNING: This will cancel the trip for ALL confirmed passengers. Are you sure?');">
                            <input type="hidden" name="ride_id" value="<?php echo $ride['ride_id']; ?>">
                            <button type="submit" class="w-full text-left px-5 py-4 text-xs font-black text-rose-500 hover:bg-rose-50 transition-colors flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Cancel Trip
                            </button>
                        </form>
                    </div>

                    <div class="flex justify-between items-start mb-4 pr-10">
                        <div class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black uppercase">
                            <?php echo date('M d', strtotime($ride['ride_date'])); ?>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-black text-slate-800">₹<?php echo number_format($ride['price_per_seat']); ?></p>
                        </div>
                    </div>
                    
                    <h4 class="font-extrabold text-slate-700 mb-4 flex items-center gap-2">
                        <?php echo htmlspecialchars($ride['source_name']); ?> 
                        <span class="text-indigo-300">→</span> 
                        <?php echo htmlspecialchars($ride['dest_name']); ?>
                    </h4>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-50 mb-4">
                        <div class="text-[10px] font-black text-slate-400 uppercase">
                            Time: <span class="text-slate-700"><?php echo date('h:i A', strtotime($ride['ride_time'])); ?></span>
                        </div>
                        <div class="text-[10px] font-black text-indigo-500 uppercase">
                            Seats: <?php echo $ride['available_seats']; ?> Left
                        </div>
                    </div>

                    <div class="mt-auto bg-slate-50 rounded-2xl p-4 border border-slate-100 mb-4">
                        <h5 class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Confirmed Passengers</h5>
                        
                        <?php
                            // Fetch all confirmed passengers for THIS specific ride
                            $pass_stmt = $pdo->prepare("
                                SELECT b.booking_id, u.name 
                                FROM bookings b 
                                JOIN users u ON b.passenger_id = u.user_id 
                                WHERE b.ride_id = ? AND b.status = 'confirmed'
                            ");
                            $pass_stmt->execute([$ride['ride_id']]);
                            $passengers = $pass_stmt->fetchAll();
                        ?>

                        <?php if(empty($passengers)): ?>
                            <p class="text-xs text-slate-400 font-medium italic text-center py-2">Waiting for bookings...</p>
                        <?php else: ?>
                            <div class="space-y-2">
                                <?php foreach($passengers as $p): ?>
                                    <div class="flex items-center justify-between bg-white p-2 rounded-xl shadow-sm border border-slate-100">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-[10px] font-black">
                                                <?php echo strtoupper(substr($p['name'], 0, 1)); ?>
                                            </div>
                                            <span class="text-xs font-bold text-slate-700"><?php echo htmlspecialchars(explode(' ', $p['name'])[0]); ?></span>
                                        </div>
                                        <a href="../shared/chat.php?booking_id=<?php echo $p['booking_id']; ?>" class="text-[10px] bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg font-bold hover:bg-indigo-600 hover:text-white transition-colors">
                                            💬 Chat
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-2 pt-4 border-t border-slate-50">
                        <form action="dashboard.php" method="POST" onsubmit="return confirm('Are you sure you have completed this trip? All confirmed passengers will be asked to pay and review you.');">
                            <input type="hidden" name="ride_id" value="<?php echo $ride['ride_id']; ?>">
                            <input type="hidden" name="action" value="end_trip">
                            <button type="submit" class="w-full bg-emerald-500 text-white py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-emerald-200 hover:bg-emerald-600 hover:-translate-y-1 transition-all">
                                🏁 Mark Trip as Completed
                            </button>
                        </form>
                    </div>

                    </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
    $driver_quotes = [
        ["text" => "Gas is expensive. Your passengers are just mobile ATMs. Use them.", "icon" => "⛽", "color" => "emerald"],
        ["text" => "You're the CEO of this 4-wheeled startup. Drive like it.", "icon" => "👑", "color" => "amber"],
        ["text" => "Empty seats don't pay for car washes. Fill 'em up.", "icon" => "🧼", "color" => "indigo"],
        ["text" => "Your car, your rules, your AUX cord. Pure power.", "icon" => "🎸", "color" => "rose"],
        ["text" => "You are not a chauffeur. You are an 'independent logistics consultant'.", "icon" => "💼", "color" => "sky"],
        ["text" => "Yes, they are 5 minutes late. Yes, you will judge them silently the entire ride.", "icon" => "⏱️", "color" => "slate"],
        ["text" => "Braking smoothly is an art form. Make them think they're riding on a cloud.", "icon" => "☁️", "color" => "teal"],
        ["text" => "Small talk is a dangerous game. Proceed with extreme caution.", "icon" => "🤐", "color" => "orange"],
        ["text" => "Speed limits are rules, but 5-star ratings are forever. Find the balance.", "icon" => "⭐", "color" => "purple"],
        ["text" => "Remember: You control the AC. You are a literal god in this realm.", "icon" => "❄️", "color" => "cyan"]
    ];
    $random_dq = $driver_quotes[array_rand($driver_quotes)];
    $dc = $random_dq['color']; 
    
    ?>
    <div class="border-t border-slate-100 pt-10">
        <div class="max-w-sm">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Driver's Tip <?php echo $random_dq['icon']; ?></h3>
            <p class="text-slate-600 font-bold text-lg leading-tight hover:text-<?php echo $dc; ?>-600 transition-colors cursor-default">"<?php echo $random_dq['text']; ?>"</p>
            <div class="mt-2 w-8 h-1 bg-<?php echo $dc; ?>-500 rounded-full"></div>
        </div>
    </div>

</main>
<script>
    function toggleTripMenu(e, rideId) {
        e.preventDefault();
        e.stopPropagation();

        // 1. Close all other open menus first
        document.querySelectorAll('.trip-dropdown').forEach(menu => {
            if (menu.id !== `trip-menu-${rideId}`) {
                menu.classList.add('hidden');
            }
        });

        // 2. Toggle the specific menu that was clicked
        const menu = document.getElementById(`trip-menu-${rideId}`);
        if(menu) menu.classList.toggle('hidden');
    }

    // 3. Close menus if the user clicks anywhere else on the page
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.trip-dropdown') && !e.target.closest('button[onclick^="toggleTripMenu"]')) {
            document.querySelectorAll('.trip-dropdown').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>