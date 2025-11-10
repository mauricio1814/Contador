<?php
include_once 'includes/session.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="Estilos/principal.css">
    <style>
        .navbar-custom {
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
            padding: 0.7rem 3rem;
        }
        .navbar-brand {
            font-weight: bold;
            color: #0d6efd !important;
        }
        .nav-item {
            margin-right: 10px;
        }
        .user-name {
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
            color: #0d6efd;
            font-weight: 500;
        }
        .user-name:hover {
            background-color: #f8f9fa;
            color: #0056d2;
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="principal.php">
                <i class="fa-solid fa-chart-line me-2"></i> Renta Segura
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'principal.php' ? 'active' : ''; ?>" 
                           href="principal.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'declarar.php' ? 'active' : ''; ?>" 
                           href="declarar.php">Declarar Renta</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'historial.php' ? 'active' : ''; ?>" 
                           href="historial.php">Historial</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'guias.php' ? 'active' : ''; ?>" 
                           href="guias.php">Guías</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'soporte.php' ? 'active' : ''; ?>" 
                           href="soporte.php">Soporte</a>
                    </li>
                    
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <span class="user-name" onclick="redirectToProfile()">
                                <i class="fas fa-user me-1"></i>
                                <?php echo $_SESSION['user_nombre']; ?>
                            </span>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Iniciar Sesión</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <script>
        function redirectToProfile() {
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    window.location.href = 'perfil-admin.php';
                <?php elseif (isContador()): ?>
                    window.location.href = 'perfil-contador.php';
                <?php else: ?>
                    window.location.href = 'perfil-usuario.php';
                <?php endif; ?>
            <?php else: ?>
                window.location.href = 'login.php';
            <?php endif; ?>
        }

        // Marcar como activo el enlace correspondiente según la página actual
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split("/").pop();
            const navLinks = document.querySelectorAll(".nav-link");
            
            navLinks.forEach(link => {
                const linkPage = link.getAttribute("href");
                if (linkPage === currentPage) {
                    link.classList.add("active");
                } else {
                    link.classList.remove("active");
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>