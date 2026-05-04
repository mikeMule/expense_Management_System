<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth();
$error = '';

if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

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
            $error = 'Invalid username or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Access — <?php echo APP_NAME; ?></title>
    <meta name="description" content="Secure login to the Mule Wave Executive Ledger platform.">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: '#000000' }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }

        /* ── Full-screen two-column layout ── */
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
        }

        /* Left panel — brand */
        .brand-panel {
            background: linear-gradient(160deg, #0f0f0f 0%, #1a1a2e 60%, #16213e 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
            flex: 1;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            top: -120px; right: -120px;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 70%);
        }
        .brand-panel::after {
            content: '';
            position: absolute;
            bottom: -80px; left: -80px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.03) 0%, transparent 70%);
        }

        /* Right panel — form */
        .form-panel {
            background: #f8f8f8;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem 2rem;
            width: 100%;
            max-width: 480px;
            min-height: 100vh;
        }

        /* 4px silver border on inputs */
        .login-input {
            border: 4px solid #C0C0C0 !important;
            background: #fff !important;
            width: 100%;
            height: 56px;
            border-radius: 16px;
            padding-left: 3rem;
            padding-right: 1rem;
            font-weight: 700;
            font-size: 0.875rem;
            color: #111;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            box-sizing: border-box;
        }
        .login-input:focus {
            border-color: #000 !important;
            box-shadow: 0 0 0 3px rgba(0,0,0,0.08);
        }

        .form-card {
            background: #fff;
            border-radius: 32px;
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
        }

        /* Fade-in */
        .fade-in { animation: fadeInUp 0.5s ease-out forwards; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Support pulse */
        .support-btn { animation: subtlePulse 3s ease-in-out infinite; }
        @keyframes subtlePulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
            50%       { box-shadow: 0 0 0 6px rgba(16,185,129,0.12); }
        }

        /* Mobile: stack vertically */
        @media (max-width: 767px) {
            body { flex-direction: column; }
            .brand-panel { min-height: 220px; flex: none; padding: 2rem 1.5rem; }
            .form-panel { max-width: 100%; padding: 2rem 1.25rem; }
        }
    </style>
</head>
<body>

    <!-- ── Support Call Button ── -->
    <a href="tel:+251916182957"
       class="support-btn fixed top-4 right-4 z-50 flex items-center gap-2 bg-white border-4 border-gray-200 hover:border-black text-gray-700 hover:text-gray-900 rounded-2xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all shadow-lg">
        <i class="fas fa-phone text-emerald-500"></i>
        <span class="hidden sm:inline">Support</span>
        <span class="sm:hidden">Call</span>
    </a>

    <!-- ══ LEFT — Brand Panel ══ -->
    <div class="brand-panel" style="display: none;" id="brandPanel">
        <div class="relative z-10 text-center">
            <!-- Logo -->
            <div class="mb-8 flex justify-center">
                <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-3xl p-4 inline-block shadow-2xl">
                    <img src="assets/Gebeta_logo.png"
                         alt="<?php echo APP_NAME; ?> Logo"
                         style="max-width:160px; max-height:100px; width:auto; height:auto; display:block;"
                         onerror="this.style.display='none'; document.getElementById('logo-fallback').style.display='flex';">
                    <div id="logo-fallback" style="display:none; width:80px; height:80px;" class="items-center justify-center">
                        <i class="fas fa-wallet text-white text-5xl"></i>
                    </div>
                </div>
            </div>

            <!-- Tagline -->
            <h2 class="text-white text-3xl font-black tracking-tight mb-3 leading-tight">
                Executive<br>Ledger
            </h2>
            <p class="text-white/50 text-xs font-bold uppercase tracking-[0.2em] mb-10">
                Mule Wave Technology
            </p>

            <!-- Feature pills -->
            <div class="space-y-3 text-left">
                <?php foreach ([
                    ['fa-shield-halved', 'Fully Encrypted & Secure'],
                    ['fa-chart-line',   'Real-Time Financial Insights'],
                    ['fa-users',        'Multi-Branch Management'],
                ] as [$icon, $text]): ?>
                <div class="flex items-center gap-3 bg-white/5 border border-white/10 rounded-xl px-4 py-3">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                        <i class="fas <?php echo $icon; ?> text-white/80 text-xs"></i>
                    </div>
                    <span class="text-white/70 text-xs font-semibold"><?php echo $text; ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Footer credit -->
            <p class="text-white/25 text-[10px] font-bold uppercase tracking-widest mt-12">
                &copy; <?php echo date('Y'); ?> Mule Wave &bull; v2.0
            </p>
        </div>
    </div>

    <!-- ══ RIGHT — Form Panel ══ -->
    <div class="form-panel">
        <div class="form-card fade-in">

            <!-- Mobile logo (only shows on mobile) -->
            <div class="flex justify-center mb-6 md:hidden">
                <img src="assets/Gebeta_logo.png" alt="Logo"
                     style="max-width:120px; max-height:70px; width:auto; height:auto;">
            </div>

            <!-- Header -->
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 rounded-xl bg-black text-white flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shield-alt text-sm"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest m-0">Platform Access</p>
                    <p class="text-sm font-black text-gray-900 m-0">Identity Verification</p>
                </div>
            </div>

            <!-- Error -->
            <?php if ($error): ?>
            <div class="flex items-start gap-3 bg-rose-50 p-4 rounded-2xl border-4 border-rose-200 mb-6">
                <i class="fas fa-circle-exclamation text-rose-500 text-base mt-0.5 flex-shrink-0"></i>
                <span class="font-bold text-xs text-rose-700 leading-relaxed"><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" id="loginForm" novalidate class="space-y-5">

                <!-- Username -->
                <div>
                    <label for="username" class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">
                        Access Identity
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-at text-sm"></i>
                        </div>
                        <input type="text" name="username" id="username" required autocomplete="username"
                            class="login-input"
                            placeholder="Enter your username"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">
                        Security Credential
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-lock text-sm"></i>
                        </div>
                        <input type="password" name="password" id="password" required autocomplete="current-password"
                            class="login-input" style="padding-right:3rem;"
                            placeholder="••••••••">
                        <button type="button" id="togglePassword"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-700 transition-colors">
                            <i class="fas fa-eye text-sm" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <div class="pt-2">
                    <button type="submit" id="loginBtn" disabled
                        style="width:100%; height:56px; border-radius:16px; border:none; font-size:0.7rem; font-weight:900; letter-spacing:0.15em; text-transform:uppercase; cursor:not-allowed; transition:all 0.3s; display:flex; align-items:center; justify-content:center; gap:10px; background:#e5e7eb; color:#9ca3af;">
                        <i class="fas fa-arrow-right-to-bracket"></i>
                        Authenticate
                    </button>
                </div>
            </form>

            <!-- Footer -->
            <p class="mt-6 text-center text-gray-400 text-[10px] font-bold uppercase tracking-widest">
                &copy; <?php echo date('Y'); ?> Mule Wave Technology
            </p>
        </div>
    </div>

    <script>
        // Show brand panel only on desktop
        function checkLayout() {
            const panel = document.getElementById('brandPanel');
            if (window.innerWidth >= 768) {
                panel.style.display = 'flex';
            } else {
                panel.style.display = 'none';
            }
        }
        checkLayout();
        window.addEventListener('resize', checkLayout);

        // Form logic
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const loginBtn      = document.getElementById('loginBtn');
        const togglePwd     = document.getElementById('togglePassword');
        const eyeIcon       = document.getElementById('eyeIcon');

        function toggleButton() {
            const ready = usernameInput.value.trim() !== '' && passwordInput.value.trim() !== '';
            loginBtn.disabled = !ready;
            if (ready) {
                loginBtn.style.background = '#000';
                loginBtn.style.color = '#fff';
                loginBtn.style.cursor = 'pointer';
                loginBtn.style.boxShadow = '0 20px 40px rgba(0,0,0,0.2)';
            } else {
                loginBtn.style.background = '#e5e7eb';
                loginBtn.style.color = '#9ca3af';
                loginBtn.style.cursor = 'not-allowed';
                loginBtn.style.boxShadow = 'none';
            }
        }

        usernameInput.addEventListener('input', toggleButton);
        passwordInput.addEventListener('input', toggleButton);

        togglePwd.addEventListener('click', function() {
            const isPass = passwordInput.type === 'password';
            passwordInput.type = isPass ? 'text' : 'password';
            eyeIcon.className = isPass ? 'fas fa-eye-slash text-sm' : 'fas fa-eye text-sm';
        });

        document.getElementById('loginForm').addEventListener('submit', function() {
            loginBtn.disabled = true;
            loginBtn.style.opacity = '0.8';
            loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
        });
    </script>
</body>
</html>