<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayMate | Smart Commuting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-900">

    <nav class="flex justify-between items-center px-6 py-4 bg-white/80 backdrop-blur-md sticky top-0 z-50">
        <div class="text-2xl font-extrabold text-indigo-600 tracking-tight">WayMate</div>
        <div class="space-x-4">
            <a href="login.php" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition">Login</a>
            <a href="register.php" class="bg-indigo-600 text-white px-5 py-2.5 rounded-full text-sm font-bold hover:shadow-lg hover:shadow-indigo-200 transition">Join Community</a>
        </div>
    </nav>

    <header class="px-6 py-16 text-center max-w-4xl mx-auto">
        <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest">BCA Final Year Project 2026</span>
        <h1 class="mt-6 text-5xl md:text-6xl font-extrabold leading-tight tracking-tighter">
            Share your ride, <br><span class="text-indigo-600">Split the cost.</span>
        </h1>
        <p class="mt-6 text-lg text-slate-600 leading-relaxed">
            Reducing travel expenses by up to 70% for the students of <br><strong>Himalaya BCA College, Ankola.</strong>
        </p>
        
        <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="search.php" class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-bold text-lg hover:bg-slate-800 transition">Find a Ride</a>
            <a href="offer.php" class="bg-white border-2 border-slate-200 text-slate-900 px-8 py-4 rounded-2xl font-bold text-lg hover:border-indigo-600 hover:text-indigo-600 transition">Offer a Seat</a>
        </div>
    </header>

    <section class="px-6 py-12 bg-white border-y border-slate-100">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-6xl mx-auto">
            <div class="text-center">
                <div class="text-3xl font-bold text-indigo-600">70%</div>
                <div class="text-sm text-slate-500 font-medium">Cost Savings</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-indigo-600">100%</div>
                <div class="text-sm text-slate-500 font-medium">Verified Peers</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-indigo-600">ECO</div>
                <div class="text-sm text-slate-500 font-medium">Reduced Emissions</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-indigo-600">Local</div>
                <div class="text-sm text-slate-500 font-medium">Ankola Focused</div>
            </div>
        </div>
    </section>

</body>
</html>