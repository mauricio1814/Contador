<?php
include_once '../config/database.php';
include_once '../includes/session.php';

redirectIfNotLoggedIn();
if (!isAdmin()) {
    header("Location: principal.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// =============================================
// LÓGICA PARA ELIMINAR USUARIO
// =============================================
if (isset($_GET['eliminar']) && !empty($_GET['eliminar'])) {
    $id_usuario_eliminar = $_GET['eliminar'];
    
    try {
        // Iniciar transacción
        $db->beginTransaction();
        
        // 1. Primero obtener información del usuario para mensajes y validaciones
        $query_info = "SELECT id_usuario, rol, nombre, apellido FROM usuario WHERE id_usuario = ?";
        $stmt_info = $db->prepare($query_info);
        $stmt_info->execute([$id_usuario_eliminar]);
        $usuario_info = $stmt_info->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario_info) {
            $_SESSION['error'] = "Usuario no encontrado";
            header("Location: admin-usuarios.php");
            exit();
        }
        
        // 2. Si es un contador, liberar a los contribuyentes que tenía asignados
        if ($usuario_info['rol'] === 'contador') {
            $query_liberar = "UPDATE usuario SET contador_asignado = NULL WHERE contador_asignado = ?";
            $stmt_liberar = $db->prepare($query_liberar);
            $stmt_liberar->execute([$id_usuario_eliminar]);
        }
        
        // 3. ELIMINACIÓN FÍSICA - Eliminar completamente de la base de datos
        $query_eliminar = "DELETE FROM usuario WHERE id_usuario = ?";
        $stmt_eliminar = $db->prepare($query_eliminar);
        $resultado = $stmt_eliminar->execute([$id_usuario_eliminar]);
        
        if ($resultado) {
            // Confirmar la transacción
            $db->commit();
            $_SESSION['success'] = "Usuario " . $usuario_info['nombre'] . " " . $usuario_info['apellido'] . " eliminado completamente del sistema";
        } else {
            // Revertir la transacción en caso de error
            $db->rollBack();
            $_SESSION['error'] = "Error al eliminar el usuario";
        }
        
    } catch (PDOException $e) {
        // Revertir la transacción en caso de excepción
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $_SESSION['error'] = "Error de base de datos: " . $e->getMessage();
    }
    
    // Redirigir para evitar reenvío del formulario
    header("Location: admin-usuarios.php");
    exit();
}

// =============================================
// OBTENER USUARIOS PARA MOSTRAR
// =============================================
// Obtener todos los usuarios activos
$query = "SELECT u.*, c.nombre as contador_nombre, c.apellido as contador_apellido 
          FROM usuario u 
          LEFT JOIN usuario c ON u.contador_asignado = c.id_usuario 
          WHERE u.activo = 1 
          ORDER BY u.rol, u.nombre";
$stmt = $db->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separar usuarios por tipo
$contribuyentes = array_filter($usuarios, function($user) {
    return $user['rol'] === 'usuario';
});

$contadores = array_filter($usuarios, function($user) {
    return $user['rol'] === 'contador';
});

$admins = array_filter($usuarios, function($user) {
    return $user['rol'] === 'admin';
});

// Mostrar mensajes de éxito/error
$mensaje_exito = '';
$mensaje_error = '';

if (isset($_SESSION['success'])) {
    $mensaje_exito = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $mensaje_error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .user-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #0d6efd;
            transition: transform 0.2s ease;
            position: relative;
        }
        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .contador-card {
            border-left-color: #28a745;
        }
        .admin-card {
            border-left-color: #dc3545;
        }
        .user-role {
            font-size: 0.8rem;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }
        .role-contador {
            background: #d4edda;
            color: #155724;
        }
        .role-usuario {
            background: #d1ecf1;
            color: #0c5460;
        }
        .role-admin {
            background: #f8d7da;
            color: #721c24;
        }
        .user-id {
            font-size: 0.8rem;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }
        .id-contador {
            background: #d4edda;
            color: #155724;
        }
        .id-usuario {
            background: #d1ecf1;
            color: #0c5460;
        }
        .id-admin {
            background: #f8d7da;
            color: #721c24;
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
        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
            border: none;
            padding: 12px 20px;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
            background: transparent;
        }
        .tab-pane {
            padding: 20px 0;
        }
        .user-info {
            font-size: 0.9rem;
        }
        .user-info i {
            width: 16px;
            color: #6c757d;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }
        .user-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 8px;
        }
        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.3s ease;
            font-size: 0.8rem;
        }
        .btn-edit {
            background: #0d6efd;
            color: white;
        }
        .btn-edit:hover {
            background: #0056d2;
            transform: scale(1.1);
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background: #c82333;
            transform: scale(1.1);
        }
        .user-header {
            padding-right: 70px; 
        }
        .role-container {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
        }
        /* CORRECCIÓN PARA LAS PESTAÑAS */
        .tab-content > .tab-pane {
            display: none;
        }
        .tab-content > .active {
            display: block;
        }
        /* Asegurar que Bootstrap maneje la visibilidad */
        .fade:not(.show) {
            opacity: 0;
        }
        .fade.show {
            opacity: 1;
            transition: opacity 0.15s linear;
        }
        /* ESTILOS PARA EL HEADER COMO EN LA CAPTURA */
        .page-header {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .page-subtitle {
            font-size: 1.2rem;
            color: #0d6efd;
            font-weight: 600;
            margin-bottom: 0;
        }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="container mt-4">
        <!-- Mostrar mensajes de éxito/error -->
        <?php if ($mensaje_exito): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $mensaje_exito; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($mensaje_error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $mensaje_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- HEADER COMO EN LA CAPTURA -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Renta Segura</h1>
                    <h2 class="page-subtitle">Gestión de Usuarios</h2>
                </div>
                <div class="header-actions">
                    <a href="admin-registro.php" class="btn btn-agregar">
                        <i class="fas fa-plus me-2"></i>Agregar Usuario
                    </a>
                    <a href="perfil-admin.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Perfil
                    </a>
                </div>
            </div>
        </div>

        <!-- Pestañas -->
        <ul class="nav nav-tabs mb-4" id="userTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="todos-tab" data-bs-toggle="tab" 
                        data-bs-target="#todos" type="button" role="tab" aria-controls="todos" aria-selected="true">
                    <i class="fas fa-users me-2"></i>Todos los Usuarios
                    <span class="badge bg-secondary ms-2"><?php echo count($usuarios); ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="contribuyentes-tab" data-bs-toggle="tab" 
                        data-bs-target="#contribuyentes" type="button" role="tab" aria-controls="contribuyentes" aria-selected="false">
                    <i class="fas fa-user me-2"></i>Contribuyentes
                    <span class="badge bg-primary ms-2"><?php echo count($contribuyentes); ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="contadores-tab" data-bs-toggle="tab" 
                        data-bs-target="#contadores" type="button" role="tab" aria-controls="contadores" aria-selected="false">
                    <i class="fas fa-user-tie me-2"></i>Contadores
                    <span class="badge bg-success ms-2"><?php echo count($contadores); ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="admins-tab" data-bs-toggle="tab" 
                        data-bs-target="#admins" type="button" role="tab" aria-controls="admins" aria-selected="false">
                    <i class="fas fa-user-shield me-2"></i>Administradores
                    <span class="badge bg-danger ms-2"><?php echo count($admins); ?></span>
                </button>
            </li>
        </ul>

        <!-- Contenido de Pestañas -->
        <div class="tab-content" id="userTabsContent">
            <!-- Pestaña Todos los Usuarios -->
            <div class="tab-pane fade show active" id="todos" role="tabpanel" aria-labelledby="todos-tab">
                <div class="row">
                    <?php if (empty($usuarios)): ?>
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <h5>No hay usuarios registrados</h5>
                                <p>Comienza agregando el primer usuario al sistema</p>
                                <a href="admin-registro.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Agregar Usuario
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach($usuarios as $user): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="user-card <?php echo $user['rol'] === 'contador' ? 'contador-card' : ($user['rol'] === 'admin' ? 'admin-card' : ''); ?>">
                                    <!-- Botones de acción -->
                                    <div class="user-actions">
                                        <a href="editar-usuario.php?id=<?php echo $user['id_usuario']; ?>" 
                                           class="btn-action btn-edit" 
                                           title="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn-action btn-delete" 
                                                title="Eliminar usuario"
                                                onclick="confirmarEliminacion(<?php echo $user['id_usuario']; ?>, '<?php echo $user['nombre'] . ' ' . $user['apellido']; ?>', '<?php echo $user['rol']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="user-header">
                                        <h6 class="mb-0"><?php echo $user['nombre'] . ' ' . $user['apellido']; ?></h6>
                                        <div class="role-container">
                                            <span class="user-role <?php echo 'role-' . $user['rol']; ?>">
                                                <?php 
                                                    switch($user['rol']) {
                                                        case 'admin': echo 'Administrador'; break;
                                                        case 'contador': echo 'Contador'; break;
                                                        case 'usuario': echo 'Contribuyente'; break;
                                                        default: echo $user['rol'];
                                                    }
                                                ?>
                                            </span>
                                            <span class="user-id <?php echo 'id-' . $user['rol']; ?>" title="ID de usuario en la base de datos">
                                                ID: <?php echo $user['id_usuario']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="user-info mt-3">
                                        <p class="mb-1">
                                            <i class="fas fa-envelope me-1"></i>
                                            <?php echo $user['correo']; ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-id-card me-1"></i>
                                            <?php echo $user['tipo_documento'] . ' ' . $user['numero_documento']; ?>
                                        </p>
                                        
                                        <?php if ($user['rol'] === 'usuario' && $user['contador_nombre']): ?>
                                            <p class="text-success mb-1">
                                                <i class="fas fa-user-tie me-1"></i>
                                                Contador: <?php echo $user['contador_nombre'] . ' ' . $user['contador_apellido']; ?>
                                            </p>
                                        <?php elseif ($user['rol'] === 'usuario'): ?>
                                            <p class="text-warning mb-1">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Sin contador asignado
                                            </p>
                                        <?php endif; ?>
                                        
                                        <?php if ($user['telefono']): ?>
                                            <p class="mb-0">
                                                <i class="fas fa-phone me-1"></i>
                                                <?php echo $user['telefono']; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pestaña Contribuyentes -->
            <div class="tab-pane fade" id="contribuyentes" role="tabpanel" aria-labelledby="contribuyentes-tab">
                <div class="row">
                    <?php if (empty($contribuyentes)): ?>
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="fas fa-user"></i>
                                <h5>No hay contribuyentes registrados</h5>
                                <p>Los contribuyentes son usuarios que declaran renta</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach($contribuyentes as $user): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="user-card">
                                    <!-- Botones de acción -->
                                    <div class="user-actions">
                                        <a href="editar-usuario.php?id=<?php echo $user['id_usuario']; ?>" 
                                           class="btn-action btn-edit" 
                                           title="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn-action btn-delete" 
                                                title="Eliminar usuario"
                                                onclick="confirmarEliminacion(<?php echo $user['id_usuario']; ?>, '<?php echo $user['nombre'] . ' ' . $user['apellido']; ?>', '<?php echo $user['rol']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="user-header">
                                        <h6 class="mb-0"><?php echo $user['nombre'] . ' ' . $user['apellido']; ?></h6>
                                        <div class="role-container">
                                            <span class="user-role role-usuario">Contribuyente</span>
                                            <span class="user-id id-usuario" title="ID de usuario en la base de datos">
                                                ID: <?php echo $user['id_usuario']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="user-info mt-3">
                                        <p class="mb-1">
                                            <i class="fas fa-envelope me-1"></i>
                                            <?php echo $user['correo']; ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-id-card me-1"></i>
                                            <?php echo $user['tipo_documento'] . ' ' . $user['numero_documento']; ?>
                                        </p>
                                        
                                        <?php if ($user['contador_nombre']): ?>
                                            <p class="text-success mb-1">
                                                <i class="fas fa-user-tie me-1"></i>
                                                Contador: <?php echo $user['contador_nombre'] . ' ' . $user['contador_apellido']; ?>
                                            </p>
                                        <?php else: ?>
                                            <p class="text-warning mb-1">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Sin contador asignado
                                            </p>
                                        <?php endif; ?>
                                        
                                        <?php if ($user['telefono']): ?>
                                            <p class="mb-0">
                                                <i class="fas fa-phone me-1"></i>
                                                <?php echo $user['telefono']; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pestaña Contadores -->
            <div class="tab-pane fade" id="contadores" role="tabpanel" aria-labelledby="contadores-tab">
                <div class="row">
                    <?php if (empty($contadores)): ?>
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="fas fa-user-tie"></i>
                                <h5>No hay contadores registrados</h5>
                                <p>Los contadores gestionan a los contribuyentes</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach($contadores as $user): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="user-card contador-card">
                                    <!-- Botones de acción -->
                                    <div class="user-actions">
                                        <a href="editar-usuario.php?id=<?php echo $user['id_usuario']; ?>" 
                                           class="btn-action btn-edit" 
                                           title="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn-action btn-delete" 
                                                title="Eliminar usuario"
                                                onclick="confirmarEliminacion(<?php echo $user['id_usuario']; ?>, '<?php echo $user['nombre'] . ' ' . $user['apellido']; ?>', '<?php echo $user['rol']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="user-header">
                                        <h6 class="mb-0"><?php echo $user['nombre'] . ' ' . $user['apellido']; ?></h6>
                                        <div class="role-container">
                                            <span class="user-role role-contador">Contador</span>
                                            <span class="user-id id-contador" title="ID de usuario en la base de datos">
                                                ID: <?php echo $user['id_usuario']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="user-info mt-3">
                                        <p class="mb-1">
                                            <i class="fas fa-envelope me-1"></i>
                                            <?php echo $user['correo']; ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-id-card me-1"></i>
                                            <?php echo $user['tipo_documento'] . ' ' . $user['numero_documento']; ?>
                                        </p>
                                        
                                        <?php if ($user['telefono']): ?>
                                            <p class="mb-0">
                                                <i class="fas fa-phone me-1"></i>
                                                <?php echo $user['telefono']; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pestaña Administradores -->
            <div class="tab-pane fade" id="admins" role="tabpanel" aria-labelledby="admins-tab">
                <div class="row">
                    <?php if (empty($admins)): ?>
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="fas fa-user-shield"></i>
                                <h5>No hay administradores registrados</h5>
                                <p>Los administradores gestionan el sistema completo</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach($admins as $user): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="user-card admin-card">
                                    <!-- Botones de acción -->
                                    <div class="user-actions">
                                        <a href="editar-usuario.php?id=<?php echo $user['id_usuario']; ?>" 
                                           class="btn-action btn-edit" 
                                           title="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn-action btn-delete" 
                                                title="Eliminar usuario"
                                                onclick="confirmarEliminacion(<?php echo $user['id_usuario']; ?>, '<?php echo $user['nombre'] . ' ' . $user['apellido']; ?>', '<?php echo $user['rol']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="user-header">
                                        <h6 class="mb-0"><?php echo $user['nombre'] . ' ' . $user['apellido']; ?></h6>
                                        <div class="role-container">
                                            <span class="user-role role-admin">Administrador</span>
                                            <span class="user-id id-admin" title="ID de usuario en la base de datos">
                                                ID: <?php echo $user['id_usuario']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="user-info mt-3">
                                        <p class="mb-1">
                                            <i class="fas fa-envelope me-1"></i>
                                            <?php echo $user['correo']; ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-id-card me-1"></i>
                                            <?php echo $user['tipo_documento'] . ' ' . $user['numero_documento']; ?>
                                        </p>
                                        
                                        <?php if ($user['telefono']): ?>
                                            <p class="mb-0">
                                                <i class="fas fa-phone me-1"></i>
                                                <?php echo $user['telefono']; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Función para confirmar eliminación de usuario
        function confirmarEliminacion(idUsuario, nombreUsuario, rolUsuario) {
            let mensaje = `¿Estás seguro de que deseas ELIMINAR COMPLETAMENTE al usuario "${nombreUsuario}"?\n\n`;
            mensaje += `⚠️ Esta acción NO se puede deshacer. El usuario será eliminado permanentemente.`;
            
            // Mensaje adicional si es un contador
            if (rolUsuario === 'contador') {
                mensaje += '\n\n📋 ADVERTENCIA: Este usuario es un CONTADOR. Al eliminarlo, todos los contribuyentes que tenía asignados quedarán sin contador.';
            }
            
            if (confirm(mensaje)) {
                // Mostrar indicador de carga
                const boton = event.target;
                const iconoOriginal = boton.innerHTML;
                boton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                boton.disabled = true;
                
                // Redirigir para eliminar el usuario
                window.location.href = `admin-usuarios.php?eliminar=${idUsuario}`;
            }
        }

        // Inicialización adicional para asegurar que las pestañas funcionen correctamente
        document.addEventListener('DOMContentLoaded', function() {
            // Asegurar que la primera pestaña esté activa
            const firstTab = document.querySelector('#todos-tab');
            const firstTabPane = document.querySelector('#todos');
            
            if (firstTab && firstTabPane) {
                firstTab.classList.add('active');
                firstTab.setAttribute('aria-selected', 'true');
                firstTabPane.classList.add('show', 'active');
            }
        });
    </script>
</body>
</html>