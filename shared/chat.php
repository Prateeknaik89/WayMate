<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['booking_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = $_GET['booking_id'];

// 1. Verify this user is actually part of this booking AND it is officially confirmed!
$verify_stmt = $pdo->prepare("
    SELECT b.*, r.driver_id, p.name as passenger_name, d.name as driver_name 
    FROM bookings b
    JOIN rides r ON b.ride_id = r.ride_id
    JOIN users p ON b.passenger_id = p.user_id
    JOIN users d ON r.driver_id = d.user_id
    WHERE b.booking_id = ? 
    AND b.status = 'confirmed' /* 🚀 THE SECURITY LOCK */
    AND (b.passenger_id = ? OR r.driver_id = ?)
");
$verify_stmt->execute([$booking_id, $user_id, $user_id]);
$booking = $verify_stmt->fetch();

// 2. If the ride isn't confirmed or they don't belong here, show a beautiful error screen
if (!$booking) {
    include '../includes/header.php';
    echo '
    <main class="min-h-[80vh] flex items-center justify-center py-12 px-6">
        <div class="max-w-md w-full bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-10 text-center">
            <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center text-4xl mx-auto mb-6 shadow-inner">
                🔒
            </div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight mb-2">Chat Locked</h1>
            <p class="text-slate-500 font-medium mb-8">Messaging unlocks automatically as soon as the driver accepts the ride request. Hang tight!</p>
            <button onclick="history.back()" class="block w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm shadow-lg hover:bg-slate-800 transition-all">
                Go Back
            </button>
        </div>
    </main>
    ';
    include '../includes/footer.php';
    exit(); // Stop the rest of the page from loading
}

// Determine who they are talking to
$is_driver = ($user_id == $booking['driver_id']);
$chat_partner_name = $is_driver ? $booking['passenger_name'] : $booking['driver_name'];
$chat_partner_id = $is_driver ? $booking['passenger_id'] : $booking['driver_id'];
$back_link = $is_driver ? '../driver/dashboard.php' : '../passenger/dashboard.php';

// 3. Fetch Chat History
$msg_stmt = $pdo->prepare("SELECT * FROM messages WHERE booking_id = ? ORDER BY created_at ASC");
$msg_stmt->execute([$booking_id]);
$messages = $msg_stmt->fetchAll();

include '../includes/header.php';
?>

<main class="max-w-md mx-auto bg-white min-h-[90vh] shadow-2xl relative flex flex-col">
    
    <div class="bg-white border-b border-slate-100 p-4 flex items-center gap-4 sticky top-0 z-10">
        <a href="<?php echo $back_link; ?>" class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors">
            ←
        </a>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-black">
                <?php echo strtoupper(substr($chat_partner_name, 0, 1)); ?>
            </div>
            <div>
                <h2 class="font-black text-slate-800 leading-tight"><?php echo htmlspecialchars($chat_partner_name); ?></h2>
                <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">Connected</p>
            </div>
        </div>
    </div>

    <div class="flex-1 p-6 overflow-y-auto bg-slate-50 space-y-4" id="chat-box">
        
        <div class="text-center mb-6">
            <span class="bg-slate-200 text-slate-500 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full">Chat Started</span>
        </div>

        <?php foreach($messages as $msg): 
            $is_me = ($msg['sender_id'] == $user_id);
        ?>
            <div class="flex <?php echo $is_me ? 'justify-end' : 'justify-start'; ?>">
                <div class="max-w-[75%] <?php echo $is_me ? 'bg-indigo-600 text-white rounded-t-2xl rounded-bl-2xl' : 'bg-white border border-slate-100 text-slate-700 rounded-t-2xl rounded-br-2xl'; ?> p-4 shadow-sm">
                    <p class="text-sm font-medium"><?php echo htmlspecialchars($msg['message']); ?></p>
                    <p class="text-[9px] font-bold mt-1 <?php echo $is_me ? 'text-indigo-200 text-right' : 'text-slate-400'; ?>">
                        <?php echo date('h:i A', strtotime($msg['created_at'])); ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
        
    </div>

    <div class="bg-white border-t border-slate-100 p-4 sticky bottom-0">
        <form action="../actions/send_message.php" method="POST" class="flex items-center gap-2" id="chat-form">
            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
            <input type="hidden" name="receiver_id" value="<?php echo $chat_partner_id; ?>">
            
            <input type="text" name="message" placeholder="Message <?php echo explode(' ', $chat_partner_name)[0]; ?>..." required autocomplete="off"
                   class="flex-1 bg-slate-50 border-none rounded-full py-4 px-6 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-600 outline-none">
            
            <button type="submit" class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center text-white hover:bg-indigo-700 transition-transform active:scale-95 shadow-md">
                ➤
            </button>
        </form>
    </div>

</main>

<script>
    const chatBox = document.getElementById('chat-box');
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.querySelector('input[name="message"]');
    const bookingId = <?php echo $booking_id; ?>;

    // 🚀 THE FIX: A dedicated function that gives the browser 10ms to draw the bubbles before scrolling
    function scrollToBottom() {
        setTimeout(() => {
            chatBox.scrollTo({
                top: chatBox.scrollHeight,
                behavior: 'smooth' // Adds a nice slick sliding animation!
            });
        }, 10);
    }

    // Scroll to bottom immediately on page load
    scrollToBottom();

// 1. AJAX FORM SUBMISSION
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        // 🚀 THE FIX: Scroll down instantly the millisecond they hit send!
        scrollToBottom();

        const formData = new FormData(this);
        formData.append('ajax', 'true'); 

        fetch('../actions/send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            messageInput.value = ''; 
            fetchNewMessages(true); // Fetch the new bubble and ensure it stays at the bottom
        })
        .catch(err => console.error("Message Send Error:", err));
    });

    // 2. BACKGROUND POLLING
    function fetchNewMessages(forceScroll = false) {
        fetch(`../actions/fetch_messages.php?booking_id=${bookingId}`)
        .then(response => {
            if(!response.ok) throw new Error("Fetch failed");
            return response.text();
        })
        .then(html => {
            // Check if they are already at the bottom BEFORE we add new HTML
            const isScrolledToBottom = chatBox.scrollHeight - chatBox.clientHeight <= chatBox.scrollTop + 50;
            
            chatBox.innerHTML = html; 
            
            // 🚀 Only scroll if they just sent a message (forceScroll), OR if they were already at the bottom
            if(forceScroll || isScrolledToBottom) {
                scrollToBottom();
            }
        })
        .catch(err => console.error("Fetch Loop Error:", err));
    }

    // Run the background fetch every 2 seconds, but DO NOT force scroll
    setInterval(() => fetchNewMessages(false), 2000);
</script>


<?php include '../includes/footer.php'; ?>