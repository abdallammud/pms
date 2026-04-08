<?php
session_start();
require_once 'app/init.php';

// If already logged in, redirect to index
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kaad PMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #0a0a0a;
            overflow: hidden;
        }

        /* ── Full-screen background ── */
        .login-page {
            position: relative;
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            background-image: url('public/images/cover.png');
            background-size: cover;
            background-position: center;
        }

        /* Dark overlay on the entire background */
        .login-page::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg,
                    rgba(0, 15, 30, 0.65) 0%,
                    rgba(0, 20, 40, 0.45) 50%,
                    rgba(0, 10, 25, 0.55) 100%);
            z-index: 1;
        }

        /* ── Bottom-left text overlay ── */
        .hero-text {
            position: absolute;
            bottom: 60px;
            left: 60px;
            z-index: 2;
            max-width: 500px;
        }

        .hero-text h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.25;
            margin-bottom: 16px;
            text-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
        }

        .hero-text p {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.7;
            text-shadow: 0 1px 10px rgba(0, 0, 0, 0.2);
        }

        /* ── Glassmorphism login card ── */
        .login-card {
            position: relative;
            z-index: 2;
            width: 460px;
            margin-right: 80px;
            padding: 48px 44px;
            background: linear-gradient(160deg,
                    rgba(10, 40, 60, 0.85) 0%,
                    rgba(8, 30, 50, 0.88) 40%,
                    rgba(5, 22, 38, 0.92) 100%);
            backdrop-filter: blur(24px) saturate(1.4);
            -webkit-backdrop-filter: blur(24px) saturate(1.4);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            box-shadow:
                0 32px 64px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.05) inset;
        }

        /* Logo row */
        .card-logo {
            text-align: right;
            margin-bottom: 8px;
        }

        .card-logo img {
            height: 85px;
            filter: brightness(0) invert(1);
        }

        /* Headings */
        .login-card h2 {
            font-size: 1.85rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 6px;
        }

        .login-card .subtitle {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.55);
            margin-bottom: 32px;
        }

        /* Form labels */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 8px;
            letter-spacing: 0.02em;
        }

        /* Inputs */
        .form-group .input-wrap {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            padding-right: 48px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            color: #ffffff;
            font-size: 0.92rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.25s ease;
            outline: none;
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .form-group input:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.28);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.06);
        }

        /* Password toggle */
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.45);
            cursor: pointer;
            font-size: 1.1rem;
            padding: 4px;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: rgba(255, 255, 255, 0.75);
        }

        /* Meta row: remember me + forgot */
        .meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #2e62a8;
            cursor: pointer;
            border-radius: 4px;
        }

        .remember-me span {
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.65);
        }

        .forgot-link {
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.65);
            text-decoration: underline;
            text-underline-offset: 3px;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #ffffff;
        }

        /* Alert messages */
        #alert-msg .alert {
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 16px;
            border: none;
        }

        #alert-msg .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            color: #ff8a95;
            border: 1px solid rgba(220, 53, 69, 0.25);
        }

        #alert-msg .alert-success {
            background: rgba(25, 135, 84, 0.2);
            color: #75e6a8;
            border: 1px solid rgba(25, 135, 84, 0.25);
        }

        /* Login button */
        .btn-login {
            width: 100%;
            padding: 15px;
            background: #ffffff;
            color: #0a1e32;
            border: none;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.01em;
        }

        .btn-login:hover {
            background: #f0f0f0;
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(255, 255, 255, 0.15);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Sign up link */
        .signup-text {
            text-align: center;
            margin-top: 24px;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .signup-text a {
            color: #ffffff;
            font-weight: 600;
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .signup-text a:hover {
            opacity: 0.8;
            text-decoration: underline;
        }

        /* ── Responsive ── */
        @media (max-width: 1024px) {
            .login-card {
                margin-right: 40px;
                width: 420px;
            }

            .hero-text {
                left: 40px;
                bottom: 40px;
            }

            .hero-text h1 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 768px) {
            .login-page {
                justify-content: center;
            }

            .login-card {
                margin: 20px;
                width: calc(100% - 40px);
                max-width: 440px;
                padding: 36px 28px;
            }

            .hero-text {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 28px 20px;
                border-radius: 16px;
            }

            .login-card h2 {
                font-size: 1.5rem;
            }
        }

        /* Subtle shimmer animation on card border */
        @keyframes borderShimmer {

            0%,
            100% {
                border-color: rgba(255, 255, 255, 0.08);
            }

            50% {
                border-color: rgba(255, 255, 255, 0.14);
            }
        }

        .login-card {
            animation: borderShimmer 4s ease-in-out infinite;
        }
    </style>
</head>

<body>
    <div class="login-page">
        <!-- Bottom-left hero text -->
        <div class="hero-text">
            <h1>Take Control of Your Property Management</h1>
            <p>Log in to a secure and intuitive PMS that helps you manage your properties, streamline internal
                processes, track performance, and make data-driven decisions that strengthen your teams and culture.</p>
        </div>

        <!-- Login card -->
        <div class="login-card">
            <div class="card-logo">
                <img src="public/images/logo.png" alt="Kaad PMS">
            </div>

            <h2>Welcome Back!</h2>
            <p class="subtitle">Sign in to manage your properties.</p>

            <div id="alert-msg"></div>

            <form id="loginForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrap">
                        <input type="email" id="email" name="email" placeholder="" required autocomplete="email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password" placeholder="••••••••" required
                            autocomplete="current-password">
                        <button type="button" class="toggle-password" id="togglePassword"
                            aria-label="Toggle password visibility">
                            <i class="bi bi-eye-slash-fill"></i>
                        </button>
                    </div>
                </div>

                <div class="meta-row">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">Login</button>
            </form>

            <p class="signup-text">
                <!-- Do not have an account? <a href="#">Sign up here</a> -->
            </p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Password visibility toggle
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye-slash-fill');
                icon.classList.add('bi-eye-fill');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-fill');
                icon.classList.add('bi-eye-slash-fill');
            }
        });

        // Login form AJAX handler
        $(document).ready(function () {
            $('#loginForm').on('submit', function (e) {
                e.preventDefault();

                var btn = $('#loginBtn');
                var originalText = btn.text();
                btn.prop('disabled', true).text('Logging in...');

                $.ajax({
                    url: './app/auth.php?action=login',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function (response) {
                        console.log(response);
                        if (response.error) {
                            $('#alert-msg').html('<div class="alert alert-danger">' + response.msg + '</div>');
                            btn.prop('disabled', false).text(originalText);
                        } else {
                            $('#alert-msg').html('<div class="alert alert-success">' + response.msg + '</div>');
                            setTimeout(function () {
                                window.location.href = './';
                            }, 1000);
                        }
                    },
                    error: function () {
                        $('#alert-msg').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                        btn.prop('disabled', false).text(originalText);
                    }
                });
            });
        });
    </script>
</body>

</html>