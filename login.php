<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

session_start();

$auth = new Auth();
$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        if ($auth->login($username, $password)) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

$page_title = 'Login';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Access - <?php echo APP_NAME; ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '#000000',
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .amount { font-family: 'JetBrains Mono', 'Courier New', monospace; letter-spacing: -0.05em; }
        .page-animate { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-50 p-6">
    <div class="w-full max-w-lg page-animate">
        <!-- Logo/Brand -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-white rounded-3xl shadow-2xl shadow-black/5 mb-6 border-3 border-gray-100">
                <i class="fas fa-wallet text-black text-4xl"></i>
            </div>
            <h1 class="text-4xl font-black text-gray-900 tracking-tighter mb-2 uppercase">Platform Access</h1>
            <p class="text-gray-400 font-bold text-[10px] uppercase tracking-[0.2em]">Mule Wave Executive Ledger</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-[40px] border-3 border-gray-100 shadow-2xl shadow-black/5 p-10 md:p-14">
            <div class="flex items-center gap-3 mb-10">
                <div class="w-8 h-8 rounded-lg bg-black text-white flex items-center justify-center">
                    <i class="fas fa-shield-alt text-xs"></i>
                </div>
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Identity Verification</h3>
            </div>

            <?php if ($error): ?>
                <div class="bg-rose-50 text-rose-700 p-5 rounded-2xl border-2 border-rose-100 mb-8 flex items-center">
                    <i class="fas fa-exclamation-circle text-rose-500 text-xl mr-4"></i>
                    <span class="font-black text-xs uppercase tracking-tight"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-8">
                <div>
                    <label for="username" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block ml-1">Access Identity</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-at text-xs"></i>
                        </div>
                        <input type="text" name="username" id="username" required 
                            class="block w-full h-14 pl-12 pr-4 bg-gray-50 border-3 border-gray-50 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm"
                            placeholder="Enter username" value="<?php echo htmlspecialchars($username ?? ''); ?>">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block ml-1">Security Credential</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-lock text-xs"></i>
                        </div>
                        <input type="password" name="password" id="password" required 
                            class="block w-full h-14 pl-12 pr-4 bg-gray-50 border-3 border-gray-50 text-gray-900 rounded-2xl focus:bg-white focus:border-brand outline-none transition-all font-bold text-sm"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" id="loginBtn" disabled
                        class="w-full h-16 flex items-center justify-center gap-3 border-none rounded-2xl shadow-xl shadow-black/10 text-xs font-black text-white bg-gray-300 cursor-not-allowed transition-all uppercase tracking-[0.2em]">
                        Login <i class="fas fa-arrow-right text-[10px]"></i>
                    </button>
                </div>
            </form>


        </div>

        <!-- Footer -->
        <p class="mt-12 text-center text-gray-400 text-[10px] font-bold uppercase tracking-widest leading-loose">
            &copy; <?php echo date('Y'); ?> Mule Wave Technology Solutions<br>
            <span class="text-gray-900">Executive Ledger v2.0 • Fully Encrypted</span>
        </p>
    </div>
    <script>
        const form = document.querySelector('form');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const loginBtn = document.getElementById('loginBtn');

        function toggleButton() {
            if (usernameInput.value.trim() !== '' && passwordInput.value.trim() !== '') {
                loginBtn.disabled = false;
                loginBtn.classList.remove('bg-gray-300', 'cursor-not-allowed');
                loginBtn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
            } else {
                loginBtn.disabled = true;
                loginBtn.classList.add('bg-gray-300', 'cursor-not-allowed');
                loginBtn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
            }
        }

        usernameInput.addEventListener('input', toggleButton);
        passwordInput.addEventListener('input', toggleButton);

        form.addEventListener('submit', function() {
            loginBtn.disabled = true;
            loginBtn.classList.add('cursor-not-allowed', 'opacity-80');
            loginBtn.innerHTML = 'Verifying <i class="fas fa-spinner fa-spin text-[10px]"></i>';
        });
    </script>
</body>

</html>