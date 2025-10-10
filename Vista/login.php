<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="/Estilos/login.css">
</head>

<body>
    <!-- 🔹 Barra superior derecha -->
    <header class="position-absolute top-0 end-0 p-3">
        <ul class="nav nav-pills justify-content-end">
            <li class="nav-item">
                <a class="nav-link" href="#">Iniciar Sesion</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">Registrarse</a>
            </li>
        </ul>
    </header>

    <!-- 🔹 Contenido principal -->
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100 justify-content-center align-items-center">

            <!-- Sección Izquierda -->
            <div class="col-md-6 text-center text-md-start px-5">
                <h2 class="fw-bold mb-4">Renta Segura</h2>
                <img src="/IMG/Picture.png" alt="Renta Segura" class="img-fluid w-50 mb-4">
                <p>Si no tienes una Cuenta <br> Puedes <a href="#" class="text-primary fw-semibold">Registrarse Aqui!</a></p>
            </div>

            <!-- Sección Derecha -->
            <div class="col-md-4">
                <div class="card shadow p-4 border-0 rounded-4">
                    <h5 class="text-center mb-4 fw-bold">Login</h5>

                    <form>
                        <div class="mb-3">
                            <input type="email" class="form-control form-control-lg rounded-3" placeholder="Ingresa el Email" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control form-control-lg rounded-3" placeholder="Ingresa el Password" required>
                        </div>
                        <div class="text-end mb-3">
                            <a href="#" class="small text-decoration-none text-primary">Recuperar Contraseña ?</a>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg rounded-3 shadow-sm">Iniciar Sesion</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>