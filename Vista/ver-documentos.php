<?php
// ver_documentos.php
include_once '../config/database.php';
include_once '../includes/session.php';

redirectIfNotLoggedIn();

// Solo contadores pueden acceder a esta página
if (!isContador()) {
    header("Location: principal.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$contador_id = $_SESSION['user_id'];
$mensaje = '';

// Verificar que el parámetro id_usuario esté presente y sea válido
if (!isset($_GET['id_usuario']) || !is_numeric($_GET['id_usuario'])) {
    header("Location: gestionar-contribuyentes.php");
    exit();
}

$usuario_id = $_GET['id_usuario'];

// Verificar que el usuario esté asignado a este contador
$sql_verificar = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo 
                  FROM usuario u 
                  WHERE u.id_usuario = ? AND u.contador_asignado = ?";
$stmt_verificar = $db->prepare($sql_verificar);
$stmt_verificar->execute([$usuario_id, $contador_id]);
$usuario = $stmt_verificar->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: gestionar-contribuyentes.php");
    exit();
}

// Función para determinar el tipo de documento
function obtenerTipoDocumento($nombreArchivo) {
    $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
    
    if ($extension == 'pdf') {
        return 'pdf';
    } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
        return 'image';
    } elseif (in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'])) {
        return 'office';
    } else {
        return 'other';
    }
}

// **FUNCIÓN CORREGIDA** - Obtener la URL corregida del documento
function obtenerUrlDocumento($urlDocumento) {
    // Si ya es una URL completa, devolverla tal cual
    if (strpos($urlDocumento, 'http') === 0) {
        return $urlDocumento;
    }
    
    // Si es una ruta absoluta del servidor, extraer solo el nombre del archivo
    if (strpos($urlDocumento, '../uploads/documentos/') !== false) {
        $nombreArchivo = basename($urlDocumento);
        return '../uploads/documentos/' . $nombreArchivo;
    }
    
    // Si contiene la ruta uploads/documentos/, mantenerla
    if (strpos($urlDocumento, 'uploads/documentos/') !== false) {
        return '../' . $urlDocumento;
    }
    
    // Si es solo el nombre del archivo, agregar la ruta
    if (strpos($urlDocumento, '/') === false) {
        return '../uploads/documentos/' . $urlDocumento;
    }
    
    // Para cualquier otro caso, devolver tal cual
    return $urlDocumento;
}

// **NUEVA FUNCIÓN** - Verificar si el archivo existe físicamente
function verificarArchivoExiste($urlDocumento) {
    $rutaFisica = $urlDocumento;
    
    // Convertir ruta web a ruta física si es necesario
    if (strpos($rutaFisica, '../') === 0) {
        $rutaFisica = realpath($rutaFisica);
    }
    
    // Si realpath falla, intentar construir la ruta manualmente
    if (!$rutaFisica) {
        $rutaFisica = __DIR__ . '/' . $urlDocumento;
    }
    
    return $rutaFisica && file_exists($rutaFisica);
}

// **PROCESAR CAMBIO DE ESTADO DE DECLARACIÓN**
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cambiar_estado'])) {
    try {
        $declaracion_id = $_POST['declaracion_id'];
        $nuevo_estado = $_POST['nuevo_estado'];
        $observaciones = trim($_POST['observaciones'] ?? '');
        
        $db->beginTransaction();
        
        // VERIFICAR QUE LA DECLARACIÓN EXISTA Y PERTENEZCA AL USUARIO
        $sql_verificar_declaracion = "SELECT d.* FROM declaracion d 
                                     WHERE d.id_declaracion = ? AND d.id_usuario = ?";
        $stmt_verificar = $db->prepare($sql_verificar_declaracion);
        $stmt_verificar->execute([$declaracion_id, $usuario_id]);
        $declaracion_existente = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
        
        if (!$declaracion_existente) {
            throw new Exception("La declaración no existe o no pertenece a este usuario");
        }
        
        // ACTUALIZAR ESTADO
        $sql_actualizar = "UPDATE declaracion SET estado = ? WHERE id_declaracion = ?";
        $stmt_actualizar = $db->prepare($sql_actualizar);
        $resultado = $stmt_actualizar->execute([$nuevo_estado, $declaracion_id]);
        
        if (!$resultado) {
            throw new Exception("Error en la ejecución del UPDATE");
        }
        
        // **CORRECCIÓN: ACTUALIZAR AUTOMÁTICAMENTE TODOS LOS DOCUMENTOS CUANDO SE APRUEBA LA DECLARACIÓN**
        if ($nuevo_estado == 'aprobada') {
            $sql_actualizar_documentos = "UPDATE documentos_soporte SET estado = 'aprobada' WHERE id_declaracion = ?";
            $stmt_actualizar_documentos = $db->prepare($sql_actualizar_documentos);
            $stmt_actualizar_documentos->execute([$declaracion_id]);
        }
        
        // Si hay observaciones, actualizar en documentos_soporte
        if (!empty($observaciones)) {
            $sql_observaciones = "UPDATE documentos_soporte 
                                 SET observaciones = CONCAT(IFNULL(observaciones, ''), '\n[CONTADOR]: ', ?)
                                 WHERE id_declaracion = ?";
            $stmt_observaciones = $db->prepare($sql_observaciones);
            $stmt_observaciones->execute([$observaciones, $declaracion_id]);
        }
        
        $db->commit();
        
        $_SESSION['mensaje'] = "<div class='alert alert-success'>
            <i class='fa-solid fa-circle-check me-2'></i>Estado actualizado correctamente a: <strong>" . htmlspecialchars($nuevo_estado) . "</strong>" . 
            ($nuevo_estado == 'aprobada' ? "<br><small>Todos los documentos han sido marcados como aprobados.</small>" : "") . "
        </div>";
        
        // Redireccionar manteniendo los parámetros
        header("Location: ver-documentos.php?id_usuario=" . $usuario_id . "&declaracion_id=" . $declaracion_id);
        exit();
        
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $mensaje = "<div class='alert alert-danger'>
            <i class='fa-solid fa-circle-xmark me-2'></i>Error al actualizar el estado: " . $e->getMessage() . "
        </div>";
    }
}

// Procesar cierre de declaración
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cerrar_declaracion'])) {
    try {
        $declaracion_id = $_POST['declaracion_id'];
        $observaciones_finales = trim($_POST['observaciones_finales'] ?? '');
        
        if (empty($observaciones_finales)) {
            throw new Exception("Las observaciones finales son obligatorias para cerrar la declaración");
        }
        
        $db->beginTransaction();
        
        // **VERIFICAR QUE LA DECLARACIÓN ESTÉ EN ESTADO "APROBADA"**
        $sql_verificar_declaracion = "SELECT d.* FROM declaracion d 
                                     WHERE d.id_declaracion = ? AND d.estado = 'aprobada'";
        $stmt_verificar = $db->prepare($sql_verificar_declaracion);
        $stmt_verificar->execute([$declaracion_id]);
        $declaracion_existente = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
        
        if (!$declaracion_existente) {
            throw new Exception("No se puede cerrar la declaración. La declaración debe estar en estado 'aprobada'.");
        }
        
        // Cerrar la declaración
        $sql_cerrar = "UPDATE declaracion 
                      SET estado = 'cerrada', 
                          observaciones_finales = ?,
                          fecha_cierre = NOW() 
                      WHERE id_declaracion = ?";
        $stmt_cerrar = $db->prepare($sql_cerrar);
        $resultado = $stmt_cerrar->execute([$observaciones_finales, $declaracion_id]);
        
        if (!$resultado) {
            throw new Exception("Error al cerrar la declaración");
        }
        
        $db->commit();
        
        $_SESSION['mensaje'] = "<div class='alert alert-success'>
            <i class='fa-solid fa-circle-check me-2'></i>Declaración cerrada correctamente. Ya no se pueden realizar más cambios.
        </div>";
        
        header("Location: ver-documentos.php?id_usuario=" . $usuario_id . "&declaracion_id=" . $declaracion_id);
        exit();
        
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $mensaje = "<div class='alert alert-danger'>
            <i class='fa-solid fa-circle-xmark me-2'></i>Error al cerrar declaración: " . $e->getMessage() . "
        </div>";
    }
}

// Obtener mensaje de la sesión
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

// Obtener las declaraciones del usuario
$sql_declaraciones = "SELECT d.id_declaracion, d.anio_fiscal, d.estado, d.fecha_creacion,
                             COUNT(ds.id_documento) as total_documentos
                      FROM declaracion d
                      LEFT JOIN documentos_soporte ds ON d.id_declaracion = ds.id_declaracion
                      WHERE d.id_usuario = ?
                      GROUP BY d.id_declaracion
                      ORDER BY d.anio_fiscal DESC";
$stmt_declaraciones = $db->prepare($sql_declaraciones);
$stmt_declaraciones->execute([$usuario_id]);
$declaraciones = $stmt_declaraciones->fetchAll(PDO::FETCH_ASSOC);

// Obtener documentos de una declaración específica (si se selecciona)
$documentos = [];
$declaracion_actual = null;
$puede_cerrar = false;
$declaracion_cerrada = false;

if (isset($_GET['declaracion_id']) && is_numeric($_GET['declaracion_id'])) {
    $declaracion_id_actual = $_GET['declaracion_id'];
    
    // Verificar que la declaración pertenezca al usuario
    $sql_verificar_declaracion = "SELECT d.* FROM declaracion d 
                                 WHERE d.id_declaracion = ? AND d.id_usuario = ?";
    $stmt_verificar = $db->prepare($sql_verificar_declaracion);
    $stmt_verificar->execute([$declaracion_id_actual, $usuario_id]);
    $declaracion_actual = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
    
    if ($declaracion_actual) {
        // Verificar si la declaración está cerrada
        $declaracion_cerrada = ($declaracion_actual['estado'] == 'cerrada');
        
        // Obtener documentos de esta declaración
        $sql_documentos = "SELECT * FROM documentos_soporte 
                          WHERE id_declaracion = ? 
                          ORDER BY tipo_documento, nombre_original";
        $stmt_documentos = $db->prepare($sql_documentos);
        $stmt_documentos->execute([$declaracion_id_actual]);
        $documentos = $stmt_documentos->fetchAll(PDO::FETCH_ASSOC);
        
        // **MODIFICADO: Solo verificar el estado de la declaración**
        $puede_cerrar = ($declaracion_actual['estado'] == 'aprobada' && !$declaracion_cerrada);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos del Contribuyente - Renta Segura</title>
    <link rel="icon" type="image/png" href="../IMG/chart-line-solid-full.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .header-gestion {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .documento-card {
            border-left: 4px solid #007bff;
            transition: all 0.3s ease;
        }
        .documento-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .estado-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
        }
        .declaracion-item {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .declaracion-item:hover {
            background-color: #f8f9fa;
        }
        .declaracion-activa {
            background-color: #e3f2fd;
            border-left: 4px solid #007bff;
        }
        .file-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        .btn-download {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-download:hover {
            background: #218838;
            color: white;
        }
        .documento-activo {
            border: 2px solid #007bff !important;
            background-color: #f8f9fa;
        }
        .documento-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .documento-item:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
        }
        .btn-ver-documento:hover {
            background-color: #007bff;
            color: white;
        }
        .btn-ver-documento:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        #pdf-frame {
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        #image-preview {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-height: 500px;
            border: 1px solid #dee2e6;
        }
        .visor-contenido {
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .estado-documentos {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .btn-cerrar-declaracion {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-cerrar-declaracion:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        .btn-cerrar-declaracion:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        .modal-observaciones .modal-content {
            border-radius: 15px;
            border: none;
        }
        .modal-observaciones .modal-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 15px 15px 0 0;
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
                        <i class="fas fa-folder-open me-2"></i>Documentos del Contribuyente
                    </h2>
                    <h4 class="mb-0"><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></h4>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($usuario['correo']); ?></p>
                </div>

                <div class="col-md-4 text-end">
                    <div class="btn-group-header">
                        <a href="gestionar-contribuyentes.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Volver a Lista
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <div class="row">
            <!-- Panel lateral con declaraciones -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Declaraciones</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($declaraciones)): ?>
                            <p class="text-muted text-center">No hay declaraciones registradas</p>
                        <?php else: ?>
                            <?php foreach ($declaraciones as $declaracion): ?>
                                <div class="declaracion-item p-3 border-bottom <?php echo ($declaracion_actual && $declaracion_actual['id_declaracion'] == $declaracion['id_declaracion']) ? 'declaracion-activa' : ''; ?>"
                                     onclick="window.location.href='ver-documentos.php?id_usuario=<?php echo $usuario_id; ?>&declaracion_id=<?php echo $declaracion['id_declaracion']; ?>'">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Año <?php echo $declaracion['anio_fiscal']; ?></h6>
                                            <small class="text-muted">
                                                <?php echo $declaracion['total_documentos']; ?> documento(s)
                                            </small>
                                        </div>
                                        <span class="badge estado-badge 
                                            <?php echo $declaracion['estado'] == 'aprobada' ? 'bg-success' : 
                                                   ($declaracion['estado'] == 'rechazada' ? 'bg-danger' : 
                                                   ($declaracion['estado'] == 'en revisión' ? 'bg-warning' : 
                                                   ($declaracion['estado'] == 'cerrada' ? 'bg-dark' : 'bg-secondary'))); ?>">
                                            <?php echo ucfirst($declaracion['estado']); ?>
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        Creada: <?php echo date('d/m/Y', strtotime($declaracion['fecha_creacion'])); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Panel principal con documentos -->
            <div class="col-md-8">
                <?php if ($declaracion_actual): ?>
                    <!-- Información de la declaración actual -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fa-solid fa-file-invoice-dollar me-2"></i>
                                Declaración Año <?php echo $declaracion_actual['anio_fiscal']; ?>
                            </h5>
                            <div>
                                <span class="badge 
                                    <?php echo $declaracion_actual['estado'] == 'aprobada' ? 'bg-success' : 
                                           ($declaracion_actual['estado'] == 'rechazada' ? 'bg-danger' : 
                                           ($declaracion_actual['estado'] == 'en revisión' ? 'bg-warning' : 
                                           ($declaracion_actual['estado'] == 'cerrada' ? 'bg-dark' : 'bg-secondary'))); ?>">
                                    <?php echo ucfirst($declaracion_actual['estado']); ?>
                                </span>
                                
                                <!-- Botón para cerrar declaración -->
                                <?php if (!$declaracion_cerrada): ?>
                                    <button type="button" class="btn btn-cerrar-declaracion ms-2" 
                                            data-bs-toggle="modal" data-bs-target="#modalCerrarDeclaracion"
                                            <?php echo !$puede_cerrar ? 'disabled' : ''; ?>>
                                        <i class="fa-solid fa-lock me-2"></i>Cerrar Declaración
                                    </button>
                                <?php else: ?>
                                    <span class="badge bg-dark ms-2">
                                        <i class="fa-solid fa-lock me-1"></i>Cerrada
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Estado de la declaración 
                            <div class="estado-documentos">
                                <div class="row">
                                    
                                    <div class="col-md-6">
                                        <h6 class="fw-semibold mb-3">
                                            <i class="fa-solid fa-files me-2"></i>
                                            Documentos (<?php echo count($documentos); ?>)
                                        </h6>
                                        <div class="d-flex justify-content-between">
                                            <span>Estado de la declaración:</span>
                                            <strong class="text-<?php echo $declaracion_actual['estado'] == 'aprobada' ? 'success' : 
                                                                   ($declaracion_actual['estado'] == 'cerrada' ? 'dark' : 'warning'); ?>">
                                                <?php echo ucfirst($declaracion_actual['estado']); ?>
                                            </strong>
                                        </div>
                                        <div class="d-flex justify-content-between mt-2">
                                            <span>Total de documentos:</span>
                                            <strong><?php echo count($documentos); ?></strong>
                                        </div>
                                    </div>
                                       
                                    <div class="col-md-6">
                                        <?php if ($declaracion_cerrada): ?>
                                            <div class="alert alert-dark">
                                                <i class="fa-solid fa-lock me-2"></i>
                                                <strong>Declaración Cerrada</strong><br>
                                                Esta declaración ha sido cerrada y no se pueden realizar más cambios.
                                            </div>
                                        <?php elseif ($puede_cerrar): ?>
                                            <div class="alert alert-success">
                                                <i class="fa-solid fa-circle-check me-2"></i>
                                                <strong>¡Listo para cerrar!</strong><br>
                                                La declaración está aprobada. Puedes proceder a cerrarla.
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                <i class="fa-solid fa-clock me-2"></i>
                                                <strong>Declaración pendiente</strong><br>
                                                Cambia el estado a "Aprobada" para poder cerrar la declaración.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            -->

                            <!-- Formulario para cambiar estado (SOLO SI NO ESTÁ CERRADA) -->
                            <?php if (!$declaracion_cerrada): ?>
                                <form action="" method="POST" class="mb-4">
                                    <input type="hidden" name="declaracion_id" value="<?php echo $declaracion_actual['id_declaracion']; ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Cambiar Estado de la Declaración</label>
                                            <select name="nuevo_estado" class="form-select" required>
                                                <option value="">Seleccionar estado...</option>
                                                <option value="pendiente" <?php echo $declaracion_actual['estado'] == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                                <option value="en revisión" <?php echo $declaracion_actual['estado'] == 'en revisión' ? 'selected' : ''; ?>>En Revisión</option>
                                                <option value="aprobada" <?php echo $declaracion_actual['estado'] == 'aprobada' ? 'selected' : ''; ?>>Aprobada</option>
                                                <option value="rechazada" <?php echo $declaracion_actual['estado'] == 'rechazada' ? 'selected' : ''; ?>>Rechazada</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">&nbsp;</label>
                                            <button type="submit" name="cambiar_estado" class="btn btn-primary w-100">
                                                <i class="fa-solid fa-refresh me-2"></i>Actualizar Estado
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <label class="form-label fw-semibold">Observaciones (Opcional)</label>
                                        <textarea name="observaciones" class="form-control" rows="3" 
                                                  placeholder="Agregar observaciones sobre la revisión de la declaración..."></textarea>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fa-solid fa-info-circle me-2"></i>
                                    <strong>Declaración cerrada:</strong> No se pueden realizar cambios en una declaración cerrada.
                                </div>
                            <?php endif; ?>

                            <!-- Lista de documentos -->
                            <?php if (empty($documentos)): ?>
                                <div class="alert alert-info">
                                    <i class="fa-solid fa-info-circle me-2"></i>
                                    No hay documentos para esta declaración.
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <!-- Lista de documentos -->
                                        <?php foreach ($documentos as $index => $documento): 
                                            $urlDocumentoCorregida = obtenerUrlDocumento($documento['urldocumento']);
                                            $archivoExiste = verificarArchivoExiste($urlDocumentoCorregida);
                                        ?>
                                            <div class="card documento-card mb-3 documento-item <?php echo $index === 0 ? 'documento-activo' : ''; ?>" 
                                                 data-documento-url="<?php echo htmlspecialchars($urlDocumentoCorregida); ?>"
                                                 data-documento-type="<?php echo obtenerTipoDocumento($documento['nombre_original']); ?>"
                                                 data-documento-name="<?php echo htmlspecialchars($documento['nombre_original']); ?>"
                                                 data-documento-exists="<?php echo $archivoExiste ? 'true' : 'false'; ?>">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <?php
                                                        $extension = strtolower(pathinfo($documento['nombre_original'], PATHINFO_EXTENSION));
                                                        if ($extension == 'pdf') {
                                                            echo '<i class="file-icon fa-solid fa-file-pdf text-danger"></i>';
                                                        } elseif (in_array($extension, ['doc', 'docx'])) {
                                                            echo '<i class="file-icon fa-solid fa-file-word text-primary"></i>';
                                                        } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                                            echo '<i class="file-icon fa-solid fa-file-excel text-success"></i>';
                                                        } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                            echo '<i class="file-icon fa-solid fa-file-image text-warning"></i>';
                                                        } else {
                                                            echo '<i class="file-icon fa-solid fa-file text-secondary"></i>';
                                                        }
                                                        ?>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($documento['nombre_original']); ?></h6>
                                                            <small class="text-muted">
                                                                Tipo: <?php echo htmlspecialchars($documento['tipo_documento']); ?> | 
                                                                Tamaño: <?php echo round($documento['tamaño_archivo'] / 1024 / 1024, 2); ?> MB
                                                            </small>
                                                            <?php if (!$archivoExiste): ?>
                                                                <br><small class="text-danger"><i class="fa-solid fa-triangle-exclamation"></i> Archivo no encontrado en el servidor</small>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="btn-group">
                                                            <button class="btn btn-sm btn-outline-primary btn-ver-documento" 
                                                                    title="Ver documento" <?php echo !$archivoExiste ? 'disabled' : ''; ?>>
                                                                <i class="fa-solid fa-eye"></i>
                                                            </button>
                                                            <a href="<?php echo htmlspecialchars($urlDocumentoCorregida); ?>" 
                                                               class="btn btn-sm btn-outline-success <?php echo !$archivoExiste ? 'disabled' : ''; ?>" 
                                                               target="_blank"
                                                               download="<?php echo htmlspecialchars($documento['nombre_original']); ?>"
                                                               title="Descargar" <?php echo !$archivoExiste ? 'onclick="return false;"' : ''; ?>>
                                                                <i class="fa-solid fa-download"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($documento['observaciones'])): ?>
                                                        <div class="mt-2 p-2 bg-light rounded">
                                                            <small class="text-muted">
                                                                <strong>Observaciones:</strong> <?php echo nl2br(htmlspecialchars($documento['observaciones'])); ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <!-- Visor de documentos -->
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="fa-solid fa-eye me-2"></i>Vista Previa</h6>
                                            </div>
                                            <div class="card-body text-center" id="visor-documentos">
                                                <div id="contenido-vacio" class="py-5 visor-contenido">
                                                    <div>
                                                        <i class="fa-solid fa-file fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">Selecciona un documento para verlo</p>
                                                    </div>
                                                </div>
                                                <div id="contenido-documento" style="display: none;">
                                                    <!-- PDF Viewer -->
                                                    <div id="pdf-viewer" style="display: none;">
                                                        <iframe id="pdf-frame" src="" width="100%" height="500" style="border: none;"></iframe>
                                                    </div>
                                                    
                                                    <!-- Image Viewer -->
                                                    <div id="image-viewer" style="display: none;">
                                                        <img id="image-preview" src="" alt="Vista previa" class="img-fluid" style="max-height: 500px;">
                                                    </div>
                                                    
                                                    <!-- Archivo no encontrado -->
                                                    <div id="file-not-found" style="display: none;" class="visor-contenido">
                                                        <div>
                                                            <i class="fa-solid fa-file-exclamation fa-3x text-danger mb-3"></i>
                                                            <h6 class="text-danger">Archivo no encontrado</h6>
                                                            <p class="text-muted">El archivo físico no existe en el servidor.</p>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Documentos no visualizables -->
                                                    <div id="unsupported-viewer" style="display: none;" class="visor-contenido">
                                                        <div>
                                                            <i class="fa-solid fa-file fa-3x text-warning mb-3"></i>
                                                            <h6 class="text-warning">Vista previa no disponible</h6>
                                                            <p class="text-muted">Este tipo de archivo no se puede visualizar en el navegador.</p>
                                                            <a href="#" id="download-link" class="btn btn-primary">
                                                                <i class="fa-solid fa-download me-2"></i>Descargar Archivo
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Mensaje cuando no hay declaración seleccionada -->
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Selecciona una declaración para ver los documentos</h5>
                            <p class="text-muted">Haz clic en una declaración del panel lateral para visualizar sus documentos.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para cerrar declaración -->
    <?php if ($declaracion_actual && !$declaracion_cerrada): ?>
    <div class="modal fade modal-observaciones" id="modalCerrarDeclaracion" tabindex="-1" aria-labelledby="modalCerrarDeclaracionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCerrarDeclaracionLabel">
                        <i class="fa-solid fa-lock me-2"></i>Cerrar Declaración - Año <?php echo $declaracion_actual['anio_fiscal']; ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST" id="formCerrarDeclaracion">
                        <input type="hidden" name="declaracion_id" value="<?php echo $declaracion_actual['id_declaracion']; ?>">
                        
                        <div class="alert alert-success">
                            <i class="fa-solid fa-circle-check me-2"></i>
                            <strong>¡Listo para cerrar!</strong> La declaración está aprobada y lista para ser cerrada.
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Observaciones y Recomendaciones Finales <span class="text-danger">*</span></label>
                            <textarea name="observaciones_finales" class="form-control" rows="6" 
                                    placeholder="Describe detalladamente:
                                        • Resumen de la declaración presentada
                                        • Recomendaciones específicas para el contribuyente
                                        • Observaciones importantes sobre los documentos
                                        • Próximos pasos a seguir
                                        • Plazos importantes
                                        • Cualquier información relevante para el contribuyente..." 
                                    required></textarea>
                            <div class="form-text">
                                Estas observaciones quedarán registradas permanentemente en el historial de la declaración 
                                y serán visibles para el contribuyente. Una vez cerrada, no se podrán realizar más cambios.
                            </div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fa-solid fa-exclamation-triangle me-2"></i>
                            <strong>Esta acción no se puede deshacer.</strong> Una vez cerrada la declaración, 
                            no podrás realizar más cambios en ella ni en sus documentos.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" form="formCerrarDeclaracion" name="cerrar_declaracion" class="btn btn-success">
                        <i class="fa-solid fa-lock me-2"></i>Sí, Cerrar Declaración
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const documentoItems = document.querySelectorAll('.documento-item');
            const visorDocumentos = document.getElementById('visor-documentos');
            const contenidoVacio = document.getElementById('contenido-vacio');
            const contenidoDocumento = document.getElementById('contenido-documento');
            const pdfViewer = document.getElementById('pdf-viewer');
            const pdfFrame = document.getElementById('pdf-frame');
            const imageViewer = document.getElementById('image-viewer');
            const imagePreview = document.getElementById('image-preview');
            const unsupportedViewer = document.getElementById('unsupported-viewer');
            const fileNotFound = document.getElementById('file-not-found');
            const downloadLink = document.getElementById('download-link');

            // Seleccionar el primer documento por defecto (si existe y está disponible)
            if (documentoItems.length > 0) {
                const primerDocumento = documentoItems[0];
                const archivoExiste = primerDocumento.getAttribute('data-documento-exists') === 'true';
                if (archivoExiste) {
                    mostrarDocumento(primerDocumento);
                }
            }

            // Event listeners para los documentos
            documentoItems.forEach(item => {
                const btnVer = item.querySelector('.btn-ver-documento');
                const archivoExiste = item.getAttribute('data-documento-exists') === 'true';
                
                if (archivoExiste && !btnVer.disabled) {
                    btnVer.addEventListener('click', function() {
                        mostrarDocumento(item);
                    });

                    item.addEventListener('click', function(e) {
                        if (!e.target.closest('.btn-group')) {
                            mostrarDocumento(item);
                        }
                    });
                }
            });

            function mostrarDocumento(documentoItem) {
                // Remover clase activa de todos los documentos
                documentoItems.forEach(item => {
                    item.classList.remove('documento-activo', 'border-primary');
                    item.classList.add('border-light');
                });

                // Agregar clase activa al documento seleccionado
                documentoItem.classList.add('documento-activo', 'border-primary');
                documentoItem.classList.remove('border-light');

                const documentoUrl = documentoItem.getAttribute('data-documento-url');
                const documentType = documentoItem.getAttribute('data-documento-type');
                const documentName = documentoItem.getAttribute('data-documento-name');
                const archivoExiste = documentoItem.getAttribute('data-documento-exists') === 'true';

                // Ocultar contenido vacío y mostrar visor
                contenidoVacio.style.display = 'none';
                contenidoDocumento.style.display = 'block';

                // Ocultar todos los viewers primero
                pdfViewer.style.display = 'none';
                imageViewer.style.display = 'none';
                unsupportedViewer.style.display = 'none';
                fileNotFound.style.display = 'none';

                // Configurar enlaces de descarga
                downloadLink.href = documentoUrl;
                downloadLink.download = documentName;

                // Verificar si el archivo existe
                if (!archivoExiste) {
                    fileNotFound.style.display = 'block';
                    return;
                }

                // Mostrar el viewer correspondiente
                switch(documentType) {
                    case 'pdf':
                        pdfFrame.src = documentoUrl + '#view=FitH';
                        pdfViewer.style.display = 'block';
                        break;
                    case 'image':
                        imagePreview.src = documentoUrl;
                        imagePreview.onload = function() {
                            imageViewer.style.display = 'block';
                        };
                        imagePreview.onerror = function() {
                            // Si falla la carga de imagen, mostrar error
                            fileNotFound.style.display = 'block';
                        };
                        break;
                    case 'office':
                    case 'other':
                        unsupportedViewer.style.display = 'block';
                        break;
                }
            }

            // Efecto visual para documentos activos
            documentoItems.forEach(item => {
                item.style.cursor = 'pointer';
                item.style.transition = 'all 0.3s ease';
            });

            // Confirmación para cerrar declaración
            const formCerrar = document.getElementById('formCerrarDeclaracion');
            if (formCerrar) {
                formCerrar.addEventListener('submit', function(e) {
                    if (!confirm('¿Estás seguro de que deseas cerrar esta declaración? Esta acción no se puede deshacer y bloqueará todos los cambios.')) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
</body>
</html>