<?php
include_once '../config/database.php';
include_once '../includes/session.php';

redirectIfNotLoggedIn();

$database = new Database();
$db = $database->getConnection();

// Obtener información del usuario actual
$usuario_id = $_SESSION['user_id'];
$rol_usuario = $_SESSION['user_rol'];

// Determinar a qué perfil debe volver según el rol
$perfil_destino = 'perfil-usuario.php'; // Por defecto para contribuyentes

if ($rol_usuario === 'contador') {
    $perfil_destino = 'perfil-contador.php';
} elseif ($rol_usuario === 'admin') {
    $perfil_destino = 'perfil-admin.php';
}

// Obtener los reportes del usuario actual
$query = "SELECT s.*, 
                 u_responde.nombre as admin_nombre, 
                 u_responde.apellido as admin_apellido
          FROM soporte s 
          LEFT JOIN usuario u_responde ON s.id_admin_responde = u_responde.id_usuario
          WHERE s.id_usuario = ? 
          ORDER BY s.fecha_creacion DESC";
$stmt = $db->prepare($query);
$stmt->execute([$usuario_id]);
$reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reportes - Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
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
        .header-usuario {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .btn-usuario {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-usuario:hover {
            background: #218838;
            transform: scale(1.05);
            color: white;
        }
        .btn-contador {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-contador:hover {
            background: #2980b9;
            transform: scale(1.05);
            color: white;
        }
        .btn-volver {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
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
        
        /* Estilos específicos según el rol */
        .header-contador {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            border-left: 4px solid #3498db;
        }
        
        .header-admin {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            border-left: 4px solid #0d6efd;
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="container mt-4">
        <!-- Header dinámico según el rol -->
        <div class="header-<?php echo $rol_usuario === 'contador' ? 'contador' : ($rol_usuario === 'admin' ? 'admin' : 'usuario'); ?>">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="fas fa-headset me-2"></i>Mis Reportes de Soporte</h2>
                    <p class="text-muted mb-0">
                        <?php 
                        if ($rol_usuario === 'contador') {
                            echo 'Historial de todos los reportes que has enviado como contador';
                        } elseif ($rol_usuario === 'admin') {
                            echo 'Historial de todos los reportes que has enviado como administrador';
                        } else {
                            echo 'Historial de todos los reportes que has enviado como contribuyente';
                        }
                        ?>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="soporte.php" class="btn btn-<?php echo $rol_usuario === 'contador' ? 'contador' : 'usuario'; ?>">
                        <i class="fas fa-plus"></i> Nuevo Reporte
                    </a>
                    <a href="<?php echo $perfil_destino; ?>" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Volver al Perfil
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?php if (empty($reportes)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No has enviado reportes aún</h4>
                        <p class="text-muted">Cuando envíes un reporte de soporte, aparecerá aquí.</p>
                        <a href="soporte.php" class="btn btn-<?php echo $rol_usuario === 'contador' ? 'contador' : 'usuario'; ?> mt-3">
                            <i class="fas fa-plus me-2"></i>Crear mi primer reporte
                        </a>
                    </div>
                <?php else: ?>

                    
                    <div class="accordion" id="accordionReportes">
                        <?php foreach ($reportes as $index => $reporte): ?>
                            <div class="reporte-card">
                                <div class="reporte-header" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h5 class="mb-1">Reporte #<?php echo $reporte['id_soporte']; ?></h5>
                                            <p class="mb-0 text-muted small">
                                                Categoría: <?php echo htmlspecialchars($reporte['categoria']); ?> |
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
                                                <strong><i class="fas fa-tag me-2"></i>Categoría:</strong>
                                                <p class="mb-2"><?php echo htmlspecialchars($reporte['categoria']); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-exclamation-triangle me-2"></i>Severidad:</strong>
                                                <p class="mb-2">
                                                    <span class="badge bg-<?php 
                                                        switch($reporte['severidad']) {
                                                            case 'critica': echo 'danger'; break;
                                                            case 'alta': echo 'warning'; break;
                                                            case 'media': echo 'info'; break;
                                                            default: echo 'secondary';
                                                        }
                                                    ?>">
                                                        <?php echo ucfirst($reporte['severidad']); ?>
                                                    </span>
                                                </p>
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
                                                <strong><i class="fas fa-calendar me-2"></i>Fecha de envío:</strong>
                                                <p class="mb-2"><?php echo date('d/m/Y H:i', strtotime($reporte['fecha_creacion'])); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-comments me-2"></i>Contacto:</strong>
                                                <p class="mb-2">
                                                    <?php echo $reporte['desea_contacto'] ? 
                                                        '<span class="badge bg-success">Sí deseo contacto</span>' : 
                                                        '<span class="badge bg-secondary">No deseo contacto</span>'; ?>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($reporte['respuesta_admin'])): ?>
                                        <div class="respuesta-admin">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <strong><i class="fas fa-reply me-2"></i>Respuesta del Administrador:</strong>
                                                <span class="badge bg-success">Resuelto</span>
                                            </div>
                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($reporte['respuesta_admin'])); ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    Respondido el: <?php echo date('d/m/Y H:i', strtotime($reporte['fecha_respuesta'])); ?>
                                                </small>
                                                <?php if (!empty($reporte['admin_nombre'])): ?>
                                                <small class="text-muted">
                                                    Por: <?php echo htmlspecialchars($reporte['admin_nombre'] . ' ' . $reporte['admin_apellido']); ?>
                                                </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-clock me-2"></i>
                                            <strong>Estado: <?php echo ucfirst(str_replace('_', ' ', $reporte['estado'])); ?></strong>
                                            <p class="mb-0 mt-1">Tu reporte está siendo revisado por nuestro equipo de soporte. Te notificaremos cuando tengamos una respuesta.</p>
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