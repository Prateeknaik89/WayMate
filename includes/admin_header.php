<?php
// waymate/includes/admin_header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_name = $_SESSION['user_name'] ?? 'Founder';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; } 
        html { scroll-behavior: smooth; }
    </style>
</head>

<body class="text-slate-900 min-h-screen relative overflow-x-hidden">

    <nav class="flex justify-between items-center px-6 md:px-12 py-4 bg-slate-900 text-white border-b border-slate-800 sticky top-0 z-50 shadow-2xl">
        
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center text-white font-black shadow-lg shadow-indigo-500/30">W</div>
            <div class="flex flex-col">
                <span class="text-xl font-black tracking-tighter leading-none">WayMate</span>
                <span class="text-[9px] font-black tracking-widest text-indigo-400 uppercase mt-1">Command Center</span>
            </div>
        </div>

        <div class="flex items-center gap-5">
            
            <div class="flex items-center gap-3 text-right">
                <div class="hidden sm:block">
                    <p class="text-[9px] font-black text-amber-400 uppercase tracking-widest leading-none mb-1">
                        👑 Founder
                    </p>
                    <p class="text-xs font-bold text-slate-300"><?php echo htmlspecialchars($user_name); ?></p>
                </div>
                <div class="w-10 h-10 bg-slate-800 text-amber-400 rounded-xl flex items-center justify-center shadow-inner border border-slate-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>

            <div class="h-6 w-[1px] bg-slate-700 hidden sm:block"></div>

            <a href="../actions/logout.php" 
               class="p-2.5 bg-slate-800 text-rose-400 rounded-xl border border-slate-700 hover:bg-rose-500 hover:text-white transition-all shadow-sm hover:shadow-rose-500/20 active:scale-95" 
               title="Secure Logout">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </a>
        </div>
    </nav>