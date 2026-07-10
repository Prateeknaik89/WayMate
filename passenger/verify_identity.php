<?php
session_start();
require_once '../config/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'passenger'; 
$error_message = '';
$success_message = '';

// 1. Fetch current verification status
$stmt = $pdo->prepare("SELECT verification_status FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$status = $stmt->fetchColumn() ?: 'unverified';

// Helper function for secure image uploads
function uploadImage($fileKey, $prefix, $destFolder, $user_id) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) return false;
    
    $fileTmpPath = $_FILES[$fileKey]['tmp_name'];
    $fileName = $_FILES[$fileKey]['name'];
    $fileSize = $_FILES[$fileKey]['size'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Security: Validate file type and size (5MB)
    $allowedExts = ['jpg', 'jpeg', 'png'];
    if (!in_array($fileExtension, $allowedExts) || $fileSize > 5000000) return false;
    
    // Auto-create directory with safe 0755 permissions
    $uploadDir = '../uploads/' . $destFolder . '/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return false; // Folder creation failed
        }
    }
    
    $newFileName = $prefix . '_user_' . $user_id . '_' . time() . '.' . $fileExtension;
    $destPath = $uploadDir . $newFileName;
    
    if (move_uploaded_file($fileTmpPath, $destPath)) return $newFileName;
    return false;
}

// 2. Handle the Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $status === 'unverified') {
    
    if ($role === 'driver') {
        $dl_number = trim($_POST['dl_number'] ?? '');
        
        if (empty($dl_number)) {
            $error_message = "Please enter your Driving License number.";
        } else {
            $dl_filename = uploadImage('dl_document', 'dl', 'dl_proofs', $user_id);
            $selfie_filename = uploadImage('selfie', 'selfie', 'selfies', $user_id);
            
            if ($dl_filename && $selfie_filename) {
                // Insert into documents vault
                $insertDoc = $pdo->prepare("INSERT INTO user_documents (user_id, document_type, id_number, document_path, selfie_path) VALUES (?, 'driving_license', ?, ?, ?)");
                $insertDoc->execute([$user_id, $dl_number, $dl_filename, $selfie_filename]);
                
                // Update user status
                $updateUser = $pdo->prepare("UPDATE users SET verification_status = 'pending' WHERE user_id = ?");
                $updateUser->execute([$user_id]);
                
                $status = 'pending';
                $success_message = "License and photos submitted securely. Under review.";
            } else {
                $error_message = "Upload failed: Please ensure images are JPG/PNG and under 5MB.";
            }
        }
    } else {
        // --- PASSENGER LOGIC ---
        // Sanitizing ID input by removing all non-numeric characters
        $id_val = preg_replace('/[^0-9]/', '', $_POST['aadhaar_number'] ?? '');
        
        if (strlen($id_val) !== 12) {
            $error_message = "Please enter a valid 12-digit number.";
        } else {
            $insertDoc = $pdo->prepare("INSERT INTO user_documents (user_id, document_type, id_number) VALUES (?, 'aadhaar', ?)");
            $insertDoc->execute([$user_id, $id_val]);
            
            $updateUser = $pdo->prepare("UPDATE users SET verification_status = 'pending' WHERE user_id = ?");
            $updateUser->execute([$user_id]);
            
            $status = 'pending';
            $success_message = "ID number submitted securely. Under review.";
        }
    }
}

include '../includes/header.php';
?>

<main class="max-w-5xl mx-auto px-6 py-12">
    
    <div class="text-center mb-12">
        <div class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-[2rem] flex items-center justify-center mx-auto mb-6 shadow-[0_10px_20px_rgb(99,102,241,0.2)] text-3xl border border-white">
            🛡️
        </div>
        <h1 class="text-4xl font-black text-slate-800 tracking-tight mb-3">Trust & Safety</h1>
        <p class="text-slate-500 font-medium max-w-lg mx-auto">
            <?php echo $role === 'driver' 
                ? 'To ensure a safe community, all WayMate drivers must verify their driving credentials and identity before posting a ride.PPPPPPP' 
                : 'To ensure a safe community, all WayMate passengers must verify their identity before booking a ride.'; ?>
        </p>
    </div>

    <?php if ($error_message): ?>
        <div class="bg-rose-50 text-rose-600 px-6 py-4 rounded-2xl font-bold text-sm mb-8 flex items-center gap-3 border border-rose-100 shadow-sm mx-auto max-w-2xl">
            ⚠️ <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="bg-emerald-50 text-emerald-600 px-6 py-4 rounded-2xl font-bold text-sm mb-8 flex items-center gap-3 border border-emerald-100 shadow-sm mx-auto max-w-2xl">
            ✅ <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($status === 'pending'): ?>
        <div class="max-w-2xl mx-auto bg-amber-50 border-2 border-amber-200 border-dashed rounded-[2.5rem] p-10 text-center relative overflow-hidden shadow-sm">
            <div class="text-5xl mb-4 animate-pulse">⏳</div>
            <h3 class="text-xl font-black text-amber-800 mb-2">Verification Pending</h3>
            <p class="text-amber-700/80 font-medium text-sm">Your documents are currently under review by the WayMate trust team. This usually takes less than 24 hours.</p>
            <a href="dashboard.php" class="inline-block mt-8 bg-amber-200 text-amber-800 px-8 py-3 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-amber-300 transition-all shadow-sm">Return to Dashboard</a>
        </div>

    <?php elseif ($status === 'verified'): ?>
        <div class="max-w-2xl mx-auto bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-[2.5rem] p-10 text-center text-white shadow-xl shadow-emerald-200">
            <div class="text-6xl mb-4">✅</div>
            <h3 class="text-2xl font-black mb-2">Identity Verified</h3>
            <p class="text-emerald-50 font-medium">Thank you for keeping the WayMate community safe. You have full access to the platform.</p>
            <a href="dashboard.php" class="inline-block mt-8 bg-white text-emerald-600 px-8 py-3 rounded-2xl font-bold text-xs uppercase tracking-widest shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all">Go to Dashboard</a>
        </div>

    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">
            
            <div class="space-y-8 bg-white/50 p-8 rounded-[2.5rem]">
                <h3 class="text-xl font-black text-slate-800">How verification works</h3>
                
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-black text-sm shadow-sm">1</div>
                        <div class="w-[2px] h-full bg-indigo-50 my-2"></div>
                    </div>
                    <div class="pb-6">
                        <h4 class="font-bold text-slate-700">Submit your Details</h4>
                        <p class="text-xs text-slate-500 mt-1">
                            <?php echo $role === 'driver' ? 'Upload your license number along with a clear photo of the ID and a live selfie.' : 'We just need your official ID number. No need to upload any photos or documents right now.'; ?>
                        </p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center font-black text-sm">2</div>
                        <div class="w-[2px] h-full bg-slate-50 my-2"></div>
                    </div>
                    <div class="pb-6">
                        <h4 class="font-bold text-slate-700">Background Check</h4>
                        <p class="text-xs text-slate-500 mt-1">Our admin team securely verifies the credentials against public registry databases.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center font-black text-sm">3</div>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-700">Unlock Full Access</h4>
                        <p class="text-xs text-slate-500 mt-1">Once approved, you can immediately start booking or offering rides.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white backdrop-blur-xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-[2.5rem] p-8 md:p-10 relative overflow-hidden">
                <form action="verify_identity.php" method="POST" enctype="multipart/form-data" class="space-y-6 relative z-10">
                    
                    <?php if ($role === 'driver'): ?>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Driving License Number</label>
                            <input type="text" name="dl_number" required placeholder="e.g., KA01 20260000000" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Photo of DL</label>
                                <div id="dl_box" class="relative w-full h-32 bg-slate-50 border-2 border-dashed border-slate-300 rounded-2xl flex flex-col items-center justify-center hover:bg-indigo-50 hover:border-indigo-300 transition-all text-center group cursor-pointer overflow-hidden">
                                    <input type="file" name="dl_document" required accept=".jpg,.jpeg,.png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" onchange="previewImage(this, 'dl_preview', 'dl_content')">
                                    <div id="dl_content" class="z-10">
                                        <div class="text-3xl mb-1 grayscale group-hover:grayscale-0 transition-all">🪪</div>
                                        <p class="font-bold text-slate-600 text-[10px] uppercase tracking-widest">Upload DL</p>
                                    </div>
                                    <img id="dl_preview" class="absolute inset-0 w-full h-full object-cover z-10 hidden" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Live Selfie</label>
                                <div id="selfie_box" class="relative w-full h-32 bg-slate-50 border-2 border-dashed border-slate-300 rounded-2xl flex flex-col items-center justify-center hover:bg-indigo-50 hover:border-indigo-300 transition-all text-center group cursor-pointer overflow-hidden">
                                    <input type="file" name="selfie" required accept=".jpg,.jpeg,.png" capture="user" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" onchange="previewImage(this, 'selfie_preview', 'selfie_content')">
                                    <div id="selfie_content" class="z-10">
                                        <div class="text-3xl mb-1 grayscale group-hover:grayscale-0 transition-all">🤳</div>
                                        <p class="font-bold text-slate-600 text-[10px] uppercase tracking-widest">Take Selfie</p>
                                    </div>
                                    <img id="selfie_preview" class="absolute inset-0 w-full h-full object-cover z-10 hidden" />
                                </div>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-400 font-bold mt-2">Ensure both images are clear and well-lit.</p>

                    <?php else: ?>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">12-Digit Aadhaar Number</label>
                            <input type="text" name="aadhaar_number" required maxlength="14" placeholder="e.g., 1234 5678 9012" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner tracking-widest">
                            <p class="text-[10px] text-slate-400 font-bold mt-3">Your number is encrypted and never shared with other users.</p>
                        </div>
                    <?php endif; ?>

                    <div class="pt-6">
                        <button type="submit" class="w-full bg-slate-900 text-white rounded-2xl px-6 py-4 font-black text-sm uppercase tracking-widest shadow-[0_8px_20px_rgb(15,23,42,0.2)] hover:bg-indigo-600 hover:-translate-y-1 transition-all">
                            Submit for Review
                        </button>
                        <p class="text-[10px] text-center text-slate-400 font-bold tracking-wider uppercase mt-4">🔒 256-bit Secure Connection</p>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

</main>

<script>
    function previewImage(input, previewImgId, contentContainerId) {
        const previewImg = document.getElementById(previewImgId);
        const contentContainer = document.getElementById(contentContainerId);
        const box = input.parentElement;

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                // Hide the emoji and text
                contentContainer.classList.add('hidden');
                
                // Show the image and set its source
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
                
                // Remove dashed border to make it look cleaner once an image is there
                box.classList.remove('border-dashed', 'border-slate-300');
                box.classList.add('border-solid', 'border-indigo-500', 'shadow-md');
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php include '../includes/footer.php'; ?>