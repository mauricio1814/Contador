<?php
include_once 'config/database.php';
include_once 'includes/session.php';

redirectIfNotLoggedIn();
if (!isAdmin()) {
    header("Location: principal.php");
    exit();
}

// Verificar que se recibió el ID del usuario
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID de usuario no válido";
    header("Location: admin-usuarios.php");
    exit();
}

$id_usuario = $_GET['id'];
$database = new Database();
$db = $database->getConnection();

try {
    // Iniciar transacción
    $db->beginTransaction();
    
    // PRIMERO: Verificar si el usuario existe y obtener su información
    $query_verificar = "SELECT id_usuario, rol, nombre, apellido FROM usuario WHERE id_usuario = ? AND activo = 1";
    $stmt_verificar = $db->prepare($query_verificar);
    $stmt_verificar->execute([$id_usuario]);
    $usuario = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        $_SESSION['error'] = "Usuario no encontrado o ya fue eliminado";
        header("Location: admin-usuarios.php");
        exit();
    }
    
    // SEGUNDO: Si es un contador, primero liberar a los contribuyentes que tenía asignados
    if ($usuario['rol'] === 'contador') {
        $query_liberar_contribuyentes = "UPDATE usuario SET contador_asignado = NULL WHERE contador_asignado = ? AND activo = 1";
        $stmt_liberar = $db->prepare($query_liberar_contribuyentes);
        $stmt_liberar->execute([$id_usuario]);
    }
    
    // TERCERO: "Eliminar" el usuario (en realidad marcarlo como inactivo)
    $query_eliminar = "UPDATE usuario SET activo = 0 WHERE id_usuario = ?";
    $stmt_eliminar = $db->prepare($query_eliminar);
    $resultado = $stmt_eliminar->execute([$id_usuario]);
    
    if ($resultado) {
        // Confirmar la transacción
        $db->commit();
        
        $_SESSION['success'] = "Usuario " . $usuario['nombre'] . " " . $usuario['apellido'] . " eliminado correctamente";
        
        // Registrar la acción en logs si tienes una tabla de logs
        // $query_log = "INSERT INTO logs (usuario_id, accion, fecha) VALUES (?, ?, NOW())";
        // $stmt_log = $db->prepare($query_log);
        // $stmt_log->execute([$_SESSION['user_id'], "Eliminó usuario: " . $usuario['nombre'] . " " . $usuario['apellido']]);
        
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

// Redirigir de vuelta a la gestión de usuarios
header("Location: admin-usuarios.php");
exit();
?>