<?php
session_start();
require_once '../config/db.php';

// 1. BASIC SECURITY
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// 2. ADVANCED SECURITY: Check if they are VERIFIED
$stmt_check = $pdo->prepare("SELECT verification_status FROM users WHERE user_id = ?");
$stmt_check->execute([$user_id]);
$user_status = $stmt_check->fetchColumn();

// If not verified, kick them back to the dashboard
if ($user_status !== 'verified') {
    header("Location: dashboard.php");
    exit();
}

// 3. HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $make_model = trim($_POST['make_model']);
    $color = trim($_POST['color']);
    // Strip spaces and make license plate uppercase for clean database formatting
    $license_plate = strtoupper(str_replace(' ', '', trim($_POST['license_plate'])));

    if (empty($make_model) || empty($color) || empty($license_plate)) {
        $error_message = "Please fill in all vehicle details.";
    } else {
        // Check if they already have a vehicle registered
        $stmt_exist = $pdo->prepare("SELECT vehicle_id FROM vehicles WHERE user_id = ?");
        $stmt_exist->execute([$user_id]);
        $existing = $stmt_exist->fetch();

        if ($existing) {
            // Update existing car
            $updateStmt = $pdo->prepare("UPDATE vehicles SET make_model = ?, color = ?, license_plate = ? WHERE user_id = ?");
            if ($updateStmt->execute([$make_model, $color, $license_plate, $user_id])) {
                $success_message = "Vehicle updated successfully! 🚘";
            }
        } else {
            // Insert new car
            $insertStmt = $pdo->prepare("INSERT INTO vehicles (user_id, make_model, color, license_plate) VALUES (?, ?, ?, ?)");
            if ($insertStmt->execute([$user_id, $make_model, $color, $license_plate])) {
                $success_message = "Vehicle added to your garage! 🚘";
            }
        }
    }
}

// 4. FETCH CURRENT VEHICLE (To show in the UI)
$stmt_veh = $pdo->prepare("SELECT * FROM vehicles WHERE user_id = ?");
$stmt_veh->execute([$user_id]);
$my_vehicle = $stmt_veh->fetch();

include '../includes/header.php';
?>

<main class="max-w-3xl mx-auto px-6 py-12">

    <div class="mb-10 text-center">
        <div class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner text-4xl">
            🏎️
        </div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">My Garage</h1>
        <p class="text-slate-500 font-medium mt-2">Manage your registered WayMate vehicle.</p>
    </div>

    <?php if ($error_message): ?>
        <div class="bg-rose-50 text-rose-600 px-6 py-4 rounded-2xl font-bold text-sm mb-6 flex items-center gap-3 border border-rose-100">
            ⚠️ <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="bg-emerald-50 text-emerald-600 px-6 py-4 rounded-2xl font-bold text-sm mb-6 flex items-center gap-3 border border-emerald-100">
            ✅ <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-8">
        
        <div class="md:col-span-2 space-y-6">
            <?php if ($my_vehicle): ?>
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-[2rem] p-6 text-white shadow-xl shadow-slate-200 relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 text-9xl opacity-10">🚗</div>
                    
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Active Vehicle</p>
                    <h3 class="text-2xl font-black mb-6"><?php echo htmlspecialchars($my_vehicle['make_model']); ?></h3>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Color</p>
                            <p class="font-bold text-slate-200"><?php echo htmlspecialchars($my_vehicle['color']); ?></p>
                        </div>
                        
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">License Plate</p>
                            <div class="bg-yellow-400 text-black px-4 py-2 rounded-lg font-black text-lg tracking-widest border-2 border-yellow-500 inline-block shadow-inner">
                                <?php echo htmlspecialchars($my_vehicle['license_plate']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-[2rem] p-8 text-center h-full flex flex-col items-center justify-center">
                    <div class="text-5xl mb-4 grayscale opacity-40">🚖</div>
                    <h3 class="text-lg font-black text-slate-700 mb-1">Empty Garage</h3>
                    <p class="text-xs font-bold text-slate-400">Register your car to start offering rides to passengers.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="md:col-span-3">
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <h3 class="text-xl font-black text-slate-800 mb-6">
                    <?php echo $my_vehicle ? 'Update Vehicle Details' : 'Register New Vehicle'; ?>
                </h3>
                
                <form action="my_vehicle.php" method="POST" class="space-y-6">
                    
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Make & Model</label>
                        <input type="text" name="make_model" required placeholder="e.g., Tata Safari, Honda City" 
                               value="<?php echo $my_vehicle ? htmlspecialchars($my_vehicle['make_model']) : ''; ?>"
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Color</label>
                            <input type="text" name="color" required placeholder="e.g., White" 
                                   value="<?php echo $my_vehicle ? htmlspecialchars($my_vehicle['color']) : ''; ?>"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">License Plate</label>
                            <input type="text" name="license_plate" required placeholder="KA 01 AB 1234" 
                                   value="<?php echo $my_vehicle ? htmlspecialchars($my_vehicle['license_plate']) : ''; ?>"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 font-bold text-slate-700 uppercase focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-indigo-600 text-white rounded-2xl px-6 py-4 font-black text-sm uppercase tracking-widest shadow-lg shadow-indigo-200 hover:bg-slate-900 hover:-translate-y-1 transition-all">
                            <?php echo $my_vehicle ? 'Save Changes' : 'Park in Garage 🚘'; ?>
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>