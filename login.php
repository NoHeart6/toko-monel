<?php
require_once 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = login($_POST['email'], $_POST['password']);
    if ($result['success']) {
        header('Location: index.php');
        exit;
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Monel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #304ffe;
            --accent-color: #536dfe;
            --text-color: #1a237e;
            --background-color: #e8eaf6;
            --error-color: #ff5252;
            --success-color: #69f0ae;
        }

        body {
            background: linear-gradient(135deg, var(--background-color) 0%, #c5cae9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            width: 100%;
            padding: 20px;
            max-width: 450px;
            margin: auto;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(26, 35, 126, 0.2);
            backdrop-filter: blur(10px);
            transform: translateY(0);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInDown 1s;
        }

        .login-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(26, 35, 126, 0.25);
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 35px;
            color: var(--primary-color);
            font-weight: 700;
            font-size: 32px;
            position: relative;
            padding-bottom: 15px;
            letter-spacing: 1px;
            animation: fadeIn 1s ease-in;
        }

        .login-box h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(to right, var(--secondary-color), var(--accent-color));
            border-radius: 2px;
            transition: width 0.3s ease;
            animation: slideInLeft 1s ease-out;
        }

        .login-box:hover h2::after {
            width: 100px;
        }

        .input-group {
            position: relative;
            margin-bottom: 30px;
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
        }

        .input-group:nth-child(1) { animation-delay: 0.2s; }
        .input-group:nth-child(2) { animation-delay: 0.4s; }

        .input-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 16px;
            color: var(--text-color);
            background: rgba(255, 255, 255, 0.9);
        }

        .input-group label {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            background: transparent;
            padding: 0 5px;
            color: #9fa8da;
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 16px;
            font-weight: 500;
        }

        .input-group input:focus,
        .input-group input:not(:placeholder-shown) {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 4px rgba(48, 79, 254, 0.1);
        }

        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label {
            top: 0;
            font-size: 14px;
            color: var(--secondary-color);
            font-weight: 600;
            background: white;
        }

        .password-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9fa8da;
            transition: all 0.3s ease;
            padding: 5px;
        }

        .password-toggle:hover {
            color: var(--secondary-color);
            transform: translateY(-50%) scale(1.1);
        }

        .remember {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            color: var(--text-color);
            user-select: none;
            animation: fadeInUp 0.5s ease-out 0.6s forwards;
            opacity: 0;
        }

        .remember input {
            margin-right: 10px;
            cursor: pointer;
            width: 18px;
            height: 18px;
            accent-color: var(--secondary-color);
        }

        .remember label {
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
        }

        button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.5s ease-out 0.8s forwards;
            opacity: 0;
        }

        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.5s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(83, 109, 254, 0.3);
        }

        button:hover::before {
            left: 100%;
        }

        button:active {
            transform: translateY(0);
        }

        .links {
            margin-top: 30px;
            text-align: center;
            color: var(--text-color);
            animation: fadeInUp 0.5s ease-out 1s forwards;
            opacity: 0;
        }

        .links a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background: var(--secondary-color);
            transition: width 0.3s ease;
        }

        .links a:hover::after {
            width: 100%;
        }

        .register-link {
            margin-top: 20px;
            font-size: 15px;
            opacity: 0.9;
        }

        .alert {
            border: none;
            border-radius: 12px;
            margin-bottom: 30px;
            padding: 15px 20px;
            background: rgba(255, 82, 82, 0.1);
            color: var(--error-color);
            font-weight: 500;
            animation: headShake 1s;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInLeft {
            from {
                width: 0;
                opacity: 0;
            }
            to {
                width: 60px;
                opacity: 1;
            }
        }

        /* Animasi loading saat submit */
        button.loading {
            position: relative;
            pointer-events: none;
        }

        button.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin: -10px 0 0 -10px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Tambahan animasi dari Animate.css */
        .animate__animated {
            animation-duration: 1s;
            animation-fill-mode: both;
        }

        .animate__fadeInDown {
            animation-name: fadeInDown;
        }

        .animate__headShake {
            animation-name: headShake;
            animation-timing-function: ease-in-out;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box animate__animated animate__fadeInDown">
            <h2 class="animate__animated animate__fadeIn">Login Monel Store</h2>
            <?php if($error): ?>
                <div class="alert animate__animated animate__headShake"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="" method="post" id="loginForm">
                <div class="input-group">
                    <input type="email" name="email" id="email" required placeholder=" ">
                    <label for="email">Email</label>
                </div>
                <div class="input-group">
                    <input type="password" name="password" id="password" required placeholder=" ">
                    <label for="password">Password</label>
                    <span class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </span>
                </div>
                <div class="remember">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat saya</label>
                </div>
                <button type="submit" id="loginButton">
                    <span class="button-text">Masuk</span>
                </button>
                <div class="links">
                    <div class="register-link">
                        <p>Belum punya akun? <a href="register.php" class="animate__animated animate__pulse">Daftar disini</a></p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    // Animasi loading saat submit
    document.getElementById('loginForm').addEventListener('submit', function() {
        const button = document.getElementById('loginButton');
        const buttonText = button.querySelector('.button-text');
        button.classList.add('loading');
        buttonText.textContent = 'Memproses...';
    });

    // Tambahkan animasi hover pada link
    document.querySelectorAll('.links a').forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.classList.add('animate__animated', 'animate__pulse');
        });
        
        link.addEventListener('animationend', function() {
            this.classList.remove('animate__animated', 'animate__pulse');
        });
    });
    </script>
</body>
</html> 