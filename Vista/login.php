<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Contaduría</title>
    <link rel="icon" type="image/png" href="../IMG/chart-line-solid-full.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
        }

        .row-custom {
            display: flex;
            align-items: center;
            min-height: 500px;
        }

        /* Lado Izquierdo */
        .left-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .left-section::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -100px;
            right: -100px;
        }

        .left-section::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -50px;
            left: -50px;
        }

        .brand-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .illustration {
            width: 200px;
            height: 200px;
            margin: 30px 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            position: relative;
            z-index: 1;
        }

        .brand-description {
            font-size: 1.1rem;
            margin-top: 20px;
            position: relative;
            z-index: 1;
            line-height: 1.6;
        }

        .register-link {
            color: #FFD700;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .register-link:hover {
            color: white;
            text-decoration: underline;
        }

        /* Lado Derecho */
        .right-section {
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .top-links {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            margin-bottom: 40px;
            margin-top: -40px;
        }

        .top-link {
            color: #667eea;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            border-bottom: 2px solid transparent;
            padding-bottom: 5px;
            transition: all 0.3s ease;
        }

        .top-link:hover {
            border-bottom-color: #667eea;
        }

        .top-link.active {
            border-bottom-color: #667eea;
            color: #667eea;
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 40px;
            align-items: center;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: white;
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
            outline: none;
        }

        .form-control::placeholder {
            color: #adb5bd;
        }

        .password-field {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #adb5bd;
            font-size: 1.2rem;
            user-select: none;
        }

        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 10px;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .forgot-password:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 14px 20px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 10px;
            margin-top: 20px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                margin: 20px;
            }

            .row-custom {
                flex-direction: column;
                min-height: auto;
            }

            .left-section {
                min-height: 300px;
                padding: 40px 20px;
            }

            .right-section {
                padding: 40px 30px;
            }

            .brand-title {
                font-size: 2rem;
            }

            .login-title {
                font-size: 1.5rem;
            }

            .top-links {
                margin-top: 0;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="row-custom">
            <!-- Lado Izquierdo -->
            <div class="col-lg-5 left-section">
                <h2 class="brand-title">Renta Segura</h2>
                <div class="illustration"> <i class="fa-solid fa-chart-line me-2"></i></div>
                <p class="brand-description">
                    Administra tu declaracion de forma <br>
                    <a href="#" class="register-link">rapida facil y segura</a>
                </p>
            </div>

            <!-- Lado Derecho -->
            <div class="col-lg-7 right-section">
                <h3 class="login-title">Login</h3>
                <form>
                    <div class="form-group">
                        <input type="email" class="form-control" placeholder="Enter Email" required>
                    </div>

                    <div class="form-group password-field">
                        <input type="password" class="form-control" id="password" placeholder="••••••••" required>
                        <span class="toggle-password" onclick="togglePassword()">👁️</span>
                    </div>

                    <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>

                    <button type="submit" class="btn-login">Sign in</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.toggle-password');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }
    </script>
</body>

</html>