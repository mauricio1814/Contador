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

// Obtener estadísticas
$query_usuarios = "SELECT COUNT(*) as total FROM usuario WHERE activo = 1";
$query_contadores = "SELECT COUNT(*) as total FROM usuario WHERE rol = 'contador' AND activo = 1";
$query_contribuyentes = "SELECT COUNT(*) as total FROM usuario WHERE rol = 'usuario' AND activo = 1";

$total_usuarios = $db->query($query_usuarios)->fetch(PDO::FETCH_ASSOC)['total'];
$total_contadores = $db->query($query_contadores)->fetch(PDO::FETCH_ASSOC)['total'];
$total_contribuyentes = $db->query($query_contribuyentes)->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Admin - Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .admin-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 25px;
            transition: transform 0.3s ease;
        }
        .admin-card:hover {
            transform: translateY(-5px);
        }
        .stats-card {
            text-align: center;
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
        .btn-admin {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-admin:hover {
            background: #0056d2;
            transform: scale(1.05);
        }
        .header-admin {
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
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="container mt-4">
        <!-- Header del Perfil -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="header-admin">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h2 class="mb-1">Bienvenido, <?php echo $_SESSION['user_nombre']; ?></h2>
                            <p class="text-muted mb-0">Administrador del Sistema</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="d-flex justify-content-end align-items-center gap-3">
                                <a href="admin-usuarios.php" class="btn btn-admin">
                                    <i class="fas fa-users me-2"></i>Gestionar Usuarios
                                </a>
                                
                                <!-- Botón de Cerrar Sesión -->
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
                    <div class="stats-number"><?php echo $total_usuarios; ?></div>
                    <div class="stats-label">Total Usuarios</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $total_contadores; ?></div>
                    <div class="stats-label">Contadores</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $total_contribuyentes; ?></div>
                    <div class="stats-label">Contribuyentes</div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="row">
            <div class="col-md-6">
                <div class="admin-card">
                    <h4 class="mb-3"><i class="fas fa-user-plus me-2"></i>Registro Rápido</h4>
                    <p>Registrar nuevos usuarios en el sistema</p>
                    <a href="admin-registro.php" class="btn btn-admin">
                        <i class="fas fa-plus me-2"></i>Agregar Usuario
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="admin-card">
                    <h4 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Reportes</h4>
                    <p>Generar reportes del sistema</p>
                    <button class="btn btn-admin" onclick="alert('Funcionalidad en desarrollo')">
                        <i class="fas fa-download me-2"></i>Generar Reporte
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>