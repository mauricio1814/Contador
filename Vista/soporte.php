<?php
include_once '../config/database.php';
include_once '../includes/session.php';

redirectIfNotLoggedIn();

$database = new Database();
$db = $database->getConnection();

$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_usuario = $_SESSION['user_id'];
        $nombre_completo = $_POST['nombre'] ?? '';
        $correo = $_POST['email'] ?? '';
        $categoria = $_POST['categoria'] ?? '';
        $severidad = $_POST['severidad'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $pasos_reproducir = $_POST['pasos'] ?? '';
        $desea_contacto = isset($_POST['contacto']) ? 1 : 0;

        // Validar campos requeridos
        if (empty($nombre_completo) || empty($correo) || empty($categoria) || empty($severidad) || empty($descripcion)) {
            throw new Exception('Todos los campos marcados como requeridos deben ser completados.');
        }

        // Insertar en la base de datos
        $query = "INSERT INTO soporte (id_usuario, nombre_completo, correo, categoria, severidad, descripcion, pasos_reproducir, desea_contacto) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$id_usuario, $nombre_completo, $correo, $categoria, $severidad, $descripcion, $pasos_reproducir, $desea_contacto]);

        $mensaje = '¡Gracias por reportar el problema! Hemos recibido tu queja. Te contactaremos pronto.';
        $tipo_mensaje = 'success';

    } catch (Exception $e) {
        $mensaje = 'Error al enviar el reporte: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte - Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #5B6EFF;
            --secondary: #FF6B6B;
            --success: #10B981;
            --light-bg: #F8FAFC;
            --dark-text: #1F2937;
        }

        * {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f8f9fa;
            min-height: 100vh;
            color: var(--dark-text);
        }
        /* Header Section */
        .header-section {
            color: black;
            padding: 4rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }

        .header-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .header-section p {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        /* Support Cards */
        .support-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary);
        }

        .support-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .support-card h3 {
            color: var(--primary);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .support-card p {
            color: #6B7280;
            line-height: 1.6;
        }

        /* Form Section */
        .form-section {
            background: white;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .form-section h2 {
            color: var(--dark-text);
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid #E5E7EB;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(91, 110, 255, 0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: #9CA3AF;
        }

        .form-check {
            margin-bottom: 1rem;
        }

        .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
            margin-top: 0.2rem;
            border: 2px solid #E5E7EB;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        /* Submit Button */
        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, #7C3AED 100%);
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            cursor: pointer;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(91, 110, 255, 0.3);
            color: white;
        }

        /* Alert Messages */
        .alert-success {
            background: #ECFDF5;
            border: 2px solid var(--success);
            color: #065F46;
            border-radius: 8px;
            padding: 1rem;
        }

        .alert-error {
            background: #FEF2F2;
            border: 2px solid var(--secondary);
            color: #7F1D1D;
            border-radius: 8px;
            padding: 1rem;
        }

        /* FAQ Section */
        .faq-section {
            margin-top: 3rem;
        }

        .accordion-button {
            background: white;
            border: 2px solid #E5E7EB;
            color: var(--dark-text);
            font-weight: 600;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .accordion-button:not(.collapsed) {
            background: linear-gradient(135deg, var(--primary) 0%, #7C3AED 100%);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 4px 15px rgba(91, 110, 255, 0.2);
        }

        .accordion-body {
            border: 2px solid #E5E7EB;
            border-top: none;
            border-radius: 0 0 8px 8px;
            padding: 1.5rem;
            line-height: 1.6;
        }

        /* Footer */
        .footer-custom {
            background: white;
            border-top: 1px solid #E5E7EB;
            padding: 2rem 0;
            margin-top: 3rem;
            text-align: center;
            color: #6B7280;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-section h1 {
                font-size: 1.8rem;
            }

            .form-section {
                padding: 1.5rem;
            }

            .support-card {
                padding: 1.5rem;
            }
        }
        
        .page-header {
        text-align: center;
        margin-bottom: 50px;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #000000ff;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #666;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>   

    <!-- Header -->
    <div class="header-section">
        <div class="page-header" >
            <h1><i class="fas fa-headset"></i> Centro de Soporte</h1>
            <p>¿Tienes problemas? Estamos aquí para ayudarte. Cuéntanos qué pasó y lo resolveremos rápido.</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Mostrar mensajes -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Info Cards 
            <div class="col-lg-4 col-md-6">
                <div class="support-card">
                    <h3><i class="fas fa-clock"></i> Respuesta Rápida</h3>
                    <p>Nuestro equipo responde todas las quejas en máximo 24 horas hábiles. Tu satisfacción es nuestra prioridad.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="support-card">
                    <h3><i class="fas fa-shield-alt"></i> Datos Seguros</h3>
                    <p>Tu información es 100% confidencial. Utilizamos encriptación de nivel bancario para proteger tus datos.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="support-card">
                    <h3><i class="fas fa-phone"></i> Contacto Directo</h3>
                    <p>Si prefieres, puedes llamarnos al +57 (1) 3456-7890 o escribirnos a soporte@rentasegura.com</p>
                </div>
            </div>
        </div>
        -->

        <!-- Form Section -->
        <div class="form-section">
            <h2>Reportar un Problema</h2>
            <form id="supportForm" method="POST" action="">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?php echo $_SESSION['user_nombre'] . ' ' . ($_SESSION['user_apellido'] ?? ''); ?>" 
                                   placeholder="Juan Pérez" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo $_SESSION['user_email'] ?? ''; ?>" 
                                   placeholder="juan@example.com" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="categoria" class="form-label">Categoría del Problema</label>
                    <select class="form-select" id="categoria" name="categoria" required>
                        <option value="">Selecciona una categoría</option>
                        <option value="bug">Error o Fallo Técnico</option>
                        <option value="interfaz">Problema en la Interfaz</option>
                        <option value="funcionalidad">Funcionalidad No Disponible</option>
                        <option value="datos">Problema con mis Datos</option>
                        <option value="acceso">Problema de Acceso/Login</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="severidad" class="form-label">Nivel de Severidad</label>
                    <select class="form-select" id="severidad" name="severidad" required>
                        <option value="">Selecciona el nivel</option>
                        <option value="baja">Baja - No afecta mucho</option>
                        <option value="media">Media - Afecta el uso</option>
                        <option value="alta">Alta - No puedo usar la app</option>
                        <option value="critica">Crítica - Pérdida de datos</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="descripcion" class="form-label">Descripción del Problema</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="5" 
                              placeholder="Describe detalladamente qué pasó, cuándo ocurrió y qué estabas haciendo..." required></textarea>
                </div>


                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Enviar Queja
                </button>
            </form>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section">
            <h2 class="mb-4" style="text-align: center; font-weight: 700; color: var(--dark-text);">Preguntas Frecuentes</h2>
            
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            <i class="fas fa-question-circle" style="margin-right: 10px;"></i> ¿Cuánto tiempo tarda en responder el soporte?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Nuestro equipo responde dentro de 24 horas hábiles. Para problemas críticos, respondemos en máximo 2 horas durante el horario de atención (8 AM - 6 PM, lunes a viernes).
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            <i class="fas fa-question-circle" style="margin-right: 10px;"></i> ¿Cómo puedo recuperar mi contraseña?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            En la página de login, haz clic en "¿Olvidaste tu contraseña?". Recibirás un correo con instrucciones para crear una nueva contraseña en 5 minutos.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            <i class="fas fa-question-circle" style="margin-right: 10px;"></i> ¿Es seguro compartir mi información?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Sí, utilizamos encriptación SSL de 256 bits y cumplimos con todas las normativas de protección de datos. Tu información nunca será compartida con terceros.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            <i class="fas fa-question-circle" style="margin-right: 10px;"></i> ¿Qué navegadores son compatibles?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Renta Segura es compatible con Chrome, Firefox, Safari, Edge y todos los navegadores modernos. Recomendamos usar la versión más reciente para mejor experiencia.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                            <i class="fas fa-question-circle" style="margin-right: 10px;"></i> ¿Cómo descargo mi certificado de declaración?
                        </button>
                    </h2>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            En la sección "Historial", busca tu declaración aprobada y haz clic en "Ver más" o el icono de descarga. El certificado se descargará en formato PDF.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer 
    <div class="footer-custom">
        <div class="container">
            <p>&copy; 2025 Renta Segura. Todos los derechos reservados.</p>
            <p style="font-size: 0.9rem; margin-top: 0.5rem;">Horario de atención: Lunes a Viernes 8 AM - 6 PM | Contacto: soporte@rentasegura.com | +57 (1) 3456-7890</p>
        </div>
    </div>
    -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Manejo del formulario
        document.getElementById('supportForm').addEventListener('submit', function(e) {
            // La validación y envío se maneja por PHP, este script es para UX adicional
            const mensajeDiv = document.createElement('div');
            mensajeDiv.innerHTML = `
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-spinner fa-spin me-2"></i> Procesando tu reporte...
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            this.parentNode.insertBefore(mensajeDiv, this);
        });
    </script>
</body>
</html>