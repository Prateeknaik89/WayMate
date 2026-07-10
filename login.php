<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | WayMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white w-full max-w-md rounded-[2.5rem] p-10 shadow-xl border border-slate-100">
        <div class="flex flex-col items-center mb-10">
            <div onclick="window.location.href='index.php'" class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-2xl shadow-lg cursor-pointer mb-4 hover:scale-105 transition-transform">W</div>
            <h1 class="text-2xl font-black text-slate-800">Welcome Back</h1>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-600 text-xs font-bold rounded-2xl text-center animate-bounce-short">
                Error: <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

       <form action="actions/login_action.php" method="POST" class="space-y-5">
    <div>
        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest ml-4 mb-2">Phone Number</label>
        <input type="tel" name="phone" placeholder="Enter 10-digit phone" required pattern="[0-9]{10}" 
            class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none transition font-bold text-slate-800">
    </div>

    <div>
        <div class="flex justify-between items-center ml-4 mr-4 mb-2">
            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">Password</label>
            <a href="forgot_password.php" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">Forgot Password?</a>
        </div>
        <div class="relative">
            <input type="password" id="password" name="password" placeholder="••••••••" required 
                class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none transition font-bold text-slate-800">
            
            <button type="button" onclick="togglePass()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 transition-colors">
                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
        </div>
    </div>

    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-1 transition-all active:scale-95">
        Login to Dashboard
    </button>
</form>

        </form>
    </div>

    <script>
        // Updated to use 'phoneNumber' instead of 'email'
        function quickLogin(phoneNumber, password) {
            const phoneInput = document.querySelector('input[name="phone"]');
            const passwordInput = document.querySelector('input[name="password"]');
            
            if(phoneInput && passwordInput) {
                phoneInput.value = phoneNumber;
                passwordInput.value = password;
                
                const loginForm = phoneInput.closest('form');
                if(loginForm) {
                    loginForm.submit();
                }
            } else {
                console.error("Quick Login Error: Couldn't find the phone or password input fields.");
            }
        }

        function togglePass() {
            const passInput = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />';
            } else {
                passInput.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        }
    </script>
</body>
</html>