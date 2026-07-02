<?php
session_start();
require_once '../config/db.php';

// 1. Check if the user is logged in and has a booking ID
if (!isset($_SESSION['user_id']) || !isset($_GET['booking_id'])) {
    http_response_code(403);
    exit("Unauthorized Access");
}

try {
    $user_id = $_SESSION['user_id'];
    $booking_id = $_GET['booking_id'];

    // 2. Fetch Chat History
    $msg_stmt = $pdo->prepare("SELECT * FROM messages WHERE booking_id = ? ORDER BY created_at ASC");
    $msg_stmt->execute([$booking_id]);
    $messages = $msg_stmt->fetchAll();

    // 3. Generate the HTML for the chat bubbles
    echo '<div class="text-center mb-6">
            <span class="bg-slate-200 text-slate-500 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full">Chat Started</span>
          </div>';

    foreach($messages as $msg) {
        $is_me = ($msg['sender_id'] == $user_id);
        $alignment = $is_me ? 'justify-end' : 'justify-start';
        $bubble_color = $is_me ? 'bg-indigo-600 text-white rounded-t-2xl rounded-bl-2xl' : 'bg-white border border-slate-100 text-slate-700 rounded-t-2xl rounded-br-2xl';
        $time_color = $is_me ? 'text-indigo-200 text-right' : 'text-slate-400';
        
        echo '
        <div class="flex ' . $alignment . ' mb-4">
            <div class="max-w-[75%] ' . $bubble_color . ' p-4 shadow-sm">
                <p class="text-sm font-medium">' . htmlspecialchars($msg['message']) . '</p>
                <p class="text-[9px] font-bold mt-1 ' . $time_color . '">
                    ' . date('h:i A', strtotime($msg['created_at'])) . '
                </p>
            </div>
        </div>';
    }

} catch (PDOException $e) {
    // If the database crashes, this will catch it and tell us exactly what went wrong!
    http_response_code(500);
    echo "DB Error: " . $e->getMessage();
}
?>