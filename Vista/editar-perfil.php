<?php
include_once '../config/database.php';
include_once '../includes/session.php';

redirectIfNotLoggedIn();
if (!isContador()) {
    header("Location: principal.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener información del contador actual
$contador_id = $_SESSION['user_id'];
$query = "SELECT * FROM usuario WHERE id_usuario = ? AND rol = 'contador'";
$stmt = $db->prepare($query);
$stmt->execute([$contador_id]);
$contador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contador) {
    header("Location: perfil-contador.php");
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
    

    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($correo)) {
        $error = "Los campos nombre, apellido y correo son obligatorios.";
    } else {
        // Verificar si el correo ya existe (excluyendo el usuario actual)
        $check_query = "SELECT id_usuario FROM usuario WHERE correo = ? AND id_usuario != ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$correo, $contador_id]);
        
        if ($check_stmt->rowCount() > 0) {
            $error = "El correo electrónico ya está registrado por otro usuario.";
        } else {
            // Actualizar solo los campos permitidos para contador
            $query = "UPDATE usuario 
                      SET nombre = ?, apellido = ?, correo = ?, telefono = ?
                      WHERE id_usuario = ?";
            
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$nombre, $apellido, $correo, $telefono, $contador_id])) {
                $success = "Perfil actualizado exitosamente.";
                // Actualizar la información en la variable y sesión
                $contador = array_merge($contador, [
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
        .user-header {
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .btn-guardar {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 10px;
            font-weight: 600;
        }
        .btn-guardar:hover {
            background: #218838;
            transform: scale(1.05);
        }
        .btn-volver {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
        }
        .field-disabled {
            background-color: #e9ecef;
            opacity: 1;
            cursor: not-allowed;
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
                                    Contador
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
                        <div class="row">
                            <!-- Información NO editable -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ID Usuario</label>
                                <input type="text" class="form-control field-disabled" 
                                       value="<?php echo htmlspecialchars($contador['id_usuario']); ?>" 
                                       readonly disabled>
                                <small class="text-muted">No editable</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo Documento</label>
                                <input type="text" class="form-control field-disabled" 
                                       value="<?php echo htmlspecialchars($contador['tipo_documento']); ?>" 
                                       readonly disabled>
                                <small class="text-muted">No editable</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Número Documento</label>
                                <input type="text" class="form-control field-disabled" 
                                       value="<?php echo htmlspecialchars($contador['numero_documento']); ?>" 
                                       readonly disabled>
                                <small class="text-muted">No editable</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rol</label>
                                <input type="text" class="form-control field-disabled" 
                                       value="Contador" readonly disabled>
                                <small class="text-muted">No editable</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Información EDITABLE -->
                        <h5 class="mb-3 text-primary">
                            <i class="fas fa-edit me-2"></i>Información 
                        </h5>

                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($contador['nombre']); ?>" required>

                            </div>

                            <!-- Apellido -->
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellido" name="apellido"
                                       value="<?php echo htmlspecialchars($contador['apellido']); ?>" required>

                            </div>
                        </div>

                        <div class="row">
                            <!-- Correo -->
                            <div class="col-md-6 mb-3">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo"
                                       value="<?php echo htmlspecialchars($contador['correo']); ?>" required>

                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono"
                                       value="<?php echo htmlspecialchars($contador['telefono'] ?? ''); ?>">

                            </div>
                        </div>

                        <div class="row">
                            <!-- Dirección 
                            <div class="col-12 mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <textarea class="form-control" id="direccion" name="direccion" 
                                          rows="3"><?php echo htmlspecialchars($contador['direccion'] ?? ''); ?></textarea>
                                <small class="text-muted">Puede actualizar su dirección</small>
                            </div>
                            -->
                        </div>

                        <!-- Botones -->
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="actualizar_perfil" class="btn btn-guardar me-3">
                                    <i class="fas fa-save me-2"></i>Guardar Cambios
                                </button>
                                <a href="perfil-contador.php" class="btn btn-volver">
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