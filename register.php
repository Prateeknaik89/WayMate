<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">

    <div class="w-full max-w-md bg-white p-8 rounded-[2rem] shadow-xl border border-slate-100">
        <div class="mb-8">
            <h1 class="text-2xl font-extrabold">Create Account</h1>
            <p class="text-slate-500 text-sm">Join the community and start saving.</p>
        </div>

        <form action="actions/register_action.php" method="POST" class="space-y-5">
            <div>
                <label class="text-xs font-bold text-slate-400 uppercase ml-1">Full Name</label>
                <input type="text" name="name" required class="w-full mt-1 p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none transition">
            </div>

            <div>
                <label class="text-xs font-bold text-slate-400 uppercase ml-1">Phone Number</label>
                <input type="tel" name="phone" required class="w-full mt-1 p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none transition">
            </div>

            <div>
                <label class="text-xs font-bold text-slate-400 uppercase ml-1">Password</label>
                <input type="password" name="password" required class="w-full mt-1 p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 outline-none transition">
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 active:scale-[0.98] transition">
                Sign Up
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-slate-500">
            Already have an account? <a href="login.php" class="text-indigo-600 font-bold">Log In</a>
        </p>
    </div>

</body>
</html>