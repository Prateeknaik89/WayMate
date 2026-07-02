<?php include '../includes/header.php'; ?>

<main class="max-w-md mx-auto px-6 py-20 text-center">
    <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-8 animate-bounce">
        ✅
    </div>
    
    <h1 class="text-3xl font-black text-slate-800 mb-2">Seat Reserved!</h1>
    <p class="text-slate-500 font-medium mb-10">Your ride is confirmed. Get your playlist ready, bro.</p>

    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl mb-10 relative overflow-hidden">
        <div class="absolute -left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-[#F8FAFC] rounded-full border-r border-slate-100"></div>
        <div class="absolute -right-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-[#F8FAFC] rounded-full border-l border-slate-100"></div>
        
        <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em] mb-4 text-center">WayMate Official Pass</p>
        <div class="flex justify-between items-center py-4 border-b border-dashed border-slate-100 mb-4">
             <span class="text-xs font-bold text-slate-400">Booking ID</span>
             <span class="text-xs font-black text-slate-800">#WM-<?php echo rand(1000, 9999); ?></span>
        </div>
        <p class="text-xs font-bold text-indigo-600 uppercase mb-1">Status</p>
        <p class="text-xl font-black text-slate-800 italic uppercase tracking-tighter">Confirmed</p>
    </div>

    <a href="dashboard.php" class="inline-block text-slate-400 font-bold hover:text-indigo-600 transition underline">
        Back to Dashboard
    </a>
</main>

<?php include '../includes/footer.php'; ?>