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

if (!$contador) {
    header("Location: perfil-contador.php");
    exit();
}

// Obtener contribuyentes asignados a este contador
$query_contribuyentes = "SELECT * FROM usuario WHERE contador_asignado = ? AND rol = 'usuario' ORDER BY nombre, apellido";
$stmt_contribuyentes = $db->prepare($query_contribuyentes);
$stmt_contribuyentes->execute([$contador_id]);
$contribuyentes = $stmt_contribuyentes->fetchAll(PDO::FETCH_ASSOC);

// Procesar eliminación de asignación
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_asignacion'])) {
    $contribuyente_id = intval($_POST['contribuyente_id']);
    
    // Verificar que el contribuyente realmente está asignado a este contador
    $check_query = "SELECT id_usuario FROM usuario WHERE id_usuario = ? AND contador_asignado = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$contribuyente_id, $contador_id]);
    
    if ($check_stmt->rowCount() > 0) {
        // Eliminar asignación (poner contador_asignado en NULL)
        $update_query = "UPDATE usuario SET contador_asignado = NULL WHERE id_usuario = ?";
        $update_stmt = $db->prepare($update_query);
        
        if ($update_stmt->execute([$contribuyente_id])) {
            $success = "Contribuyente eliminado de tu lista de asignados exitosamente.";
            // Actualizar la lista de contribuyentes inmediatamente
            $stmt_contribuyentes->execute([$contador_id]);
            $contribuyentes = $stmt_contribuyentes->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error = "Error al eliminar la asignación. Por favor, intenta nuevamente.";
        }
    } else {
        $error = "El contribuyente no está asignado a tu cuenta.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Contribuyentes - Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .gestion-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 25px;
        }
        .user-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #0d6efd;
            transition: transform 0.2s;
        }
        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .header-gestion {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .btn-danger-custom {
            background: #e74c3c;
            border-color: #e74c3c;
            color: white;
        }
        .btn-danger-custom:hover {
            background: #c0392b;
            border-color: #c0392b;
        }
        .btn-agregar {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-agregar:hover {
            background: #0056d2;
            transform: scale(1.05);
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        /* Animación para remover elementos */
        .fade-out {
            opacity: 0;
            transition: opacity 0.3s ease-out;
        }
        
        /* Prevenir problemas de modal */
        body.modal-open {
            overflow: auto !important;
            padding-right: 0 !important;
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
                        <i class="fas fa-users me-2"></i>Gestión de Contribuyentes
                    </h2>
                    <p class="text-muted mb-0">Administra los contribuyentes asignados a tu cuenta</p>
                </div>

                <div class="col-md-4 text-end">
                    <div class="btn-group-header">
                        <a href="registro-contribuyente.php" class="btn btn-agregar">
                            <i class="fas fa-plus me-2"></i>Agregar Usuario
                        </a>
                        <a href="perfil-contador.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Lista de Contribuyentes -->
        <div class="gestion-card">
            <h4 class="mb-4">
                <i class="fas fa-list me-2"></i>Contribuyentes Asignados
                
            </h4>

            <div id="listaContribuyentes">
                <?php if (count($contribuyentes) > 0): ?>
                    <div class="row">
                        <?php foreach ($contribuyentes as $contribuyente): ?>
                            <div class="col-md-6 mb-3" id="contribuyente-<?php echo $contribuyente['id_usuario']; ?>">
                                <div class="user-card">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-3">
                                                <?php echo substr($contribuyente['nombre'], 0, 1) . substr($contribuyente['apellido'], 0, 1); ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($contribuyente['nombre'] . ' ' . $contribuyente['apellido']); ?></h6>
                                                <span class="badge bg-success">Contribuyente</span>
                                                <small class="text-muted d-block">ID: <?php echo $contribuyente['id_usuario']; ?></small>
                                            </div>
                                        </div>
                                        <span class="badge bg-<?php echo ($contribuyente['activo'] == 1) ? 'success' : 'secondary'; ?>">
                                            <?php echo ($contribuyente['activo'] == 1) ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </div>

                                    <!-- Información del Contribuyente -->
                                    <div class="mb-3">
                                        <p class="mb-1">
                                            <i class="fas fa-envelope me-2 text-muted"></i>
                                            <?php echo htmlspecialchars($contribuyente['correo']); ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-id-card me-2 text-muted"></i>
                                            <?php echo htmlspecialchars($contribuyente['tipo_documento'] . ' ' . $contribuyente['numero_documento']); ?>
                                        </p>
                                        <?php if (!empty($contribuyente['telefono'])): ?>
                                            <p class="mb-0">
                                                <i class="fas fa-phone me-2 text-muted"></i>
                                                <?php echo htmlspecialchars($contribuyente['telefono']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Botones de Acción -->
                                    <div class="d-flex gap-2">
                                        <a href="ver-contribuyente.php?id=<?php echo $contribuyente['id_usuario']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>Ver
                                        </a>
                                        <a href="editar-contribuyente.php?id=<?php echo $contribuyente['id_usuario']; ?>" 
                                           class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-edit me-1"></i>Editar
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar" 
                                                data-id="<?php echo $contribuyente['id_usuario']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($contribuyente['nombre'] . ' ' . $contribuyente['apellido']); ?>">
                                            <i class="fas fa-times me-1"></i>Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5" id="sinContribuyentes">
                        <i class="fas fa-users fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted">No tienes contribuyentes asignados</h4>
                        <p class="text-muted">Puedes agregar nuevos contribuyentes usando el botón "Agregar Usuario".</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="registro-contribuyente.php" class="btn btn-agregar">
                                <i class="fas fa-plus me-2"></i>Agregar Usuario
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal único para todas las eliminaciones -->
    <div class="modal fade" id="modalEliminarGlobal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="mensajeEliminacion">¿Estás seguro de que deseas eliminar a este contribuyente de tu lista de asignados?</p>
                    <p class="text-muted"><small>Esta acción solo elimina la asignación, el contribuyente permanecerá en el sistema.</small></p>
                </div>
                <div class="modal-footer">
                    <form method="POST" action="" id="formEliminarGlobal">
                        <input type="hidden" name="contribuyente_id" id="contribuyenteId" value="">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="eliminar_asignacion" class="btn btn-danger-custom" id="btnConfirmarEliminar">
                            <i class="fas fa-trash me-1"></i>Eliminar Asignación
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales
        let contribuyenteAEliminar = null;
        const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminarGlobal'));

        // Configurar event listeners para botones de eliminar
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar botones de eliminar
            document.querySelectorAll('.btn-eliminar').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const nombre = this.getAttribute('data-nombre');
                    abrirModalEliminacion(id, nombre);
                });
            });

            // Configurar envío del formulario
            document.getElementById('formEliminarGlobal').addEventListener('submit', function(e) {
                e.preventDefault();
                eliminarContribuyente();
            });

            // Limpiar estado al cerrar el modal
            document.getElementById('modalEliminarGlobal').addEventListener('hidden.bs.modal', function() {
                contribuyenteAEliminar = null;
                document.getElementById('contribuyenteId').value = '';
                document.getElementById('btnConfirmarEliminar').disabled = false;
                document.getElementById('btnConfirmarEliminar').innerHTML = '<i class="fas fa-trash me-1"></i>Eliminar Asignación';
            });
        });

        function abrirModalEliminacion(id, nombre) {
            contribuyenteAEliminar = { id: id, nombre: nombre };
            document.getElementById('contribuyenteId').value = id;
            document.getElementById('mensajeEliminacion').innerHTML = 
                '¿Estás seguro de que deseas eliminar a <strong>' + nombre + '</strong> de tu lista de contribuyentes asignados?';
            modalEliminar.show();
        }

        function eliminarContribuyente() {
            if (!contribuyenteAEliminar) return;

            const btnConfirmar = document.getElementById('btnConfirmarEliminar');
            btnConfirmar.disabled = true;
            btnConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Eliminando...';

            // Crear FormData para enviar el formulario
            const formData = new FormData();
            formData.append('contribuyente_id', contribuyenteAEliminar.id);
            formData.append('eliminar_asignacion', 'true');

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                // Crear un documento temporal para parsear la respuesta
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Extraer la nueva lista de contribuyentes
                const nuevaLista = doc.getElementById('listaContribuyentes');
                const nuevoContador = doc.getElementById('contadorContribuyentes');
                
                if (nuevaLista) {
                    // Actualizar la lista con animación
                    const elementoAEliminar = document.getElementById('contribuyente-' + contribuyenteAEliminar.id);
                    if (elementoAEliminar) {
                        elementoAEliminar.classList.add('fade-out');
                        setTimeout(() => {
                            document.getElementById('listaContribuyentes').innerHTML = nuevaLista.innerHTML;
                            if (nuevoContador) {
                                document.getElementById('contadorContribuyentes').textContent = nuevoContador.textContent;
                            }
                            reconfigurarEventListeners();
                            mostrarMensajeExito();
                        }, 300);
                    }
                }
                
                modalEliminar.hide();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar el contribuyente. Por favor, intenta nuevamente.');
                btnConfirmar.disabled = false;
                btnConfirmar.innerHTML = '<i class="fas fa-trash me-1"></i>Eliminar Asignación';
            });
        }

        function reconfigurarEventListeners() {
            // Reconfigurar los event listeners para los nuevos botones
            document.querySelectorAll('.btn-eliminar').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const nombre = this.getAttribute('data-nombre');
                    abrirModalEliminacion(id, nombre);
                });
            });
        }

        function mostrarMensajeExito() {
            // Crear y mostrar mensaje de éxito
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                Contribuyente eliminado de tu lista de asignados exitosamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Insertar después del header
            const header = document.querySelector('.header-gestion');
            header.parentNode.insertBefore(alertDiv, header.nextSibling);
            
            // Auto-eliminar después de 5 segundos
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Limpiar estado inicial de modales
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
        });
    </script>
</body>
</html>