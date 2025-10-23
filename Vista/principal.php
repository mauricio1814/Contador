<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../Estilos/principal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>

<body>
    <!-- 🔹 NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fa-solid fa-chart-line me-2"></i> Renta Segura
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Declarar Renta</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Historial</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Guías</a></li>
                    <li class="nav-item"><a class="nav-link disabled" aria-disabled="true">Perfil</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 🔹 CONTENIDO PRINCIPAL -->
    <main class="container hero">
        <div class="hero-text">
            <h2>Hola, Wilson 👋</h2>
            <h3 class="text-primary">Bienvenido a Renta Segura</h3>
            <p>Administra tu declaración de <span class="text-primary fw-bold">forma</span> fácil, rápida y segura.</p>
        </div>
        <div>
            <i class="fa-solid fa-user-circle"></i>
        </div>
    </main>

    <!-- 🔹 SECCIÓN DE OPCIONES -->
    <div class="container icon-section">
        <div class="icon-card">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            <h5 class="mt-3">Declarar Renta</h5>
        </div>
        <div class="icon-card">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <h5 class="mt-3">Historial de Declaraciones</h5>
        </div>
        <div class="icon-card">
            <i class="fa-solid fa-book"></i>
            <h5 class="mt-3">Guías Prácticas</h5>
        </div>
        <div class="icon-card">
            <i class="fa-solid fa-headset"></i>
            <h5 class="mt-3">Soporte</h5>
        </div>
    </div>

    <!-- 🔹 FOOTER -->
    <footer>
        <div class="container">
            <div class="alert alert-secondary d-flex justify-content-between align-items-center" role="alert">
                <span>Recuerda que la fecha límite para declarar es el <strong>15 de agosto de 2025</strong></span>
                <a href="#" class="btn btn-primary btn-sm">Ver más</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>