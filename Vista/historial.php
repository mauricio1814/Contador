<?php
include_once '../config/database.php';
include_once '../includes/session.php';

redirectIfNotLoggedIn();

$database = new Database();
$db = $database->getConnection();
$usuario_id = $_SESSION['user_id'];

// Obtener declaraciones cerradas del usuario
$sql_declaraciones = "SELECT d.id_declaracion, d.anio_fiscal, d.estado, d.fecha_creacion, 
                             d.fecha_cierre, d.observaciones_finales,
                             COUNT(ds.id_documento) as total_documentos
                      FROM declaracion d
                      LEFT JOIN documentos_soporte ds ON d.id_declaracion = ds.id_declaracion
                      WHERE d.id_usuario = ? AND d.estado = 'cerrada'
                      GROUP BY d.id_declaracion
                      ORDER BY d.anio_fiscal DESC";
$stmt_declaraciones = $db->prepare($sql_declaraciones);
$stmt_declaraciones->execute([$usuario_id]);
$declaraciones = $stmt_declaraciones->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial - Renta Segura</title>
    <link rel="icon" type="image/png" href="../IMG/chart-line-solid-full.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        /* Mantener los estilos existentes y agregar: */
        .observaciones-box {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .declaracion-detalle {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .declaracion-detalle:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>

    <main class="bg-light min-vh-100">
        <div class="container">
            <h1 class="page-title"><i class="fa-solid fa-clock-rotate-left me-2"></i>Historial de Declaraciones</h1>

            <?php if (empty($declaraciones)): ?>
                <div class="card text-center py-5">
                    <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay declaraciones en el historial</h4>
                    <p class="text-muted">Las declaraciones aparecerán aquí una vez que sean cerradas por tu contador.</p>
                </div>
            <?php else: ?>
                <div class="card border-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Año Fiscal</th>
                                    <th>Estado</th>
                                    <th>Documentos</th>
                                    <th>Fecha Cierre</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($declaraciones as $declaracion): ?>
                                <tr class="declaracion-detalle" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#detalle-<?php echo $declaracion['id_declaracion']; ?>">
                                    <td class="fw-semibold"><?php echo $declaracion['anio_fiscal']; ?></td>
                                    <td>
                                        <span class="badge bg-success">Cerrada</span>
                                    </td>
                                    <td><?php echo $declaracion['total_documentos']; ?> documento(s)</td>
                                    <td><?php echo date('d/m/Y', strtotime($declaracion['fecha_cierre'])); ?></td>
                                    <td>
                                        <button class="btn btn-outline-primary btn-sm">
                                            <i class="fa-solid fa-eye me-1"></i>Ver Detalles
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="p-0 border-0">
                                        <div class="collapse" id="detalle-<?php echo $declaracion['id_declaracion']; ?>">
                                            <div class="p-3 bg-light">
                                                <!-- Observaciones del contador -->
                                                <?php if (!empty($declaracion['observaciones_finales'])): ?>
                                                <div class="mb-3">
                                                    <h6><i class="fa-solid fa-comment me-2 text-primary"></i>Observaciones del Contador</h6>
                                                    <div class="observaciones-box">
                                                        <?php echo nl2br(htmlspecialchars($declaracion['observaciones_finales'])); ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <!-- Lista de documentos -->
                                                <div>
                                                    <h6><i class="fa-solid fa-files me-2 text-primary"></i>Documentos Incluidos</h6>
                                                    <?php
                                                    $sql_docs = "SELECT * FROM documentos_soporte 
                                                                WHERE id_declaracion = ? 
                                                                ORDER BY tipo_documento";
                                                    $stmt_docs = $db->prepare($sql_docs);
                                                    $stmt_docs->execute([$declaracion['id_declaracion']]);
                                                    $documentos = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);
                                                    ?>
                                                    
                                                    <?php if (!empty($documentos)): ?>
                                                        <div class="row">
                                                            <?php foreach ($documentos as $doc): ?>
                                                            <div class="col-md-6 mb-2">
                                                                <div class="d-flex align-items-center p-2 bg-white rounded">
                                                                    <i class="fa-solid fa-file-pdf text-danger me-2"></i>
                                                                    <div class="flex-grow-1">
                                                                        <small class="fw-semibold"><?php echo htmlspecialchars($doc['nombre_original']); ?></small><br>
                                                                        <small class="text-muted"><?php echo htmlspecialchars($doc['tipo_documento']); ?></small>
                                                                    </div>
                                                                    <a href="<?php echo htmlspecialchars($doc['urldocumento']); ?>" 
                                                                       class="btn btn-sm btn-outline-success" 
                                                                       target="_blank"
                                                                       download>
                                                                        <i class="fa-solid fa-download"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Efecto visual para las filas desplegables
        document.querySelectorAll('.declaracion-detalle').forEach(row => {
            row.style.cursor = 'pointer';
        });
    </script>
</body>
</html>