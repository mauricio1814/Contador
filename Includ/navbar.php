<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../Estilos/principal.css">
</head>
<body>
    <!-- NAVBAR -->
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
                    <li class="nav-item"><a class="nav-link active" href="principal.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="declarar.php">Declarar Renta</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Historial</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Guías</a></li>
                    <li class="nav-item"><a class="nav-link disabled" aria-disabled="true">Perfil</a></li>
                </ul>
            </div>
        </div>
    </nav>
  <script>
        // Detecta la página actual y aplica la clase "active" al enlace correspondiente
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
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

