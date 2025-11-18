<?php
// declaraciones_pendientes.php
include_once '../config/database.php';
include_once '../includes/session.php';

redirectIfNotLoggedIn();
if (!isContador()) {
    header("Location: principal.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener declaraciones asignadas al contador
$contador_id = $_SESSION['user_id'];
$sql = "SELECT d.*, u.nombre as nombre_usuario,
               (SELECT COUNT(*) FROM documentos_soporte ds WHERE ds.id_declaracion = d.id_declaracion) as total_documentos
        FROM declaracion d 
        JOIN usuario u ON d.id_usuario = u.id_usuario 
        WHERE d.id_contador = ? 
        ORDER BY d.fecha_creacion DESC";
$stmt = $db->prepare($sql);
$stmt->execute([$contador_id]);
$declaraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Declaraciones Pendientes - Renta Segura</title>
    <link rel="icon" type="image/png" href="../IMG/chart-line-solid-full.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
    <?php include '../navbar.php'; ?>

    <main class="container mt-4">
        <h1 class="fw-bold text-primary mb-4">
            <i class="fa-solid fa-list-check"></i> Declaraciones Asignadas
        </h1>

        <?php if (empty($declaraciones)): ?>
            <div class="alert alert-info text-center">
                <i class="fa-solid fa-folder-open fa-2x mb-3"></i>
                <h5>No hay declaraciones asignadas</h5>
                <p class="mb-0">No tienes declaraciones pendientes de revisión.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($declaraciones as $declaracion): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="badge 
                                    <?php echo $declaracion['estado'] == 'pendiente' ? 'bg-warning' : 
                                          ($declaracion['estado'] == 'en revision' ? 'bg-info' : 
                                          ($declaracion['estado'] == 'aprobada' ? 'bg-success' : 'bg-danger')); ?>">
                                    <?php echo ucfirst($declaracion['estado']); ?>
                                </span>
                                <span class="badge bg-secondary">
                                    <i class="fa-solid fa-file"></i> <?php echo $declaracion['total_documentos']; ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">Año Fiscal: <?php echo $declaracion['anio_fiscal']; ?></h5>
                                <p class="card-text">
                                    <strong>Contribuyente:</strong> <?php echo htmlspecialchars($declaracion['nombre_usuario']); ?><br>
                                    <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($declaracion['fecha_creacion'])); ?>
                                </p>
                            </div>
                            <div class="card-footer">
                                <a href="ver_documentos.php?id=<?php echo $declaracion['id_declaracion']; ?>" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-eye"></i> Ver Documentos
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>