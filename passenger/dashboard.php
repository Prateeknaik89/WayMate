<?php
session_start();
require_once '../config/db.php';
include '../includes/header.php';

// Kick back to landing page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data including new location fields
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Check if they have set their locality
$has_locality = !empty($user['district']) && !empty($user['taluku']);


// 🚀 THE X-RAY QUERY: Added r.status AS ride_status so we know if the driver clicked "End Trip"
$booking_query = "SELECT b.booking_id, b.status AS booking_status, r.status AS ride_status, r.ride_date, r.ride_time, 
                         -- 🚨 NEW: Grab passenger's custom route, but keep the names 'source' and 'dest'!
                         COALESCE(b.pickup_location, sl.location_name) as source, 
                         COALESCE(b.dropoff_location, dl.location_name) as dest,
                         u.name as driver_name, u.user_id as driver_id
                  FROM bookings b
                  LEFT JOIN rides r ON b.ride_id = r.ride_id
                  LEFT JOIN locations sl ON r.source_id = sl.location_id
                  LEFT JOIN locations dl ON r.destination_id = dl.location_id
                  LEFT JOIN users u ON r.driver_id = u.user_id
                  WHERE b.passenger_id = ? 
                  ORDER BY b.booking_id DESC
                  LIMIT 1";

$stmt_book = $pdo->prepare($booking_query);
$stmt_book->execute([$user_id]);
$next_ride = $stmt_book->fetch();
?>

<main class="max-w-4xl mx-auto px-6 py-10">

    <?php if (!$has_locality): ?>
        <div class="mb-10 bg-indigo-900 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-indigo-200 flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
            <div class="relative z-10 text-center md:text-left">
                <h3 class="text-xl font-black mb-1">Personalize your commute 🏠</h3>
                <p class="text-indigo-200 text-sm font-medium">Add your District and Taluk to find rides closer to you.</p>
            </div>
            <a href="complete_profile.php" class="relative z-10 bg-white text-indigo-900 px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-50 transition-all">
                Update Locality
            </a>
            <div class="absolute right-0 top-0 w-64 h-64 bg-white/5 rounded-full -mr-20 -mt-20"></div>
        </div>
    <?php endif; ?>

    <!-- Search locations -->
    <div class="mb-10">
        <h1 class="text-3xl font-black text-slate-800 mb-2">Where are you headed?</h1>
        <p class="text-slate-500 font-medium italic">Showing the best results for <?php echo $has_locality ? htmlspecialchars($user['taluku']) : 'your area'; ?>.</p>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 mb-10">
        <form action="search_results.php" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
            
            <div class="md:col-span-4 relative">
    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4 mb-2">Pickup</label>

    <div class="relative flex items-center">
        <!-- Notice pr-20 so text doesn't go under the two buttons -->
        <input type="text" id="source-input" name="source_name" placeholder="Search locality..." autocomplete="off"
            class="w-full p-4 pr-20 bg-slate-50 rounded-2xl border-none font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500" required>
        
        <!-- NEW: Google Maps Style GPS Button -->
        <button type="button" id="getLocationBtn" class="absolute right-10 text-indigo-500 hover:text-indigo-700 hover:bg-indigo-50 p-1.5 rounded-lg transition-colors flex items-center justify-center" title="Detect Location">
            <!-- GPS Crosshair SVG -->
            <svg id="location-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M12 2v2"></path><path d="M12 20v2"></path>
                <path d="M2 12h2"></path><path d="M20 12h2"></path>
            </svg>
        </button>

        <!-- Your existing clear button -->
        <button type="button" id="clear-source" class="absolute right-4 text-slate-300 hidden hover:text-slate-500">✕</button>
    </div>

    <input type="hidden" name="source_id" id="source-id" required>
        <input type="hidden" name="search_source_lat" id="source-lat">
        <input type="hidden" name="search_source_lon" id="source-lon">

    <div id="source-results" class="absolute w-full mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 hidden overflow-hidden"></div>
</div>
            <div class="md:col-span-4 relative">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4 mb-2">Drop Location</label>
                <div class="relative">
                    <input type="text" id="dest-input" name="destination_name" placeholder="Search destination..." autocomplete="off"
                        class="w-full p-4 pr-12 bg-slate-50 rounded-2xl border-none font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500" required>
                    <button type="button" id="clear-dest" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hidden">✕</button>
                </div>

                <!-- Destination -->
                <input type="hidden" id="dest-id" name="destination_id" required>
                    <input type="hidden" name="search_dest_lat" id="dest-lat">
                    <input type="hidden" name="search_dest_lon" id="dest-lon">


                <div id="dest-results" class="absolute w-full mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 hidden overflow-hidden"></div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4 mb-2">Date</label>
                <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>"
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-700 focus:ring-2 focus:ring-indigo-600 outline-none">
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="w-full h-[60px] bg-slate-900 text-white rounded-2xl font-bold shadow-lg shadow-slate-200 hover:bg-indigo-600 transition-all active:scale-95">
                    Find Rides
                </button>
            </div>
        </form>
    </div>

    <section class="mb-10">
        <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Latest Booking</h2>
        <?php if($next_ride): ?>
            
            <div class="relative">

                <?php if(in_array($next_ride['booking_status'], ['pending', 'confirmed', 'accepted']) && $next_ride['ride_status'] !== 'completed'): ?>
                    <button onclick="toggleTicketMenu(event)" class="absolute top-6 right-6 z-20 w-10 h-10 bg-slate-50 hover:bg-slate-100 rounded-full flex flex-col items-center justify-center gap-[3px] transition-colors shadow-sm">
                        <div class="w-1 h-1 bg-slate-400 rounded-full"></div>
                        <div class="w-1 h-1 bg-slate-400 rounded-full"></div>
                        <div class="w-1 h-1 bg-slate-400 rounded-full"></div>
                    </button>

                    <div id="ticket-menu" class="hidden absolute top-16 right-6 z-30 w-48 bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden origin-top-right transition-all">
                        <form action="../actions/cancel_booking.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this ride?');">
                            <input type="hidden" name="booking_id" value="<?php echo $next_ride['booking_id']; ?>">
                            <button type="submit" class="w-full text-left px-5 py-4 text-xs font-black text-rose-500 hover:bg-rose-50 transition-colors flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Cancel Ride
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <a href="ride_details.php?ride_id=<?php echo $next_ride['booking_id']; ?>" class="block group">
                    <div class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm group-hover:shadow-xl transition-all">
                        
                        <div class="flex justify-between items-start mb-6 pr-12">
                            <?php 
                            // ⭐️ NEW BULLETPROOF LOGIC ⭐️ 
                            // If the driver clicked end trip, the ride_status becomes 'completed'. We check this FIRST.
                            if($next_ride['ride_status'] === 'completed' || $next_ride['booking_status'] === 'completed'): 
                            ?>
                                <span class="bg-emerald-50 text-emerald-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider text-center border border-emerald-100 shadow-sm">Successful 🎉</span>
                            
                            <?php elseif(in_array($next_ride['booking_status'], ['confirmed', 'accepted'])): ?>
                                <span class="bg-indigo-50 text-indigo-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider text-center border border-indigo-100">Confirmed Trip ✅</span>
                            
                            <?php elseif($next_ride['booking_status'] === 'pending'): ?>
                                <span class="bg-amber-50 text-amber-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider text-center border border-amber-100 animate-pulse">Pending Approval ⏳</span>
                            
                            <?php elseif($next_ride['booking_status'] === 'rejected'): ?>
                                <span class="bg-rose-50 text-rose-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider text-center border border-rose-100">Trip Declined ❌</span>
                            
                            <?php elseif($next_ride['booking_status'] === 'cancelled'): ?>
                                <span class="bg-slate-100 text-slate-500 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider text-center border border-slate-200">Cancelled 🚫</span>
                            
                            <?php else: ?>
                                <span class="bg-slate-50 text-slate-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider text-center border border-slate-200">
                                    Status: <?php echo htmlspecialchars($next_ride['booking_status']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <span class="text-xs font-black text-slate-400 mt-1"><?php echo $next_ride['ride_date'] ? date('D, M d', strtotime($next_ride['ride_date'])) : 'N/A'; ?></span>
                        </div>

                        <div class="flex items-center gap-6 mb-8">
                            <div>
                                <h3 class="text-2xl font-black text-slate-800"><?php echo htmlspecialchars($next_ride['source']); ?></h3>
                                <p class="text-[10px] font-black text-slate-400 uppercase">Pickup</p>
                            </div>
                            <div class="text-indigo-400 text-2xl font-light tracking-widest">→</div>
                            <div>
                                <h3 class="text-2xl font-black text-slate-800"><?php echo htmlspecialchars($next_ride['dest']); ?></h3>
                                <p class="text-[10px] font-black text-slate-400 uppercase">Dropoff</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-50 pt-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-black text-sm">
                                    <?php echo strtoupper(substr($next_ride['driver_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase leading-none">Driver</p>
                                    <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($next_ride['driver_name']); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] font-black text-slate-400 uppercase leading-none">Time</p>
                                <p class="text-sm font-black text-slate-800"><?php echo date('h:i A', strtotime($next_ride['ride_time'])); ?></p>
                            </div>
                        </div>

                        <?php if($next_ride['ride_status'] === 'completed' || $next_ride['booking_status'] === 'completed'): ?>
                            <div class="mt-6 pt-6 border-t border-slate-50">
                                <button onclick="event.preventDefault(); window.location.href='rate_driver.php?driver_id=<?php echo $next_ride['driver_id']; ?>&booking_id=<?php echo $next_ride['booking_id']; ?>'" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-[0_8px_20px_rgb(15,23,42,0.2)] hover:bg-indigo-600 hover:-translate-y-1 hover:shadow-[0_8px_25px_rgb(79,70,229,0.3)] transition-all flex items-center justify-center gap-3 border border-slate-800">
                                    <span class="text-amber-400 text-lg leading-none">⭐</span> Rate Driver & Pay
                                </button>
                                <p class="text-center text-[10px] text-slate-400 font-bold mt-4 uppercase tracking-widest">Pay driver directly via Cash or UPI</p>
                            </div>
                        <?php endif; ?>

                    </div>
                </a>
            </div>
        <?php else: ?>
            <div class="bg-white border-2 border-dashed border-slate-200 rounded-[2.5rem] p-12 text-center">
                <div class="text-4xl mb-4 grayscale opacity-20">🎒</div>
                <p class="text-slate-400 font-bold">No trips booked today.</p>
            </div>
        <?php endif; ?>
    </section>

    
    <?php
    $passenger_quotes = [
        ["text" => "Being the passenger princess is a full-time job. Act accordingly.", "icon" => "👑", "color" => "rose"],
        ["text" => "Your driver is not your therapist, but the aux cord is yours if you're brave enough.", "icon" => "🎵", "color" => "indigo"],
        ["text" => "Five stars means they got you there alive. Don't overthink it.", "icon" => "⭐", "color" => "amber"],
        ["text" => "Please don't be that person who eats crunchy snacks in a stranger's car.", "icon" => "🍟", "color" => "emerald"],
        ["text" => "The driver is using GPS. Pointing aggressively at the windshield doesn't help.", "icon" => "🗺️", "color" => "sky"],
        ["text" => "Remember: A 4.9 rating is just a 5.0 passenger who slammed the door too hard.", "icon" => "🚪", "color" => "slate"],
        ["text" => "'I'll tip you in the app' is the biggest lie in history. Prove us wrong.", "icon" => "💸", "color" => "teal"],
        ["text" => "If their music is weird, just nod your head. You are in their house now.", "icon" => "🎧", "color" => "purple"],
        ["text" => "Clicking your seatbelt loudly is the best way to say 'I trust you, but I value my life'.", "icon" => "💺", "color" => "orange"],
        ["text" => "Backseat driving is a federal crime punishable by a 1-star rating.", "icon" => "🤐", "color" => "rose"]
    ];
    $random_pq = $passenger_quotes[array_rand($passenger_quotes)];
    $pc = $random_pq['color']; 
    ?>
    <div class="mt-12 border-t border-slate-100 pt-10 mb-10">
        <div class="max-w-sm">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Passenger Check <?php echo $random_pq['icon']; ?></h3>
            <p class="text-slate-600 font-bold text-lg leading-tight hover:text-<?php echo $pc; ?>-600 transition-colors cursor-default">"<?php echo $random_pq['text']; ?>"</p>
            <div class="mt-2 w-8 h-1 bg-<?php echo $pc; ?>-500 rounded-full"></div>
        </div>
    </div>
<script src="../assets/js/location-handler.js"></script>
<script>
// Passenger Geoapify Autocomplete Logic
const geoapifyKey = "5fb7b88d10c24b6cae2e91954189d334"; 

function setupPassengerAutocomplete(inputId, dropdownId, latId, lonId) {
    const input = document.getElementById(inputId);
    const dropdown = document.getElementById(dropdownId);
    let timeout = null;

    input.addEventListener('input', function() {
        clearTimeout(timeout);
        const query = this.value;
        
        if (query.length < 3) {
            dropdown.innerHTML = '';
            dropdown.classList.add('hidden');
            return;
        }

        timeout = setTimeout(() => {
            fetch(`https://api.geoapify.com/v1/geocode/autocomplete?text=${query}&format=json&apiKey=${geoapifyKey}`)
            .then(response => response.json())
            .then(data => {
                dropdown.innerHTML = '';
                if(data.results.length > 0) {
                    dropdown.classList.remove('hidden');
                    data.results.forEach(loc => {
                        const div = document.createElement('div');
                        div.className = 'p-3 hover:bg-slate-100 cursor-pointer border-b border-slate-50';
                        div.innerText = `${loc.city || loc.name}, ${loc.state}`;
                        
                        // WHEN THEY CLICK THE CITY:
                        div.onclick = () => {
                            input.value = `${loc.city || loc.name}`;
                            // 🚨 THIS IS THE MAGIC: Save the exact GPS coordinates to the hidden fields!
                            document.getElementById(latId).value = loc.lat;
                            document.getElementById(lonId).value = loc.lon;
                            
                            dropdown.classList.add('hidden');
                        };
                        dropdown.appendChild(div);
                    });
                }
            });
        }, 300); // 300ms debounce
    });

    // Hide dropdown if clicked outside
    document.addEventListener('click', (e) => {
        if (e.target !== input && e.target !== dropdown) {
            dropdown.classList.add('hidden');
        }
    });
}

// Activate it for both Source and Destination
setupPassengerAutocomplete('pass-source-input', 'pass-source-dropdown', 'search-source-lat', 'search-source-lon');
setupPassengerAutocomplete('pass-dest-input', 'pass-dest-dropdown', 'search-dest-lat', 'search-dest-lon');
</script>
</main>


<?php include '../includes/footer.php'; ?>