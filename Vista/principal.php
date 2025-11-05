<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renta</title>
    <link rel="icon" type="image/png" href="../IMG/chart-line-solid-full.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../Estilos/principal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>

<body>
    <?php include '../Includ/navbar.php'; ?>


    <!-- CONTENIDO PRINCIPAL -->
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

    <!-- SECCIÓN DE OPCIONES -->
    <div class="container icon-section mt-4">
        <div class="row justify-content-center">
            <div class="col-6 col-md-3">
                <div class="icon-card" data-href="declarar.php">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <h5 class="mt-3">Declarar Renta</h5>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="icon-card" data-href="historial.php">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <h5 class="mt-3">Historial de Declaraciones</h5>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="icon-card" data-href="guias.php">
                    <i class="fa-solid fa-book"></i>
                    <h5 class="mt-3">Guías Prácticas</h5>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="icon-card" data-href="soporte.php">
                    <i class="fa-solid fa-headset"></i>
                    <h5 class="mt-3">Soporte</h5>
                </div>
            </div>
        </div>
    </div>

    <!--  FOOTER -->
    <footer>
        <div class="container">
            <div class="alert alert-secondary d-flex justify-content-between align-items-center" role="alert">
                <span>Recuerda que la fecha límite para declarar es el <strong>15 de agosto de 2025</strong></span>
                <a href="#" class="btn btn-primary btn-sm">Ver más</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const iconCards = document.querySelectorAll(".icon-card");

            iconCards.forEach(card => {
                card.addEventListener("click", () => {
                    const target = card.getAttribute("data-href");
                    if (target) window.location.href = target;
                });
            });

            // 🔹 Variable renombrada para evitar conflicto
            const currentPath = window.location.pathname.split("/").pop();

            iconCards.forEach(card => {
                const page = card.getAttribute("data-href");
                if (page === currentPath) {
                    card.classList.add("active-card");
                } else {
                    card.classList.remove("active-card");
                }
            });
        });
    </script>


</body>

</html>