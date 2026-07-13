<?php
session_start();
require_once '../config/db.php';

// 1. Security check
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

$passenger_id = $_SESSION['user_id'];
$trip_id = $_POST['trip_id'] ?? null;

// 🚨 NEW: Catch the passenger's specific pickup and dropoff locations
$pickup = $_POST['pickup_location'] ?? 'Not Specified';
$dropoff = $_POST['dropoff_location'] ?? 'Not Specified';

$message = "";
$status = "error";

if (!$ride_id) {
    header("Location: dashboard.php?error=invalid_ride");
    exit();
}

try {
    $pdo->beginTransaction();

    // 2. Fetch the ride details 
    $ride_stmt = $pdo->prepare("
        SELECT r.*, u.name as driver_name 
        FROM rides r
        JOIN users u ON r.driver_id = u.user_id
        WHERE r.ride_id = ? FOR UPDATE
    ");
    $ride_stmt->execute([$ride_id]);
    $ride = $ride_stmt->fetch();

    if (!$ride) {
        throw new Exception("Ride not found.");
    }

    if ($ride['available_seats'] <= 0) {
        throw new Exception("Sorry, this ride is already full!");
    }

    if ($ride['driver_id'] == $passenger_id) {
        throw new Exception("You cannot book your own ride.");
    }

    // 3. Check for existing booking
    $check_stmt = $pdo->prepare("SELECT status FROM bookings WHERE ride_id = ? AND passenger_id = ?");
    $check_stmt->execute([$ride_id, $passenger_id]);
    $existing_booking = $check_stmt->fetch();

    if ($existing_booking) {
        if ($existing_booking['status'] === 'pending') {
            throw new Exception("You already requested this ride. Waiting for driver approval.");
        } elseif ($existing_booking['status'] === 'confirmed') {
            throw new Exception("You are already booked on this trip!");
        } elseif ($existing_booking['status'] === 'rejected') {
            throw new Exception("The driver previously declined your request for this trip.");
        }
    }

        // 4. Update the INSERT statement to match your database column name (trip_id)
        $insert_stmt = $pdo->prepare("INSERT INTO bookings (trip_id, passenger_id, pickup_location, dropoff_location, status) VALUES (?, ?, ?, ?, 'pending')");
        $insert_stmt->execute([$ride_id, $passenger_id, $pickup, $dropoff]);

    $pdo->commit();
    $status = "success";
    $message = "Request sent successfully!";

} catch (Exception $e) {
    $pdo->rollBack();
    $status = "error";
    $message = $e->getMessage();
}

// Now we show the UI
include '../includes/header.php';
?>

<main class="min-h-[80vh] flex items-center justify-center py-12 px-6">
    <div class="max-w-md w-full bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden text-center p-10">
        
        <?php if ($status === 'success'): ?>
            <div class="w-24 h-24 bg-amber-50 rounded-full flex items-center justify-center text-4xl mx-auto mb-6 shadow-inner animate-pulse">
                ⏳
            </div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight mb-2">Request Sent!</h1>
            <p class="text-slate-500 font-medium mb-8">We've notified <span class="font-bold text-slate-700"><?php echo htmlspecialchars($ride['driver_name'] ?? 'the driver'); ?></span>. You'll be notified once they accept your request.</p>
            
            <div class="bg-slate-50 rounded-2xl p-4 mb-8 text-left border border-slate-100">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Your Route Details</p>
                <div class="flex items-center gap-2 mb-2">
                    <span class="font-bold text-slate-700 text-sm"><?php echo htmlspecialchars($pickup); ?></span>
                    <span class="text-indigo-400">→</span>
                    <span class="font-bold text-slate-700 text-sm"><?php echo htmlspecialchars($dropoff); ?></span>
                </div>
                <p class="text-xs font-bold text-slate-500">
                    🗓️ <?php echo date('M d', strtotime($ride['ride_date'])); ?> @ <?php echo date('h:i A', strtotime($ride['ride_time'])); ?>
                </p>
            </div>

            <a href="dashboard.php" class="block w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm shadow-lg hover:bg-indigo-600 transition-all">
                Back to Dashboard
            </a>

        <?php else: ?>
            <div class="w-24 h-24 bg-rose-50 rounded-full flex items-center justify-center text-4xl mx-auto mb-6 shadow-inner">
                ⚠️
            </div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight mb-2">Oops!</h1>
            <p class="text-rose-500 font-bold mb-8"><?php echo htmlspecialchars($message); ?></p>
            
            <a href="dashboard.php" class="block w-full bg-slate-100 text-slate-600 py-4 rounded-2xl font-bold text-sm hover:bg-slate-200 transition-all">
                Return to Search
            </a>
        <?php endif; ?>

    </div>
</main>

<?php include '../includes/footer.php'; ?>