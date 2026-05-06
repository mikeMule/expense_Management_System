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
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $result = $auth->login($username, $password, $csrf_token);
        if ($result['success']) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = $result['error'];
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,600;0,700;0,900;1,400&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --gold: #c9a84c;
            --gold-light: #e8c97a;
            --dark: #0a0a0f;
            --dark2: #13131e;
            --silver: #c0c0c0;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--dark);
            overflow: hidden;
        }

        /* ── Equal 50/50 split ── */
        .left-panel,
        .right-panel {
            flex: 1 1 50%;
            max-width: 50%;
        }

        /* ═══════════════════════════════
           LEFT PANEL
        ═══════════════════════════════ */
        .left-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
            background: linear-gradient(145deg, #0d0d1a 0%, #0a0a14 60%, #070710 100%);
        }

        /* Animated orb backgrounds */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.18;
            animation: orbFloat 8s ease-in-out infinite;
        }
        .orb-1 { width:500px; height:500px; background:#4f46e5; top:-150px; right:-100px; animation-delay:0s; }
        .orb-2 { width:350px; height:350px; background:#7c3aed; bottom:-100px; left:-80px; animation-delay:3s; }
        .orb-3 { width:250px; height:250px; background:#c9a84c; top:50%; left:40%; animation-delay:5s; }

        @keyframes orbFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50%       { transform: translateY(-30px) scale(1.05); }
        }

        /* Fine grid overlay */
        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        .brand-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 420px;
        }

        .logo-ring {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 28px 32px;
            border-radius: 32px;
            background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));
            border: 1px solid rgba(255,255,255,0.14);
            box-shadow: 0 20px 60px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.12);
            margin-bottom: 2.5rem;
            backdrop-filter: blur(20px);
        }

        .logo-ring img {
            max-width: 260px;
            max-height: 160px;
            width: auto;
            height: auto;
            display: block;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(201,168,76,0.12);
            border: 1px solid rgba(201,168,76,0.3);
            color: var(--gold-light);
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            padding: 6px 14px;
            border-radius: 100px;
            margin-bottom: 1.25rem;
        }

        .brand-title {
            font-size: clamp(2rem, 3vw, 2.75rem);
            font-weight: 900;
            color: #fff;
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: 1rem;
        }

        .brand-title span {
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-desc {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.4);
            font-weight: 500;
            line-height: 1.7;
            margin-bottom: 2.5rem;
        }

        /* Feature chips */
        .features {
            display: flex;
            flex-direction: column;
            gap: 10px;
            text-align: left;
        }

        .feature-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 14px;
            padding: 12px 16px;
            transition: border-color 0.3s;
        }
        .feature-chip:hover { border-color: rgba(201,168,76,0.25); }

        .feature-icon {
            width: 34px; height: 34px;
            border-radius: 10px;
            background: rgba(201,168,76,0.1);
            border: 1px solid rgba(201,168,76,0.2);
            display: flex; align-items: center; justify-content: center;
            color: var(--gold-light);
            font-size: 12px;
            flex-shrink: 0;
        }

        .feature-text {
            font-size: 11px;
            font-weight: 600;
            color: rgba(255,255,255,0.55);
        }

        .brand-copyright {
            margin-top: 3rem;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.18);
        }

        /* ═══════════════════════════════
           RIGHT PANEL
        ═══════════════════════════════ */
        .right-panel {
            min-height: 100vh;
            background: #f4f4f6;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2.5rem 3rem;
            position: relative;
            overflow-y: auto;
        }

        .form-card {
            width: 100%;
            max-width: 400px;
            animation: slideUp 0.5s cubic-bezier(0.16,1,0.3,1) forwards;
        }

        @keyframes slideUp {
            from { opacity:0; transform: translateY(24px); }
            to   { opacity:1; transform: translateY(0); }
        }

        .form-eyebrow {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 2rem;
        }

        .shield-icon {
            width: 44px; height: 44px;
            background: #000;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 16px;
            flex-shrink: 0;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
        }

        .form-eyebrow-text p:first-child {
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 2px;
        }
        .form-eyebrow-text p:last-child {
            font-size: 16px;
            font-weight: 900;
            color: #111;
            letter-spacing: -0.02em;
        }

        /* Error box */
        .error-box {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fff0f0;
            border: 4px solid #fca5a5;
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 1.5rem;
        }
        .error-box i { color: #ef4444; font-size: 14px; margin-top: 1px; flex-shrink: 0; }
        .error-box span { font-size: 12px; font-weight: 700; color: #b91c1c; line-height: 1.5; }

        /* Form fields */
        .field { margin-bottom: 1.25rem; }

        .field label {
            display: block;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .field-wrap { position: relative; }

        .field-icon {
            position: absolute;
            left: 16px;
            top: 50%; transform: translateY(-50%);
            color: #9ca3af;
            font-size: 13px;
            pointer-events: none;
        }

        .field-input {
            width: 100%;
            height: 56px;
            border: 4px solid var(--silver);
            border-radius: 16px;
            background: #fff;
            padding: 0 48px 0 46px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #111;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .field-input:focus {
            border-color: #000;
            box-shadow: 0 0 0 4px rgba(0,0,0,0.07);
        }
        .field-input::placeholder { color: #d1d5db; font-weight: 500; }

        .toggle-btn {
            position: absolute;
            right: 14px;
            top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #9ca3af;
            font-size: 14px;
            padding: 4px;
            transition: color 0.2s;
        }
        .toggle-btn:hover { color: #374151; }

        /* Submit button */
        .submit-btn {
            width: 100%;
            height: 58px;
            border: none;
            border-radius: 18px;
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            cursor: not-allowed;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s cubic-bezier(0.16,1,0.3,1);
            margin-top: 1.75rem;
            background: #e5e7eb;
            color: #9ca3af;
        }
        .submit-btn.active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            cursor: pointer;
            box-shadow: 0 16px 40px rgba(16,185,129,0.25);
        }
        .submit-btn.active:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 50px rgba(0,0,0,0.35);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 1.75rem 0;
        }
        .divider-line { flex:1; height:1px; background:#e5e7eb; }
        .divider-text { font-size: 9px; font-weight: 800; color: #d1d5db; letter-spacing: 0.15em; text-transform: uppercase; }

        /* Support button */
        .support-pill {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            border: 3px solid #e5e7eb;
            border-radius: 100px;
            padding: 8px 16px 8px 12px;
            text-decoration: none;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #374151;
            transition: all 0.2s;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        .support-pill:hover {
            border-color: #000;
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        .support-pill .dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 2px rgba(34,197,94,0.25);
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 2px rgba(34,197,94,0.25); }
            50%       { box-shadow: 0 0 0 5px rgba(34,197,94,0.12); }
        }

        .form-footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #9ca3af;
            line-height: 2;
        }

        /* ─── Mobile ─── */
        @media (max-width: 800px) {
            body { flex-direction: column; overflow: auto; }
            .left-panel,
            .right-panel { flex: none; max-width: 100%; width: 100%; }
            .left-panel {
                min-height: 260px;
                padding: 2rem 1.5rem;
            }
            .brand-title { font-size: 1.6rem; }
            .features { display: none; }
            .logo-ring img { max-width: 160px; max-height: 90px; }
            .right-panel { min-height: auto; padding: 2rem 1.5rem; }
            .support-pill { top: 12px; right: 12px; }
        }
    </style>
</head>
<body>

<!-- ── Support Call ── -->
<a href="tel:+251916182957" class="support-pill">
    <span class="dot"></span>
    <i class="fas fa-phone" style="color:#22c55e; font-size:11px;"></i>
    Support
</a>

<!-- ══ LEFT PANEL ══ -->
<div class="left-panel">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="brand-content">
        <div class="logo-ring">
            <img src="https://mulewave.com/myFile/uploads/1778069925_Gebeta_logo.png" alt="Gebeta Logo" style="width: 100%; height: 100%; object-fit: contain; padding: 10px;">
        </div>

        <div class="brand-badge">
            <i class="fas fa-circle" style="font-size:6px; color:#22c55e;"></i>
            Live Platform
        </div>

        <h1 class="brand-title">
            Executive<br><span>Financial Ledger</span>
        </h1>

        <p class="brand-desc">
            A centralized financial intelligence platform for<br>
            multi-branch operations, payroll, and analytics.
        </p>

        <div class="features">
            <?php foreach ([
                ['fa-shield-halved',  'AES-256 Encrypted Sessions'],
                ['fa-chart-line',     'Real-Time Financial Analytics'],
                ['fa-building',       'Bahirdar & Addis Ababa Branches'],
                ['fa-users-gear',     'Role-Based Access Control'],
            ] as [$icon, $text]): ?>
            <div class="feature-chip">
                <div class="feature-icon">
                    <i class="fas <?php echo $icon; ?>"></i>
                </div>
                <span class="feature-text"><?php echo $text; ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <p class="brand-copyright">
            &copy; <?php echo date('Y'); ?> Mule Wave Technology Solutions &bull; v2.0
        </p>
    </div>
</div>

<!-- ══ RIGHT PANEL ══ -->
<div class="right-panel">
    <div class="form-card">

        <!-- Mobile logo -->
        <div style="text-align:center; margin-bottom:1.5rem; display:none;" class="mobile-logo">
            <img src="assets/Gebeta_logo.png" alt="Logo"
                 style="max-width:120px; max-height:60px; width:auto; height:auto; margin:0 auto;">
        </div>

        <div class="form-eyebrow">
            <div class="shield-icon">
                <i class="fas fa-fingerprint"></i>
            </div>
            <div class="form-eyebrow-text">
                <p>Identity Verification</p>
                <p>Sign in to continue</p>
            </div>
        </div>

        <?php if ($error): ?>
        <div class="error-box">
            <i class="fas fa-circle-exclamation"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" id="loginForm" novalidate>
            <?php require_once 'classes/Csrf.php'; Csrf::insertTokenField(); ?>

            <!-- Username -->
            <div class="field">
                <label for="username">Access Identity</label>
                <div class="field-wrap">
                    <i class="fas fa-at field-icon"></i>
                    <input type="text" id="username" name="username" class="field-input"
                           placeholder="Enter your username" required autocomplete="username"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
            </div>

            <!-- Password -->
            <div class="field">
                <label for="password">Security Credential</label>
                <div class="field-wrap">
                    <i class="fas fa-lock field-icon"></i>
                    <input type="password" id="password" name="password" class="field-input"
                           placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" id="togglePwd" class="toggle-btn" tabindex="-1">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" id="loginBtn" class="submit-btn" disabled>
                <i class="fas fa-arrow-right-to-bracket"></i>
                Authenticate
            </button>
        </form>

        <div class="divider">
            <div class="divider-line"></div>
            <span class="divider-text">Secure &bull; Encrypted &bull; Private</span>
            <div class="divider-line"></div>
        </div>

        <div class="form-footer">
            &copy; <?php echo date('Y'); ?> Mule Wave Technology Solutions<br>
            <span style="color:#d1d5db;">Executive Ledger &bull; All Rights Reserved</span>
        </div>
    </div>
</div>

<script>
    const u = document.getElementById('username');
    const p = document.getElementById('password');
    const btn = document.getElementById('loginBtn');
    const togglePwd = document.getElementById('togglePwd');
    const eyeIcon = document.getElementById('eyeIcon');

    function check() {
        const ok = u.value.trim() && p.value.trim();
        btn.disabled = !ok;
        btn.className = 'submit-btn' + (ok ? ' active' : '');
    }

    u.addEventListener('input', check);
    p.addEventListener('input', check);

    togglePwd.addEventListener('click', () => {
        const show = p.type === 'password';
        p.type = show ? 'text' : 'password';
        eyeIcon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
    });

    document.getElementById('loginForm').addEventListener('submit', function() {
        btn.disabled = true;
        btn.classList.remove('active');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
    });

    // Show mobile logo on small screens
    if (window.innerWidth <= 800) {
        document.querySelectorAll('.mobile-logo').forEach(el => el.style.display = 'block');
    }
</script>
</body>
</html>