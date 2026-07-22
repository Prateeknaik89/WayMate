<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayMate | Ride-Sharing for Himalaya BCA College</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/style.css">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; } </style>
</head>
<body class="bg-white text-slate-900">

    <nav class="flex justify-between items-center px-6 py-5 sticky top-0 bg-white/90 backdrop-blur-md z-50 border-b border-slate-100">
        <div class="flex items-center gap-2">
    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-200">
        W
    </div>
    <span class="text-xl font-extrabold tracking-tight text-slate-800">WayMate</span>
</div>
        <div class="hidden md:flex gap-8 text-sm font-bold text-slate-500">
            <a href="#how" class="hover:text-indigo-600 transition">How it Works</a>
            <a href="#benefits" class="hover:text-indigo-600 transition">Benefits</a>
            <a href="#impact" class="hover:text-indigo-600 transition">Our Impact</a>
        </div>
        <div class="flex gap-3">
            <a href="login.php" class="px-5 py-2.5 text-sm font-bold text-indigo-600 hover:bg-indigo-50 rounded-xl transition">Login</a>
            <button onclick="toggleModal()" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition">Get Started</button>
        </div>
    </nav>

    <header class="relative px-6 pt-20 pb-32 text-center overflow-hidden">
        <div class="max-w-4xl mx-auto relative z-10">
            <span class="inline-block py-1.5 px-4 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold uppercase tracking-widest mb-6">Beta Version 1.0</span>
            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight leading-[1.1] mb-8 italic">
                Rides shared. <br><span class="text-indigo-600 font-black">Costs split.</span>
            </h1>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto mb-10 leading-relaxed">
                The smart way to commute. Connect with verified peers heading your way and turn those empty seats into savings.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="toggleModal()" class="bg-slate-900 text-white px-10 py-4 rounded-2xl font-bold text-lg hover:bg-slate-800 transition shadow-xl">Find a Ride</button>
                <button onclick="toggleModal()" class="bg-white border-2 border-slate-200 text-slate-900 px-10 py-4 rounded-2xl font-bold text-lg hover:border-indigo-600 transition">Offer a Seat</button>
            </div>
        </div>
    </header>

    <section class="py-12 px-6">
        <div class="max-w-5xl mx-auto grid md:grid-cols-2 gap-8 items-center bg-indigo-50 p-10 rounded-[2rem]">
            <div>
                <h2 class="text-3xl font-extrabold mb-4">Built for your daily grind.</h2>
                <p class="text-slate-600 leading-relaxed">
                    Stop overpaying for solo trips. WayMate connects local commuters to optimize travel, reduce carbon footprints, and put money back in your pocket. 
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <span class="bg-white px-4 py-2 rounded-full text-sm font-bold shadow-sm">#EcoFriendly</span>
                <span class="bg-white px-4 py-2 rounded-full text-sm font-bold shadow-sm">#StudentSavings</span>
                <span class="bg-white px-4 py-2 rounded-full text-sm font-bold shadow-sm">#VerifiedOnly</span>
                <span class="bg-white px-4 py-2 rounded-full text-sm font-bold shadow-sm">#ZeroCommission</span>
            </div>
        </div>
    </section>

    <section id="how" class="py-24 px-6 bg-slate-50">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-extrabold mb-4">How it works</h2>
                <p class="text-slate-500">Simple, secure, and student-friendly.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-12">
                <div class="text-center p-8 bg-white rounded-3xl shadow-sm border border-slate-100">
                    <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-6">1</div>
                    <h3 class="text-xl font-bold mb-3">Post or Search</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Drivers share their route; passengers search for matching trips heading to college.</p>
                </div>
                <div class="text-center p-8 bg-white rounded-3xl shadow-sm border border-slate-100">
                    <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-6">2</div>
                    <h3 class="text-xl font-bold mb-3">Connect & Verify</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Review profiles and ratings. Safety is ensured through tiered ID verification.</p>
                </div>
                <div class="text-center p-8 bg-white rounded-3xl shadow-sm border border-slate-100">
                    <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-6">3</div>
                    <h3 class="text-xl font-bold mb-3">Share & Save</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Meet at the location, share the ride, and split fuel costs automatically.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="benefits" class="py-24 px-6">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row gap-16 items-center">
            <div class="md:w-1/2">
                <h2 class="text-4xl font-extrabold mb-6 leading-tight">Why choose <br><span class="text-indigo-600">WayMate?</span></h2>
                <p class="text-slate-600 mb-8">We address the core challenges of urban daily commuting by optimizing existing resources.</p>
                
                <ul class="space-y-6">
                    <li class="flex gap-4">
                        <div class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center flex-shrink-0 mt-1">✓</div>
                        <div>
                            <span class="font-bold block">70% Cost Reduction</span>
                            <span class="text-sm text-slate-500 italic">Save more than commercial taxis.</span>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <div class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center flex-shrink-0 mt-1">✓</div>
                        <div>
                            <span class="font-bold block">Zero Data Risk</span>
                            <span class="text-sm text-slate-500 italic">Transient client-side verification for privacy.</span>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <div class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center flex-shrink-0 mt-1">✓</div>
                        <div>
                            <span class="font-bold block">Community Trusted</span>
                            <span class="text-sm text-slate-500 italic">Built for the students, by the students.</span>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="md:w-1/2 grid grid-cols-1 gap-6">
                <div class="p-6 bg-slate-900 rounded-3xl text-white shadow-xl">
                    <p class="text-indigo-400 font-bold text-sm mb-2">The Problem</p>
                    <p class="text-lg italic font-medium">"Most private vehicles run with vacant seats, wasting fuel and increasing congestion."</p>
                </div>
                <div class="p-6 bg-indigo-600 rounded-3xl text-white shadow-xl">
                    <p class="text-indigo-200 font-bold text-sm mb-2">Our Mission</p>
                    <p class="text-lg italic font-medium">"Maximize seat occupancy to lower emissions and daily commute costs."</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-slate-50 border-t border-slate-200 py-12 px-6">
        <div class="max-w-6xl mx-auto text-center">
            <p class="text-slate-400 text-sm">© 2026 Himalaya BCA College, Ankola.</p>
            <p class="text-slate-500 font-bold mt-2">WayMate: A Peer-to-Peer Ride Sharing Solution</p>
        </div>
    </footer>

    <div id="authModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-[2.5rem] p-8 shadow-2xl relative">
            <button onclick="toggleModal()" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
            
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-black">Join WayMate</h2>
                <p class="text-slate-500 text-sm">One step away from smarter commutes.</p>
            </div>

            <form action="actions/register_lite.php" method="POST" class="space-y-6">
    
    <div class="grid grid-cols-2 gap-4 mb-6">
        <label class="cursor-pointer group">
            <input type="radio" name="role" value="passenger" checked class="hidden peer">
            <div class="p-4 rounded-2xl border-2 border-slate-100 bg-slate-50 text-center peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all">
                <span class="block text-2xl mb-1">🎒</span>
                <span class="block text-xs font-black text-slate-500 uppercase tracking-tighter group-hover:text-indigo-600">Passenger</span>
            </div>
        </label>

        <label class="cursor-pointer group">
            <input type="radio" name="role" value="driver" class="hidden peer">
            <div class="p-4 rounded-2xl border-2 border-slate-100 bg-slate-50 text-center peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all">
                <span class="block text-2xl mb-1">🚗</span>
                <span class="block text-xs font-black text-slate-500 uppercase tracking-tighter group-hover:text-indigo-600">Driver</span>
            </div>
        </label>
    </div>

    <div class="space-y-3">
        <input type="text" name="name" placeholder="Full Name" required class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none font-medium">
        <input type="tel" name="phone" placeholder="Phone Number" required class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none font-medium">
        <input type="password" name="password" placeholder="Create Password" required class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none font-medium">
    </div>
    
    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold shadow-lg hover:bg-indigo-700 transition">
        Create Account
    </button>
</form>
        </div>
    </div>

    <script>
        function toggleModal() {
            document.getElementById('authModal').classList.toggle('hidden');
        }
    </script>

</body>
</html>