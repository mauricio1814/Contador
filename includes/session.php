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
        header("Location: ../login.php");
        exit();
    }
}

function redirectBasedOnRole() {
    if (isLoggedIn()) {
        switch ($_SESSION['user_rol']) {
            case 'admin':
                header("Location: perfil-admin.php");
                break;
            case 'contador':
                header("Location: perfil-contador.php");
                break;
            case 'usuario':
                header("Location: principal.php");
                break;
            default:
                header("Location: principal.php");
        }
        exit();
    }
}
?>