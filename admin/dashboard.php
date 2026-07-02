<?php
session_start();
require_once '../config/db.php';

// 1. GOD MODE SECURITY CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$stmt_check = $pdo->prepare("SELECT is_admin FROM users WHERE user_id = ?");
$stmt_check->execute([$_SESSION['user_id']]);
$current_user = $stmt_check->fetch();

if (!$current_user || $current_user['is_admin'] != 1) {
    // Kick them out to the passenger dashboard if they aren't the boss
    header("Location: ../passenger/dashboard.php");
    exit();
}

$success_message = '';

// 2. HANDLE APPROVE / REJECT ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['target_user_id'])) {
    $target_id = $_POST['target_user_id'];
    $action = $_POST['action']; // 'approve' or 'reject'
    
    $new_status = ($action === 'approve') ? 'verified' : 'rejected';
    
    // We only need to update the users table status. The documents stay vaulted safely.
    $updateStmt = $pdo->prepare("UPDATE users SET verification_status = ? WHERE user_id = ?");
    if ($updateStmt->execute([$new_status, $target_id])) {
        $success_message = "User has been " . ($action === 'approve' ? 'Verified ✅' : 'Rejected ❌');
    }
}

// 3. FETCH PENDING USERS
$query_pending = "SELECT u.user_id, u.name, u.email, u.role, 
                         d.document_type, d.id_number, d.document_path, d.selfie_path, d.submitted_at 
                  FROM users u 
                  JOIN user_documents d ON u.user_id = d.user_id 
                  WHERE u.verification_status = 'pending' 
                  ORDER BY d.submitted_at ASC";
$stmt_pending = $pdo->query($query_pending);
$pending_users = $stmt_pending->fetchAll();

// 4. FETCH HISTORY (APPROVED/REJECTED)
$query_history = "SELECT u.user_id, u.name, u.email, u.role, u.verification_status, 
                         d.document_type, d.id_number, d.submitted_at 
                  FROM users u 
                  JOIN user_documents d ON u.user_id = d.user_id 
                  WHERE u.verification_status IN ('verified', 'rejected') 
                  ORDER BY d.submitted_at DESC";
$stmt_history = $pdo->query($query_history);
$history_users = $stmt_history->fetchAll();

// 5. SEPARATE HISTORY INTO DRIVERS AND PASSENGERS
$driver_history = [];
$passenger_history = [];
foreach ($history_users as $user) {
    if ($user['role'] === 'driver') {
        $driver_history[] = $user;
    } else {
        $passenger_history[] = $user;
    }
}

include '../includes/admin_header.php'; 
?>

<main class="max-w-6xl mx-auto px-6 py-10 space-y-12">
    
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 bg-slate-900 p-8 rounded-[2.5rem] text-white shadow-2xl shadow-slate-300">
        <div>
            <div class="inline-block px-4 py-1.5 bg-rose-500/20 text-rose-300 rounded-full text-[10px] font-black uppercase tracking-widest mb-4">
                Founder Access Only 👑
            </div>
            <h1 class="text-3xl font-black">Verification Command Center</h1>
            <p class="mt-2 text-slate-400 font-medium text-sm">Review user documents and keep WayMate safe.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-4">
            <a href="admin_reviews.php" class="bg-indigo-600 hover:bg-indigo-500 hover:-translate-y-1 transition-all px-6 py-4 rounded-2xl text-center shadow-lg shadow-indigo-500/30 border border-indigo-500">
                <p class="text-[10px] font-black text-indigo-200 uppercase tracking-widest mb-1">Passenger Reports</p>
                <p class="text-xl font-black text-white">Feedback ⭐️</p>
            </a>

            <div class="bg-white/10 px-6 py-4 rounded-2xl text-center border border-white/5">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Pending IDs</p>
                <p class="text-3xl font-black text-white"><?php echo count($pending_users); ?></p>
            </div>
        </div>
    </div>

    <?php if ($success_message): ?>
        <div class="bg-emerald-50 text-emerald-600 px-6 py-4 rounded-2xl font-bold text-sm flex items-center gap-3 border border-emerald-100">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <div>
        <h2 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-amber-400 animate-pulse"></span> Action Required
        </h2>
        
        <?php if(empty($pending_users)): ?>
            <div class="bg-white border-2 border-dashed border-slate-200 rounded-[3rem] p-16 text-center">
                <div class="text-5xl mb-4 grayscale opacity-30">📭</div>
                <p class="text-slate-400 font-bold mb-1">Inbox Zero!</p>
                <p class="text-[10px] text-slate-300 font-black uppercase tracking-widest">No pending verifications at the moment.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach($pending_users as $user): ?>
                    <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                        
                        <div class="flex items-center justify-between mb-6 border-b border-slate-50 pb-4">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                    <?php echo htmlspecialchars(strtoupper($user['role'])); ?>
                                </p>
                                <h3 class="text-xl font-bold text-slate-800"><?php echo htmlspecialchars($user['name']); ?></h3>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 font-black text-xl shadow-inner">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                        </div>

                        <div class="space-y-4 mb-8">
                            <?php if($user['document_type'] === 'aadhaar'): ?>
                                <div class="bg-slate-50 p-4 rounded-2xl">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Aadhaar Number</p>
                                    <p class="font-bold text-slate-700 font-mono tracking-widest"><?php echo htmlspecialchars($user['id_number']); ?></p>
                                </div>
                            
                            <?php elseif($user['document_type'] === 'driving_license'): ?>
                                <div class="bg-slate-50 p-4 rounded-2xl">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Driving License</p>
                                    <p class="font-bold text-slate-700 font-mono tracking-widest"><?php echo htmlspecialchars($user['id_number']); ?></p>
                                    <a href="../uploads/dl_proofs/<?php echo htmlspecialchars($user['document_path']); ?>" target="_blank" class="text-indigo-600 text-xs font-bold mt-2 inline-block hover:underline">
                                        🪪 View DL Photo ↗
                                    </a>
                                </div>
                                <div class="bg-slate-50 p-4 rounded-2xl flex justify-between items-center">
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Live Selfie</p>
                                        <p class="font-bold text-slate-700 text-xs">Verify face matches DL</p>
                                    </div>
                                    <a href="../uploads/selfies/<?php echo htmlspecialchars($user['selfie_path']); ?>" target="_blank" class="bg-indigo-100 text-indigo-600 px-4 py-2 rounded-xl text-xs font-bold hover:bg-indigo-200 shadow-sm transition-all">
                                        View Selfie ↗
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <p class="text-[10px] text-slate-400 font-medium text-right">
                                Submitted: <?php echo date('M j, Y g:i A', strtotime($user['submitted_at'])); ?>
                            </p>
                        </div>

                        <div class="flex gap-4">
                            <form action="dashboard.php" method="POST" class="w-1/2">
                                <input type="hidden" name="target_user_id" value="<?php echo $user['user_id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="w-full bg-rose-50 text-rose-600 py-3 rounded-2xl font-bold text-sm hover:bg-rose-100 transition-all">
                                    Reject ❌
                                </button>
                            </form>
                            
                            <form action="dashboard.php" method="POST" class="w-1/2">
                                <input type="hidden" name="target_user_id" value="<?php echo $user['user_id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="w-full bg-emerald-500 text-white py-3 rounded-2xl font-bold text-sm shadow-lg shadow-emerald-200 hover:bg-emerald-600 hover:-translate-y-1 transition-all">
                                    Approve ✅
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-slate-300"></span> Decision History
        </h2>
        
        <div class="flex flex-wrap items-center gap-3">
            <select id="statusFilter" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="all">All Statuses</option>
                <option value="verified">Verified Only ✅</option>
                <option value="rejected">Rejected Only ❌</option>
            </select>

            <div class="relative">
                <input type="date" id="dateFilter" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <button id="clearFilters" class="bg-slate-100 text-slate-500 hover:text-slate-800 px-4 py-2.5 rounded-xl text-xs font-bold transition-colors">
                Clear
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <div>
            <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Driver Decisions</h3>
            <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
                <?php if(empty($driver_history)): ?>
                    <div class="p-10 text-center text-slate-400 font-medium">No driver decisions recorded yet.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="py-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Driver</th>
                                    <th class="py-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                                    <th class="py-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50" id="driverTableBody">
                                <?php foreach($driver_history as $hist): ?>
                                    <tr class="history-row hover:bg-slate-50/50 transition-colors" data-status="<?php echo $hist['verification_status']; ?>" data-date="<?php echo date('Y-m-d', strtotime($hist['submitted_at'])); ?>">
                                        <td class="py-4 px-6">
                                            <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($hist['name']); ?></p>
                                            <p class="text-[10px] text-slate-400 uppercase tracking-wider"><?php echo htmlspecialchars(str_replace('_', ' ', $hist['document_type'])); ?> Check</p>
                                        </td>
                                        <td class="py-4 px-6 text-xs text-slate-500 font-medium">
                                            <?php echo date('M j, Y', strtotime($hist['submitted_at'])); ?>
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <?php if($hist['verification_status'] === 'verified'): ?>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest">Verified</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-rose-50 text-rose-600 text-[10px] font-black uppercase tracking-widest">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Passenger Decisions</h3>
            <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
                <?php if(empty($passenger_history)): ?>
                    <div class="p-10 text-center text-slate-400 font-medium">No passenger decisions recorded yet.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="py-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Passenger</th>
                                    <th class="py-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                                    <th class="py-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50" id="passengerTableBody">
                                <?php foreach($passenger_history as $hist): ?>
                                    <tr class="history-row hover:bg-slate-50/50 transition-colors" data-status="<?php echo $hist['verification_status']; ?>" data-date="<?php echo date('Y-m-d', strtotime($hist['submitted_at'])); ?>">
                                        <td class="py-4 px-6">
                                            <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($hist['name']); ?></p>
                                            <p class="text-[10px] text-slate-400 uppercase tracking-wider"><?php echo htmlspecialchars(str_replace('_', ' ', $hist['document_type'])); ?> Check</p>
                                        </td>
                                        <td class="py-4 px-6 text-xs text-slate-500 font-medium">
                                            <?php echo date('M j, Y', strtotime($hist['submitted_at'])); ?>
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <?php if($hist['verification_status'] === 'verified'): ?>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest">Verified</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-rose-50 text-rose-600 text-[10px] font-black uppercase tracking-widest">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</main>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const statusFilter = document.getElementById('statusFilter');
        const dateFilter = document.getElementById('dateFilter');
        const clearBtn = document.getElementById('clearFilters');
        const rows = document.querySelectorAll('.history-row');

        function filterTables() {
            const selectedStatus = statusFilter.value;
            const selectedDate = dateFilter.value;

            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                const rowDate = row.getAttribute('data-date');
                
                let matchesStatus = (selectedStatus === 'all' || rowStatus === selectedStatus);
                let matchesDate = (!selectedDate || rowDate === selectedDate);

                if (matchesStatus && matchesDate) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Attach event listeners
        statusFilter.addEventListener('change', filterTables);
        dateFilter.addEventListener('change', filterTables);
        
        clearBtn.addEventListener('click', () => {
            statusFilter.value = 'all';
            dateFilter.value = '';
            filterTables();
        });
    });
</script>

<?php include '../includes/footer.php'; ?>