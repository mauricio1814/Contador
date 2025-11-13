<?php
include_once '../config/database.php';
include_once '../includes/session.php';

redirectIfNotLoggedIn();
// PERMITIR ACCESO A CONTADORES Y USUARIOS (CONTRIBUYENTES)
if (!isContador() && !isContribuyente()) {
    header("Location: principal.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Determinar el rol del usuario actual
$user_id = $_SESSION['user_id'];
$user_rol = $_SESSION['user_rol'];

// Obtener información del usuario actual según su rol
if ($user_rol === 'contador') {
    $query = "SELECT * FROM usuario WHERE id_usuario = ? AND rol = 'contador'";
    $perfil_redirect = "perfil-contador.php";
    $rol_display = "Contador";
    // COLORES PARA CONTADOR: VERDE
    $color_principal = '#28a745';
    $color_hover = '#218838';
} else {
    $query = "SELECT * FROM usuario WHERE id_usuario = ? AND rol = 'usuario'";
    $perfil_redirect = "perfil-usuario.php";
    $rol_display = "Contribuyente";
    // COLORES PARA CONTRIBUYENTE: AZUL
    $color_principal = '#3498db';
    $color_hover = '#2980b9';
}

$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: " . $perfil_redirect);
    exit();
}

// Procesar actualización
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_perfil'])) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $nueva_contrasena = trim($_POST['contrasena']);

    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($correo)) {
        $error = "Los campos nombre, apellido y correo son obligatorios.";
    } else {
        // Verificar si el correo ya existe (excluyendo el usuario actual)
        $check_query = "SELECT id_usuario FROM usuario WHERE correo = ? AND id_usuario != ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$correo, $user_id]);
        
        if ($check_stmt->rowCount() > 0) {
            $error = "El correo electrónico ya está registrado por otro usuario.";
        } else {
            // Construir la consulta UPDATE dinámicamente
            $query = "UPDATE usuario 
                     SET nombre = ?, apellido = ?, correo = ?, telefono = ?";
            
            $params = [$nombre, $apellido, $correo, $telefono];
            
            // Si se proporcionó una nueva contraseña, actualizarla
            if (!empty($nueva_contrasena)) {
                $query .= ", contrasena = ?";
                $params[] = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
            }
            
            $query .= " WHERE id_usuario = ?";
            $params[] = $user_id;
            
            $stmt = $db->prepare($query);
            
            if ($stmt->execute($params)) {
                $success = "Perfil actualizado exitosamente.";
                if (!empty($nueva_contrasena)) {
                    $success .= " La contraseña ha sido actualizada.";
                }
                
                // Actualizar la información en la variable y sesión
                $usuario = array_merge($usuario, [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'correo' => $correo,
                    'telefono' => $telefono,
                ]);
                
                // Actualizar datos en sesión
                $_SESSION['user_nombre'] = $nombre;
                $_SESSION['user_apellido'] = $apellido;
                $_SESSION['user_email'] = $correo;
            } else {
                $error = "Error al actualizar el perfil. Por favor, intenta nuevamente.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .editar-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: <?php echo $color_principal; ?>;
            box-shadow: 0 0 0 0.2rem rgba(<?php 
                if ($user_rol === 'contador') {
                    echo '40, 167, 69'; // Verde
                } else {
                    echo '52, 152, 219'; // Azul
                }
            ?>, 0.25);
        }
        .user-header {
            background: linear-gradient(135deg, <?php echo $color_principal; ?> 0%, #2c3e50 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .btn-guardar {
            background: <?php echo $color_principal; ?>;
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-guardar:hover {
            background: <?php echo $color_hover; ?>;
            transform: scale(1.05);
        }
        .btn-volver {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-volver:hover {
            background: #5a6268;
            transform: scale(1.05);
        }
        .field-disabled {
            background-color: #e9ecef;
            opacity: 1;
            cursor: not-allowed;
        }
        .password-note {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .info-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid <?php echo $color_principal; ?>;
        }
        .icon-color {
            color: <?php echo $color_principal; ?>;
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="editar-card">
                    <!-- Header -->
                    <div class="user-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-1">
                                    <i class="fas fa-user-edit me-2"></i>Editar Perfil
                                </h2>
                                <p class="mb-0">Modifica tu información personal</p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark fs-6">
                                    <?php echo $rol_display; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Mensajes -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <!-- Formulario -->
                    <form method="POST" action="">
                        <!-- Información NO editable -->
                        <div class="info-section">
                            <h5 class="mb-3 icon-color">
                                <i class="fas fa-id-card me-2"></i>Información del Sistema
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ID Usuario</label>
                                    <input type="text" class="form-control field-disabled" 
                                           value="<?php echo htmlspecialchars($usuario['id_usuario']); ?>" 
                                           readonly disabled>
                                    <small class="text-muted">No editable</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipo Documento</label>
                                    <input type="text" class="form-control field-disabled" 
                                           value="<?php echo htmlspecialchars($usuario['tipo_documento']); ?>" 
                                           readonly disabled>
                                    <small class="text-muted">No editable</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Número Documento</label>
                                    <input type="text" class="form-control field-disabled" 
                                           value="<?php echo htmlspecialchars($usuario['numero_documento']); ?>" 
                                           readonly disabled>
                                    <small class="text-muted">No editable</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Rol</label>
                                    <input type="text" class="form-control field-disabled" 
                                           value="<?php echo $rol_display; ?>" readonly disabled>
                                    <small class="text-muted">No editable</small>
                                </div>
                            </div>
                        </div>

                        <!-- Información EDITABLE -->
                        <div class="info-section">
                            <h5 class="mb-3 icon-color">
                                <i class="fas fa-edit me-2"></i>Información Personal
                            </h5>

                            <div class="row">
                                <!-- Nombre -->
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                                </div>

                                <!-- Apellido -->
                                <div class="col-md-6 mb-3">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido"
                                           value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Correo -->
                                <div class="col-md-6 mb-3">
                                    <label for="correo" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="correo" name="correo"
                                           value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                                </div>

                                <!-- Teléfono -->
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono"
                                           value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="row">
                                <!-- Contraseña (opcional) -->
                                <div class="col-12 mb-3">
                                    <label for="contrasena" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="contrasena" name="contrasena" 
                                           placeholder="Ingresa nueva contraseña">
                                    <div class="password-note">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Dejar en blanco para mantener la contraseña actual
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="actualizar_perfil" class="btn btn-guardar me-3">
                                    <i class="fas fa-save me-2"></i>Guardar Cambios
                                </button>
                                <a href="<?php echo $perfil_redirect; ?>" class="btn btn-volver">
                                    <i class="fas fa-arrow-left me-2"></i>Volver al Perfil
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>