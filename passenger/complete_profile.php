<?php
session_start();
require_once '../config/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $state = $_POST['state'];
    $district = $_POST['district'];
    $taluku = $_POST['taluku'];
    $phone = $_POST['phone']; 

    $update_sql = "UPDATE users SET 
                   state = ?, 
                   district = ?, 
                   taluku = ?, 
                   phone = ?, 
                   profile_completed = 1 
                   WHERE user_id = ?";
    
    $stmt = $pdo->prepare($update_sql);
    if ($stmt->execute([$state, $district, $taluku, $phone, $user_id])) {
        $_SESSION['profile_completed'] = 1;
        // Redirect based on role
        $redirect = ($_SESSION['role'] === 'driver') ? '../driver/dashboard.php' : 'dashboard.php';
        header("Location: $redirect?status=profile_updated");
        exit();
    } else {
        $message = "Something went wrong. Please try again.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

include '../includes/header.php';
?>

<main class="min-h-[80vh] flex items-center justify-center py-12 px-6">
    <div class="max-w-xl w-full">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-600 rounded-2xl text-white font-black text-xl mb-4 shadow-xl shadow-indigo-100">W</div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Set Your Home Base</h1>
            <p class="text-slate-500 font-medium mt-2">We'll show you rides starting from your area.</p>
        </div>

        <?php if($message): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-2xl mb-6 font-bold text-center border border-red-100">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100">
                
                <div class="mb-6">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-4 mb-2">Phone Number</label>
                    <input type="text" name="phone" required maxlength="10" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                           placeholder="Enter 10 digit number"
                           class="w-full p-4 bg-slate-50 rounded-2xl border-2 border-transparent focus:border-indigo-500 focus:bg-white outline-none font-bold text-slate-800 transition-all">
                </div>

                <hr class="border-slate-50 mb-8">

                <div class="mb-6">
                    <label class="block text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] ml-4 mb-2">Enter Pincode (Fetch Area)</label>
                    <input type="text" id="pincode-input" maxlength="6" placeholder="e.g. 560064"
                           class="w-full p-4 bg-indigo-50/50 rounded-2xl border-2 border-dashed border-indigo-200 focus:border-indigo-500 focus:bg-white outline-none font-black text-indigo-600 transition-all">
                    <p id="pincode-loader" class="text-[10px] text-indigo-400 font-bold ml-4 mt-2 hidden">Searching for your area...</p>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-4 mb-2">State</label>
                        <input type="text" name="state" id="state-field" readonly required value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>"
                               class="w-full p-4 bg-slate-100 rounded-2xl border-none font-bold text-slate-500 cursor-not-allowed">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-4 mb-2">District</label>
                            <input type="text" name="district" id="district-field" readonly required value="<?php echo htmlspecialchars($user['district'] ?? ''); ?>"
                                   class="w-full p-4 bg-slate-100 rounded-2xl border-none font-bold text-slate-500 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-4 mb-2">Taluku</label>
                            <input type="text" name="taluku" id="taluku-field" readonly required value="<?php echo htmlspecialchars($user['taluku'] ?? ''); ?>"
                                   class="w-full p-4 bg-slate-100 rounded-2xl border-none font-bold text-slate-500 cursor-not-allowed">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full mt-10 bg-slate-900 text-white py-5 rounded-[2rem] font-bold text-lg shadow-xl shadow-slate-200 hover:bg-indigo-600 transition-all transform active:scale-95">
                    Save Changes ✨
                </button>
            </div>
        </form>

        <p class="text-center mt-8 text-sm text-slate-400 font-medium">
            Not ready? <a href="dashboard.php" class="text-indigo-600 font-bold hover:underline">Go back to Dashboard</a>
        </p>
    </div>
</main>

<script>
    const pincodeInput = document.getElementById('pincode-input');
    const loader = document.getElementById('pincode-loader');
    
    const stateField = document.getElementById('state-field');
    const districtField = document.getElementById('district-field');
    const talukuField = document.getElementById('taluku-field');

    pincodeInput.addEventListener('input', function() {
        const pin = this.value;

        if (pin.length === 6) {
            loader.classList.remove('hidden');
            
            // Using the Free India Post Pincode API
            fetch(`https://api.postalpincode.in/pincode/${pin}`)
                .then(res => res.json())
                .then(data => {
                    loader.classList.add('hidden');
                    if (data[0].Status === "Success") {
                        const postOffice = data[0].PostOffice[0];
                        
                        stateField.value = postOffice.State;
                        districtField.value = postOffice.District;
                        talukuField.value = postOffice.Block; // 'Block' is usually the Taluku
                        
                        // Success visual feedback
                        pincodeInput.classList.remove('border-indigo-200');
                        pincodeInput.classList.add('border-emerald-500', 'bg-emerald-50');
                    } else {
                        alert("Invalid Pincode. Please check again.");
                    }
                })
                .catch(err => {
                    loader.classList.add('hidden');
                    console.error("API Error:", err);
                });
        } else {
            pincodeInput.classList.remove('border-emerald-500', 'bg-emerald-50');
            pincodeInput.classList.add('border-indigo-200');
        }
    });
</script>

<?php include '../includes/footer.php'; ?>  