<?php
// mis-declaraciones.php
include_once '../config/database.php';
include_once '../includes/session.php';

redirectIfNotLoggedIn();

// Solo contribuyentes pueden acceder a esta página
if (!isContribuyente()) {
    header("Location: principal.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$usuario_id = $_SESSION['user_id'];
$mensaje = '';

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

// Función para obtener la URL del documento
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

// Verificar si el archivo existe físicamente
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

// Obtener mensaje de la sesión
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

// Obtener información del usuario
$sql_usuario = "SELECT nombre, apellido, correo FROM usuario WHERE id_usuario = ?";
$stmt_usuario = $db->prepare($sql_usuario);
$stmt_usuario->execute([$usuario_id]);
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

// Obtener las declaraciones del usuario
$sql_declaraciones = "SELECT d.id_declaracion, d.anio_fiscal, d.estado, d.fecha_creacion, d.observaciones_finales,
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

if (isset($_GET['declaracion_id']) && is_numeric($_GET['declaracion_id'])) {
    $declaracion_id_actual = $_GET['declaracion_id'];
    
    // Verificar que la declaración pertenezca al usuario
    $sql_verificar_declaracion = "SELECT d.* FROM declaracion d 
                                 WHERE d.id_declaracion = ? AND d.id_usuario = ?";
    $stmt_verificar = $db->prepare($sql_verificar_declaracion);
    $stmt_verificar->execute([$declaracion_id_actual, $usuario_id]);
    $declaracion_actual = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
    
    if ($declaracion_actual) {
        // Obtener documentos de esta declaración
        $sql_documentos = "SELECT * FROM documentos_soporte 
                          WHERE id_declaracion = ? 
                          ORDER BY tipo_documento, nombre_original";
        $stmt_documentos = $db->prepare($sql_documentos);
        $stmt_documentos->execute([$declaracion_id_actual]);
        $documentos = $stmt_documentos->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Declaraciones - Renta Segura</title>
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
        .info-solo-lectura {
            background: #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .btn-descargar {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-descargar:hover {
            background: #218838;
            color: white;
        }
        .btn-descargar:disabled {
            background: #6c757d;
            cursor: not-allowed;
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
                        <i class="fas fa-folder-open me-2"></i>Mis Declaraciones
                    </h2>
                    <h4 class="mb-0"><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></h4>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($usuario['correo']); ?></p>
                </div>

                <div class="col-md-4 text-end">
                    <div class="btn-group-header">
                        <a href="perfil-usuario.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Perfil
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
                        <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Mis Declaraciones</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($declaraciones)): ?>
                            <p class="text-muted text-center">No hay declaraciones registradas</p>
                        <?php else: ?>
                            <?php foreach ($declaraciones as $declaracion): ?>
                                <div class="declaracion-item p-3 border-bottom <?php echo ($declaracion_actual && $declaracion_actual['id_declaracion'] == $declaracion['id_declaracion']) ? 'declaracion-activa' : ''; ?>"
                                     onclick="window.location.href='mis-declaraciones.php?declaracion_id=<?php echo $declaracion['id_declaracion']; ?>'">
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
                                        <?php if ($declaracion_actual['estado'] == 'cerrada'): ?>
                                            <div class="alert alert-dark">
                                                <i class="fa-solid fa-lock me-2"></i>
                                                <strong>Declaración Cerrada</strong><br>
                                                Esta declaración ha sido cerrada por tu contador.
                                            </div>
                                        <?php elseif ($declaracion_actual['estado'] == 'aprobada'): ?>
                                            <div class="alert alert-success">
                                                <i class="fa-solid fa-circle-check me-2"></i>
                                                <strong>Declaración Aprobada</strong><br>
                                                Tu declaración ha sido revisada y aprobada.
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                <i class="fa-solid fa-clock me-2"></i>
                                                <strong>Declaración en Proceso</strong><br>
                                                Tu declaración está siendo revisada por tu contador.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                                        -->

                            <!-- Observaciones finales (si la declaración está cerrada) -->
                            <?php if ($declaracion_actual['estado'] == 'cerrada' && !empty($declaracion_actual['observaciones_finales'])): ?>
                                <div class="alert alert-info">
                                    <h6><i class="fa-solid fa-comment me-2"></i>Observaciones Finales del Contador</h6>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($declaracion_actual['observaciones_finales'])); ?></p>
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
                                                                <br><small class="text-danger"><i class="fa-solid fa-triangle-exclamation"></i> Archivo no disponible</small>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="btn-group">
                                                            <button class="btn btn-sm btn-outline-primary btn-ver-documento" 
                                                                    title="Ver documento" <?php echo !$archivoExiste ? 'disabled' : ''; ?>>
                                                                <i class="fa-solid fa-eye"></i>
                                                            </button>
                                                            <a href="<?php echo htmlspecialchars($urlDocumentoCorregida); ?>" 
                                                               class="btn btn-sm btn-outline-success btn-descargar <?php echo !$archivoExiste ? 'disabled' : ''; ?>" 
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
                                                                <strong>Observaciones del contador:</strong> <?php echo nl2br(htmlspecialchars($documento['observaciones'])); ?>
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
                                                            <h6 class="text-danger">Archivo no disponible</h6>
                                                            <p class="text-muted">El archivo no está disponible para visualización.</p>
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
        });
    </script>
</body>
</html>