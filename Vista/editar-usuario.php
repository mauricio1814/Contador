<?php
include_once '../config/database.php';
include_once '../includes/session.php';

redirectIfNotLoggedIn();
if (!isAdmin()) {
    header("Location: /Vista/principal.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener el ID del usuario a editar
$usuario_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($usuario_id === 0) {
    header("Location: admin-usuarios.php");
    exit();
}

// Obtener información del usuario
$query = "SELECT u.*, c.nombre as contador_nombre, c.apellido as contador_apellido 
          FROM usuario u 
          LEFT JOIN usuario c ON u.contador_asignado = c.id_usuario 
          WHERE u.id_usuario = :id_usuario";
$stmt = $db->prepare($query);
$stmt->bindParam(":id_usuario", $usuario_id);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    header("Location: admin-usuarios.php");
    exit();
}

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener lista de contadores para asignación
$contadores = [];
$query_contadores = "SELECT id_usuario, nombre, apellido FROM usuario WHERE rol = 'contador' AND activo = 1 AND id_usuario != :current_id";
$stmt_contadores = $db->prepare($query_contadores);
$stmt_contadores->bindParam(":current_id", $usuario_id);
$stmt_contadores->execute();
$contadores = $stmt_contadores->fetchAll(PDO::FETCH_ASSOC);

// Procesar actualización
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_usuario'])) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $tipo_documento = $_POST['tipo_documento'];
    $numero_documento = trim($_POST['numero_documento']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $rol = $_POST['rol'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    $contador_asignado = ($rol == 'usuario' && !empty($_POST['contador_asignado'])) ? $_POST['contador_asignado'] : null;

    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($correo)) {
        $error = "Todos los campos obligatorios deben ser completados.";
    } else {
        // Verificar si el correo ya existe (excluyendo el usuario actual)
        $check_query = "SELECT id_usuario FROM usuario WHERE correo = :correo AND id_usuario != :id_usuario";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(":correo", $correo);
        $check_stmt->bindParam(":id_usuario", $usuario_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $error = "El correo electrónico ya está registrado por otro usuario.";
        } else {
            // Actualizar usuario
            $query = "UPDATE usuario 
                      SET nombre = :nombre, apellido = :apellido, tipo_documento = :tipo_documento, 
                          numero_documento = :numero_documento, correo = :correo, telefono = :telefono,
                          rol = :rol, activo = :activo, contador_asignado = :contador_asignado
                      WHERE id_usuario = :id_usuario";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":apellido", $apellido);
            $stmt->bindParam(":tipo_documento", $tipo_documento);
            $stmt->bindParam(":numero_documento", $numero_documento);
            $stmt->bindParam(":correo", $correo);
            $stmt->bindParam(":telefono", $telefono);
            $stmt->bindParam(":rol", $rol);
            $stmt->bindParam(":activo", $activo);
            $stmt->bindParam(":contador_asignado", $contador_asignado);
            $stmt->bindParam(":id_usuario", $usuario_id);
            
            if ($stmt->execute()) {
                $success = "Usuario actualizado exitosamente.";
                // Actualizar la información del usuario en la variable
                $usuario = array_merge($usuario, [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'tipo_documento' => $tipo_documento,
                    'numero_documento' => $numero_documento,
                    'correo' => $correo,
                    'telefono' => $telefono,
                    'rol' => $rol,
                    'activo' => $activo,
                    'contador_asignado' => $contador_asignado
                ]);
            } else {
                $error = "Error al actualizar el usuario. Por favor, intenta nuevamente.";
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
    <title>Editar Usuario - Renta Segura</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
                                    <i class="fas fa-user-edit me-2"></i>Editar Usuario
                                </h2>
                                <p class="mb-0">Modifica la información del usuario</p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark fs-6">
                                    <?php 
                                        switch($usuario['rol']) {
                                            case 'admin': echo 'Administrador'; break;
                                            case 'contador': echo 'Contador'; break;
                                            case 'usuario': echo 'Contribuyente'; break;
                                        }
                                    ?>
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
                            <!-- Tipo y Número de Documento -->
                            <div class="col-md-6 mb-3">
                                <label for="tipo_documento" class="form-label">Tipo documento</label>
                                <select class="form-control" id="tipo_documento" name="tipo_documento" required>
                                    <option value="CC" <?php echo $usuario['tipo_documento'] == 'CC' ? 'selected' : ''; ?>>Cédula de Ciudadanía</option>
                                    <option value="CE" <?php echo $usuario['tipo_documento'] == 'CE' ? 'selected' : ''; ?>>Cédula de Extranjería</option>
                                    <option value="TI" <?php echo $usuario['tipo_documento'] == 'TI' ? 'selected' : ''; ?>>Tarjeta de Identidad</option>
                                    <option value="PAS" <?php echo $usuario['tipo_documento'] == 'PAS' ? 'selected' : ''; ?>>Pasaporte</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="numero_documento" class="form-label">No. documento</label>
                                <input type="text" class="form-control" id="numero_documento" name="numero_documento"
                                       value="<?php echo htmlspecialchars($usuario['numero_documento']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Correo -->
                            <div class="col-md-6 mb-3">
                                <label for="correo" class="form-label">Correo</label>
                                <input type="email" class="form-control" id="correo" name="correo"
                                       value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono"
                                       value="<?php echo htmlspecialchars($usuario['telefono']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Rol -->
                            <div class="col-md-6 mb-3">
                                <label for="rol" class="form-label">Rol</label>
                                <select class="form-control" id="rol" name="rol" required onchange="toggleContadorField()">
                                    <option value="contador" <?php echo $usuario['rol'] == 'contador' ? 'selected' : ''; ?>>Contador</option>
                                    <option value="usuario" <?php echo $usuario['rol'] == 'usuario' ? 'selected' : ''; ?>>Contribuyente</option>
                                    <option value="admin" <?php echo $usuario['rol'] == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estado</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                           <?php echo $usuario['activo'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="activo">
                                        Usuario activo
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Campo Contador (solo para contribuyentes) -->
                        <div class="row" id="contador-field" style="display: <?php echo $usuario['rol'] == 'usuario' ? 'block' : 'none'; ?>;">
                            <div class="col-12 mb-3">
                                <label for="contador_asignado" class="form-label">Asignar Contador</label>
                                <select class="form-control" id="contador_asignado" name="contador_asignado">
                                    <option value="">Seleccionar contador...</option>
                                    <?php foreach ($contadores as $contador): ?>
                                        <option value="<?php echo $contador['id_usuario']; ?>"
                                            <?php echo $usuario['contador_asignado'] == $contador['id_usuario'] ? 'selected' : ''; ?>>
                                            <?php echo $contador['nombre'] . ' ' . $contador['apellido']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Selecciona el contador que gestionará este contribuyente</small>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="actualizar_usuario" class="btn btn-guardar me-3">
                                    <i class="fas fa-save me-2"></i>Guardar Cambios
                                </button>
                                <a href="admin-usuarios.php" class="btn btn-volver">
                                    <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleContadorField() {
            const rol = document.getElementById('rol').value;
            const contadorField = document.getElementById('contador-field');
            
            if (rol === 'usuario') {
                contadorField.style.display = 'block';
            } else {
                contadorField.style.display = 'none';
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>