<?php
include_once '../config/database.php';
include_once '../includes/session.php';

redirectIfNotLoggedIn();
if (!isContribuyente()) {
    header("Location: /Vista/principal.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener información del usuario actual
$usuario_id = $_SESSION['user_id'];
$query_usuario = "SELECT * FROM usuario WHERE id_usuario = ? AND rol = 'usuario'";
$stmt_usuario = $db->prepare($query_usuario);
$stmt_usuario->execute([$usuario_id]);
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

// Si no encontramos al usuario, usar datos de sesión
if (!$usuario) {
    $usuario = [
        'nombre' => $_SESSION['user_nombre'],
        'apellido' => $_SESSION['user_apellido'],
        'correo' => $_SESSION['user_email'] ?? 'N/A',
        'tipo_documento' => $_SESSION['tipo_documento'] ?? 'N/A',
        'numero_documento' => $_SESSION['numero_documento'] ?? 'N/A',
        'telefono' => $_SESSION['telefono'] ?? 'No registrado'
    ];
}

// Obtener información del contador asignado
$query_contador = "SELECT u.* FROM usuario u 
                   WHERE u.id_usuario = (SELECT contador_asignado FROM usuario WHERE id_usuario = ?) 
                   AND u.rol = 'contador' AND u.activo = 1";
$stmt_contador = $db->prepare($query_contador);
$stmt_contador->execute([$usuario_id]);
$contador = $stmt_contador->fetch(PDO::FETCH_ASSOC);

// Si no hay contador asignado, usar datos por defecto
if (!$contador) {
    $contador = [
        'nombre' => 'No asignado',
        'apellido' => '',
        'correo' => 'N/A',
        'tipo_documento' => 'N/A',
        'numero_documento' => 'N/A',
        'telefono' => 'N/A'
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .usuario-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 25px;
            transition: transform 0.3s ease;
        }
        .usuario-card:hover {
            transform: translateY(-5px);
        }
        .stats-card {
            text-align: center;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0;
        }
        .stats-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        .btn-usuario {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-usuario:hover {
            background: #218838;
            transform: scale(1.05);
        }
        .header-usuario {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-logout {
            border: 2px solid #dc3545;
            color: #dc3545;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-logout:hover {
            background: #dc3545;
            color: white;
            transform: scale(1.05);
        }
        .contador-card {
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="container mt-4">
        <!-- Header del Perfil -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="header-usuario">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h2 class="mb-1">Bienvenido, <?php echo $usuario['nombre'] . ' ' . $usuario['apellido']; ?></h2>
                            <p class="text-muted mb-0">Contribuyente</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="d-flex justify-content-end align-items-center gap-3">
                                <a href="mis-declaraciones.php" class="btn btn-usuario">
                                    <i class="fas fa-file-invoice-dollar me-2"></i>Mis Declaraciones
                                </a>
                                
                                <a href="../logout.php" class="btn btn-outline-danger" 
                                   onclick="return confirm('¿Estás seguro de que deseas cerrar sesión?')">
                                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number">3</div>
                    <div class="stats-label">Declaraciones Realizadas</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number">1</div>
                    <div class="stats-label">Pendientes</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number">2</div>
                    <div class="stats-label">Aprobadas</div>
                </div>
            </div>
        </div>
        

        <div class="row">
            <!-- Información Personal -->
            <div class="col-md-6">
                <div class="usuario-card">
                    <h4 class="mb-3"><i class="fas fa-user-circle me-2"></i>Información Personal</h4>
                    
                    <div class="mb-3">
                        <strong><i class="fa fa-user me-2 text-primary"></i>Nombre:</strong><br>
                        <?php echo $usuario['nombre'] ?? 'N/A'; ?> <?php echo $usuario['apellido'] ?? 'N/A'; ?>
                    </div>
                    
                    <div class="mb-3">
                        <strong><i class="fas fa-id-card me-2 text-primary"></i>Documento:</strong><br>
                        <?php echo $usuario['tipo_documento'] ?? 'N/A'; ?>: <?php echo $usuario['numero_documento'] ?? 'N/A'; ?>
                    </div>
                    
                    <div class="mb-3">
                        <strong><i class="fas fa-envelope me-2 text-primary"></i>Correo:</strong><br>
                        <?php echo $usuario['correo'] ?? 'N/A'; ?>
                    </div>
                    
                    <div class="mb-3">
                        <strong><i class="fas fa-phone me-2 text-primary"></i>Teléfono:</strong><br>
                        <?php echo $usuario['telefono'] ?? 'No registrado'; ?>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fa fa-user me-2 text-primary"></i>Cargo:</strong><br>
                        Contribuyente
                    </div>
                    
                    <div class="d-grid">
                        <a href="editar-perfil.php" class="btn btn-usuario">
                            <i class="fas fa-edit me-2"></i>Editar Información
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contador Asignado -->
            <div class="col-md-6">
                <div class="usuario-card contador-card">
                    <h4 class="mb-3"><i class="fas fa-user-tie me-2"></i>Contador Asignado</h4>
                    
                    <?php if ($contador['nombre'] !== 'No asignado'): ?>
                        <div class="mb-3">
                            <strong><i class="fa fa-user me-2 text-success"></i>Nombre:</strong><br>
                            <?php echo $contador['nombre'] . ' ' . $contador['apellido']; ?>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-id-card me-2 text-success"></i>Documento:</strong><br>
                            <?php echo $contador['tipo_documento'] . ': ' . $contador['numero_documento']; ?>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-envelope me-2 text-success"></i>Correo:</strong><br>
                            <?php echo $contador['correo']; ?>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-phone me-2 text-success"></i>Teléfono:</strong><br>
                            <?php echo $contador['telefono']; ?>
                        </div>

                        <div class="mb-3">
                            <strong><i class="fa fa-user me-2 text-success"></i>Cargo:</strong><br>
                            Contador
                        </div>
                        
                        <!--
                        <div class="d-grid">
                            <a href="contactar-contador.php" class="btn btn-usuario">
                                <i class="fas fa-envelope me-2"></i>Contactar Contador
                            </a>
                        </div>
                        -->

                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No tienes contador asignado</h5>
                            <p class="text-muted">Contacta al administrador para que te asigne un contador</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="usuario-card text-center">
                    <i class="fas fa-file-invoice-dollar fa-2x text-success mb-3"></i>
                    <h5>Declaraciones</h5>
                    <p class="text-muted">Gestionar mis declaraciones</p>
                    <a href="mis-declaraciones.php" class="btn btn-usuario">Ver Declaraciones</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="usuario-card text-center">
                    <i class="fas fa-history fa-2x text-info mb-3"></i>
                    <h5>Historial</h5>
                    <p class="text-muted">Ver mi historial tributario</p>
                    <a href="historial.php" class="btn btn-usuario">Ver Historial</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="usuario-card text-center">
                    <i class="fas fa-cogs fa-2x text-warning mb-3"></i>
                    <h5>Configuración</h5>
                    <p class="text-muted">Ajustes de mi cuenta</p>
                    <a href="configuracion.php" class="btn btn-usuario">Ajustes</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>