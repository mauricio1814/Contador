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
$query_contador = "SELECT * FROM usuario WHERE id_usuario = ? AND rol = 'contador'";
$stmt_contador = $db->prepare($query_contador);
$stmt_contador->execute([$contador_id]);
$contador = $stmt_contador->fetch(PDO::FETCH_ASSOC);

if (!$contador) {
    header("Location: perfil-contador.php");
    exit();
}

// Procesar registro de nuevo contribuyente
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_usuario'])) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $tipo_documento = $_POST['tipo_documento'];
    $numero_documento = trim($_POST['numero_documento']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $contrasena = $_POST['contrasena'];
    
    // El rol siempre será 'usuario' (contribuyente) y se asigna automáticamente al contador actual
    $rol = 'usuario';
    $contador_asignado = $contador_id;

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
                    $success = "Contribuyente registrado exitosamente y asignado a tu cuenta.";
                    
                    // Limpiar formulario
                    $_POST = array();
                } else {
                    $error = "Error al registrar el contribuyente. Por favor, intenta nuevamente.";
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
    <title>Registro de Contribuyente - Renta Segura</title>
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
        .contador-info {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .field-disabled {
            background-color: #f8f9fa;
            opacity: 0.7;
            cursor: not-allowed;
        }
        .info-badge {
            background: #e7f3ff;
            color: #0d6efd;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.9rem;
            margin-bottom: 15px;
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
                        <h4 class="text-primary">Registro de Contribuyente</h4>
                    </div>

                    <!-- Información del Contador -->
                    <div class="contador-info">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-2"><i class="fas fa-user-tie me-2"></i>Contador: <?php echo $contador['nombre'] . ' ' . $contador['apellido']; ?></h6>
                                <p class="mb-1">
                                    <i class="fas fa-envelope me-2"></i><?php echo $contador['correo']; ?>
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-id-card me-2"></i><?php echo $contador['tipo_documento'] . ' ' . $contador['numero_documento']; ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="bg-white text-dark rounded-pill px-3 py-2 d-inline-block">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>
                                    <strong>Modo Contador</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de asignación automática -->
                    <div class="info-badge">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Asignación automática:</strong> El nuevo contribuyente se asignará automáticamente a tu cuenta.
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

                            <!-- Rol (solo contribuyente, deshabilitado) -->
                            <div class="col-md-6 mb-3">
                                <label for="rol" class="form-label">Rol</label>
                                <input type="text" class="form-control field-disabled" value="Contribuyente" readonly>
                                <input type="hidden" name="rol" value="usuario">
                                <small class="text-muted">Los contadores solo pueden registrar contribuyentes</small>
                            </div>
                        </div>

                        <!-- Información de asignación automática -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-user-tie me-2"></i>
                                    <strong>Asignación automática:</strong> Este contribuyente será asignado automáticamente a tu cuenta de contador.
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="registrar_usuario" class="btn btn-registro me-3">
                                    <i class="fas fa-user-plus me-2"></i>Registrar Contribuyente
                                </button>
                                <a href="gestionar-contribuyentes.php" class="btn btn-volver">
                                    <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>