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

// Si no encontramos al contador, usar datos de sesión
if (!$contador) {
    $contador = [
        'nombre' => $_SESSION['user_nombre'],
        'apellido' => $_SESSION['user_apellido'],
        'correo' => $_SESSION['user_email'] ?? 'N/A',
        'tipo_documento' => $_SESSION['tipo_documento'] ?? 'N/A',
        'numero_documento' => $_SESSION['numero_documento'] ?? 'N/A',
        'telefono' => $_SESSION['telefono'] ?? 'No registrado'
    ];
}

// Obtener contribuyentes asignados a este contador
$query_contribuyentes = "SELECT * FROM usuario WHERE contador_asignado = ? AND rol = 'usuario' AND activo = 1";
$stmt_contribuyentes = $db->prepare($query_contribuyentes);
$stmt_contribuyentes->execute([$contador_id]);
$contribuyentes = $stmt_contribuyentes->fetchAll(PDO::FETCH_ASSOC);

$total_contribuyentes = count($contribuyentes);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Contador - Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .contador-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 25px;
            transition: transform 0.3s ease;
        }

        .contador-card:hover {
            transform: translateY(-5px);
        }

        .stats-card {
            text-align: center;
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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

        .btn-contador {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-contador:hover {
            background: #2980b9;
            transform: scale(1.05);
        }

        .header-contador {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

        .contribuyente-item {
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>
    <?php include '../modal-logout.php'; ?>

    <div class="container mt-4">
        <!-- Header del Perfil -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="header-contador">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h2 class="mb-1">Bienvenido, <?php echo $contador['nombre'] . ' ' . $contador['apellido']; ?></h2>
                            <p class="text-muted mb-0">Contador</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="d-flex justify-content-end align-items-center gap-3">
                                <a href="gestionar-contribuyentes.php" class="btn btn-contador">
                                    <i class="fas fa-users me-2"></i>Gestionar Contribuyentes
                                </a>

                                <button class="btn btn-outline-danger" onclick="confirmarLogout()">
                                    <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas 
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $total_contribuyentes; ?></div>
                    <div class="stats-label">Contribuyentes Asignados</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number">0</div>
                    <div class="stats-label">Declaraciones Pendientes</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number">0</div>
                    <div class="stats-label">Reportes Generados</div>
                </div>
            </div>
        </div>
        
        -->

        <div class="row">
            <!-- Información Personal -->
            <div class="col-md-6">
                <div class="contador-card">
                    <h4 class="mb-3"><i class="fas fa-user-circle me-2"></i>Información Personal</h4>

                    <div class="mb-3">
                        <strong><i class="fa fa-user me-2 text-success"></i>Nombre:</strong><br>
                        <?php echo $contador['nombre'] ?? 'N/A'; ?> <?php echo $contador['apellido'] ?? 'N/A'; ?>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-id-card me-2 text-success"></i>Documento:</strong><br>
                        <?php echo $contador['tipo_documento'] ?? 'N/A'; ?>: <?php echo $contador['numero_documento'] ?? 'N/A'; ?>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-envelope me-2 text-success"></i>Correo:</strong><br>
                        <?php echo $contador['correo'] ?? 'N/A'; ?>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-phone me-2 text-success"></i>Teléfono:</strong><br>
                        <?php echo $contador['telefono'] ?? 'No registrado'; ?>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fa fa-user me-2 text-success"></i>Cargo:</strong><br>
                        <?php echo $contador['rol'] ?? 'N/A'; ?>
                    </div>

                    <div class="d-grid">
                        <a href="editar-perfil.php" class="btn btn-contador">
                            <i class="fas fa-edit me-2"></i>Editar Información
                        </a>
                    </div>

                </div>
            </div>

            <!-- Contribuyentes Asignados -->
            <div class="col-md-6">
                <div class="contador-card">
                    <h4 class="mb-3"><i class="fas fa-users me-2"></i>Contribuyentes Asignados</h4>

                    <?php if ($total_contribuyentes > 0): ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($contribuyentes as $contribuyente): ?>
                                <div class="contribuyente-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo $contribuyente['nombre'] . ' ' . $contribuyente['apellido']; ?></h6>
                                            <p class="text-muted small mb-1">
                                                <?php echo $contribuyente['tipo_documento'] . ': ' . $contribuyente['numero_documento']; ?>
                                            </p>
                                            <p class="text-muted small mb-0">
                                                <?php echo $contribuyente['correo']; ?>
                                            </p>
                                        </div>
                                        <span class="badge bg-<?php echo ($contribuyente['activo'] == 1) ? 'success' : 'secondary'; ?>">
                                            <?php echo ($contribuyente['activo'] == 1) ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </div>
                                    <!-- En la sección de Contribuyentes Asignados, cambiar: -->
                                    <div class="mt-2">
                                        <a href="ver-documentos.php?id_usuario=<?php echo $contribuyente['id_usuario']; ?>"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>Ver
                                        </a>
                                        <a href="editar-contribuyente.php?id=<?php echo $contribuyente['id_usuario']; ?>"
                                            class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-edit me-1"></i>Editar
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="d-grid">
                            <a href="gestionar-contribuyentes.php" class="btn btn-contador">
                                <i class="fas fa-users me-2"></i>Gestionar Contribuyentes
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No tienes contribuyentes asignados</h5>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="contador-card text-center">
                    <i class="fas fa-user fa-2x text-success mb-3"></i>
                    <h5>Añadir</h5>
                    <p class="text-muted">Añadir nuevo Contribuyente</p>
                    <a href="registro-contribuyente.php" class="btn btn-contador">Añadir</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contador-card text-center">
                    <i class="fas fa-users fa-2x text-success mb-3"></i>
                    <h5>Contribuyentes</h5>
                    <p class="text-muted">Gestionar Contribuyentes</p>
                    <a href="gestionar-contribuyentes.php" class="btn btn-contador">Gestionar</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contador-card text-center">
                    <i class="fas fa-headset fa-2x text-success mb-3"></i>
                    <h5>Mis Reportes</h5>
                    <p class="text-muted">Ver mis reportes de soporte</p>
                    <a href="mis-reportes.php" class="btn btn-contador">Ver Reportes</a>
                </div>
            </div>
        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarLogout() {
            Swal.fire({
                title: "Cerrar Sesión",
                text: "¿Seguro que deseas cerrar sesión?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí",
                cancelButtonText: "Cancelar",
                backdrop: true,
                customClass: {
                    popup: 'rounded-4'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../logout.php";
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>