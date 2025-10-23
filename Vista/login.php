<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../Estilos/login.css">
</head>

<body>
    <header class="position-absolute top-0 p-3 header-login">
        <ul class="nav nav-pills justify-content-end">
            <li class="nav-item">
                <a class="nav-link login me-3 active" href="#">Iniciar Sesión</a>
            </li>
            <li class="nav-item">
                <a class="nav-link register" href="#">Registrarse</a>
            </li>
        </ul>

    </header>

    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100 justify-content-center align-items-center">

            <div class="col-md-6 text-center text-md-start px-5">
                <h2 class="fw-bold mb-4">Renta Segura</h2>
                <img src="https://cdn-icons-png.flaticon.com/512/2331/2331941.png" alt="Renta Segura"
                    class="img-fluid w-50 mb-4">
                <p>Si no tienes una cuenta <br> puedes <a href="#" class="text-primary fw-semibold registro-aqui">Registrarse Aquí!</a></p>
            </div>

            <div class="col-md-4 position-relative">

                <!-- FORMULARIO LOGIN -->
                <div id="formLogin" class="card shadow p-4 border-0 rounded-4">
                    <h5 class="text-center mb-4 fw-bold">Login</h5>

                    <form>
                        <div class="mb-3">
                            <input type="email" class="form-control form-control-lg rounded-3"
                                placeholder="Ingresa el Email" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control form-control-lg rounded-3"
                                placeholder="Ingresa el Password" required>
                        </div>
                        <div class="text-end mb-3">
                            <a href="#" class="small text-decoration-none text-primary">Recuperar Contraseña ?</a>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg rounded-3 shadow-sm">Iniciar Sesión</button>
                        </div>
                    </form>
                </div>

                <!-- FORMULARIO REGISTRO -->
                <div id="formRegistro" class="card shadow border-2 rounded-3 p-4 d-none">
                    <h4 class="text-center fw-bold mb-4">Registro</h4>
                    <form>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Nombres :</label>
                                <input type="text" class="form-control form-control-sm rounded-3" placeholder="Enter your name.." required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Apellidos :</label>
                                <input type="text" class="form-control form-control-sm rounded-3" placeholder="Enter your name.." required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Email :</label>
                                <input type="email" class="form-control form-control-sm rounded-3" placeholder="info@xyz.com" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Telefono. :</label>
                                <input type="text" class="form-control form-control-sm rounded-3" placeholder="+91 - 98595 58000" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Constraseña :</label>
                                <input type="password" class="form-control form-control-sm rounded-3" placeholder="xxxxxxxx" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Confirmar contraseña :</label>
                                <input type="password" class="form-control form-control-sm rounded-3" placeholder="xxxxxxxx" required>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg w-50 rounded-3 shadow-sm">Registrarse</button>
                        </div>
                    </form>
                </div>


            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const linkLogin = document.querySelector('.nav-link.login');
            const linkRegister = document.querySelector('.nav-link.register');
            const formLogin = document.getElementById('formLogin');
            const formRegistro = document.getElementById('formRegistro');
            const registroAqui = document.querySelector('.registro-aqui'); // 👈 nuevo

            // función para cambiar de vista
            const mostrarRegistro = () => {
                formLogin.classList.add('d-none');
                formRegistro.classList.remove('d-none');
                linkRegister.classList.add('active');
                linkLogin.classList.remove('active');
            };

            const mostrarLogin = () => {
                formRegistro.classList.add('d-none');
                formLogin.classList.remove('d-none');
                linkLogin.classList.add('active');
                linkRegister.classList.remove('active');
            };

            // eventos de clic en los botones del header
            linkLogin.addEventListener('click', (e) => {
                e.preventDefault();
                mostrarLogin();
            });

            linkRegister.addEventListener('click', (e) => {
                e.preventDefault();
                mostrarRegistro();
            });

            if (registroAqui) {
                registroAqui.addEventListener('click', (e) => {
                    e.preventDefault();
                    mostrarRegistro();
                });
            }
        });
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>