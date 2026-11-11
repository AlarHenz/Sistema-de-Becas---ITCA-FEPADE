<?php
/**
 * public/admin/carga_masiva.php
 * Carga masiva de aspirantes desde archivo Excel (.xlsx) y CSV.
 * Procesamiento nativo OpenXML con inserción masiva mediante PDO en MySQL.
 * Interfaz con Bootstrap 5 respetando la identidad institucional ITCA FEPADE.
 */
require_once '../../includes/auth.php';
verificar_rol('administrador');

require_once '../../config/database.php';
require_once '../../includes/xlsx_reader.php';

$mensaje_exito = '';
$mensaje_error = '';
$detalles_carga = null;

// --- 1. Procesar el archivo subido (.xlsx o .csv) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_FILES['archivo_excel']) || isset($_FILES['archivo_xlsx']) || isset($_FILES['archivo_csv']))) {
    $archivo = $_FILES['archivo_excel'] ?? ($_FILES['archivo_xlsx'] ?? $_FILES['archivo_csv']);

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        switch ($archivo['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $mensaje_error = 'El archivo supera el tamaño máximo permitido por el servidor.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $mensaje_error = 'Por favor, selecciona un archivo Excel (.xlsx) para cargar.';
                break;
            default:
                $mensaje_error = 'Ocurrió un error al subir el archivo (Código de error: ' . $archivo['error'] . ').';
                break;
        }
    } else {
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $filas_datos = [];

        // Validar formato admitido
        if (!in_array($extension, ['xlsx', 'csv'], true)) {
            $mensaje_error = 'Formato no compatible. Por favor sube un archivo con formato <strong>.xlsx</strong> (Microsoft Excel) o <strong>.csv</strong>.';
        } else {
            // --- Lectura del archivo según su extensión ---
            if ($extension === 'xlsx') {
                // Lectura nativa OpenXML mediante ZipArchive y SimpleXML
                $filas_datos = leer_archivo_xlsx($archivo['tmp_name'], 6);
                if ($filas_datos === false) {
                    $mensaje_error = 'No fue posible interpretar el archivo .xlsx. Asegúrate de que sea un archivo de Excel válido y no esté protegido por contraseña.';
                }
            } else {
                // Fallback para archivo .csv
                $handle = fopen($archivo['tmp_name'], 'r');
                if ($handle !== false) {
                    // Omitir BOM de UTF-8
                    $bom = fread($handle, 3);
                    if ($bom !== "\xEF\xBB\xBF") {
                        rewind($handle);
                    }
                    // Detectar delimitador
                    $pos = ftell($handle);
                    $primera_linea = fgets($handle);
                    $delimitador = (substr_count($primera_linea, ';') > substr_count($primera_linea, ',')) ? ';' : ',';
                    fseek($handle, $pos);

                    while (($datos_linea = fgetcsv($handle, 2000, $delimitador)) !== false) {
                        $fila_norm = [];
                        for ($c = 0; $c < 6; $c++) {
                            $fila_norm[$c] = trim((string)($datos_linea[$c] ?? ''));
                        }
                        $filas_datos[] = $fila_norm;
                    }
                    fclose($handle);
                } else {
                    $mensaje_error = 'No fue posible abrir el archivo CSV para lectura.';
                }
            }

            // --- Procesamiento e inserción masiva en MySQL mediante PDO ---
            if (empty($mensaje_error)) {
                if (empty($filas_datos) || count($filas_datos) <= 1) {
                    $mensaje_error = 'El archivo no contiene registros de datos para procesar (únicamente encabezados o vacío).';
                } else {
                    $insertados = 0;
                    $duplicados = 0;
                    $errores = 0;
                    $fila_num = 0;
                    $bitacora = [];
                    $exitosos = [];

                    // Hash único precalculado para optimizar el rendimiento del bucle
                    $password_default = password_hash('123456', PASSWORD_BCRYPT);

                    // Consultas preparadas de alto rendimiento con PDO
                    $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE carnet = ? OR correo_institucional = ? LIMIT 1");
                    $stmt_insert = $pdo->prepare("INSERT INTO usuarios (carnet, nombre_completo, telefono_fijo, telefono_movil, correo_institucional, correo_personal, password, rol) VALUES (?, ?, ?, ?, ?, ?, ?, 'estudiante')");
                    $stmt_solicitud = $pdo->prepare("INSERT INTO solicitudes (id_usuario, estado) VALUES (?, 'borrador')");

                    // Control de duplicados en el mismo archivo
                    $carnets_en_lote = [];
                    $correos_en_lote = [];

                    // Iniciamos transacción para acelerar inserciones masivas y asegurar consistencia
                    $pdo->beginTransaction();

                    try {
                        foreach ($filas_datos as $datos) {
                            $fila_num++;

                            // Omitir la fila 1 (encabezados de columnas)
                            if ($fila_num === 1) {
                                continue;
                            }

                            // Omitir filas en blanco
                            if (empty(array_filter($datos, fn($v) => trim((string)$v) !== ''))) {
                                continue;
                            }

                            // Mapeo ordenado de columnas:
                            // [0] Código/Carnet, [1] Nombre completo, [2] Tel. fijo, [3] Tel. móvil, [4] Correo ITCA, [5] Correo personal
                            $carnet          = trim($datos[0] ?? '');
                            $nombre_completo = trim($datos[1] ?? '');
                            $tel_fijo        = trim($datos[2] ?? '');
                            $tel_movil       = trim($datos[3] ?? '');
                            $correo_itca     = trim($datos[4] ?? '');
                            $correo_personal = trim($datos[5] ?? '');

                            // Normalizar campos opcionales
                            $tel_fijo        = !empty($tel_fijo) ? $tel_fijo : null;
                            $tel_movil       = !empty($tel_movil) ? $tel_movil : null;
                            $correo_personal = !empty($correo_personal) ? $correo_personal : null;

                            // 1. Validar obligatoriedad
                            if (empty($carnet) || empty($nombre_completo) || empty($correo_itca)) {
                                $errores++;
                                $bitacora[] = [
                                    'tipo'    => 'danger',
                                    'icono'   => 'bi-x-circle-fill',
                                    'fila'    => $fila_num,
                                    'carnet'  => $carnet ?: '(No especificado)',
                                    'detalle' => 'Faltan campos obligatorios (requiere Código, Nombre completo y Correo ITCA).'
                                ];
                                continue;
                            }

                            // 2. Validar formato de correo institucional
                            if (!filter_var($correo_itca, FILTER_VALIDATE_EMAIL)) {
                                $errores++;
                                $bitacora[] = [
                                    'tipo'    => 'danger',
                                    'icono'   => 'bi-envelope-exclamation-fill',
                                    'fila'    => $fila_num,
                                    'carnet'  => $carnet,
                                    'detalle' => "Formato de correo no válido: '$correo_itca'."
                                ];
                                continue;
                            }

                            // 3. Validar duplicidad interna dentro del archivo
                            $c_lower = strtolower($carnet);
                            $m_lower = strtolower($correo_itca);

                            if (in_array($c_lower, $carnets_en_lote, true) || in_array($m_lower, $correos_en_lote, true)) {
                                $duplicados++;
                                $bitacora[] = [
                                    'tipo'    => 'warning',
                                    'icono'   => 'bi-copy',
                                    'fila'    => $fila_num,
                                    'carnet'  => $carnet,
                                    'detalle' => "Registro duplicado dentro del mismo archivo (Carnet: $carnet)."
                                ];
                                continue;
                            }

                            // 4. Validar existencia previa en la base de datos MySQL
                            $stmt_check->execute([$carnet, $correo_itca]);
                            if ($stmt_check->fetch()) {
                                $duplicados++;
                                $bitacora[] = [
                                    'tipo'    => 'warning',
                                    'icono'   => 'bi-person-exclamation',
                                    'fila'    => $fila_num,
                                    'carnet'  => $carnet,
                                    'detalle' => "El carnet '$carnet' o correo '$correo_itca' ya se encuentra registrado."
                                ];
                                continue;
                            }

                            // 5. Inserción masiva de usuario y creación de solicitud inicial
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

                            $stmt_solicitud->execute([$id_nuevo_usuario]);

                            $insertados++;
                            $carnets_en_lote[] = $c_lower;
                            $correos_en_lote[] = $m_lower;

                            $exitosos[] = [
                                'carnet' => $carnet,
                                'nombre' => $nombre_completo,
                                'correo' => $correo_itca,
                                'movil'  => $tel_movil ?: 'N/D'
                            ];
                        }

                        // Confirmar todas las inserciones
                        $pdo->commit();

                        $total_evaluados = $insertados + $duplicados + $errores;
                        $mensaje_exito = "Archivo procesado e importado con éxito.";
                        $detalles_carga = [
                            'total'          => $total_evaluados,
                            'insertados'     => $insertados,
                            'duplicados'     => $duplicados,
                            'errores'        => $errores,
                            'bitacora'       => $bitacora,
                            'exitosos'       => $exitosos,
                            'nombre_archivo' => htmlspecialchars($archivo['name']),
                            'formato'        => strtoupper($extension)
                        ];

                    } catch (PDOException $ex) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        $mensaje_error = 'Ocurrió un error en la base de datos durante la importación: ' . $ex->getMessage();
                    }
                }
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
    <title>Carga Masiva de Aspirantes (.xlsx) - ITCA FEPADE</title>
    
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
            --itca-red: #b22a14;
            --itca-red-dark: #921a08;
            --itca-red-active: #a82015;
            --itca-gray-bg: #f4f4f4;
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
            box-shadow: 0 4px 10px rgba(178, 42, 20, 0.25);
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
            box-shadow: 0 4px 10px rgba(178, 42, 20, 0.2);
        }

        /* Estilo de la tabla de referencia */
        .tabla-referencia-card {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            overflow: hidden;
            border-top: 4px solid var(--itca-red) !important;
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
            color: #374151;
        }
        .tabla-referencia tbody tr:hover {
            background-color: #fbfbfb !important;
        }

        /* Zona de carga para Excel (.xlsx) */
        .zona-arrastre-xlsx {
            border: 2px dashed var(--itca-red);
            border-radius: 8px;
            background-color: #ffffff;
            padding: 38px 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease-in-out;
            position: relative;
        }
        .zona-arrastre-xlsx:hover, .zona-arrastre-xlsx.arrastrando {
            background-color: #fff9e6;
            border-color: var(--itca-red-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(178, 42, 20, 0.12);
        }
        .zona-arrastre-xlsx input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 5;
        }
        .icono-excel {
            font-size: 52px;
            color: var(--itca-red);
            display: inline-block;
            transition: transform 0.2s ease;
        }
        .zona-arrastre-xlsx:hover .icono-excel {
            transform: scale(1.12);
        }
    </style>
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
                            <i class="bi bi-file-earmark-excel-fill text-success me-2"></i>Carga Masiva de Aspirantes (.xlsx)
                        </h2>
                        <p class="text-secondary small mb-0">
                            Importación directa a base de datos MySQL desde hojas de cálculo Microsoft Excel (.XLSX) con consultas preparadas PDO.
                        </p>
                    </div>
                    <nav aria-label="breadcrumb" class="mt-2 mt-md-0">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item active text-danger fw-semibold" aria-current="page">Carga Masiva</li>
                        </ol>
                    </nav>
                </div>

                <!-- Notificación de Error -->
                <?php if ($mensaje_error): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-octagon-fill fs-4 me-3 text-danger"></i>
                        <div>
                            <strong>Error en la importación:</strong> <?php echo $mensaje_error; ?>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php endif; ?>

                <!-- Resumen de Procesamiento Masivo Exitoso -->
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
                                        <p class="text-muted small mb-0">
                                            Archivo: <strong><?php echo $detalles_carga['nombre_archivo']; ?></strong>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-2">Formato <?php echo $detalles_carga['formato']; ?></span>
                                        </p>
                                    </div>
                                </div>
                                <span class="badge bg-success bg-opacity-25 text-success px-3 py-2 rounded-pill fw-semibold">
                                    <i class="bi bi-database-check me-1"></i> Transacción MySQL Completada
                                </span>
                            </div>

                            <!-- Métricas de Carga Masiva -->
                            <div class="row g-3 my-2">
                                <div class="col-6 col-md-3">
                                    <div class="p-3 bg-light rounded-3 text-center border">
                                        <div class="text-secondary small fw-semibold text-uppercase">Total Evaluados</div>
                                        <div class="fs-3 fw-bold text-dark"><?php echo $detalles_carga['total']; ?></div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 bg-success bg-opacity-10 rounded-3 text-center border border-success-subtle">
                                        <div class="text-success small fw-semibold text-uppercase">Insertados en BD</div>
                                        <div class="fs-3 fw-bold text-success"><?php echo $detalles_carga['insertados']; ?></div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 bg-warning bg-opacity-10 rounded-3 text-center border border-warning-subtle">
                                        <div class="text-warning-emphasis small fw-semibold text-uppercase">Duplicados Omitidos</div>
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

                            <!-- Bitácora de Observaciones -->
                            <?php if (!empty($detalles_carga['bitacora'])): ?>
                                <div class="mt-4">
                                    <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBitacora" aria-expanded="false">
                                        <i class="bi bi-journal-text"></i> Ver bitácora de omisiones y advertencias (<?php echo count($detalles_carga['bitacora']); ?>)
                                    </button>
                                    <div class="collapse mt-2" id="collapseBitacora">
                                        <div class="table-responsive border rounded-3 bg-white" style="max-height: 250px; overflow-y: auto;">
                                            <table class="table table-sm table-hover align-middle mb-0 small">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th class="py-2 ps-3">Fila Excel</th>
                                                        <th class="py-2">Código</th>
                                                        <th class="py-2">Detalle de la Observación</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($detalles_carga['bitacora'] as $item): ?>
                                                        <tr>
                                                            <td class="ps-3"><span class="badge bg-secondary">Fila <?php echo $item['fila']; ?></span></td>
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

                            <!-- Listado de Registros Guardados -->
                            <?php if (!empty($detalles_carga['exitosos'])): ?>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-success d-flex align-items-center gap-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExitosos" aria-expanded="false">
                                        <i class="bi bi-people-fill"></i> Ver estudiantes incorporados exitosamente (<?php echo count($detalles_carga['exitosos']); ?>)
                                    </button>
                                    <div class="collapse mt-2" id="collapseExitosos">
                                        <div class="table-responsive border rounded-3 bg-white" style="max-height: 250px; overflow-y: auto;">
                                            <table class="table table-sm table-hover align-middle mb-0 small">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th class="py-2 ps-3">Carnet</th>
                                                        <th class="py-2">Nombre Completo</th>
                                                        <th class="py-2">Correo ITCA</th>
                                                        <th class="py-2">Teléfono Móvil</th>
                                                        <th class="py-2 text-center">Estado Inicial</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($detalles_carga['exitosos'] as $ex): ?>
                                                        <tr>
                                                            <td class="ps-3 font-monospace fw-bold text-danger"><?php echo htmlspecialchars($ex['carnet']); ?></td>
                                                            <td class="fw-medium"><?php echo htmlspecialchars($ex['nombre']); ?></td>
                                                            <td class="text-secondary"><?php echo htmlspecialchars($ex['correo']); ?></td>
                                                            <td class="text-secondary"><?php echo htmlspecialchars($ex['movil']); ?></td>
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
                     1. TABLA DE REFERENCIA SUPERIOR (Formato de Columnas)
                     ======================================================== -->
                <div class="card tabla-referencia-card mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center">
                            <span class="badge p-2 me-2" style="background-color: var(--itca-red); color: #fff;">
                                <i class="bi bi-table fs-6"></i>
                            </span>
                            <div>
                                <h5 class="card-title mb-0 fw-bold text-dark">Formato de Columnas Obligatorio</h5>
                                <small class="text-muted">El archivo Excel (.xlsx) debe estructurarse obligatoriamente con el siguiente encabezado y orden:</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="descargar_plantilla.php?formato=xlsx" class="btn btn-outline-success btn-sm shadow-sm d-inline-flex align-items-center gap-1">
                                <i class="bi bi-file-earmark-excel"></i>
                                <span>Descargar Plantilla (.xlsx)</span>
                            </a>
                            <a href="descargar_plantilla.php?formato=csv" class="btn btn-outline-secondary btn-sm shadow-sm d-inline-flex align-items-center gap-1">
                                <i class="bi bi-filetype-csv"></i>
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
                                <span>La <strong>primera fila</strong> de la hoja de cálculo corresponde al encabezado de las columnas. Las filas siguientes representan los registros individuales.</span>
                            </div>
                            <div>
                                <span class="badge bg-white text-success border border-success-subtle">
                                    <i class="bi bi-check-circle-fill me-1"></i>Compatibilidad nativa con Excel (.xlsx)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================
                     2. FORMULARIO CON INPUT FILE PARA ARCHIVOS .XLSX
                     ======================================================== -->
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center">
                                    <i class="bi bi-file-earmark-arrow-up-fill text-success me-2 fs-5"></i>
                                    Adjuntar y Cargar Archivo (.xlsx)
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <form method="POST" action="" enctype="multipart/form-data" id="formCargaMasiva">
                                    <div class="zona-arrastre-xlsx mb-3" id="dropZone">
                                        <div class="mb-3">
                                            <i class="bi bi-file-earmark-excel-fill icono-excel"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">
                                            Arrastra y suelta tu archivo Excel (.xlsx) aquí
                                        </h6>
                                        <p class="text-muted small mb-3">
                                            o haz clic sobre esta zona para examinar en tu dispositivo
                                        </p>
                                        
                                        <!-- Previsualización del archivo seleccionado -->
                                        <div id="infoArchivo" class="d-none">
                                            <div class="d-inline-flex align-items-center bg-white border border-success-subtle px-3 py-2 rounded-pill shadow-sm">
                                                <i class="bi bi-file-earmark-spreadsheet-fill text-success fs-5 me-2"></i>
                                                <span id="nombreArchivo" class="fw-semibold small text-dark me-2 text-truncate" style="max-width: 260px;"></span>
                                                <span id="tamanoArchivo" class="badge bg-success me-2"></span>
                                                <button type="button" class="btn-close btn-sm" id="btnQuitarArchivo" aria-label="Quitar archivo"></button>
                                            </div>
                                        </div>

                                        <!-- Input tipo file obligatorio para archivos .xlsx -->
                                        <input type="file" 
                                               name="archivo_excel" 
                                               id="archivo_excel" 
                                               accept=".xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, .csv" 
                                               required>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center pt-2">
                                        <span class="text-muted small">
                                            <i class="bi bi-database-lock me-1 text-success"></i>Inserción protegida con transacciones PDO.
                                        </span>
                                        <button type="submit" class="btn btn-itca px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2" id="btnSubmit">
                                            <i class="bi bi-upload"></i>
                                            <span id="btnSubmitTexto">Procesar e Insertar en Base de Datos</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Informativo Lateral -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-3 mb-3">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center">
                                    <i class="bi bi-gear-wide-connected text-danger me-2"></i>
                                    Parámetros de Importación
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <ul class="list-unstyled mb-0 small">
                                    <li class="d-flex align-items-start mb-3">
                                        <i class="bi bi-shield-check text-success me-2 fs-6"></i>
                                        <div>
                                            <strong>Consultas Preparadas (PDO):</strong>
                                            <p class="text-muted mb-0">Prevención total de inyección SQL y manejo transaccional en lote.</p>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-3">
                                        <i class="bi bi-key-fill text-danger me-2 fs-6"></i>
                                        <div>
                                            <strong>Credencial Inicial:</strong>
                                            <p class="text-muted mb-0">Contraseña asignada por defecto <code>123456</code> (cifrada con BCRYPT).</p>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-3">
                                        <i class="bi bi-file-earmark-text-fill text-primary me-2 fs-6"></i>
                                        <div>
                                            <strong>Expediente Asociado:</strong>
                                            <p class="text-muted mb-0">Se crea de inmediato una solicitud en estado <span class="badge bg-secondary-subtle text-dark border">Borrador</span>.</p>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start">
                                        <i class="bi bi-person-fill-check text-info me-2 fs-6"></i>
                                        <div>
                                            <strong>Rol Asignado:</strong>
                                            <p class="text-muted mb-0">Todos los aspirantes se crean con el rol <strong>estudiante</strong>.</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Tarjeta de Plantilla Rápida -->
                        <div class="p-3 bg-white rounded-3 border shadow-sm">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-file-earmark-arrow-down-fill text-success fs-5"></i>
                                <span class="fw-bold small text-dark">Plantilla Oficial (.xlsx)</span>
                            </div>
                            <p class="text-muted small mb-2">
                                Utiliza el archivo prediseñado para evitar inconsistencias con nombres de columnas.
                            </p>
                            <a href="descargar_plantilla.php?formato=xlsx" class="btn btn-sm btn-outline-success w-100 mb-1">
                                <i class="bi bi-download me-1"></i> Descargar Plantilla Excel (.xlsx)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inclusión de Bootstrap JS -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

            <!-- Manejo interactivo de archivo y arrastre -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const dropZone = document.getElementById('dropZone');
                    const fileInput = document.getElementById('archivo_excel');
                    const infoArchivo = document.getElementById('infoArchivo');
                    const nombreArchivo = document.getElementById('nombreArchivo');
                    const tamanoArchivo = document.getElementById('tamanoArchivo');
                    const btnQuitar = document.getElementById('btnQuitarArchivo');
                    const formCarga = document.getElementById('formCargaMasiva');
                    const btnSubmit = document.getElementById('btnSubmit');
                    const btnSubmitTexto = document.getElementById('btnSubmitTexto');

                    function formatearTamano(bytes) {
                        if (bytes === 0) return '0 Bytes';
                        const k = 1024;
                        const sizes = ['Bytes', 'KB', 'MB'];
                        const i = Math.floor(Math.log(bytes) / Math.log(k));
                        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                    }

                    function actualizarVista(file) {
                        if (file) {
                            const extension = file.name.split('.').pop().toLowerCase();
                            if (extension !== 'xlsx' && extension !== 'csv') {
                                alert('Por favor, selecciona un archivo válido con formato .xlsx o .csv');
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
                            actualizarVista(this.files[0]);
                        } else {
                            actualizarVista(null);
                        }
                    });

                    btnQuitar.addEventListener('click', function(e) {
                        e.stopPropagation();
                        e.preventDefault();
                        fileInput.value = '';
                        actualizarVista(null);
                    });

                    // Eventos de arrastre
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
                            actualizarVista(files[0]);
                        }
                    }, false);

                    // Indicador de carga
                    formCarga.addEventListener('submit', function() {
                        btnSubmit.disabled = true;
                        btnSubmitTexto.textContent = 'Procesando inserción masiva...';
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
