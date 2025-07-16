<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

session_start();

$auth = new Auth();
$error = '';
$success = '';

$connectionStatus = 'not connected';

try {
    $db = new Database(); // Attempt DB connection
    $connectionStatus = 'connected';
} catch (Exception $e) {
    $connectionStatus = 'connection failed: ' . $e->getMessage();
}

// Handle login form submission
if ($_POST) {
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
    <title><?php echo $page_title . ' - ' . APP_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: none;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
            background: url('https://img.freepik.com/free-photo/desktop-with-office-elements_23-2148174136.jpg?semt=ais_hybrid&w=740') center center/cover no-repeat;
            opacity: 0.28;
        }

        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
            background: #000;
            opacity: 0.35;
            pointer-events: none;
        }

        .login-card {
            position: relative;
            z-index: 2;
            max-width: 400px;
            width: 100%;
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.25);
            background: #fff;
            padding: 2.5rem 2rem 2rem 2rem;
            margin: 2rem 0;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header .fa-user-circle {
            color: #764ba2;
            font-size: 3.5rem;
            margin-bottom: 0.5rem;
        }

        .login-header h3 {
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: #333;
        }

        .login-header p {
            color: #888;
            font-size: 1rem;
        }

        .form-label {
            font-weight: 500;
        }

        .form-control {
            border-radius: 0.5rem;
            font-size: 1.1rem;
        }

        .btn-primary {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 0.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #764ba2 0%, #667eea 100%);
        }

        .alert {
            border-radius: 0.5rem;
            font-size: 1rem;
        }

        .demo-credentials {
            margin-top: 2rem;
            text-align: center;
            color: #888;
            font-size: 0.95rem;
        }

        .demo-credentials strong {
            color: #764ba2;
        }
    </style>
</head>

<body>
    <div class="login-card mx-auto">
        <div class="login-header">
            <i class="fas fa-user-circle"></i>
            <h3><?php echo APP_NAME; ?></h3>
            <p>Admin Login</p>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            </div>
        <?php endif; ?>
        <form method="POST" class="needs-validation" novalidate autocomplete="off">
            <div class="mb-3">
                <label for="username" class="form-label">
                    <i class="fas fa-user me-1"></i>Username
                </label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required autofocus>
                <div class="invalid-feedback">Please enter your username.</div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">
                    <i class="fas fa-lock me-1"></i>Password
                </label>
                <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                <div class="invalid-feedback">Please enter your password.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-2">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
        </form>
        <div class="demo-credentials">
            <strong>Demo Credentials:</strong><br>
            Username: admin<br>
            Password: admin
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Bootstrap validation
        (() => {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>

</html>