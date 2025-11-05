<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Declarar Renta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../Estilos/declarar.css">
</head>

<body>
    <?php include '../Includ/navbar.php'; ?>

    <!-- 🔹 CONTENIDO -->
    <main class="container form-section">
        <h1>Declarar Renta</h1>
        <div class="row mt-5">
            <div class="col-md-6 text-center">
                <label for="fecha" class="form-label fw-semibold">Fecha</label>
                <input type="date" id="fecha" class="form-control">
            </div>

            <div class="col-md-6 text-center">
                <label for="documentos" class="form-label fw-semibold">Documentos</label>
                <input type="file" id="documentos" class="form-control" multiple>
            </div>
        </div>

        <div class="mt-4" id="listaDocumentos"></div>

        <div class="row mt-4">
            <div class="col-md-6 text-center">
                <button id="btnCargar" class="btn btn-custom">
                    <i class="fa-solid fa-folder-open me-2"></i> Cargar Documentos
                </button>
            </div>
            <div class="col-md-6 text-center">
                <button id="btnSubir" class="btn btn-success">
                    <i class="fa-solid fa-cloud-arrow-up me-2"></i> Subir
                </button>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para manejar archivos -->
    <script>
        const documentosInput = document.getElementById('documentos');
        const listaDocumentos = document.getElementById('listaDocumentos');

        let archivosSeleccionados = [];

        documentosInput.addEventListener('change', () => {
            const nuevosArchivos = Array.from(documentosInput.files);

            nuevosArchivos.forEach(nuevo => {
                if (!archivosSeleccionados.some(a => a.name === nuevo.name)) {
                    archivosSeleccionados.push(nuevo);
                }
            });

            mostrarArchivos();
        });

        function mostrarArchivos() {
            listaDocumentos.innerHTML = '';

            archivosSeleccionados.forEach((archivo, index) => {
                const item = document.createElement('div');
                item.classList.add('archivo-item', 'd-flex', 'justify-content-between', 'align-items-center', 'p-2', 'border', 'rounded', 'mb-2');

                const nombre = document.createElement('span');
                nombre.textContent = archivo.name;

                const botonQuitar = document.createElement('button');
                botonQuitar.classList.add('btn', 'btn-sm', 'btn-outline-danger');
                botonQuitar.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                botonQuitar.onclick = () => {
                    archivosSeleccionados.splice(index, 1);
                    mostrarArchivos();
                };

                item.appendChild(nombre);
                item.appendChild(botonQuitar);
                listaDocumentos.appendChild(item);
            });
        }
    </script>

</body>

</html>