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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renta Segura - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .header-login {
            right: 10%;
        }
        .header-login .nav-link {
            color: #0d6efd;
            font-weight: 500;
        }
        .card {
            border-radius: 20px;
            border: none;
        }
        img {
            max-width: 280px;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            padding: 12px;
        }
        .btn-primary:hover {
            background-color: #0056d2;
        }
        .credentials-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            border-left: 4px solid #0d6efd;
        }
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>


    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100 justify-content-center align-items-center">
            <div class="col-md-6 text-center text-md-start px-5">
                <h2 class="fw-bold mb-4">Renta Segura</h2>
                <img src="https://cdn-icons-png.flaticon.com/512/2331/2331941.png" alt="Renta Segura"
                    class="img-fluid w-50 mb-4">
                <p class="text-muted">Sistema de gestión de declaración de renta</p>
            </div>

            <div class="col-md-4">
                <!-- FORMULARIO LOGIN -->
                <div class="card shadow p-4 border-0 rounded-4">
                    <h5 class="text-center mb-4 fw-bold">Iniciar Sesión</h5>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="correo" class="form-label">Email</label>
                            <input type="email" name="correo" class="form-control form-control-lg rounded-3"
                                placeholder="Ingresa el Email" required 
                                value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="contrasena" class="form-label">Contraseña</label>
                            <input type="password" name="contrasena" class="form-control form-control-lg rounded-3"
                                placeholder="Ingresa el Password" required>
                        </div>
                        <div class="text-end mb-3">
                            <a href="#" class="small text-decoration-none text-primary">¿Recuperar Contraseña?</a>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg rounded-3 shadow-sm">Iniciar Sesión</button>
                        </div>
                    </form>
                    
                    <!-- Información de credenciales -->
                    <div class="credentials-info">
                        <h6 class="fw-bold">Credenciales de prueba:</h6>
                        <p class="mb-1 small">
                            <strong>Admin:</strong> admin@admin.com / password
                        </p>
                        <p class="mb-0 small text-muted">
                            * Solo el administrador puede registrar nuevos usuarios
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>