<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin';
}

function isContador() {
    return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'contador';
}

function isContribuyente() {
    return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'usuario';
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        // Determinar la ruta correcta al login
        $current_dir = dirname($_SERVER['PHP_SELF']);
        
        // Si estamos en la carpeta Vista, subir un nivel
        if (strpos($current_dir, '/Vista') !== false) {
            header("Location: ../Vista/login.php");
        } else {
            header("Location: Vista/login.php");
        }
        exit();
    }
}

function redirectBasedOnRole() {
    if (isLoggedIn()) {
        $current_page = basename($_SERVER['PHP_SELF']);
        $current_dir = dirname($_SERVER['PHP_SELF']);
        
        $target_page = '';
        switch ($_SESSION['user_rol']) {
            case 'admin':
                $target_page = 'perfil-admin.php';
                break;
            case 'contador':
                $target_page = 'perfil-contador.php';
                break;
            case 'usuario':
                $target_page = 'principal.php';
                break;
            default:
                $target_page = 'principal.php';
        }
        
        // Solo redirigir si no estamos ya en la página objetivo
        if ($current_page !== $target_page) {
            // Si estamos en la raíz, ir a Vista
            if (strpos($current_dir, '/Vista') === false && $current_dir !== '/') {
                header("Location: Vista/" . $target_page);
            } else {
                header("Location: " . $target_page);
            }
            exit();
        }
    }
}

// Función para verificar admin
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: principal.php");
        exit();
    }
}

// Función para verificar contador
function requireContador() {
    if (!isContador()) {
        header("Location: principal.php");
        exit();
    }
}

// Función para verificar contribuyente
function requireContribuyente() {
    if (!isContribuyente()) {
        header("Location: principal.php");
        exit();
    }
}
?>