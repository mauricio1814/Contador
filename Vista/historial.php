<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial - Renta Segura</title>
    <link rel="icon" type="image/png" href="../IMG/chart-line-solid-full.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        main {
            padding: 60px 15px;
        }

        .page-title {
            font-size: 42px;
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 40px;
            text-align: center;
        }

        .card {
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        table {
            margin-bottom: 0;
        }

        thead {
            background-color: #f1f3f5;
        }

        th {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            color: #495057;
            border-bottom: none;
            padding: 18px;
        }

        td {
            padding: 16px 18px;
            vertical-align: middle;
            font-size: 15px;
        }

        tbody tr:hover {
            background-color: #f8f9fa;
            transition: 0.3s;
        }

        .badge {
            font-size: 13px;
            font-weight: 600;
            padding: 8px 14px;
        }

        .btn-outline-primary {
            border: 1.5px solid #0d6efd;
            transition: all 0.2s ease;
        }

        .btn-outline-primary:hover {
            background-color: #0d6efd;
            color: white;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
        }

        .pagination-btn {
            width: 42px;
            height: 42px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .pagination-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 32px;
            }

            td,
            th {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <?php include '../Includ/navbar.php'; ?>

    <main class="bg-light min-vh-100">
        <div class="container">
            <h1 class="page-title"><i class="fa-solid fa-clock-rotate-left me-2"></i>Historial</h1>

            <div class="card border-0 overflow-hidden">
                <div class="table-responsive px-2">
                    <table class="table align-middle text-center mb-0">
                        <thead>
                            <tr>
                                <th>Año</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-semibold">2025</td>
                                <td><span class="badge bg-warning text-dark">Presentada</span></td>
                                <td>
                                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="fa-solid fa-file-invoice me-2"></i>Ver factura
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">2024</td>
                                <td><span class="badge bg-success text-white">Aprobada</span></td>
                                <td>
                                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="fa-solid fa-file-invoice me-2"></i>Ver factura
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">2023</td>
                                <td><span class="badge bg-success text-white">Aprobada</span></td>
                                <td>
                                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="fa-solid fa-file-invoice me-2"></i>Ver factura
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">2022</td>
                                <td><span class="badge bg-success text-white">Aprobada</span></td>
                                <td>
                                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="fa-solid fa-file-invoice me-2"></i>Ver factura
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-center mt-4 gap-3">
                <button class="btn btn-light border pagination-btn shadow-sm">
                    <i class="fa-solid fa-angle-left"></i>
                </button>
                <button class="btn btn-primary pagination-btn shadow">
                    <i class="fa-solid fa-angle-right text-white"></i>
                </button>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>