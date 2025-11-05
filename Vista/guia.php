<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guía de Declaración de Renta - Renta Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .page-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .container-main {
            max-width: 1200px;
            margin: 0 auto;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .step-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-top: 4px solid #667eea;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            cursor: pointer;
        }

        .step-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            border-top-color: #764ba2;
        }

        /* Cambiar números por iconos */
        .step-icon {
            font-size: 3.5rem;
            color: #667eea;
            margin-bottom: 20px;
            transition: 0.3s ease;
        }

        .step-card:hover .step-icon {
            color: #764ba2;
            transform: scale(1.1);
        }

        .step-content h3 {
            color: #333;
            font-weight: 700;
            margin-bottom: 12px;
            font-size: 1.3rem;
        }

        /* Texto más atractivo y llamativo */
        .step-content p {
            color: #666;
            line-height: 1.6;
            font-size: 0.95rem;
            margin: 0;
            font-style: italic;
        }

        /* Modal Styles */
        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
            padding: 30px;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 40px;
        }

        .modal-body h4 {
            color: #667eea;
            font-weight: 700;
            margin-top: 25px;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .modal-body h4:first-child {
            margin-top: 0;
        }

        .modal-body p {
            color: #555;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .doc-icon {
            color: #667eea;
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .document-item {
            background: #f8f9fa;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .document-item strong {
            color: #333;
        }

        .tips-section {
            background: linear-gradient(135deg, #ffa751 0%, #ffe259 100%);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .tips-section p {
            color: #333;
            margin: 0;
            font-weight: 500;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            justify-items: center;
        }
    </style>
</head>

<body>
    <?php include '../Includ/navbar.php'; ?>

    <div class="container-main">
        <div class="page-header">
            <h1>Guía de Declaración de Renta</h1>
            <p>Aprende paso a paso cómo hacer tu declaración de renta de forma correcta y segura</p>
        </div>

        <div class="steps-grid">
            <div class="step-card" data-bs-toggle="modal" data-bs-target="#modal1">
                <i class="fas fa-file-alt step-icon"></i>
                <div class="step-content">
                    <h3>Reunir Documentos</h3>
                    <p>Prepara todos los documentos que necesitas antes de comenzar. ¡No te pierdas ninguno!</p>
                </div>
            </div>

            <div class="step-card" data-bs-toggle="modal" data-bs-target="#modal2">
                <i class="fas fa-money-bill-wave step-icon"></i>
                <div class="step-content">
                    <h3>Registrar Ingresos</h3>
                    <p>Incluye todos tus ingresos del año. Sé detallado y honesto en esta sección.</p>
                </div>
            </div>

            <div class="step-card" data-bs-toggle="modal" data-bs-target="#modal3">
                <i class="fas fa-calculator step-icon"></i>
                <div class="step-content">
                    <h3>Calcular Deducciones</h3>
                    <p>Descubre qué gastos puedes deducir y maximiza tus beneficios fiscales.</p>
                </div>
            </div>

            <div class="step-card" data-bs-toggle="modal" data-bs-target="#modal4">
                <i class="fas fa-home step-icon"></i>
                <div class="step-content">
                    <h3>Bienes y Patrimonio</h3>
                    <p>Declara todos tus bienes inmuebles, vehículos y otras propiedades importantes.</p>
                </div>
            </div>

            <div class="step-card" data-bs-toggle="modal" data-bs-target="#modal5">
                <i class="fas fa-check-circle step-icon"></i>
                <div class="step-content">
                    <h3>Revisar y Validar</h3>
                    <p>Verifica toda la información antes de enviar. Evita errores costosos.</p>
                </div>
            </div>

            <div class="step-card" data-bs-toggle="modal" data-bs-target="#modal6">
                <i class="fas fa-paper-plane step-icon"></i>
                <div class="step-content">
                    <h3>Enviar Declaración</h3>
                    <p>Presenta tu declaración dentro del plazo establecido por la autoridad fiscal.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal1" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <i class="fas fa-file-alt" style="font-size: 2rem; margin-right: 15px;"></i>
                        <h5 class="modal-title d-inline">Documentos Necesarios</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Para hacer tu declaración de renta necesitas tener a mano los siguientes documentos:</p>

                    <h4><i class="fas fa-star doc-icon"></i>Documentos Indispensables</h4>
                    <div class="document-item">
                        <strong>Registro Único Tributario (RUT)</strong> - Tu identificación fiscal principal
                    </div>
                    <div class="document-item">
                        <strong>Cédula de Ciudadanía</strong> - Documento de identidad vigente
                    </div>
                    <div class="document-item">
                        <strong>Certificados de Ingresos</strong> - Formularios 1099, recibos de honorarios
                    </div>

                    <h4><i class="fas fa-file doc-icon"></i>Documentos Complementarios</h4>
                    <div class="document-item">
                        <strong>Extractos bancarios</strong> - Últimos 3 meses del año fiscal
                    </div>
                    <div class="document-item">
                        <strong>Facturas de gastos deducibles</strong> - Gastos médicos, educativos, etc.
                    </div>
                    <div class="document-item">
                        <strong>Certificados de inversiones</strong> - Si tienes inversiones en fondos o acciones
                    </div>

                    <div class="tips-section">
                        <p><i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Organiza todos tus documentos en carpetas por categoría para facilitar el proceso.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal2" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <i class="fas fa-money-bill-wave" style="font-size: 2rem; margin-right: 15px;"></i>
                        <h5 class="modal-title d-inline">Registro de Ingresos</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Registra todos los ingresos que obtuviste durante el año fiscal.</p>

                    <h4><i class="fas fa-briefcase doc-icon"></i>Tipos de Ingresos</h4>
                    <div class="document-item">
                        <strong>Ingresos por Salario</strong> - Compensación de tu empleador principal
                    </div>
                    <div class="document-item">
                        <strong>Ingresos por Honorarios</strong> - Trabajos independientes o consultoría
                    </div>
                    <div class="document-item">
                        <strong>Ingresos por Rentas</strong> - Alquileres de inmuebles o propiedades
                    </div>
                    <div class="document-item">
                        <strong>Ingresos por Intereses</strong> - Rendimientos de inversiones
                    </div>

                    <h4><i class="fas fa-info-circle doc-icon"></i>Información Importante</h4>
                    <p>Asegúrate de incluir TODOS tus ingresos, sin importar cuánto sean. La omisión de ingresos es considerada fraude fiscal.</p>

                    <div class="tips-section">
                        <p><i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Mantén un registro mensual de tus ingresos para no olvidar ninguno.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal3" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <i class="fas fa-calculator" style="font-size: 2rem; margin-right: 15px;"></i>
                        <h5 class="modal-title d-inline">Deducciones Permitidas</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Descubre qué gastos puedes deducir de tus ingresos para reducir tu carga tributaria.</p>

                    <h4><i class="fas fa-stethoscope doc-icon"></i>Gastos Médicos</h4>
                    <div class="document-item">
                        <strong>Consultas médicas</strong> - Procedimientos y consultas
                    </div>
                    <div class="document-item">
                        <strong>Medicinas</strong> - Medicamentos prescritos
                    </div>
                    <div class="document-item">
                        <strong>Seguros de salud</strong> - Pólizas y afiliaciones
                    </div>

                    <h4><i class="fas fa-book doc-icon"></i>Gastos Educativos</h4>
                    <div class="document-item">
                        <strong>Matrículas</strong> - Colegios y universidades
                    </div>
                    <div class="document-item">
                        <strong>Cursos y capacitaciones</strong> - Educación continua
                    </div>

                    <h4><i class="fas fa-home doc-icon"></i>Gastos de Vivienda</h4>
                    <div class="document-item">
                        <strong>Intereses hipotecarios</strong> - Si tienes un préstamo de vivienda
                    </div>

                    <div class="tips-section">
                        <p><i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Guarda todas tus facturas y recibos. Serán tu prueba ante la autoridad.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal4" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <i class="fas fa-home" style="font-size: 2rem; margin-right: 15px;"></i>
                        <h5 class="modal-title d-inline">Bienes y Patrimonio</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Declara todos tus bienes y propiedades al 31 de diciembre del año fiscal.</p>

                    <h4><i class="fas fa-building doc-icon"></i>Bienes Inmuebles</h4>
                    <div class="document-item">
                        <strong>Propiedades</strong> - Casas, apartamentos, lotes
                    </div>
                    <div class="document-item">
                        <strong>Escrituras</strong> - Documentos de propiedad
                    </div>

                    <h4><i class="fas fa-car doc-icon"></i>Vehículos</h4>
                    <div class="document-item">
                        <strong>Automóviles</strong> - Carros, motos, camiones
                    </div>
                    <div class="document-item">
                        <strong>Tarjetas de propiedad</strong> - Documentos de registro
                    </div>

                    <h4><i class="fas fa-coins doc-icon"></i>Otras Propiedades</h4>
                    <div class="document-item">
                        <strong>Inversiones</strong> - Acciones, bonos, fondos
                    </div>
                    <div class="document-item">
                        <strong>Cuentas bancarias</strong> - Saldos al cierre del año
                    </div>

                    <div class="tips-section">
                        <p><i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Actualiza los avalúos de tus propiedades para declaraciones más precisas.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal5" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <i class="fas fa-check-circle" style="font-size: 2rem; margin-right: 15px;"></i>
                        <h5 class="modal-title d-inline">Revisar y Validar</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Antes de enviar tu declaración, verifica cuidadosamente toda la información.</p>

                    <h4><i class="fas fa-search doc-icon"></i>Checklist de Revisión</h4>
                    <div class="document-item">
                        ✓ <strong>Información personal</strong> - Nombres, cédula y datos correctos
                    </div>
                    <div class="document-item">
                        ✓ <strong>Ingresos totales</strong> - Suma correcta de todos los ingresos
                    </div>
                    <div class="document-item">
                        ✓ <strong>Deducciones</strong> - Gastos válidos y justificados
                    </div>
                    <div class="document-item">
                        ✓ <strong>Patrimonio</strong> - Bienes declarados correctamente
                    </div>
                    <div class="document-item">
                        ✓ <strong>Cálculo de impuestos</strong> - Resultado correcto
                    </div>

                    <h4><i class="fas fa-exclamation-triangle doc-icon"></i>Errores Comunes</h4>
                    <p>Evita estos errores que pueden causar problemas:</p>
                    <ul style="color: #555;">
                        <li>Dejar campos en blanco sin justificación</li>
                        <li>Datos inconsistentes entre documentos</li>
                        <li>Deducciones no permitidas</li>
                        <li>Errores de digitación en montos</li>
                    </ul>

                    <div class="tips-section">
                        <p><i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Usa la herramienta de validación antes de enviar.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal6" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <i class="fas fa-paper-plane" style="font-size: 2rem; margin-right: 15px;"></i>
                        <h5 class="modal-title d-inline">Enviar Declaración</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Ahora que todo está verificado, es momento de presentar tu declaración ante la autoridad.</p>

                    <h4><i class="fas fa-calendar doc-icon"></i>Fechas Importantes</h4>
                    <div class="document-item">
                        <strong>Plazo de presentación:</strong> Abril - Mayo (varía según el país)
                    </div>
                    <div class="document-item">
                        <strong>Presentación tarde:</strong> Multas e intereses aplicables
                    </div>

                    <h4><i class="fas fa-mouse doc-icon"></i>Pasos para Enviar</h4>
                    <div style="margin: 15px 0;">
                        <p><strong>1. Accede a la plataforma oficial</strong> de tu autoridad fiscal</p>
                        <p><strong>2. Carga tus archivos</strong> en el formato solicitado</p>
                        <p><strong>3. Revisa antes de confirmar</strong> - Última oportunidad para verificar</p>
                        <p><strong>4. Confirma el envío</strong> - Recibirás un número de radicado</p>
                        <p><strong>5. Guarda el comprobante</strong> - Para tus registros</p>
                    </div>

                    <h4><i class="fas fa-info-circle doc-icon"></i>Después del Envío</h4>
                    <p>Una vez enviada tu declaración:</p>
                    <ul style="color: #555;">
                        <li>Recibirás un comprobante de presentación</li>
                        <li>Tu información será procesada automáticamente</li>
                        <li>Puedes revisar tu estado en línea</li>
                        <li>Si hay problemas, te contactarán</li>
                    </ul>

                    <div class="tips-section">
                        <p><i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Envía tu declaración antes del último día para evitar congestión en los servidores.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>