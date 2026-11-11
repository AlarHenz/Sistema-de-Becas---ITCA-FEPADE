<?php
/**
 * public/admin/carga_masiva.php
 * Carga masiva de aspirantes desde CSV (HU-02 simplificada).
 */
require_once '../../includes/auth.php';
verificar_rol('administrador');

require_once '../../config/database.php';

$mensaje_exito = '';
$mensaje_error = '';
$detalles_carga = [];

// --- 1. Procesar el archivo CSV ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_csv'])) {
    $archivo = $_FILES['archivo_csv'];

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $mensaje_error = 'Error al subir el archivo.';
    } else {
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            $mensaje_error = 'El archivo debe ser formato .csv';
        } else {
            // Abrimos el archivo en modo lectura
            $handle = fopen($archivo['tmp_name'], 'r');
            if ($handle === false) {
                $mensaje_error = 'No se pudo abrir el archivo.';
            } else {
                // Detectamos el delimitador (coma o punto y coma)
                $primera_linea = fgets($handle);
                rewind($handle);
                $delimitador = (strpos($primera_linea, ';') !== false) ? ';' : ',';

                // Contadores
                $insertados = 0;
                $duplicados = 0;
                $errores = 0;
                $fila_num = 0;

                // Contraseña por defecto
                $password_default = password_hash('123456', PASSWORD_BCRYPT);

                // Preparamos las consultas
                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE carnet = ? OR correo_institucional = ?");
                $stmt_insert = $pdo->prepare("INSERT INTO usuarios (carnet, nombre_completo, telefono_fijo, telefono_movil, correo_institucional, correo_personal, password, rol) VALUES (?, ?, ?, ?, ?, ?, ?, 'estudiante')");
                $stmt_solicitud = $pdo->prepare("INSERT INTO solicitudes (id_usuario, estado) VALUES (?, 'borrador')");

                // Leemos línea por línea
                while (($datos = fgetcsv($handle, 1000, $delimitador)) !== false) {
                    $fila_num++;

                    // Saltamos la primera fila (encabezados)
                    if ($fila_num === 1) continue;

                    // Saltamos filas vacías
                    if (empty(array_filter($datos))) continue;

                    // Asignamos los datos (el orden es el mismo que la tabla de referencia)
                    $carnet = trim($datos[0] ?? '');
                    $nombre = trim($datos[1] ?? '');
                    $tel_fijo = trim($datos[2] ?? '');
                    $tel_movil = trim($datos[3] ?? '');
                    $correo_itca = trim($datos[4] ?? '');
                    $correo_personal = trim($datos[5] ?? '');

                    // Validaciones básicas
                    if (empty($carnet) || empty($nombre) || empty($correo_itca)) {
                        $errores++;
                        continue;
                    }

                    // Verificamos duplicados
                    $stmt_check->execute([$carnet, $correo_itca]);
                    if ($stmt_check->fetchColumn() > 0) {
                        $duplicados++;
                        continue;
                    }

                    try {
                        // Insertamos al usuario
                        $stmt_insert->execute([$carnet, $nombre, $tel_fijo, $tel_movil, $correo_itca, $correo_personal, $password_default]);
                        $id_nuevo_usuario = $pdo->lastInsertId();
                        // Le creamos una solicitud en borrador
                        $stmt_solicitud->execute([$id_nuevo_usuario]);
                        $insertados++;
                    } catch (PDOException $e) {
                        $errores++;
                    }
                }
                fclose($handle);

                $mensaje_exito = "Procesamiento completado.";
                $detalles_carga = [
                    'insertados' => $insertados,
                    'duplicados' => $duplicados,
                    'errores' => $errores,
                    'total' => $fila_num - 1
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
    <title>Carga Masiva - Admin</title>
    <link rel="stylesheet" href="/Sistema-de-Becas---ITCA-FEPADE/public/css/style.css">
    <style>
        .zona-arrastre {
            border: 3px dashed #B30000;
            background-color: #fffbe6;
            padding: 40px 20px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
        }
        .zona-arrastre:hover { background-color: #fff3cd; }
        .zona-arrastre input[type="file"] { display: none; }
        .tabla-referencia {
            background-color: #f8f9fa;
            font-size: 12px;
            margin-top: 15px;
        }
        .tabla-referencia th {
            background-color: #B30000;
            color: white;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="layout">
        <?php include '../../includes/sidebar_admin.php'; ?>
        
        <div class="main-content">
            <?php include '../../includes/header.php'; ?>
            
            <div class="contenido">
                <h1>Carga Masiva de Aspirantes</h1>
                <p class="subtitulo">Sube un archivo .csv para registrar múltiples aspirantes.</p>

                <?php if ($mensaje_exito): ?>
                    <div class="alert alert-exito">
                        <strong><?php echo $mensaje_exito; ?></strong><br>
                        Registros procesados: <?php echo $detalles_carga['total']; ?><br>
                        ✅ Insertados: <?php echo $detalles_carga['insertados']; ?><br>
                        ⚠️ Duplicados: <?php echo $detalles_carga['duplicados']; ?><br>
                        ❌ Errores: <?php echo $detalles_carga['errores']; ?>
                    </div>
                <?php endif; ?>
                <?php if ($mensaje_error): ?>
                    <div class="alert alert-error"><?php echo $mensaje_error; ?></div>
                <?php endif; ?>

                <div class="tarjeta" style="margin-bottom: 20px;">
                    <h3 style="color: #B30000; margin-bottom: 10px;">Formato Requerido</h3>
                    <p style="font-size: 13px; margin-bottom: 10px;">El archivo debe tener exactamente estas columnas (puedes descargar la plantilla de ejemplo):</p>
                    <table class="tabla-referencia">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre Completo</th>
                                <th>Tel. Fijo</th>
                                <th>Tel. Móvil</th>
                                <th>Correo ITCA</th>
                                <th>Correo Personal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2024002</td>
                                <td>Juan Pérez García</td>
                                <td>2440-1234</td>
                                <td>7123-9876</td>
                                <td>jperez@itca.edu.sv</td>
                                <td>juan@gmail.com</td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="margin-top: 15px;">
                        <a href="descargar_plantilla.php" class="btn btn-secundario">📥 Descargar Plantilla (.csv)</a>
                    </div>
                </div>

                <div class="tarjeta">
                    <h3 style="color: #B30000; margin-bottom: 15px;">Subir Archivo CSV</h3>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <label for="archivo_csv" class="zona-arrastre">
                            <p style="font-size: 16px; font-weight: bold; color: #333;">Haz clic aquí para seleccionar tu archivo .csv</p>
                            <p style="font-size: 13px; color: #666;" id="nombre-archivo">Ningún archivo seleccionado</p>
                            <input type="file" name="archivo_csv" id="archivo_csv" accept=".csv" required>
                        </label>
                        <div style="text-align: right; margin-top: 15px;">
                            <button type="submit" class="btn btn-primario">Procesar Archivo</button>
                        </div>
                    </form>
                </div>

                <div class="alert alert-error" style="margin-top: 20px;">
                    <strong>Nota:</strong> Los aspirantes tendrán la contraseña por defecto <code>123456</code> y se les creará una solicitud en estado <strong>Borrador</strong>.
                </div>
            </div>
            
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>

    <script>
        document.getElementById('archivo_csv').addEventListener('change', function() {
            const nombre = this.files[0] ? this.files[0].name : 'Ningún archivo seleccionado';
            document.getElementById('nombre-archivo').textContent = 'Archivo: ' + nombre;
        });
    </script>
</body>
</html>