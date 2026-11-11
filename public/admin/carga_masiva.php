<?php
/**
 * public/admin/carga_masiva.php
 * Carga masiva de aspirantes desde archivo CSV (HU-02).
 * Actualizado con Bootstrap 5 respetando la identidad institucional ITCA FEPADE.
 */
require_once '../../includes/auth.php';
verificar_rol('administrador');

require_once '../../config/database.php';

$mensaje_exito = '';
$mensaje_error = '';
$detalles_carga = null;

// --- 1. Procesar el archivo CSV ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_csv'])) {
    $archivo = $_FILES['archivo_csv'];

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        switch ($archivo['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $mensaje_error = 'El archivo supera el tamaño máximo permitido por el servidor.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $mensaje_error = 'Por favor, selecciona un archivo CSV para cargar.';
                break;
            default:
                $mensaje_error = 'Ocurrió un error al subir el archivo (Código: ' . $archivo['error'] . ').';
                break;
        }
    } else {
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            $mensaje_error = 'Formato de archivo inválido. El archivo debe tener extensión <strong>.csv</strong>.';
        } else {
            // Abrimos el archivo en modo lectura
            $handle = fopen($archivo['tmp_name'], 'r');
            if ($handle === false) {
                $mensaje_error = 'No fue posible abrir el archivo para lectura.';
            } else {
                // Detectar y omitir BOM de UTF-8 si existe (\xEF\xBB\xBF)
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                // Detectamos el delimitador leyendo la primera línea (coma o punto y coma)
                $pos_actual = ftell($handle);
                $primera_linea = fgets($handle);
                $cant_pto_coma = substr_count($primera_linea, ';');
                $cant_coma = substr_count($primera_linea, ',');
                $delimitador = ($cant_pto_coma > $cant_coma) ? ';' : ',';

                // Regresamos el puntero al inicio de los datos
                fseek($handle, $pos_actual);

                // Contadores y registros de seguimiento
                $insertados = 0;
                $duplicados = 0;
                $errores = 0;
                $fila_num = 0;
                $bitacora = [];
                $exitosos = [];

                // Hash de contraseña por defecto para los nuevos aspirantes
                $password_default = password_hash('123456', PASSWORD_BCRYPT);

                // Preparación de consultas SQL
                $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE carnet = ? OR correo_institucional = ? LIMIT 1");
                $stmt_insert = $pdo->prepare("INSERT INTO usuarios (carnet, nombre_completo, telefono_fijo, telefono_movil, correo_institucional, correo_personal, password, rol) VALUES (?, ?, ?, ?, ?, ?, ?, 'estudiante')");
                $stmt_solicitud = $pdo->prepare("INSERT INTO solicitudes (id_usuario, estado) VALUES (?, 'borrador')");

                // Arreglos auxiliares para evitar duplicados en el mismo archivo
                $carnets_lote = [];
                $correos_lote = [];

                // Procesamos el CSV fila por fila
                while (($datos = fgetcsv($handle, 2000, $delimitador)) !== false) {
                    $fila_num++;

                    // Omitir la primera fila (encabezados de columnas)
                    if ($fila_num === 1) {
                        continue;
                    }

                    // Omitir filas totalmente vacías
                    $fila_limpia = array_filter($datos, fn($val) => trim((string)$val) !== '');
                    if (empty($fila_limpia)) {
                        continue;
                    }

                    // Extracción de datos respetando el orden obligatorio:
                    // [0] Código (Carnet), [1] Nombre completo, [2] Tel. fijo, [3] Tel. móvil, [4] Correo ITCA, [5] Correo personal
                    $carnet          = trim($datos[0] ?? '');
                    $nombre_completo = trim($datos[1] ?? '');
                    $tel_fijo        = trim($datos[2] ?? '');
                    $tel_movil       = trim($datos[3] ?? '');
                    $correo_itca     = trim($datos[4] ?? '');
                    $correo_personal = trim($datos[5] ?? '');

                    // Normalizar campos opcionales a null si están vacíos
                    $tel_fijo        = !empty($tel_fijo) ? $tel_fijo : null;
                    $tel_movil       = !empty($tel_movil) ? $tel_movil : null;
                    $correo_personal = !empty($correo_personal) ? $correo_personal : null;

                    // Validación de campos obligatorios
                    if (empty($carnet) || empty($nombre_completo) || empty($correo_itca)) {
                        $errores++;
                        $bitacora[] = [
                            'tipo'    => 'danger',
                            'icono'   => 'bi-x-circle-fill',
                            'fila'    => $fila_num,
                            'carnet'  => $carnet ?: '(No especificado)',
                            'detalle' => 'Campos obligatorios incompletos (requiere Código, Nombre completo y Correo ITCA).'
                        ];
                        continue;
                    }

                    // Validación básica de formato de correo ITCA
                    if (!filter_var($correo_itca, FILTER_VALIDATE_EMAIL)) {
                        $errores++;
                        $bitacora[] = [
                            'tipo'    => 'danger',
                            'icono'   => 'bi-exclamation-triangle-fill',
                            'fila'    => $fila_num,
                            'carnet'  => $carnet,
                            'detalle' => "El correo institucional '$correo_itca' tiene un formato inválido."
                        ];
                        continue;
                    }

                    // Detección de duplicados dentro del mismo archivo subido
                    $carnet_lower = strtolower($carnet);
                    $correo_lower = strtolower($correo_itca);

                    if (in_array($carnet_lower, $carnets_lote, true) || in_array($correo_lower, $correos_lote, true)) {
                        $duplicados++;
                        $bitacora[] = [
                            'tipo'    => 'warning',
                            'icono'   => 'bi-exclamation-circle-fill',
                            'fila'    => $fila_num,
                            'carnet'  => $carnet,
                            'detalle' => "Registro repetido dentro del mismo archivo CSV (Carnet: $carnet)."
                        ];
                        continue;
                    }

                    // Verificación de duplicados en la base de datos
                    try {
                        $stmt_check->execute([$carnet, $correo_itca]);
                        if ($stmt_check->fetch()) {
                            $duplicados++;
                            $bitacora[] = [
                                'tipo'    => 'warning',
                                'icono'   => 'bi-person-x-fill',
                                'fila'    => $fila_num,
                                'carnet'  => $carnet,
                                'detalle' => "El carnet '$carnet' o correo '$correo_itca' ya se encuentra registrado en el sistema."
                            ];
                            continue;
                        }

                        // Inserción del nuevo estudiante
                        $stmt_insert->execute([
                            $carnet,
                            $nombre_completo,
                            $tel_fijo,
                            $tel_movil,
                            $correo_itca,
                            $correo_personal,
                            $password_default
                        ]);
                        $id_nuevo_usuario = $pdo->lastInsertId();

                        // Creación automática de solicitud inicial en estado borrador
                        $stmt_solicitud->execute([$id_nuevo_usuario]);

                        // Registrar éxito
                        $insertados++;
                        $carnets_lote[] = $carnet_lower;
                        $correos_lote[] = $correo_lower;
                        $exitosos[] = [
                            'carnet' => $carnet,
                            'nombre' => $nombre_completo,
                            'correo' => $correo_itca,
                            'movil'  => $tel_movil ?: 'N/D'
                        ];

                    } catch (PDOException $e) {
                        $errores++;
                        $bitacora[] = [
                            'tipo'    => 'danger',
                            'icono'   => 'bi-database-fill-x',
                            'fila'    => $fila_num,
                            'carnet'  => $carnet,
                            'detalle' => 'Error al persistir registro en la base de datos.'
                        ];
                    }
                }

                fclose($handle);

                $total_procesados = $insertados + $duplicados + $errores;
                $mensaje_exito = "El archivo se ha procesado exitosamente.";
                $detalles_carga = [
                    'total'      => $total_procesados,
                    'insertados' => $insertados,
                    'duplicados' => $duplicados,
                    'errores'    => $errores,
                    'bitacora'   => $bitacora,
                    'exitosos'   => $exitosos,
                    'nombre_csv' => htmlspecialchars($archivo['name'])
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carga Masiva de Aspirantes - ITCA FEPADE</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Estilos base del sistema -->
    <link rel="stylesheet" href="/Sistema-de-Becas---ITCA-FEPADE/public/css/style.css">

    <style>
        /* ========================================================
           Ajustes Institucionales ITCA FEPADE & Bootstrap 5
           ======================================================== */
        :root {
            --itca-red: #B30000;
            --itca-red-dark: #8F0000;
            --itca-yellow: #FFCC00;
            --itca-yellow-light: #FFF8E1;
            --itca-gray-bg: #f4f6f9;
        }

        /* Respetar menú lateral institucional */
        .sidebar {
            width: 220px;
            background-color: #ffffff;
            border-right: 2px solid var(--itca-red);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        .sidebar .logo {
            background-color: var(--itca-red);
            color: #ffffff;
            padding: 15px 10px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            line-height: 1.3;
        }
        .sidebar nav a {
            display: block;
            padding: 12px 15px;
            color: #333333 !important;
            text-decoration: none !important;
            border-left: 4px solid transparent;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .sidebar nav a:hover {
            background-color: #f1f3f5 !important;
            border-left-color: var(--itca-yellow) !important;
            color: var(--itca-red) !important;
        }
        .sidebar nav a.activo {
            background-color: var(--itca-red) !important;
            color: #ffffff !important;
            border-left-color: var(--itca-yellow) !important;
            font-weight: 700;
        }

        /* Respetar barra superior institucional */
        .header {
            background-color: var(--itca-red);
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        .header .titulo-sistema {
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 0.5px;
        }
        .header .usuario-info {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 13px;
        }
        .header .usuario-info a {
            color: var(--itca-yellow) !important;
            text-decoration: none !important;
            font-weight: 700;
            transition: opacity 0.2s;
        }
        .header .usuario-info a:hover {
            opacity: 0.85;
            text-decoration: underline !important;
        }

        /* Contenedor de contenido */
        .main-content {
            background-color: var(--itca-gray-bg);
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .contenido-bootstrap {
            padding: 24px 30px;
            flex-grow: 1;
        }

        /* Botones institucionales */
        .btn-itca {
            background-color: var(--itca-red);
            border-color: var(--itca-red);
            color: #ffffff;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-itca:hover, .btn-itca:focus {
            background-color: var(--itca-red-dark);
            border-color: var(--itca-red-dark);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(179, 0, 0, 0.25);
        }
        .btn-outline-itca {
            color: var(--itca-red);
            border-color: var(--itca-red);
            font-weight: 600;
            background-color: #ffffff;
            transition: all 0.2s ease;
        }
        .btn-outline-itca:hover {
            background-color: var(--itca-red);
            border-color: var(--itca-red);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(179, 0, 0, 0.2);
        }

        /* Estilo de la tabla de referencia */
        .tabla-referencia-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
            border-top: 4px solid var(--itca-red);
        }
        .tabla-referencia thead th {
            background-color: var(--itca-red) !important;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 12.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 12px 14px;
            border: none;
            vertical-align: middle;
        }
        .tabla-referencia tbody td {
            font-size: 13px;
            padding: 11px 14px;
            vertical-align: middle;
            color: #333333;
        }
        .tabla-referencia tbody tr:hover {
            background-color: var(--itca-yellow-light) !important;
        }

        /* Zona de carga y arrastre (Drag & Drop) */
        .zona-arrastre-bs {
            border: 2px dashed var(--itca-red);
            border-radius: 12px;
            background-color: #ffffff;
            padding: 35px 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease-in-out;
            position: relative;
        }
        .zona-arrastre-bs:hover, .zona-arrastre-bs.arrastrando {
            background-color: #fff9e6;
            border-color: #d48806;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(179, 0, 0, 0.1);
        }
        .zona-arrastre-bs input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 5;
        }
        .icono-subida {
            font-size: 48px;
            color: var(--itca-red);
            display: inline-block;
            transition: transform 0.2s ease;
        }
        .zona-arrastre-bs:hover .icono-subida {
            transform: scale(1.1);
        }

        /* Tarjetas de métricas */
        .metric-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .metric-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="layout">
        <!-- Menú Lateral Institucional -->
        <?php include '../../includes/sidebar_admin.php'; ?>
        
        <!-- Área Principal de Trabajo -->
        <div class="main-content">
            <!-- Barra Superior Roja Institucional -->
            <?php include '../../includes/header.php'; ?>
            
            <div class="contenido-bootstrap">
                <!-- Título y Encabezado de la Vista -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h2 class="h4 fw-bold mb-1" style="color: var(--itca-red);">
                            <i class="bi bi-file-earmark-arrow-up-fill me-2"></i>Carga Masiva de Aspirantes
                        </h2>
                        <p class="text-secondary small mb-0">
                            Importa rápidamente listas de aspirantes a través de archivos de valores separados por coma (.CSV).
                        </p>
                    </div>
                    <nav aria-label="breadcrumb" class="mt-2 mt-md-0">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item active text-danger fw-semibold" aria-current="page">Carga Masiva</li>
                        </ol>
                    </nav>
                </div>

                <!-- Alerta de Notificación de Errores Globales -->
                <?php if ($mensaje_error): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-octagon-fill fs-4 me-3 text-danger"></i>
                        <div>
                            <strong>¡Atención!</strong> <?php echo $mensaje_error; ?>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php endif; ?>

                <!-- Alerta y Resumen del Procesamiento Exitoso -->
                <?php if ($mensaje_exito && $detalles_carga): ?>
                    <div class="card border-0 shadow-sm mb-4" style="border-left: 5px solid #198754 !important;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 me-3">
                                        <i class="bi bi-check2-circle fs-3"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title fw-bold text-success mb-1"><?php echo $mensaje_exito; ?></h5>
                                        <p class="text-muted small mb-0">Archivo procesado: <strong><?php echo $detalles_carga['nombre_csv']; ?></strong></p>
                                    </div>
                                </div>
                                <span class="badge bg-success bg-opacity-25 text-success px-3 py-2 rounded-pill fw-semibold">
                                    <i class="bi bi-clock-history me-1"></i> Finalizado
                                </span>
                            </div>

                            <!-- Métricas Rápidas en Cuadrícula -->
                            <div class="row g-3 my-2">
                                <div class="col-6 col-md-3">
                                    <div class="p-3 bg-light rounded-3 text-center border">
                                        <div class="text-secondary small fw-semibold text-uppercase">Total Evaluados</div>
                                        <div class="fs-3 fw-bold text-dark"><?php echo $detalles_carga['total']; ?></div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 bg-success bg-opacity-10 rounded-3 text-center border border-success-subtle">
                                        <div class="text-success small fw-semibold text-uppercase">Insertados</div>
                                        <div class="fs-3 fw-bold text-success"><?php echo $detalles_carga['insertados']; ?></div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 bg-warning bg-opacity-10 rounded-3 text-center border border-warning-subtle">
                                        <div class="text-warning-emphasis small fw-semibold text-uppercase">Duplicados</div>
                                        <div class="fs-3 fw-bold text-warning-emphasis"><?php echo $detalles_carga['duplicados']; ?></div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 bg-danger bg-opacity-10 rounded-3 text-center border border-danger-subtle">
                                        <div class="text-danger small fw-semibold text-uppercase">Con Errores</div>
                                        <div class="fs-3 fw-bold text-danger"><?php echo $detalles_carga['errores']; ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bitácora de Observaciones si las hay -->
                            <?php if (!empty($detalles_carga['bitacora'])): ?>
                                <div class="mt-4">
                                    <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBitacora" aria-expanded="false">
                                        <i class="bi bi-journal-text"></i> Ver detalles de advertencias y omisiones (<?php echo count($detalles_carga['bitacora']); ?>)
                                    </button>
                                    <div class="collapse mt-2" id="collapseBitacora">
                                        <div class="table-responsive border rounded-3 bg-white" style="max-height: 240px; overflow-y: auto;">
                                            <table class="table table-sm table-hover align-middle mb-0 small">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th class="py-2 ps-3">Fila</th>
                                                        <th class="py-2">Código</th>
                                                        <th class="py-2">Detalle de la Observación</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($detalles_carga['bitacora'] as $item): ?>
                                                        <tr>
                                                            <td class="ps-3"><span class="badge bg-secondary"><?php echo $item['fila']; ?></span></td>
                                                            <td class="font-monospace fw-semibold"><?php echo htmlspecialchars($item['carnet']); ?></td>
                                                            <td>
                                                                <i class="bi <?php echo $item['icono']; ?> text-<?php echo $item['tipo']; ?> me-1"></i>
                                                                <?php echo htmlspecialchars($item['detalle']); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Listado de Estudiantes Insertados con Éxito -->
                            <?php if (!empty($detalles_carga['exitosos'])): ?>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-success d-flex align-items-center gap-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExitosos" aria-expanded="false">
                                        <i class="bi bi-people-fill"></i> Ver estudiantes incorporados (<?php echo count($detalles_carga['exitosos']); ?>)
                                    </button>
                                    <div class="collapse mt-2" id="collapseExitosos">
                                        <div class="table-responsive border rounded-3 bg-white" style="max-height: 240px; overflow-y: auto;">
                                            <table class="table table-sm table-hover align-middle mb-0 small">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th class="py-2 ps-3">Carnet / Código</th>
                                                        <th class="py-2">Nombre Completo</th>
                                                        <th class="py-2">Correo ITCA</th>
                                                        <th class="py-2 text-center">Estado Inicial</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($detalles_carga['exitosos'] as $ex): ?>
                                                        <tr>
                                                            <td class="ps-3 font-monospace fw-bold text-danger"><?php echo htmlspecialchars($ex['carnet']); ?></td>
                                                            <td><?php echo htmlspecialchars($ex['nombre']); ?></td>
                                                            <td class="text-secondary"><?php echo htmlspecialchars($ex['correo']); ?></td>
                                                            <td class="text-center">
                                                                <span class="badge bg-secondary-subtle text-secondary border">Borrador</span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ========================================================
                     1. TABLA DE REFERENCIA SUPERIOR (Formato Obligatorio)
                     ======================================================== -->
                <div class="card tabla-referencia-card mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center">
                            <span class="badge p-2 me-2" style="background-color: var(--itca-red); color: #fff;">
                                <i class="bi bi-table fs-6"></i>
                            </span>
                            <div>
                                <h5 class="card-title mb-0 fw-bold text-dark">Formato de Columnas Obligatorio</h5>
                                <small class="text-muted">El archivo CSV debe cumplir con la siguiente estructura y encabezados exactos:</small>
                            </div>
                        </div>
                        <div>
                            <a href="descargar_plantilla.php" class="btn btn-outline-itca btn-sm shadow-sm d-inline-flex align-items-center gap-2">
                                <i class="bi bi-download"></i>
                                <span>Descargar Plantilla (.csv)</span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover tabla-referencia align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center" style="width: 13%;">
                                            Código
                                            <span class="badge bg-warning text-dark d-block mt-1" style="font-size: 10px;">Obligatorio</span>
                                        </th>
                                        <th scope="col" style="width: 25%;">
                                            Nombre completo
                                            <span class="badge bg-warning text-dark d-block mt-1" style="font-size: 10px;">Obligatorio</span>
                                        </th>
                                        <th scope="col" class="text-center" style="width: 14%;">
                                            Tel. fijo
                                            <span class="badge bg-light text-secondary d-block mt-1" style="font-size: 10px;">Opcional</span>
                                        </th>
                                        <th scope="col" class="text-center" style="width: 14%;">
                                            Tel. móvil
                                            <span class="badge bg-light text-secondary d-block mt-1" style="font-size: 10px;">Opcional</span>
                                        </th>
                                        <th scope="col" style="width: 18%;">
                                            Correo ITCA
                                            <span class="badge bg-warning text-dark d-block mt-1" style="font-size: 10px;">Obligatorio</span>
                                        </th>
                                        <th scope="col" style="width: 16%;">
                                            Correo personal
                                            <span class="badge bg-light text-secondary d-block mt-1" style="font-size: 10px;">Opcional</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center font-monospace fw-bold" style="color: var(--itca-red);">2024002</td>
                                        <td class="fw-medium text-dark">Juan Pérez García</td>
                                        <td class="text-center text-muted">2440-1234</td>
                                        <td class="text-center text-dark">7123-9876</td>
                                        <td>
                                            <span class="badge bg-light text-danger border border-danger-subtle font-monospace p-1">
                                                jperez@itca.edu.sv
                                            </span>
                                        </td>
                                        <td class="text-muted">juan@gmail.com</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center font-monospace fw-bold" style="color: var(--itca-red);">2024003</td>
                                        <td class="fw-medium text-dark">Ana Sofía Ramírez</td>
                                        <td class="text-center text-muted">2440-5678</td>
                                        <td class="text-center text-dark">7234-8765</td>
                                        <td>
                                            <span class="badge bg-light text-danger border border-danger-subtle font-monospace p-1">
                                                aramirez@itca.edu.sv
                                            </span>
                                        </td>
                                        <td class="text-muted">ana@gmail.com</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center font-monospace fw-bold" style="color: var(--itca-red);">2024004</td>
                                        <td class="fw-medium text-dark">Carlos Eduardo Mendoza</td>
                                        <td class="text-center text-muted">2440-9988</td>
                                        <td class="text-center text-dark">7890-1234</td>
                                        <td>
                                            <span class="badge bg-light text-danger border border-danger-subtle font-monospace p-1">
                                                cmendoza@itca.edu.sv
                                            </span>
                                        </td>
                                        <td class="text-muted">carlos.mendoza@gmail.com</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light py-2 px-3 border-top">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 small text-secondary">
                            <div>
                                <i class="bi bi-info-circle-fill text-danger me-1"></i>
                                <span>La <strong>primera fila</strong> del archivo corresponde a los títulos de las columnas. Se admiten delimitadores por <strong>coma (,)</strong> o <strong>punto y coma (;)</strong>.</span>
                            </div>
                            <div>
                                <span class="badge bg-white text-dark border">Codificación recomendada: UTF-8</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================
                     2. ZONA DE CARGA DEL ARCHIVO (Drag & Drop + Formulario)
                     ======================================================== -->
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center">
                                    <i class="bi bi-cloud-arrow-up-fill me-2" style="color: var(--itca-red);"></i>
                                    Subir Archivo de Aspirantes (.CSV)
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <form method="POST" action="" enctype="multipart/form-data" id="formCargaMasiva">
                                    <div class="zona-arrastre-bs mb-3" id="dropZone">
                                        <div class="mb-3">
                                            <i class="bi bi-file-earmark-spreadsheet icono-subida"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">
                                            Arrastra y suelta tu archivo .csv aquí
                                        </h6>
                                        <p class="text-muted small mb-3">
                                            o haz clic para explorar los archivos de tu computadora
                                        </p>
                                        
                                        <!-- Caja con el nombre del archivo seleccionado -->
                                        <div id="infoArchivo" class="d-none">
                                            <div class="d-inline-flex align-items-center bg-white border border-danger-subtle px-3 py-2 rounded-pill shadow-sm">
                                                <i class="bi bi-filetype-csv text-danger fs-5 me-2"></i>
                                                <span id="nombreArchivo" class="fw-semibold small text-dark me-2 text-truncate" style="max-width: 260px;"></span>
                                                <span id="tamanoArchivo" class="badge bg-secondary me-2"></span>
                                                <button type="button" class="btn-close btn-sm" id="btnQuitarArchivo" aria-label="Quitar archivo"></button>
                                            </div>
                                        </div>

                                        <input type="file" name="archivo_csv" id="archivo_csv" accept=".csv,text/csv" required>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center pt-2">
                                        <span class="text-muted small">
                                            <i class="bi bi-shield-check me-1 text-success"></i>Archivos procesados de manera segura.
                                        </span>
                                        <button type="submit" class="btn btn-itca px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2" id="btnSubmit">
                                            <i class="bi bi-upload"></i>
                                            <span id="btnSubmitTexto">Procesar e Importar</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Lateral Informativo -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-3 mb-3">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center">
                                    <i class="bi bi-lightbulb-fill text-warning me-2"></i>
                                    Reglas del Proceso
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <ul class="list-unstyled mb-0 small">
                                    <li class="d-flex align-items-start mb-3">
                                        <i class="bi bi-key-fill text-danger me-2 fs-6"></i>
                                        <div>
                                            <strong>Contraseña Asignada:</strong>
                                            <p class="text-muted mb-0">Cada usuario registrado recibirá por defecto la clave <code>123456</code>, la cual podrá actualizar al iniciar sesión.</p>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-3">
                                        <i class="bi bi-file-earmark-text-fill text-primary me-2 fs-6"></i>
                                        <div>
                                            <strong>Expediente de Beca:</strong>
                                            <p class="text-muted mb-0">El sistema creará inmediatamente una solicitud vinculada en estado <span class="badge bg-secondary-subtle text-dark border">Borrador</span>.</p>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-3">
                                        <i class="bi bi-shield-lock-fill text-success me-2 fs-6"></i>
                                        <div>
                                            <strong>Protección contra Duplicados:</strong>
                                            <p class="text-muted mb-0">Si el carnet o el correo institucional ya existen, la fila se omite para evitar inconsistencias.</p>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start">
                                        <i class="bi bi-person-badge-fill text-info me-2 fs-6"></i>
                                        <div>
                                            <strong>Rol de Usuario:</strong>
                                            <p class="text-muted mb-0">Todos los aspirantes importados recibirán el rol exclusivo de <strong>estudiante</strong>.</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Acceso Directo de Ayuda -->
                        <div class="p-3 bg-white rounded-3 border shadow-sm">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-question-circle-fill text-secondary fs-5"></i>
                                <span class="fw-bold small text-dark">¿Necesitas ayuda con el formato?</span>
                            </div>
                            <p class="text-muted small mb-2">
                                Puedes descargar la plantilla oficial con datos pre-rellenados para asegurar la compatibilidad total.
                            </p>
                            <a href="descargar_plantilla.php" class="btn btn-sm btn-outline-danger w-100">
                                <i class="bi bi-file-earmark-arrow-down me-1"></i> Obtener Plantilla Ejemplo
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inclusión de Scripts antes del cierre de body -->
            <!-- Bootstrap 5 Bundle JS (incluye Popper) -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

            <!-- Interacciones de la interfaz y Zona de Arrastre -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const dropZone = document.getElementById('dropZone');
                    const fileInput = document.getElementById('archivo_csv');
                    const infoArchivo = document.getElementById('infoArchivo');
                    const nombreArchivo = document.getElementById('nombreArchivo');
                    const tamanoArchivo = document.getElementById('tamanoArchivo');
                    const btnQuitar = document.getElementById('btnQuitarArchivo');
                    const formCarga = document.getElementById('formCargaMasiva');
                    const btnSubmit = document.getElementById('btnSubmit');
                    const btnSubmitTexto = document.getElementById('btnSubmitTexto');

                    // Función para formatear bytes a KB o MB
                    function formatearTamano(bytes) {
                        if (bytes === 0) return '0 Bytes';
                        const k = 1024;
                        const sizes = ['Bytes', 'KB', 'MB'];
                        const i = Math.floor(Math.log(bytes) / Math.log(k));
                        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                    }

                    // Manejo visual al seleccionar archivo
                    function actualizarVistaArchivo(file) {
                        if (file) {
                            const extension = file.name.split('.').pop().toLowerCase();
                            if (extension !== 'csv') {
                                alert('Por favor, selecciona un archivo válido con extensión .csv');
                                fileInput.value = '';
                                infoArchivo.classList.add('d-none');
                                return;
                            }
                            nombreArchivo.textContent = file.name;
                            tamanoArchivo.textContent = formatearTamano(file.size);
                            infoArchivo.classList.remove('d-none');
                        } else {
                            infoArchivo.classList.add('d-none');
                            nombreArchivo.textContent = '';
                            tamanoArchivo.textContent = '';
                        }
                    }

                    fileInput.addEventListener('change', function() {
                        if (this.files && this.files[0]) {
                            actualizarVistaArchivo(this.files[0]);
                        } else {
                            actualizarVistaArchivo(null);
                        }
                    });

                    // Botón para quitar el archivo seleccionado
                    btnQuitar.addEventListener('click', function(e) {
                        e.stopPropagation();
                        e.preventDefault();
                        fileInput.value = '';
                        actualizarVistaArchivo(null);
                    });

                    // Eventos de arrastre (Drag & Drop)
                    ['dragenter', 'dragover'].forEach(eventName => {
                        dropZone.addEventListener(eventName, function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            dropZone.classList.add('arrastrando');
                        }, false);
                    });

                    ['dragleave', 'drop'].forEach(eventName => {
                        dropZone.addEventListener(eventName, function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            dropZone.classList.remove('arrastrando');
                        }, false);
                    });

                    dropZone.addEventListener('drop', function(e) {
                        const dt = e.dataTransfer;
                        const files = dt.files;
                        if (files && files.length > 0) {
                            fileInput.files = files;
                            actualizarVistaArchivo(files[0]);
                        }
                    }, false);

                    // Indicador de carga al enviar el formulario
                    formCarga.addEventListener('submit', function() {
                        btnSubmit.disabled = true;
                        btnSubmitTexto.textContent = 'Procesando archivo...';
                        btnSubmit.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>');
                    });
                });
            </script>

            <!-- Pie de Página Institucional -->
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>
</body>
</html>
