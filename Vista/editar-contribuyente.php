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

// Obtener ID del contribuyente a editar
$contribuyente_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($contribuyente_id === 0) {
    header("Location: gestionar-contribuyentes.php");
    exit();
}

// Verificar que el contribuyente está asignado a este contador
$query_contribuyente = "SELECT * FROM usuario WHERE id_usuario = ? AND contador_asignado = ? AND rol = 'usuario'";
$stmt_contribuyente = $db->prepare($query_contribuyente);
$stmt_contribuyente->execute([$contribuyente_id, $contador_id]);
$contribuyente = $stmt_contribuyente->fetch(PDO::FETCH_ASSOC);

if (!$contribuyente) {
    $_SESSION['error'] = "El contribuyente no está asignado a tu cuenta o no existe.";
    header("Location: gestionar-contribuyentes.php");
    exit();
}

// Procesar actualización del contribuyente
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_contribuyente'])) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $tipo_documento = $_POST['tipo_documento'];
    $numero_documento = trim($_POST['numero_documento']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($correo)) {
        $error = "Todos los campos obligatorios deben ser completados.";
    } else {
        // Verificar si el correo ya existe en otro usuario
        $check_query = "SELECT id_usuario FROM usuario WHERE correo = :correo AND id_usuario != :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(":correo", $correo);
        $check_stmt->bindParam(":id", $contribuyente_id);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            $error = "El correo electrónico ya está registrado por otro usuario.";
        } else {
            // Verificar si el número de documento ya existe en otro usuario
            if (!empty($numero_documento)) {
                $check_doc_query = "SELECT id_usuario FROM usuario WHERE numero_documento = :numero_documento AND id_usuario != :id";
                $check_doc_stmt = $db->prepare($check_doc_query);
                $check_doc_stmt->bindParam(":numero_documento", $numero_documento);
                $check_doc_stmt->bindParam(":id", $contribuyente_id);
                $check_doc_stmt->execute();

                if ($check_doc_stmt->rowCount() > 0) {
                    $error = "El número de documento ya está registrado por otro usuario.";
                }
            }

            if (empty($error)) {
                // Actualizar contraseña solo si se proporcionó una nueva
                $password_update = "";
                $params = [
                    ":nombre" => $nombre,
                    ":apellido" => $apellido,
                    ":tipo_documento" => $tipo_documento,
                    ":numero_documento" => $numero_documento,
                    ":correo" => $correo,
                    ":telefono" => $telefono,
                    ":activo" => $activo,
                    ":id" => $contribuyente_id
                ];

                if (!empty($_POST['contrasena'])) {
                    $hashed_password = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
                    $password_update = ", contrasena = :contrasena";
                    $params[":contrasena"] = $hashed_password;
                }

                $query = "UPDATE usuario 
                         SET nombre = :nombre, apellido = :apellido, tipo_documento = :tipo_documento, 
                             numero_documento = :numero_documento, correo = :correo, telefono = :telefono, 
                             activo = :activo $password_update
                         WHERE id_usuario = :id AND contador_asignado = :contador_id";

                $params[":contador_id"] = $contador_id;

                $stmt = $db->prepare($query);

                if ($stmt->execute($params)) {
                    $success = "Contribuyente actualizado exitosamente.";
                    // Actualizar datos locales
                    $contribuyente = array_merge($contribuyente, [
                        'nombre' => $nombre,
                        'apellido' => $apellido,
                        'tipo_documento' => $tipo_documento,
                        'numero_documento' => $numero_documento,
                        'correo' => $correo,
                        'telefono' => $telefono,
                        'activo' => $activo
                    ]);
                } else {
                    $error = "Error al actualizar el contribuyente. Por favor, intenta nuevamente.";
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
    <title>Editar Contribuyente - Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .editar-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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

        .btn-actualizar {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-actualizar:hover {
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

        .contribuyente-header {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #0d6efd;
        }

        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .password-note {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
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
                    <div class="text-center mb-4">
                        <h2 class="mb-1">Renta Segura</h2>
                        <h4 class="text-primary">Editar Contribuyente</h4>
                    </div>

                    <!-- Información del Contador 
                    <div class="contador-info">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-2"><i class="fas fa-user-tie me-2"></i>Contador: <?php echo $contador['nombre'] . ' ' . $contador['apellido']; ?></h6>
                                <p class="mb-1">
                                    <i class="fas fa-envelope me-2"></i><?php echo $contador['correo']; ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="bg-white text-dark rounded-pill px-3 py-2 d-inline-block">
                                    <i class="fas fa-edit me-2 text-primary"></i>
                                    <strong>Editando Contribuyente</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    -->

                    <!-- Información del Contribuyente -->
                    <div class="contribuyente-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-2"><?php echo $contribuyente['nombre'] . ' ' . $contribuyente['apellido']; ?></h5>
                                <p class="mb-1 text-muted">
                                    <i class="fas fa-id-card me-2"></i>
                                    <?php echo $contribuyente['tipo_documento'] . ' ' . $contribuyente['numero_documento']; ?>
                                </p>
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-envelope me-2"></i>
                                    <?php echo $contribuyente['correo']; ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-<?php echo ($contribuyente['activo'] == 1) ? 'success' : 'secondary'; ?> fs-6">
                                    <?php echo ($contribuyente['activo'] == 1) ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Mensajes -->
                    <?php if (!empty($error)): ?>
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                Swal.fire({
                                    icon: "error",
                                    title: "¡Error!",
                                    text: "<?php echo $error; ?>",
                                    confirmButtonColor: "#d33",
                                    background: "#fff",
                                    color: "#000"
                                });
                            });
                        </script>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                Swal.fire({
                                    icon: "success",
                                    title: "¡Usuario Actualizado!",
                                    text: "<?php echo $success; ?>",
                                    confirmButtonColor: "#0d6efd"
                                });
                            });
                        </script>
                    <?php endif; ?>

                    <!-- Formulario -->
                    <form method="POST" action="">
                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control solo-letras" id="nombre" name="nombre"
                                    value="<?php echo htmlspecialchars($contribuyente['nombre']); ?>"
                                    required>
                            </div>

                            <!-- Apellido -->
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control solo-numeros" id="apellido" name="apellido"
                                    value="<?php echo htmlspecialchars($contribuyente['apellido']); ?>"
                                    required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Tipo y Número de Documento -->
                            <div class="col-md-6 mb-3">
                                <label for="tipo_documento" class="form-label">Tipo documento</label>
                                <select class="form-control" id="tipo_documento" name="tipo_documento" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="CC" <?php echo ($contribuyente['tipo_documento'] == 'CC') ? 'selected' : ''; ?>>Cédula de Ciudadanía</option>
                                    <option value="CE" <?php echo ($contribuyente['tipo_documento'] == 'CE') ? 'selected' : ''; ?>>Cédula de Extranjería</option>
                                    <option value="TI" <?php echo ($contribuyente['tipo_documento'] == 'TI') ? 'selected' : ''; ?>>Tarjeta de Identidad</option>
                                    <option value="PAS" <?php echo ($contribuyente['tipo_documento'] == 'PAS') ? 'selected' : ''; ?>>Pasaporte</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="numero_documento" class="form-label">No. documento</label>
                                <input type="text" class="form-control solo-numeros" id="numero_documento" name="numero_documento"
                                    value="<?php echo htmlspecialchars($contribuyente['numero_documento']); ?>"
                                    required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Correo -->
                            <div class="col-md-6 mb-3">
                                <label for="correo" class="form-label">Correo</label>
                                <input type="email" class="form-control correo" id="correo" name="correo"
                                    value="<?php echo htmlspecialchars($contribuyente['correo']); ?>"
                                    required>
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control solo-numeros" id="telefono" name="telefono"
                                    value="<?php echo htmlspecialchars($contribuyente['telefono']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Contraseña (opcional) 
                            <div class="col-md-6 mb-3">
                                <label for="contrasena" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="contrasena" name="contrasena">
                                <div class="password-note">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Dejar en blanco para mantener la contraseña actual
                                </div>
                            </div>
                            -->

                            <!-- Estado Activo -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estado</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="activo" name="activo"
                                        value="1" <?php echo ($contribuyente['activo'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="activo">
                                        Usuario Activo
                                    </label>
                                </div>
                                <div class="password-note">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Desactivar para suspender el acceso del contribuyente
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="actualizar_contribuyente" class="btn btn-actualizar me-3">
                                    <i class="fas fa-save me-2"></i>Actualizar Contribuyente
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
    <script src="../JS/validaciones.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>