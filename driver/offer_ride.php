<?php
session_start();
require_once '../config/db.php';
include '../includes/header.php';

// Fetch current user's verification status
$stmt_user = $pdo->prepare("SELECT verification_status FROM users WHERE user_id = ?");
$stmt_user->execute([$_SESSION['user_id']]);
$user = $stmt_user->fetch();

// Security: Only drivers can offer rides
if ($_SESSION['role'] !== 'driver') {
    header("Location: dashboard.php");
    exit();
}
?>

<main class="max-w-2xl mx-auto px-6 py-12">

    <?php if (isset($user['verification_status']) && $user['verification_status'] === 'verified'): ?>
        
        <div class="mb-10 text-center">
            <div class="inline-block p-4 bg-indigo-600 text-white rounded-[2rem] shadow-xl shadow-indigo-100 mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </div>
            <h1 class="text-3xl font-black text-slate-800">Post a Trip</h1>
            <p class="text-slate-500 font-medium">Fill in the details to find your travel mates.</p>
        </div>

        <form action="../actions/post_ride.php" method="POST" class="space-y-4">
    
    <!-- Top Box: Route & Schedule -->
    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm space-y-6">
        
        <!-- Locations -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative">
            
            <!-- Starting Point -->
            <div class="relative">
                <label class="text-[10px] font-black text-slate-400 uppercase ml-2 mb-2 block">Starting Point</label>
                
                <!-- GPS Button: Positioned inside the input -->
                <button type="button" id="getLocationBtn" class="absolute right-4 top-[42px] z-10 text-indigo-500 hover:text-indigo-700 p-1.5 rounded-lg transition-colors flex items-center justify-center" title="Detect Location">
                    <svg id="location-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 2v2M12 20v2M2 12h2M20 12h2"></path>
                    </svg>
                </button>

                <!-- Source -->
                <input type="text" id="source-input" name="source_name" placeholder="Type city or area..." autocomplete="off" required
                    class="w-full p-4 pr-12 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700">

                <input type="hidden" name="source_id" id="source-id" required>
                <input type="hidden" name="source_lat" id="source-lat">
                <input type="hidden" name="source_lon" id="source-lon">
                <div id="source-results" class="absolute w-full mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 hidden overflow-hidden"></div>
            </div>

            <!-- Destination -->
            <div class="relative">
                <label class="text-[10px] font-black text-slate-400 uppercase ml-2 mb-2 block">Destination</label>
                <input type="text" id="dest-input" name="destination_name" placeholder="Type city or area..." autocomplete="off" required
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700">
                
                <input type="hidden" name="destination_id" id="dest-id" required>
                <input type="hidden" name="dest_lat" id="dest-lat">
                <input type="hidden" name="dest_lon" id="dest-lon">
                <div id="dest-results" class="absolute w-full mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 hidden overflow-hidden"></div>
            </div>
        </div>

        <!-- Date & Time -->
        <div class="grid grid-cols-2 gap-6 pt-4 border-t border-slate-50">
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase ml-2 mb-2 block">Date</label>
                <input type="date" name="ride_date" min="<?= date('Y-m-d') ?>" required 
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700">
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase ml-2 mb-2 block">Time</label>
                <input type="time" name="ride_time" required 
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700">
            </div>
        </div>
    </div>

    <!-- Bottom Box: Seats & Price -->
    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm grid grid-cols-2 gap-6">
        <div>
            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 mb-2 block">Seats Available</label>
            <input type="number" name="available_seats" min="1" max="10" placeholder="e.g. 3" required 
                class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700">
        </div>
        <div>
            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 mb-2 block">Price per Seat (₹)</label>
            <input type="number" name="price" min="0" placeholder="e.g. 150" required 
                class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700">
        </div>
    </div>

    <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-[2rem] font-black text-lg shadow-xl hover:bg-indigo-600 transition-all active:scale-95">
        Launch Trip 🚀
    </button>
</form>

<!-- Link to your unified JS handler -->
<div id="clear-source" class="hidden"></div>
<div id="clear-dest" class="hidden"></div>
<script src="../assets/js/location-handler.js"></script>

    <?php elseif (isset($user['verification_status']) && $user['verification_status'] === 'pending'): ?>
        
        <div class="bg-amber-50 border-2 border-dashed border-amber-200 rounded-[2.5rem] p-16 text-center mt-10">
            <div class="text-5xl mb-4">⏳</div>
            <h3 class="text-xl font-black text-amber-800 mb-2">ID Under Review</h3>
            <p class="text-amber-700/80 font-medium text-sm">Your documents are being verified by our team. You can publish rides as soon as you are approved!</p>
            <a href="dashboard.php" class="inline-block mt-8 bg-amber-200 text-amber-800 px-8 py-3 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-amber-300 transition-all">Go Back</a>
        </div>

    <?php else: ?>
        
        <div class="bg-rose-50 border border-rose-200 rounded-[2.5rem] p-16 text-center shadow-sm mt-10">
            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm text-3xl">🛡️</div>
            <h3 class="text-2xl font-black text-rose-800 mb-2">Driver Verification Required</h3>
            <p class="text-rose-600/80 font-medium mb-8">For passenger safety, all WayMate drivers must be securely verified before offering rides.</p>
            <a href="../passenger/verify_identity.php" class="inline-block bg-rose-600 text-white px-8 py-4 rounded-2xl font-black text-sm uppercase tracking-widest shadow-lg shadow-rose-200 hover:bg-rose-700 hover:-translate-y-1 transition-all">
                Verify My Identity
            </a>
        </div>

    <?php endif; ?>

</main>

<?php include '../includes/footer.php'; ?>