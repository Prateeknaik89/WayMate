<?php
require_once '../config/db.php';
include '../includes/header.php';

// Check for booking_id instead of ride_id for better security
if (!isset($_GET['ride_id'])) {
    header("Location: dashboard.php");
    exit();
}

$booking_id = $_GET['ride_id'];
$user_id = $_SESSION['user_id'];

// Fetch details JOINING the booking to the ride and driver
$query = "SELECT b.booking_id, b.status as booking_status,
                 r.ride_date, r.ride_time, r.price_per_seat,
                 u.name as driver_name, u.phone as driver_phone, u.bio as driver_bio, 
                 u.car_model, u.car_number,
                 -- Here is the magic: Grab custom route, fallback to driver route if empty
                 COALESCE(b.pickup_location, sl.location_name) as source_name, 
                 COALESCE(b.dropoff_location, dl.location_name) as dest_name
          FROM bookings b
          JOIN rides r ON b.ride_id = r.ride_id
          JOIN users u ON r.driver_id = u.user_id
          JOIN locations sl ON r.source_id = sl.location_id
          JOIN locations dl ON r.destination_id = dl.location_id
          WHERE b.booking_id = ? AND b.passenger_id = ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$booking_id, $user_id]);
$ride = $stmt->fetch();

if (!$ride) {
    echo "<div class='p-10 text-center font-bold text-slate-500'>Ticket not found or access denied.</div>";
    include '../includes/footer.php';
    exit();
}
?>

<main class="max-w-2xl mx-auto px-6 py-10">
    
    <div class="flex justify-between items-center mb-8">
        <a href="dashboard.php" class="inline-flex items-center text-xs font-black text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition">
            ← Dashboard
        </a>
        <?php if($ride['booking_status'] === 'confirmed'): ?>
            <span class="px-4 py-1.5 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                Active Ticket
            </span>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-[3rem] shadow-2xl shadow-indigo-100/50 border border-slate-100 overflow-hidden mb-10">
        
        <div class="bg-slate-900 p-10 text-white relative overflow-hidden">
            <div class="absolute -left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-slate-50 rounded-full"></div>
            <div class="absolute -right-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-slate-50 rounded-full"></div>

            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-50 mb-4 text-indigo-400">WayMate Journey</p>
                <h1 class="text-3xl font-black flex items-center gap-4 mb-2">
                    <?php echo $ride['source_name']; ?> 
                    <span class="text-indigo-500 text-xl">→</span> 
                    <?php echo $ride['dest_name']; ?>
                </h1>
                <div class="flex gap-6 mt-4">
                    <div>
                        <p class="text-[10px] font-black uppercase opacity-40">Date</p>
                        <p class="font-bold"><?php echo date('D, M d', strtotime($ride['ride_date'])); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase opacity-40">Departure</p>
                        <p class="font-bold"><?php echo date('h:i A', strtotime($ride['ride_time'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-10 bg-white">
            <div class="flex items-center gap-5 mb-10">
                <div class="w-16 h-16 bg-indigo-50 rounded-3xl flex items-center justify-center text-2xl font-black text-indigo-600">
                    <?php echo strtoupper(substr($ride['driver_name'], 0, 1)); ?>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Driver Profile</p>
                    <h2 class="text-xl font-black text-slate-800"><?php echo htmlspecialchars($ride['driver_name']); ?></h2>
                    <p class="text-xs text-slate-500 font-medium italic">"<?php echo $ride['driver_bio'] ?: 'Let\'s have a great ride!'; ?>"</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-10">
                <div class="p-5 bg-slate-50 rounded-[2rem] border border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Car Model</p>
                    <p class="font-bold text-slate-800"><?php echo $ride['car_model'] ?: 'Standard Car'; ?></p>
                </div>
                <div class="p-5 bg-slate-50 rounded-[2rem] border border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Plate Number</p>
                    <p class="font-bold text-slate-800 tracking-wider"><?php echo $ride['car_number'] ?: 'N/A'; ?></p>
                </div>
            </div>

            <div class="flex flex-col gap-3 mb-10">
    
    <a href="../shared/chat.php?booking_id=<?php echo $ride['booking_id']; ?>" 
       class="w-full flex items-center justify-between bg-slate-900 text-white p-5 rounded-[1.5rem] hover:bg-indigo-600 transition-all group shadow-xl shadow-slate-200/50">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center text-xl">
                💬
            </div>
            <div class="text-left">
                <p class="font-black text-sm text-white">Message Driver</p>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-0.5">Secure In-App Chat</p>
            </div>
        </div>
        <div class="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center group-hover:bg-white/20 transition-all">
            <span class="text-white opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all">→</span>
        </div>
    </a>

    <div class="grid grid-cols-2 gap-3">
        <a href="tel:<?php echo $ride['driver_phone']; ?>" 
           class="flex items-center justify-center gap-3 bg-white border-2 border-slate-100 text-slate-700 py-4 rounded-[1.5rem] font-black text-xs uppercase tracking-widest hover:border-slate-300 hover:bg-slate-50 transition-all">
            <span class="text-lg">📞</span> Call
        </a>
        
        <a href="https://wa.me/<?php echo $ride['driver_phone']; ?>" target="_blank"
           class="flex items-center justify-center gap-3 bg-[#25D366]/10 text-[#25D366] py-4 rounded-[1.5rem] font-black text-xs uppercase tracking-widest hover:bg-[#25D366] hover:text-white transition-all shadow-sm">
            <span class="text-lg drop-shadow-sm">📱</span> WhatsApp
        </a>
    </div>
    
</div>

            <div id="ticket-card" class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl ...">
    </div>

    <button onclick="shareTicket()" id="share-btn" class="mt-6 w-full flex items-center justify-center gap-3 bg-indigo-50 text-indigo-600 py-4 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-indigo-100 transition-all active:scale-95">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
         </svg>
     Share Ticket
    </button>
        </div>

        <div class="bg-slate-50 p-6 border-t border-dashed border-slate-200 text-center">
            <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.4em]">Booking Ref: #WM-<?php echo $booking_id; ?>-<?php echo date('Y'); ?></p>
        </div>
    </div>

</main>
<script>
function shareTicket() {
    const ticketElement = document.getElementById('ticket-card');
    const shareBtn = document.getElementById('share-btn');
    
    // Optional: Change button text to show it's loading
    const originalText = shareBtn.innerHTML;
    shareBtn.innerHTML = '⏳ Generating Image...';
    shareBtn.disabled = true;

    // html2canvas takes a picture of the div
    html2canvas(ticketElement, {
        scale: 2, // Makes the image high-resolution (Retina quality)
        backgroundColor: '#ffffff', // Ensures the background isn't transparent
        useCORS: true // Helps load any external fonts or avatars properly
    }).then(canvas => {
        // Convert the canvas to an image file
        canvas.toBlob(blob => {
            const file = new File([blob], 'waymate-ticket.png', { type: 'image/png' });

            // Check if the user's browser/phone supports Native File Sharing
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                navigator.share({
                    title: 'My WayMate Ride',
                    text: 'Catching a ride with WayMate! 🚗💨 Check out my ticket:',
                    files: [file]
                }).then(() => {
                    console.log('Shared successfully!');
                }).catch((error) => {
                    console.log('Sharing failed or was cancelled', error);
                });
            } else {
                // FALLBACK for PC users or older browsers: Download the image directly
                const link = document.createElement('a');
                link.download = 'waymate-ticket.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                alert("Ticket downloaded! You can now send it to your friends.");
            }
            
            // Reset the button
            shareBtn.innerHTML = originalText;
            shareBtn.disabled = false;
        }, 'image/png');
    });
}
</script>

<?php include '../includes/footer.php'; ?>  