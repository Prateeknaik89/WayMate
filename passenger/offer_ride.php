<?php
session_start();
require_once '../config/db.php';

// Security: Only drivers can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. THE GATEKEEPER: Check their exact verification status
$stmt_status = $pdo->prepare("SELECT verification_status FROM users WHERE user_id = ?");
$stmt_status->execute([$user_id]);
$verification_status = $stmt_status->fetchColumn() ?: 'unverified';

// Fetch Locations for the Dropdowns (Only if they are verified)
$locations = [];
if ($verification_status === 'verified') {
    $locations = $pdo->query("SELECT * FROM locations ORDER BY location_name ASC")->fetchAll();
}

include '../includes/header.php';
?>

<main class="max-w-xl mx-auto px-6 py-10">
    
    <div class="mb-10 text-center">
        <div class="inline-block p-4 bg-indigo-600 text-white rounded-[2rem] shadow-xl shadow-indigo-100 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
        </div>
        <h1 class="text-3xl font-black text-slate-800">Post a Trip</h1>
        <p class="text-slate-500 font-medium">Fill in the details to find your travel mates.</p>
    </div>

    <?php if ($verification_status !== 'verified'): ?>
        
        <div class="bg-white rounded-[2.5rem] p-12 text-center border border-slate-100 shadow-xl shadow-slate-200/40 relative overflow-hidden">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-64 bg-amber-100 rounded-full blur-[80px] -z-10"></div>
            
            <div class="w-20 h-20 bg-amber-50 text-amber-500 rounded-3xl flex items-center justify-center text-4xl mx-auto mb-6 shadow-sm border border-amber-100">
                🔒
            </div>
            
            <h2 class="text-2xl font-black text-slate-800 mb-2">Account Verification Required</h2>
            
            <?php if ($verification_status === 'pending'): ?>
                <p class="text-amber-600 font-bold mb-8 bg-amber-50 inline-block px-6 py-3 rounded-xl border border-amber-100">
                    Your documents are currently under review by our Admin team. ⏳
                </p>
            <?php elseif ($verification_status === 'rejected'): ?>
                <p class="text-rose-600 font-bold mb-8 bg-rose-50 inline-block px-6 py-3 rounded-xl border border-rose-100">
                    Your previous verification was declined. Please submit valid documents. ❌
                </p>
            <?php else: ?>
                <p class="text-slate-500 font-medium mb-8 max-w-md mx-auto">
                    To keep the WayMate community safe, you must verify your Driving License and live identity before offering rides.
                </p>
            <?php endif; ?>

            <a href="../passenger/verify_identity.php" class="inline-flex items-center justify-center bg-slate-900 text-white px-8 py-4 rounded-2xl font-black text-sm uppercase tracking-widest shadow-[0_8px_20px_rgb(15,23,42,0.2)] hover:bg-indigo-600 hover:-translate-y-1 hover:shadow-[0_8px_25px_rgb(79,70,229,0.3)] transition-all">
                Go to Trust & Safety
            </a>
        </div>

    <?php else: ?>

        <form action="../actions/post_ride.php" method="POST" class="space-y-4">
            
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2 mb-2 block">Starting Point</label>
                        <select name="source_id" required class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700 appearance-none">
                            <option value="">Select Location</option>
                            <?php foreach($locations as $loc): ?>
                                <option value="<?= $loc['location_id'] ?>"><?= $loc['location_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2 mb-2 block">Destination</label>
                        <select name="destination_id" required class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700 appearance-none">
                            <option value="">Select Location</option>
                            <?php foreach($locations as $loc): ?>
                                <option value="<?= $loc['location_id'] ?>"><?= $loc['location_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

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

    <?php endif; ?>

</main>

<?php include '../includes/footer.php'; ?>