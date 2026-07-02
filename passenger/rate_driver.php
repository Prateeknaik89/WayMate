<?php
session_start();
require_once '../config/db.php';

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$driver_id = $_GET['driver_id'] ?? null;
$booking_id = $_GET['booking_id'] ?? null;
$success_message = '';
$error_message = '';

// If they didn't pass a driver to rate, kick them back
if (!$driver_id || !$booking_id) {
    header("Location: dashboard.php");
    exit();
}

// Fetch driver details so we know who we are rating
$stmt_driver = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
$stmt_driver->execute([$driver_id]);
$driver = $stmt_driver->fetch();

if (!$driver) {
    $error_message = "Driver not found.";
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $driver) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        $error_message = "Please select a star rating.";
    } else {
        // Check if already reviewed to prevent duplicates
        $stmt_check = $pdo->prepare("SELECT review_id FROM reviews WHERE booking_id = ? AND reviewer_id = ?");
        $stmt_check->execute([$booking_id, $user_id]);
        
        if ($stmt_check->rowCount() > 0) {
            $error_message = "You have already reviewed this trip!";
        } else {
            // Insert the review
            $insertStmt = $pdo->prepare("INSERT INTO reviews (booking_id, reviewer_id, driver_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
            if ($insertStmt->execute([$booking_id, $user_id, $driver_id, $rating, $comment])) {
                $success_message = "Payment confirmed & feedback saved! Thank you.";
            } else {
                $error_message = "Something went wrong. Please try again.";
            }
        }
    }
}

include '../includes/header.php';
?>

<main class="max-w-xl mx-auto px-6 py-12">

    <?php if ($success_message): ?>
        <div class="bg-emerald-50 border-2 border-emerald-200 rounded-[2.5rem] p-10 text-center text-emerald-800 shadow-xl shadow-emerald-100">
            <div class="text-6xl mb-4">🌟</div>
            <h3 class="text-2xl font-black mb-2">Review Submitted!</h3>
            <p class="font-medium text-emerald-700/80 mb-8"><?php echo htmlspecialchars($success_message); ?></p>
            <a href="dashboard.php" class="bg-emerald-500 text-white px-8 py-4 rounded-2xl font-black text-sm uppercase tracking-widest shadow-lg shadow-emerald-200 hover:-translate-y-1 hover:shadow-xl transition-all">
                Back to Dashboard
            </a>
        </div>
    <?php else: ?>

        <div class="text-center mb-10">
            <div class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-[2rem] flex items-center justify-center mx-auto mb-6 shadow-[0_10px_20px_rgb(99,102,241,0.2)] text-4xl border border-white">
                🤝
            </div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Rate your Trip</h1>
            <p class="text-slate-500 font-medium mt-2">How was your ride with <span class="font-bold text-slate-800"><?php echo htmlspecialchars($driver['name'] ?? 'your driver'); ?></span>?</p>
        </div>

        <?php if ($error_message): ?>
            <div class="bg-rose-50 text-rose-600 px-6 py-4 rounded-2xl font-bold text-sm mb-8 flex items-center gap-3 border border-rose-100 shadow-sm">
                ⚠️ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-slate-100 rounded-[2.5rem] p-8 md:p-10 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
            <form action="rate_driver.php?driver_id=<?php echo $driver_id; ?>&booking_id=<?php echo $booking_id; ?>" method="POST" class="space-y-8">
                
                <div class="text-center">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Overall Experience</label>
                    
                    <input type="hidden" name="rating" id="rating-val" value="0">
                    
                    <div class="flex justify-center gap-2 flex-row-reverse" id="star-container">
                        <button type="button" class="star-btn text-5xl text-slate-200 hover:text-amber-400 transition-colors drop-shadow-sm" data-value="5">★</button>
                        <button type="button" class="star-btn text-5xl text-slate-200 hover:text-amber-400 transition-colors drop-shadow-sm" data-value="4">★</button>
                        <button type="button" class="star-btn text-5xl text-slate-200 hover:text-amber-400 transition-colors drop-shadow-sm" data-value="3">★</button>
                        <button type="button" class="star-btn text-5xl text-slate-200 hover:text-amber-400 transition-colors drop-shadow-sm" data-value="2">★</button>
                        <button type="button" class="star-btn text-5xl text-slate-200 hover:text-amber-400 transition-colors drop-shadow-sm" data-value="1">★</button>
                    </div>
                    <p id="rating-text" class="text-xs font-bold text-amber-500 mt-4 h-4 uppercase tracking-widest"></p>
                </div>

                <style>
                    /* Magic CSS to make previous stars light up on hover */
                    #star-container .star-btn:hover,
                    #star-container .star-btn:hover ~ .star-btn {
                        color: #fbbf24; /* Tailwind amber-400 */
                    }
                    .star-active { color: #fbbf24 !important; }
                </style>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Leave a Comment (Optional)</label>
                    <textarea name="comment" rows="4" placeholder="Great music, safe driving, very punctual..." class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner resize-none"></textarea>
                </div>

                <button type="submit" id="submit-btn" disabled class="w-full bg-slate-200 text-slate-400 rounded-2xl px-6 py-4 font-black text-sm uppercase tracking-widest transition-all">
                    Select a rating to submit
                </button>
                <p class="text-[10px] text-center text-slate-400 font-bold tracking-wider uppercase mt-4">🔒 Confirming this will finalize the ride</p>
            </form>
        </div>

    <?php endif; ?>
</main>

<script>
    // Simple logic to handle the star clicks and UI updates
    const stars = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('rating-val');
    const submitBtn = document.getElementById('submit-btn');
    const ratingText = document.getElementById('rating-text');
    
    const ratingDescriptions = {
        1: "Terrible 😞",
        2: "Below Average 😕",
        3: "Okay 😐",
        4: "Good 🙂",
        5: "Excellent! 🤩"
    };

    stars.forEach(star => {
        star.addEventListener('click', () => {
            const value = star.getAttribute('data-value');
            ratingInput.value = value;
            
            // Update text
            ratingText.innerText = ratingDescriptions[value];
            
            // Enable button and style it
            submitBtn.disabled = false;
            submitBtn.classList.remove('bg-slate-200', 'text-slate-400');
            submitBtn.classList.add('bg-slate-900', 'text-white', 'shadow-[0_8px_20px_rgb(15,23,42,0.2)]', 'hover:-translate-y-1', 'hover:bg-indigo-600');
            submitBtn.innerText = 'Submit Feedback';

            // Highlight selected stars permanently
            stars.forEach(s => {
                if (s.getAttribute('data-value') <= value) {
                    s.classList.add('star-active');
                } else {
                    s.classList.remove('star-active');
                }
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>