<?php
// config.php - Configuración centralizada de rutas

// Determinar la ruta base automáticamente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_path = dirname($_SERVER['SCRIPT_NAME']);

// Definir BASE_URL
define('BASE_URL', $protocol . '://' . $host . $script_path);
define('SITE_ROOT', realpath(dirname(__FILE__) . '/..'));

// Rutas de carpetas
define('VISTA_PATH', SITE_ROOT . '/Vista');
define('INCLUDES_PATH', SITE_ROOT . '/includes');
define('CONFIG_PATH', SITE_ROOT . '/config');
define('IMG_PATH', SITE_ROOT . '/IMG');
define('ESTILOS_PATH', SITE_ROOT . '/Estilos');

// Función para incluir archivos de forma segura
function includeSecure($file_path) {
    if (file_exists($file_path)) {
        include_once $file_path;
        return true;
    } else {
        error_log("Archivo no encontrado: " . $file_path);
        return false;
    }
}

// Función para redirigir
function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>