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
    <title>Login - Aayatiin Property Ltd</title>
    <link href="public/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap bundle -->
    <link href="public/css/bootstrap.bundle.css" rel="stylesheet">
    <link href="public/css/styles.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo img {
            max-width: 200px;
        }
        .bg-image {
            background-image: url('public/images/cover.webp');
            background-size: cover;
            background-position: center;
            position: relative;
            min-height: 100vh;
        }

        .bg-image::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /*background: rgba(255, 255, 255, 0.4);  adjust opacity here */
            /*backdrop-filter: blur(1px);   optional: softens image */
            z-index: 0;
        }

        .bg-image > * {
            position: relative;
            z-index: 1; /* keeps content above the overlay */
        }
        .btn {
            color:#fff;
            font-size: 14px;
            padding: 10px;
        }
    </style>
</head>
<body class="bg-image">
    <div class="login-card">
        <div class="login-logo">
            <img src="public/images/logo.jpg" alt="Logo">
            <!-- <h3>Aayatiin PMS</h3> -->
        </div>
        <div id="alert-msg"></div>
        <form id="loginForm">
            <div class="mb-3">
                <label for="email" class="form-label">Username</label>
                <input type="text" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100" id="loginBtn">Login</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                var btn = $('#loginBtn');
                var originalText = btn.text();
                btn.prop('disabled', true).text('Logging in...');
                
                $.ajax({
                    url: './app/auth.php?action=login',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        console.log(response);
                        if (response.error) {
                            $('#alert-msg').html('<div class="alert alert-danger">' + response.msg + '</div>');
                            btn.prop('disabled', false).text(originalText);
                        } else {
                            $('#alert-msg').html('<div class="alert alert-success">' + response.msg + '</div>');
                            setTimeout(function() {
                                window.location.href = './';
                            }, 1000);
                        }
                    },
                    error: function() {
                        $('#alert-msg').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                        btn.prop('disabled', false).text(originalText);
                    }
                });
            });
        });
    </script>
</body>
</html>
