<?php
// Start session
session_start();

// Initialize variables
$error = '';
$success = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in both fields.';
    } else {
        // For testing purposes, we use dummy credentials
        // In a real application, database checks would be performed here
        if ($username === 'admin' && $password === 'password123') {
            $_SESSION['user'] = $username;
            $success = 'Login successful! Welcome back.';
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Connect Amravati</title>
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Modern reset & CSS custom properties */
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #311042 100%);
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.4);
            --primary-hover: #7c3aed;
            --accent: #f43f5e;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-bg: rgba(30, 41, 59, 0.45);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-highlight: rgba(255, 255, 255, 0.03);
            --input-bg: rgba(15, 23, 42, 0.6);
            --input-border: rgba(255, 255, 255, 0.1);
            --input-focus: #a78bfa;
            --toast-success: #10b981;
            --toast-error: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            position: relative;
        }

        /* Abstract glowing spheres for background depth */
        .ambient-glow {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            filter: blur(100px);
            z-index: 1;
            opacity: 0.4;
            pointer-events: none;
        }

        .glow-1 {
            background: #6366f1;
            top: -10%;
            left: 10%;
            animation: float-slow 15s infinite alternate;
        }

        .glow-2 {
            background: #db2777;
            bottom: -10%;
            right: 10%;
            animation: float-slow 12s infinite alternate-reverse;
        }

        @keyframes float-slow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, 50px) scale(1.2); }
        }

        /* Glassmorphism Card Container */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            padding: 2.5rem;
            border-radius: 24px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3),
                        inset 0 1px 0 var(--glass-highlight);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-container:hover {
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4),
                        0 0 40px var(--primary-glow),
                        inset 0 1px 0 var(--glass-highlight);
        }

        /* Header Styling */
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-icon {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 8px 20px var(--primary-glow);
            animation: pulse-glow 3s infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4); }
            50% { box-shadow: 0 8px 30px rgba(139, 92, 246, 0.8); }
        }

        .login-header h1 {
            color: var(--text-main);
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* Status Messages */
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slide-in 0.3s ease;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #a7f3d0;
        }

        @keyframes slide-in {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            color: var(--text-main);
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            color: var(--text-muted);
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .form-input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.8rem;
            border-radius: 12px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            color: var(--text-main);
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.25);
        }

        .form-input:focus {
            border-color: var(--input-focus);
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.15);
        }

        .form-input:focus + .input-icon {
            color: var(--input-focus);
        }

        /* Password eye toggle */
        .password-toggle {
            position: absolute;
            right: 1rem;
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.3s ease;
            z-index: 5;
        }

        .password-toggle:hover {
            color: var(--text-main);
        }

        /* Form Footer: Remember Me & Forgot Password */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
        }

        .checkbox-container input {
            margin-right: 0.5rem;
            accent-color: var(--primary);
            width: 15px;
            height: 15px;
        }

        .forgot-link {
            color: var(--input-focus);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 0.9rem;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px var(--primary-glow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(-50%, -50%) scale(0);
            transition: transform 0.5s ease-out;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px var(--primary-glow);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Additional sign-up prompt */
        .signup-prompt {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .signup-prompt a {
            color: var(--input-focus);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-prompt a:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        /* Helper info block for demo */
        .demo-credentials {
            margin-top: 1.5rem;
            padding: 0.8rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px dashed rgba(255, 255, 255, 0.1);
            font-size: 0.8rem;
            color: var(--text-muted);
            text-align: center;
        }

        .demo-credentials strong {
            color: var(--text-main);
        }
    </style>
</head>
<body>

    <!-- Ambient glowing blur orbs behind the glass login container -->
    <div class="ambient-glow glow-1"></div>
    <div class="ambient-glow glow-2"></div>

    <div class="login-container">
        <div class="login-header">
            <div class="logo-icon">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h1>Connect Amravati</h1>
            <p>Faculty Immersion Program Portal</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" class="form-input" placeholder="Enter your username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    <i class="fa-solid fa-user input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
                    <i class="fa-solid fa-lock input-icon"></i>
                    <i class="fa-solid fa-eye password-toggle" id="togglePassword"></i>
                </div>
            </div>

            <div class="form-options">
                <label class="checkbox-container">
                    <input type="checkbox" name="remember">
                    Remember me
                </label>
                <a href="#" class="forgot-link">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-submit">Sign In</button>
        </form>

        <div class="signup-prompt">
            Don't have an account? <a href="#">Request Access</a>
        </div>

        <div class="demo-credentials">
            <i class="fa-solid fa-circle-info"></i> For demo login use: <strong>admin</strong> / <strong>password123</strong>
        </div>
    </div>

    <script>
        // Password Visibility Toggle
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            // toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // toggle the eye / eye-slash icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>