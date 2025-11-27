<?php
// Iniciar sesión al principio del archivo
session_start();

// Configuración para mostrar errores (solo en desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';

// Verificar si el archivo de configuración existe
$config_file = '../config/database.php';
if (!file_exists($config_file)) {
    $error = "Error de configuración: Archivo de base de datos no encontrado.";
}

// Procesar login solo si no hay error de configuración
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error)) {
    try {
        include_once $config_file;
        
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db === null) {
            throw new Exception("No se pudo conectar a la base de datos.");
        }
        
        $correo = trim($_POST['correo']);
        $contrasena = trim($_POST['contrasena']);
        
        if (!empty($correo) && !empty($contrasena)) {
            // Consulta para verificar usuario
            $query = "SELECT u.*, a.permisos 
                      FROM usuario u 
                      LEFT JOIN admin a ON u.id_usuario = a.id_admin 
                      WHERE u.correo = :correo AND u.activo = 1";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(":correo", $correo);
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (password_verify($contrasena, $row['contrasena'])) {
                        // Iniciar sesión
                        $_SESSION['user_id'] = $row['id_usuario'];
                        $_SESSION['user_nombre'] = $row['nombre'];
                        $_SESSION['user_apellido'] = $row['apellido'];
                        $_SESSION['user_correo'] = $row['correo'];
                        $_SESSION['user_rol'] = $row['rol'];
                        $_SESSION['user_permisos'] = $row['permisos'] ? json_decode($row['permisos'], true) : [];
                        
                        // Redirigir según el rol
                        if ($_SESSION['user_rol'] === 'admin') {
                            header("Location: perfil-admin.php");
                        } elseif ($_SESSION['user_rol'] === 'contador') {
                            header("Location: perfil-contador.php");
                        } else {
                            header("Location: principal.php");
                        }
                        exit();
                    } else {
                        $error = "Contraseña incorrecta.";
                    }
                } else {
                    $error = "El correo no está registrado o el usuario está inactivo.";
                }
            } else {
                $error = "Error en la consulta de base de datos.";
            }
        } else {
            $error = "Por favor, completa todos los campos.";
        }
    } catch (Exception $e) {
        $error = "Error del sistema: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Renta Segura</title>
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

        .credentials-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            border-left: 4px solid #667eea;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
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
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="row-custom">
            <!-- Lado Izquierdo -->
            <div class="col-lg-5 left-section">
                <h2 class="brand-title">Renta Segura</h2>
                <div class="illustration"> 
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <p class="brand-description">
                    Administra tu declaración de forma <br>
                    <span style="color: #FFD700; font-weight: 600;">rápida, fácil y segura</span>
                </p>
            </div>

            <!-- Lado Derecho -->
            <div class="col-lg-7 right-section">
                <h3 class="login-title">Iniciar Sesión</h3>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <input type="email" name="correo" class="form-control" placeholder="Ingresa tu Email" required 
                               value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>">
                    </div>

                    <div class="form-group password-field">
                        <input type="password" name="contrasena" class="form-control" id="password" placeholder="••••••••" required>
                        <span class="toggle-password" onclick="togglePassword()">👁️</span>
                    </div>

                    

                    <button type="submit" class="btn-login">Iniciar Sesión</button>
                </form>
                
                <!-- Información de credenciales 
                <div class="credentials-info">
                    <h6 class="fw-bold">Credenciales de prueba:</h6>
                    <p class="mb-1 small">
                        <strong>Admin:</strong> admin@admin.com / password
                    </p>
                    <p class="mb-0 small text-muted">
                        * Solo el administrador puede registrar nuevos usuarios
                    </p>
                </div>  
                -->
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>