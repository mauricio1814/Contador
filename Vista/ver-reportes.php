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

// Procesar respuesta del admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['responder'])) {
    $id_soporte = $_POST['id_soporte'];
    $respuesta = $_POST['respuesta'];
    $id_admin = $_SESSION['user_id'];
    
    $query = "UPDATE soporte SET respuesta_admin = ?, id_admin_responde = ?, fecha_respuesta = NOW(), estado = 'resuelto' WHERE id_soporte = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$respuesta, $id_admin, $id_soporte]);
    
    header("Location: ver-reportes.php?success=1");
    exit();
}

// Obtener todos los reportes de soporte
$query = "SELECT s.*, u.nombre, u.apellido, u.rol, u.correo as correo_usuario 
          FROM soporte s 
          JOIN usuario u ON s.id_usuario = u.id_usuario 
          ORDER BY s.fecha_creacion DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Soporte - Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .admin-header {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .btn-admin {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-admin:hover {
            background: #0056d2;
            transform: scale(1.05);
            color: white;
        }

        .btn-volver {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-volver:hover {
            background: #5a6268;
            transform: scale(1.05);
            color: white;
        }

        .reporte-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            border-left: 4px solid #0d6efd;
        }
        .reporte-header {
            padding: 15px 20px;
            cursor: pointer;
            border-bottom: 1px solid #e9ecef;
        }
        .reporte-content {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 0 0 10px 10px;
        }
        .badge-estado {
            font-size: 0.8rem;
        }
        .respuesta-admin {
            background: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin-top: 15px;
            border-radius: 5px;
        }
        .form-respuesta {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-header .d-flex {
                flex-direction: column;
                gap: 10px;
            }

            .admin-header .ms-auto {
                margin-left: 0 !important;
            }
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="container mt-4">
        <!-- Header del Administrador -->
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">
                    <i class="fas fa-headset text-primary"></i> Gestión de Reportes de Soporte
                </h3>
                <div class="d-flex gap-2 ms-auto">
                    
                    <a href="perfil-admin.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Volver al Perfil
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">


                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> Respuesta enviada correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($reportes)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No hay reportes de soporte</h4>
                        <p class="text-muted">Los usuarios aún no han enviado reportes.</p>
                    </div>
                <?php else: ?>
                    <div class="accordion" id="accordionReportes">
                        <?php foreach ($reportes as $index => $reporte): ?>
                            <div class="reporte-card">
                                <div class="reporte-header" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h5 class="mb-1"><?php echo htmlspecialchars($reporte['nombre_completo']); ?></h5>
                                            <p class="mb-0 text-muted small">
                                                ID: <?php echo $reporte['id_usuario']; ?> | 
                                                Rol: <?php echo ucfirst($reporte['rol']); ?> |
                                                <?php echo date('d/m/Y H:i', strtotime($reporte['fecha_creacion'])); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <span class="badge bg-<?php 
                                                switch($reporte['severidad']) {
                                                    case 'critica': echo 'danger'; break;
                                                    case 'alta': echo 'warning'; break;
                                                    case 'media': echo 'info'; break;
                                                    default: echo 'secondary';
                                                }
                                            ?> badge-estado">
                                                <?php echo ucfirst($reporte['severidad']); ?>
                                            </span>
                                            <span class="badge bg-<?php 
                                                switch($reporte['estado']) {
                                                    case 'pendiente': echo 'warning'; break;
                                                    case 'en_proceso': echo 'info'; break;
                                                    case 'resuelto': echo 'success'; break;
                                                    default: echo 'secondary';
                                                }
                                            ?> badge-estado">
                                                <?php echo ucfirst(str_replace('_', ' ', $reporte['estado'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="collapse<?php echo $index; ?>" class="collapse" data-bs-parent="#accordionReportes">
                                    <div class="reporte-content">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-envelope me-2"></i>Correo:</strong>
                                                <p class="mb-2"><?php echo htmlspecialchars($reporte['correo']); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-tag me-2"></i>Categoría:</strong>
                                                <p class="mb-2"><?php echo htmlspecialchars($reporte['categoria']); ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <strong><i class="fas fa-file-alt me-2"></i>Descripción:</strong>
                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($reporte['descripcion'])); ?></p>
                                        </div>
                                        
                                        <?php if (!empty($reporte['pasos_reproducir'])): ?>
                                        <div class="mb-3">
                                            <strong><i class="fas fa-list-ol me-2"></i>Pasos para reproducir:</strong>
                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($reporte['pasos_reproducir'])); ?></p>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-phone me-2"></i>Contacto:</strong>
                                                <p class="mb-2">
                                                    <?php echo $reporte['desea_contacto'] ? 
                                                        '<span class="badge bg-success">Sí desea contacto</span>' : 
                                                        '<span class="badge bg-secondary">No desea contacto</span>'; ?>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-calendar me-2"></i>Fecha de creación:</strong>
                                                <p class="mb-2"><?php echo date('d/m/Y H:i', strtotime($reporte['fecha_creacion'])); ?></p>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($reporte['respuesta_admin'])): ?>
                                        <div class="respuesta-admin">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <strong><i class="fas fa-reply me-2"></i>Respuesta del Administrador:</strong>
                                                <span class="badge bg-success">Resuelto</span>
                                            </div>
                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($reporte['respuesta_admin'])); ?></p>
                                            <small class="text-muted">
                                                Respondido el: <?php echo date('d/m/Y H:i', strtotime($reporte['fecha_respuesta'])); ?>
                                            </small>
                                        </div>
                                        <?php else: ?>
                                        <div class="form-respuesta">
                                            <form method="POST" action="">
                                                <input type="hidden" name="id_soporte" value="<?php echo $reporte['id_soporte']; ?>">
                                                <div class="mb-3">
                                                    <label for="respuesta<?php echo $index; ?>" class="form-label">
                                                        <strong><i class="fas fa-edit me-2"></i>Responder a este reporte:</strong>
                                                    </label>
                                                    <textarea class="form-control" id="respuesta<?php echo $index; ?>" 
                                                              name="respuesta" rows="4" 
                                                              placeholder="Escribe tu respuesta aquí para el usuario..." required></textarea>
                                                    <div class="form-text">
                                                        Al enviar la respuesta, el reporte se marcará como "Resuelto".
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="submit" name="responder" class="btn btn-success">
                                                        <i class="fas fa-paper-plane me-2"></i>Enviar Respuesta
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary" onclick="this.form.reset()">
                                                        <i class="fas fa-undo me-2"></i>Limpiar
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>