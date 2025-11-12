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

// Obtener contribuyentes asignados a este contador
$query_contribuyentes = "SELECT * FROM usuario WHERE contador_asignado = ? AND rol = 'usuario' ORDER BY nombre, apellido";
$stmt_contribuyentes = $db->prepare($query_contribuyentes);
$stmt_contribuyentes->execute([$contador_id]);
$contribuyentes = $stmt_contribuyentes->fetchAll(PDO::FETCH_ASSOC);

// Procesar eliminación de asignación
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_asignacion'])) {
    $contribuyente_id = intval($_POST['contribuyente_id']);
    
    // Verificar que el contribuyente realmente está asignado a este contador
    $check_query = "SELECT id_usuario FROM usuario WHERE id_usuario = ? AND contador_asignado = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$contribuyente_id, $contador_id]);
    
    if ($check_stmt->rowCount() > 0) {
        // Eliminar asignación (poner contador_asignado en NULL)
        $update_query = "UPDATE usuario SET contador_asignado = NULL WHERE id_usuario = ?";
        $update_stmt = $db->prepare($update_query);
        
        if ($update_stmt->execute([$contribuyente_id])) {
            $success = "Contribuyente eliminado de tu lista de asignados exitosamente.";
            // Recargar la lista de contribuyentes
            $stmt_contribuyentes->execute([$contador_id]);
            $contribuyentes = $stmt_contribuyentes->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error = "Error al eliminar la asignación. Por favor, intenta nuevamente.";
        }
    } else {
        $error = "El contribuyente no está asignado a tu cuenta.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Contribuyentes - Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .gestion-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 25px;
        }
        .user-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #0d6efd;
            transition: transform 0.2s;
        }
        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .header-gestion {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .btn-primary-custom {
            background: #3498db;
            border-color: #3498db;
            color: white;
        }
        .btn-primary-custom:hover {
            background: #2980b9;
            border-color: #2980b9;
        }
        .btn-success-custom {
            background: #2ecc71;
            border-color: #2ecc71;
            color: white;
        }
        .btn-success-custom:hover {
            background: #27ae60;
            border-color: #27ae60;
        }
        .btn-danger-custom {
            background: #e74c3c;
            border-color: #e74c3c;
            color: white;
        }
        .btn-danger-custom:hover {
            background: #c0392b;
            border-color: #c0392b;
        }
        .btn-agregar {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-agregar:hover {
            background: #0056d2;
            transform: scale(1.05);
        }
        .stats-badge {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .btn-group-header {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        @media (max-width: 768px) {
            .btn-group-header {
                flex-direction: column;
                width: 100%;
            }
            .btn-group-header .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="container mt-4">
        <!-- Header -->
        <div class="header-gestion">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-1">
                        <i class="fas fa-users me-2"></i>Gestión de Contribuyentes
                    </h2>
                    <p class="text-muted mb-0">Administra los contribuyentes asignados a tu cuenta</p>
                </div>

                <div class="col-md-4 text-end">
                    <div class="btn-group-header">
                        <a href="registro-contribuyente.php" class="btn btn-agregar">
                            <i class="fas fa-plus me-2"></i>Agregar Usuario
                        </a>
                        <a href="perfil-contador.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Perfil
                        </a>
                    </div>
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

        <!-- Lista de Contribuyentes -->
        <div class="gestion-card">
            <h4 class="mb-4">
                <i class="fas fa-list me-2"></i>Contribuyentes Asignados
            </h4>

            <?php if (count($contribuyentes) > 0): ?>
                <div class="row">
                    <?php foreach ($contribuyentes as $contribuyente): ?>
                        <div class="col-md-6 mb-3">
                            <div class="user-card">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            <?php echo substr($contribuyente['nombre'], 0, 1) . substr($contribuyente['apellido'], 0, 1); ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-1"><?php echo $contribuyente['nombre'] . ' ' . $contribuyente['apellido']; ?></h6>
                                            <span class="badge bg-success">Contribuyente</span>
                                            <small class="text-muted d-block">ID: <?php echo $contribuyente['id_usuario']; ?></small>
                                        </div>
                                    </div>
                                    <span class="badge bg-<?php echo ($contribuyente['activo'] == 1) ? 'success' : 'secondary'; ?>">
                                        <?php echo ($contribuyente['activo'] == 1) ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </div>

                                <!-- Información del Contribuyente -->
                                <div class="mb-3">
                                    <p class="mb-1">
                                        <i class="fas fa-envelope me-2 text-muted"></i>
                                        <?php echo $contribuyente['correo']; ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="fas fa-id-card me-2 text-muted"></i>
                                        <?php echo $contribuyente['tipo_documento'] . ' ' . $contribuyente['numero_documento']; ?>
                                    </p>
                                    <?php if (!empty($contribuyente['telefono'])): ?>
                                        <p class="mb-0">
                                            <i class="fas fa-phone me-2 text-muted"></i>
                                            <?php echo $contribuyente['telefono']; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <!-- Botones de Acción -->
                                <div class="d-flex gap-2">
                                    <a href="ver-contribuyente.php?id=<?php echo $contribuyente['id_usuario']; ?>" 
                                       class="btn btn-primary-custom btn-sm flex-fill">
                                        <i class="fas fa-eye me-1"></i>Ver
                                    </a>
                                    <a href="editar-contribuyente.php?id=<?php echo $contribuyente['id_usuario']; ?>" 
                                       class="btn btn-success-custom btn-sm flex-fill">
                                        <i class="fas fa-edit me-1"></i>Editar
                                    </a>
                                    <button type="button" class="btn btn-danger-custom btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEliminar<?php echo $contribuyente['id_usuario']; ?>">
                                        <i class="fas fa-times me-1"></i>Eliminar
                                    </button>
                                </div>

                                <!-- Modal de Confirmación para Eliminar -->
                                <div class="modal fade" id="modalEliminar<?php echo $contribuyente['id_usuario']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirmar Eliminación</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>¿Estás seguro de que deseas eliminar a <strong><?php echo $contribuyente['nombre'] . ' ' . $contribuyente['apellido']; ?></strong> de tu lista de contribuyentes asignados?</p>
                                                <p class="text-muted"><small>Esta acción solo elimina la asignación, el contribuyente permanecerá en el sistema.</small></p>
                                            </div>
                                            <div class="modal-footer">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="contribuyente_id" value="<?php echo $contribuyente['id_usuario']; ?>">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" name="eliminar_asignacion" class="btn btn-danger-custom">Eliminar Asignación</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No tienes contribuyentes asignados</h4>
                    <p class="text-muted">Puedes agregar nuevos contribuyentes usando el botón "Agregar Usuario".</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="perfil-contador.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Perfil
                        </a>
                        <a href="registro-contribuyente.php" class="btn btn-agregar">
                            <i class="fas fa-plus me-2"></i>Agregar Usuario
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>