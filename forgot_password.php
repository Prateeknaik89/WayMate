<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-md">
        <h2 class="text-2xl font-black text-slate-800 mb-2">Reset Password</h2>
        <p class="text-slate-500 mb-6 font-medium">Enter your registered phone and email to set a new password.</p>

        <form action="actions/reset_action.php" method="POST" class="space-y-5">
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest ml-4 mb-2">Phone Number</label>
                <input type="tel" name="phone" placeholder="Enter 10-digit phone" required pattern="[0-9]{10}"
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none transition font-bold text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest ml-4 mb-2">Registered Email</label>
                <input type="email" name="email" placeholder="Enter your email" required 
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none transition font-bold text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest ml-4 mb-2">New Password</label>
                <input type="password" name="new_password" placeholder="••••••••" required minlength="6"
                    class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none transition font-bold text-slate-800">
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-1 transition-all active:scale-95">
                Securely Reset Password
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="login.php" class="text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors">Return to Login</a>
        </div>
    </div>

</body>
</html>