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

// Obtener lista de contadores para asignación
$contadores = [];
$query = "SELECT id_usuario, nombre, apellido FROM usuario WHERE rol = 'contador' AND activo = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$contadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Determinar desde dónde se accedió al formulario
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$from_list = false;

// Si viene de admin-usuarios.php, el botón debe volver a la lista
if (strpos($referer, 'admin-usuarios.php') !== false) {
    $from_list = true;
    $return_url = 'admin-usuarios.php';
    $return_text = 'Volver a la Lista';
} else {
    // Por defecto, volver al perfil admin
    $return_url = 'perfil-admin.php';
    $return_text = 'Volver al Perfil';
}

// Procesar registro de nuevo usuario
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_usuario'])) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $tipo_documento = $_POST['tipo_documento'];
    $numero_documento = trim($_POST['numero_documento']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $rol = $_POST['rol'];
    $contrasena = $_POST['contrasena'];
    $contador_asignado = ($rol == 'usuario' && !empty($_POST['contador_asignado'])) ? $_POST['contador_asignado'] : null;

    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($contrasena)) {
        $error = "Todos los campos obligatorios deben ser completados.";
    } else {
        // Verificar si el correo ya existe
        $check_query = "SELECT id_usuario FROM usuario WHERE correo = :correo";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(":correo", $correo);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $error = "El correo electrónico ya está registrado.";
        } else {
            // Verificar si el número de documento ya existe
            if (!empty($numero_documento)) {
                $check_doc_query = "SELECT id_usuario FROM usuario WHERE numero_documento = :numero_documento";
                $check_doc_stmt = $db->prepare($check_doc_query);
                $check_doc_stmt->bindParam(":numero_documento", $numero_documento);
                $check_doc_stmt->execute();
                
                if ($check_doc_stmt->rowCount() > 0) {
                    $error = "El número de documento ya está registrado.";
                }
            }
            
            if (empty($error)) {
                $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
                
                $query = "INSERT INTO usuario (nombre, apellido, tipo_documento, numero_documento, correo, telefono, contrasena, rol, contador_asignado) 
                          VALUES (:nombre, :apellido, :tipo_documento, :numero_documento, :correo, :telefono, :contrasena, :rol, :contador_asignado)";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(":nombre", $nombre);
                $stmt->bindParam(":apellido", $apellido);
                $stmt->bindParam(":tipo_documento", $tipo_documento);
                $stmt->bindParam(":numero_documento", $numero_documento);
                $stmt->bindParam(":correo", $correo);
                $stmt->bindParam(":telefono", $telefono);
                $stmt->bindParam(":contrasena", $hashed_password);
                $stmt->bindParam(":rol", $rol);
                $stmt->bindParam(":contador_asignado", $contador_asignado);
                
                if ($stmt->execute()) {
                    $success = "Usuario registrado exitosamente.";
                    
                    // Si viene de la lista, mantener el contexto de lista
                    if ($from_list) {
                        $return_url = '/Vista/admin-usuarios.php';
                        $return_text = 'Volver a la Lista';
                    }
                    
                    // Limpiar formulario
                    $_POST = array();
                } else {
                    $error = "Error al registrar el usuario. Por favor, intenta nuevamente.";
                }
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
    <title>Registro de Usuario - Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .registro-card {
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
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .btn-registro {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .btn-registro:hover {
            background: #0056d2;
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
        .context-info {
            background: #e7f3ff;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="registro-card">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <h2 class="mb-1">Renta Segura</h2>
                        <h4 class="text-primary">Registro de Usuario</h4>
                        
                        <!-- Información del contexto 
                        <?php if ($from_list): ?>
                            <div class="context-info">
                                <i class="fas fa-users me-2"></i>
                                <strong>Modo Lista:</strong> Al guardar volverás a la lista de usuarios
                            </div>
                        <?php else: ?>
                            <div class="context-info">
                                <i class="fas fa-user-shield me-2"></i>
                                <strong>Modo Perfil:</strong> Al guardar volverás al perfil de administrador
                            </div>
                        <?php endif; ?>
                        -->
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
                                       value="<?php echo isset($_POST['nombre']) ? $_POST['nombre'] : ''; ?>" 
                                       required>
                            </div>

                            <!-- Apellido -->
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellido" name="apellido"
                                       value="<?php echo isset($_POST['apellido']) ? $_POST['apellido'] : ''; ?>"
                                       required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Tipo y Número de Documento -->
                            <div class="col-md-6 mb-3">
                                <label for="tipo_documento" class="form-label">Tipo documento</label>
                                <select class="form-control" id="tipo_documento" name="tipo_documento" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="CC" <?php echo (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'CC') ? 'selected' : ''; ?>>Cédula de Ciudadanía</option>
                                    <option value="CE" <?php echo (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'CE') ? 'selected' : ''; ?>>Cédula de Extranjería</option>
                                    <option value="TI" <?php echo (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'TI') ? 'selected' : ''; ?>>Tarjeta de Identidad</option>
                                    <option value="PAS" <?php echo (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'PAS') ? 'selected' : ''; ?>>Pasaporte</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="numero_documento" class="form-label">No. documento</label>
                                <input type="text" class="form-control" id="numero_documento" name="numero_documento"
                                       value="<?php echo isset($_POST['numero_documento']) ? $_POST['numero_documento'] : ''; ?>"
                                       required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Correo -->
                            <div class="col-md-6 mb-3">
                                <label for="correo" class="form-label">Correo</label>
                                <input type="email" class="form-control" id="correo" name="correo"
                                       value="<?php echo isset($_POST['correo']) ? $_POST['correo'] : ''; ?>"
                                       required>
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono"
                                       value="<?php echo isset($_POST['telefono']) ? $_POST['telefono'] : ''; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Contraseña -->
                            <div class="col-md-6 mb-3">
                                <label for="contrasena" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                            </div>

                            <!-- Rol -->
                            <div class="col-md-6 mb-3">
                                <label for="rol" class="form-label">Rol</label>
                                <select class="form-control" id="rol" name="rol" required onchange="toggleContadorField()">
                                    <option value="">Seleccionar...</option>
                                    <option value="contador" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'contador') ? 'selected' : ''; ?>>Contador</option>
                                    <option value="usuario" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'usuario') ? 'selected' : ''; ?>>Contribuyente</option>
                                </select>
                            </div>
                        </div>

                        <!-- Campo Contador (solo para contribuyentes) -->
                        <div class="row" id="contador-field" style="display: <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'usuario') ? 'block' : 'none'; ?>;">
                            <div class="col-12 mb-3">
                                <label for="contador_asignado" class="form-label">Asignar Contador</label>
                                <select class="form-control" id="contador_asignado" name="contador_asignado">
                                    <option value="">Seleccionar contador...</option>
                                    <?php foreach ($contadores as $contador): ?>
                                        <option value="<?php echo $contador['id_usuario']; ?>"
                                            <?php echo (isset($_POST['contador_asignado']) && $_POST['contador_asignado'] == $contador['id_usuario']) ? 'selected' : ''; ?>>
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
                                <button type="submit" name="registrar_usuario" class="btn btn-registro me-3">
                                    <i class="fas fa-user-plus me-2"></i>Registrar Usuario
                                </button>
                                <a href="<?php echo $return_url; ?>" class="btn btn-volver">
                                    <i class="fas fa-arrow-left me-2"></i><?php echo $return_text; ?>
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