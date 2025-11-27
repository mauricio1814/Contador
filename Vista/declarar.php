<?php
include_once '../config/database.php';
include_once '../includes/session.php';

redirectIfNotLoggedIn();

$database = new Database();
$db = $database->getConnection();

$usuario_id = $_SESSION['user_id'];
$mensaje = '';
$tipo_mensaje = '';

// Solo contribuyentes pueden subir
$puedeSubirDocumentos = isContribuyente();

// Función para obtener el total de documentos subidos por el usuario
function getTotalDocumentosUsuario($db, $usuario_id)
{
    $sql = "SELECT COUNT(*) as total 
            FROM documentos_soporte ds 
            JOIN declaracion d ON ds.id_declaracion = d.id_declaracion 
            WHERE d.id_usuario = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$usuario_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}

// Obtener el total actual de documentos del usuario
$total_documentos_usuario = getTotalDocumentosUsuario($db, $usuario_id);

// Procesar formulario - IMPLEMENTACIÓN PRG (Post-Redirect-Get)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['subir_declaracion'])) {
    if (!$puedeSubirDocumentos) {
        $_SESSION['mensaje'] = "<div class='alert alert-danger'>
            <h4><i class='fa-solid fa-circle-xmark me-2'></i>ACCESO DENEGADO</h4>
            <p>No tienes permisos para subir declaraciones.</p>
        </div>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        try {
            $fecha = $_POST['fecha'];
            $observaciones = $_POST['descripcion'] ?? '';
            $anio_fiscal = date('Y', strtotime($fecha));

            // 1. VERIFICAR CARPETA
            $carpeta_documentos = "../uploads/documentos/";

            // Crear carpeta si no existe
            if (!file_exists($carpeta_documentos)) {
                mkdir($carpeta_documentos, 0777, true);
            }

            // Verificar si se puede escribir
            if (!is_writable($carpeta_documentos)) {
                throw new Exception("La carpeta no tiene permisos de escritura: " . $carpeta_documentos);
            }

            $db->beginTransaction();

            // 2. INSERTAR DECLARACIÓN - SIN CONTADOR (usa NULL)
            $sql_declaracion = "INSERT INTO declaracion (id_usuario, id_contador, anio_fiscal, estado) 
                               VALUES (?, NULL, ?, 'pendiente')";
            $stmt_declaracion = $db->prepare($sql_declaracion);
            $stmt_declaracion->execute([$usuario_id, $anio_fiscal]);

            $declaracion_id = $db->lastInsertId();
            $archivos_subidos = 0;

            // 3. PROCESAR ARCHIVOS
            if (isset($_FILES['documentos']) && !empty($_FILES['documentos']['name'][0])) {

                foreach ($_FILES['documentos']['name'] as $key => $nombre_archivo) {

                    // Solo procesar si no hay error
                    if ($_FILES['documentos']['error'][$key] === UPLOAD_ERR_OK) {

                        $archivo_tmp = $_FILES['documentos']['tmp_name'][$key];
                        $tamaño = $_FILES['documentos']['size'][$key];

                        // Validar tamaño (10MB máximo)
                        if ($tamaño > 10 * 1024 * 1024) {
                            continue; // Saltar archivos muy grandes
                        }

                        // Crear nombre único
                        $nuevo_nombre = uniqid() . '_' . $nombre_archivo;
                        $ruta_destino = $carpeta_documentos . $nuevo_nombre;

                        // MOVER ARCHIVO
                        if (move_uploaded_file($archivo_tmp, $ruta_destino)) {

                            // Determinar tipo de documento
                            $tipo_documento = 'Otros';
                            $nombre_minuscula = strtolower($nombre_archivo);

                            if (strpos($nombre_minuscula, 'ingreso') !== false || strpos($nombre_minuscula, 'salario') !== false) {
                                $tipo_documento = 'Ingresos';
                            } elseif (strpos($nombre_minuscula, 'gasto') !== false || strpos($nombre_minuscula, 'factura') !== false) {
                                $tipo_documento = 'Gastos';
                            } elseif (strpos($nombre_minuscula, 'banco') !== false || strpos($nombre_minuscula, 'cuenta') !== false) {
                                $tipo_documento = 'Bancarios';
                            }

                            // INSERTAR DOCUMENTO
                            $sql_documento = "INSERT INTO documentos_soporte 
                                             (id_declaracion, nombre_original, tipo_documento, urldocumento, tamaño_archivo, estado, observaciones) 
                                             VALUES (?, ?, ?, ?, ?, 'pendiente', ?)";
                            $stmt_documento = $db->prepare($sql_documento);
                            $stmt_documento->execute([
                                $declaracion_id,
                                $nombre_archivo,
                                $tipo_documento,
                                $ruta_destino,
                                $tamaño,
                                $observaciones
                            ]);

                            $archivos_subidos++;
                        }
                    }
                }
            }

            $db->commit();

            // Actualizar el total de documentos después de la subida exitosa
            $nuevo_total = getTotalDocumentosUsuario($db, $usuario_id);

            // MENSAJE DE CONFIRMACIÓN - Guardar en sesión para el redirect
            if ($archivos_subidos > 0) {
                $_SESSION['mensaje'] = "<div class='alert alert-success'>
                    <h4><i class='fa-solid fa-circle-check me-2'></i>¡ÉXITO!</h4>
                    <p><strong>Declaración del año {$anio_fiscal} enviada correctamente</strong></p>
                    <hr>
                    <p><i class='fa-solid fa-file me-2'></i> <strong>{$archivos_subidos} documentos</strong> subidos en esta declaración</p>
                    <p><i class='fa-solid fa-chart-line me-2'></i> <strong>Total acumulado: {$nuevo_total} documentos</strong> subidos por ti</p>
                    <p><i class='fa-solid fa-database me-2'></i> Guardados en base de datos</p>
                    <p><i class='fa-solid fa-folder me-2'></i> Guardados en carpeta uploads</p>
                    <p><i class='fa-solid fa-clock me-2'></i> Estado: <span class='badge bg-warning'>Pendiente de revisión</span></p>
                </div>";
            } else {
                throw new Exception("No se pudieron subir los documentos.");
            }

            // REDIRECT para evitar reenvío del formulario al recargar
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['mensaje'] = "<div class='alert alert-danger'>
                <h4><i class='fa-solid fa-circle-xmark me-2'></i>ERROR</h4>
                <p>{$e->getMessage()}</p>
            </div>";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Obtener mensaje de la sesión si existe (después del redirect)
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']); // Limpiar el mensaje después de mostrarlo
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Declarar Renta - Renta Segura</title>
    <link rel="icon" type="image/svg" href="../IMG/chart-line-solid-full.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .form-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 20px;
        }

        .btn-custom {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>

    <main class="container">

        <div class="form-section">
            <h1 class="fw-bold text-dark text-center">
                <i class="fa-solid fa-file-invoice-dollar"></i> Declarar Renta
            </h1>
            <p class="text-muted text-center">Sube los documentos necesarios para tu declaración de renta</p>

            <?php echo $mensaje; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row mt-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Año Fiscal</label>
                        <input type="number" name="fecha" class="form-control"
                            min="2020" max="2024" value="2024" required>
                        <small class="form-text text-muted">Selecciona el año fiscal que corresponde a tu declaración</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Documentos de Soporte</label>
                        <input type="file" name="documentos[]" class="form-control" multiple
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                        <small class="form-text text-muted">PDF, Word, Excel, JPG, PNG (Máx. 10MB por archivo)</small>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Observaciones (Opcional)</label>
                        <textarea name="descripcion" class="form-control" rows="3"
                            placeholder="Agrega alguna observación importante sobre tu declaración..."></textarea>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <button type="submit" name="subir_declaracion" class="btn btn-success btn-lg">
                            <i class='fa-solid fa-cloud-arrow-up me-2'></i> Subir Declaración
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Información importante -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle me-2"></i>Documentos recomendados para tu declaración:</h5>
                    <ul class="mb-0">
                        <li>Estados de cuenta bancarios</li>
                        <li>Comprobantes de ingresos (nómina, contratos)</li>
                        <li>Facturas de gastos deducibles</li>
                        <li>Documentos de identificación</li>
                        <li>Certificados de retención en la fuente</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>