<?php
// waymate/includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Force the role to be lowercase just in case it saved as "Driver" instead of "driver"
$current_role = strtolower($_SESSION['role'] ?? 'passenger');
$user_name = $_SESSION['user_name'] ?? 'Guest';

// STRICT Admin Check
$is_admin = false;
if (isset($_SESSION['user_id']) && isset($pdo)) {
    $stmt_admin = $pdo->prepare("SELECT is_admin FROM users WHERE user_id = ?");
    $stmt_admin->execute([$_SESSION['user_id']]);
    $user_data = $stmt_admin->fetch();
    
    // Force it to check for a strict integer 1
    if ($user_data && (int)$user_data['is_admin'] === 1) {
        $is_admin = true;
    }
}
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
        html { scroll-behavior: smooth; }
    </style>
</head>

<body class="bg-[#F8FAFC] text-slate-900 min-h-screen relative overflow-x-hidden">

    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-[10%] -right-[10%] w-[50%] h-[50%] rounded-full bg-indigo-100/40 blur-[120px]"></div>
        <div class="absolute -bottom-[10%] -left-[10%] w-[50%] h-[50%] rounded-full bg-sky-100/40 blur-[120px]"></div>
    </div>

    <nav class="flex justify-between items-center px-6 md:px-12 py-5 bg-white/70 backdrop-blur-md border-b border-slate-100 sticky top-0 z-50">
        
        <div class="flex items-center gap-2 cursor-pointer group" onclick="window.location.href='../index.php'">
            <div class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-black shadow-lg shadow-indigo-200 group-hover:scale-110 transition-transform">
                <img src="web-logo/WayMate.png" alt="Website Logo" class="site-logo">
            </div>
            <span class="text-xl font-black tracking-tighter text-slate-800">WayMate</span>
        </div>

        <div class="hidden md:flex items-center gap-8 text-sm font-bold text-slate-500">
            
            <?php if($current_role === 'driver'): ?>
                <a href="../driver/dashboard.php" class="hover:text-indigo-600 transition">Home</a>
                <a href="../driver/requests.php" class="hover:text-indigo-600 transition">Requests</a>
                <a href="../driver/my_vehicle.php" class="hover:text-indigo-600 transition">My Vehicle</a>
                <a href="../driver/offer_ride.php" class="text-indigo-600 flex items-center gap-1 hover:scale-105 transition-transform">
                    <span class="text-lg">+</span> Post Trip
                </a>
            <?php else: ?>
                <a href="../passenger/dashboard.php" class="hover:text-indigo-600 transition">Find Ride</a>
                <a href="../passenger/my_bookings.php" class="hover:text-indigo-600 transition">My Bookings</a>
            <?php endif; ?>

            <?php if ($is_admin): ?>
                <div class="w-[1px] h-6 bg-slate-200 mx-2"></div>
                <a href="../admin/dashboard.php" class="bg-gradient-to-r from-slate-900 to-slate-800 text-white px-5 py-2.5 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-slate-300 hover:-translate-y-0.5 hover:shadow-xl transition-all flex items-center gap-2">
                    <span>👑</span> Command Center
                </a>
            <?php endif; ?>

        </div>

        <div class="flex items-center gap-4">
            
            <?php if ($is_admin): ?>
                <div class="flex items-center gap-3 text-right">
                    <div class="hidden sm:block">
                        <p class="text-[9px] font-black text-amber-500 uppercase tracking-widest leading-none mb-1">
                            👑 Founder
                        </p>
                        <p class="text-xs font-bold text-slate-700"><?php echo htmlspecialchars($user_name); ?></p>
                    </div>
                    <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center shadow-sm border border-amber-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>

            <?php else: ?>
                <a href="../passenger/complete_profile.php" class="flex items-center gap-3 group text-right transition-all">
                    <div class="hidden sm:block">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1 group-hover:text-indigo-600 transition-colors">
                            <?php echo htmlspecialchars($current_role); ?>
                        </p>
                        <p class="text-xs font-bold text-slate-700"><?php echo htmlspecialchars($user_name); ?></p>
                    </div>
                    <div class="w-10 h-10 bg-slate-100 text-slate-500 rounded-xl flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </a>
            <?php endif; ?>
            
            <div class="h-6 w-[1px] bg-slate-200 hidden sm:block"></div>

            <a href="../actions/logout.php" 
               class="p-2.5 bg-white text-rose-500 rounded-xl border border-rose-100 hover:bg-rose-500 hover:text-white transition-all shadow-sm hover:shadow-rose-100 active:scale-95" 
               title="Logout">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </a>
        </div>
    </nav>