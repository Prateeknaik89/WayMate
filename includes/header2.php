<?php
// waymate/includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_role = $_SESSION['role'] ?? 'passenger';
$user_name = $_SESSION['user_name'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Plus Jakarta Sans', sans-serif; } 
        /* Smooth scrolling for the whole page */
        html { scroll-behavior: smooth; }
    </style>
</head>

<body class="bg-[#F8FAFC] text-slate-900 min-h-screen relative overflow-x-hidden">

    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-[10%] -right-[10%] w-[50%] h-[50%] rounded-full bg-indigo-100/40 blur-[120px]"></div>
        <div class="absolute -bottom-[10%] -left-[10%] w-[50%] h-[50%] rounded-full bg-sky-100/40 blur-[120px]"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[30%] h-[30%] rounded-full bg-purple-50/30 blur-[100px]"></div>
    </div>

    <nav class="flex justify-between items-center px-6 md:px-12 py-5 bg-white/70 backdrop-blur-md border-b border-slate-100 sticky top-0 z-50">
        <div class="flex items-center gap-2 cursor-pointer group" onclick="window.location.href='../index.php'">
            <div class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-black shadow-lg shadow-indigo-200 group-hover:scale-110 transition-transform">W</div>
            <span class="text-xl font-black tracking-tighter text-slate-800">WayMate</span>
        </div>

        <div class="hidden md:flex items-center gap-8 text-sm font-bold text-slate-500">
            <?php if($current_role === 'driver'): ?>
                <a href="../driver/dashboard.php" class="hover:text-indigo-600 transition">Home</a>
                <a href="../driver/my_rides.php" class="hover:text-indigo-600 transition">Manage Rides</a>
                <a href="../passenger/offer_ride.php" class="text-indigo-600 flex items-center gap-1">
                    <span class="text-lg">+</span> Post Trip
                </a>
            <?php else: ?>
                <a href="../passenger/dashboard.php" class="hover:text-indigo-600 transition">Find Ride</a>
                <a href="#" class="hover:text-indigo-600 transition">My Bookings</a>
            <?php endif; ?>
        </div>

        <div class="flex items-center gap-4">
            <div class="hidden sm:block text-right">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo $current_role; ?></p>
                <p class="text-xs font-bold text-slate-700"><?php echo htmlspecialchars($user_name); ?></p>
            </div>
            
            <a href="../actions/logout.php" 
               class="p-2.5 bg-white text-rose-500 rounded-xl border border-rose-100 hover:bg-rose-500 hover:text-white transition-all shadow-sm hover:shadow-rose-100 active:scale-95" 
               title="Logout">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </a>
        </div>
    </nav>