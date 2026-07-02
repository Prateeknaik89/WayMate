<?php
require_once '../config/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$message = "";

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $bio = $_POST['bio'];
    $car_model = $_POST['car_model'] ?? null;
    $car_number = $_POST['car_number'] ?? null;

    $sql = "UPDATE users SET name = ?, bio = ?, car_model = ?, car_number = ? WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$name, $bio, $car_model, $car_number, $user_id])) {
        $_SESSION['user_name'] = $name; // Update session name
        $message = "success";
    }
}

// Fetch Fresh Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<main class="max-w-2xl mx-auto px-6 py-10">
    
    <div class="mb-10 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-slate-800 mb-1">My Profile</h1>
            <p class="text-slate-500 font-medium text-sm">Update your identity on WayMate.</p>
        </div>
        <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-2xl font-black shadow-lg shadow-indigo-100">
            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
        </div>
    </div>

    <?php if($message == "success"): ?>
        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-2xl border border-emerald-100 mb-8 font-bold text-center text-sm">
            Profile updated successfully! ✨
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="space-y-6">
        
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 text-center">Identity</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2 mb-1 block">Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required
                        class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700 transition">
                </div>

                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2 mb-1 block">Phone Number (Verified)</label>
                    <input type="text" value="<?php echo $user['phone']; ?>" disabled
                        class="w-full p-4 bg-slate-100 border-none rounded-2xl text-slate-400 font-medium cursor-not-allowed">
                </div>

                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-2 mb-1 block">Short Bio</label>
                    <textarea name="bio" rows="2" placeholder="Tell people about your vibe..."
                        class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none font-medium text-slate-700 transition"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                </div>
            </div>
        </div>

        <?php if($user['role'] === 'driver'): ?>
        <div class="bg-indigo-600/5 p-8 rounded-[2.5rem] border border-indigo-100 shadow-sm">
            <h3 class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-6 text-center">Vehicle Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-indigo-400 uppercase ml-2 mb-1 block">Car/Bike Model</label>
                    <input type="text" name="car_model" value="<?php echo htmlspecialchars($user['car_model']); ?>" placeholder="e.g. White Swift"
                        class="w-full p-4 bg-white border-none rounded-2xl ring-1 ring-indigo-100 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700 transition">
                </div>
                <div>
                    <label class="text-[10px] font-black text-indigo-400 uppercase ml-2 mb-1 block">Vehicle Number</label>
                    <input type="text" name="car_number" value="<?php echo htmlspecialchars($user['car_number']); ?>" placeholder="TS 07 XX 1234"
                        class="w-full p-4 bg-white border-none rounded-2xl ring-1 ring-indigo-100 focus:ring-2 focus:ring-indigo-600 outline-none font-bold text-slate-700 transition">
                </div>
            </div>
        </div>
        <?php endif; ?>

        <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-[2rem] font-black text-lg shadow-xl hover:bg-indigo-600 transition-all active:scale-95">
            Save Changes
        </button>

    </form>

</main>

<?php include '../includes/footer.php'; ?>