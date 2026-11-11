<?php
/**
 * public/estudiante/solicitud.php
 * Formulario de solicitud de beca (HU-09, HU-10, HU-11).
 */
require_once '../../includes/auth.php';
verificar_rol('estudiante');

require_once '../../config/database.php';

$id_usuario = $_SESSION['usuario_id'];
$mensaje_exito = '';
$mensaje_error = '';

// --- 1. Procesar el envío del formulario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_especialidad = $_POST['id_especialidad'] ?? null;
    $accion = $_POST['accion'] ?? 'enviar'; // 'borrador' o 'enviar'

    if (empty($id_especialidad)) {
        $mensaje_error = 'Debes seleccionar una especialidad de interés.';
    } else {
        // Verificamos si ya existe una solicitud para este usuario (para actualizarla o crearla)
        $stmt = $pdo->prepare("SELECT id FROM solicitudes WHERE id_usuario = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$id_usuario]);
        $solicitud_existente = $stmt->fetch();

        $nuevo_estado = ($accion === 'borrador') ? 'borrador' : 'en_revision';

        if ($solicitud_existente) {
            // Actualizamos
            $stmt = $pdo->prepare("UPDATE solicitudes SET id_especialidad = ?, estado = ?, fecha_actualizacion = NOW() WHERE id = ?");
            $stmt->execute([$id_especialidad, $nuevo_estado, $solicitud_existente['id']]);
            $id_solicitud = $solicitud_existente['id'];
        } else {
            // Insertamos
            $stmt = $pdo->prepare("INSERT INTO solicitudes (id_usuario, id_especialidad, estado) VALUES (?, ?, ?)");
            $stmt->execute([$id_usuario, $id_especialidad, $nuevo_estado]);
            $id_solicitud = $pdo->lastInsertId();
        }

        // --- Procesar subida de archivo (DUI) ---
        if (isset($_FILES['documento_dui']) && $_FILES['documento_dui']['error'] === UPLOAD_ERR_OK) {
            $archivo = $_FILES['documento_dui'];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $permitidos = ['pdf', 'jpg', 'jpeg', 'png'];
            $tamano_maximo = 5 * 1024 * 1024; // 5 MB

            if (!in_array($extension, $permitidos)) {
                $mensaje_error = 'Formato no permitido. Solo PDF, JPG o PNG.';
            } elseif ($archivo['size'] > $tamano_maximo) {
                $mensaje_error = 'El archivo supera el tamaño máximo de 5MB.';
            } else {
                // Creamos la carpeta si no existe
                $carpeta_destino = '../../public/uploads/';
                if (!is_dir($carpeta_destino)) {
                    mkdir($carpeta_destino, 0777, true);
                }
                $nombre_archivo = 'dui_' . $id_usuario . '_' . time() . '.' . $extension;
                $ruta_final = $carpeta_destino . $nombre_archivo;

                if (move_uploaded_file($archivo['tmp_name'], $ruta_final)) {
                    // Guardamos el registro en la tabla documentos (eliminamos el anterior si existe)
                    $pdo->prepare("DELETE FROM documentos WHERE id_solicitud = ? AND tipo_documento = 'dui'")->execute([$id_solicitud]);
                    $stmt = $pdo->prepare("INSERT INTO documentos (id_solicitud, tipo_documento, ruta_archivo) VALUES (?, 'dui', ?)");
                    $stmt->execute([$id_solicitud, $nombre_archivo]);
                } else {
                    $mensaje_error = 'Error al guardar el archivo en el servidor.';
                }
            }
        }

        if (empty($mensaje_error)) {
            $mensaje_exito = ($accion === 'borrador') ? 'Borrador guardado exitosamente.' : 'Solicitud enviada a revisión exitosamente.';
        }
    }
}

// --- 2. Obtener datos actuales del estudiante para mostrar ---
// Traemos la solicitud existente (si la hay)
$stmt = $pdo->prepare("SELECT * FROM solicitudes WHERE id_usuario = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$id_usuario]);
$solicitud = $stmt->fetch();

// Traemos el documento actual (si existe)
$documento_actual = null;
if ($solicitud) {
    $stmt_doc = $pdo->prepare("SELECT * FROM documentos WHERE id_solicitud = ? AND tipo_documento = 'dui' LIMIT 1");
    $stmt_doc->execute([$solicitud['id']]);
    $documento_actual = $stmt_doc->fetch();
}

// Traemos los datos personales del usuario
$stmt_user = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt_user->execute([$id_usuario]);
$datos_estudiante = $stmt_user->fetch();

// Traemos las especialidades para el select
$especialidades = $pdo->query("SELECT e.*, i.nombre_institucion FROM especialidades e JOIN instituciones i ON e.id_institucion = i.id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Solicitud - ITCA FEPADE</title>
    <link rel="stylesheet" href="/Sistema-de-Becas---ITCA-FEPADE/public/css/style.css">
</head>
<body>
    <div class="layout">
        <?php include '../../includes/sidebar_estudiante.php'; ?>
        
        <div class="main-content">
            <?php include '../../includes/header.php'; ?>
            
            <div class="contenido">
                <h1 class="page-title">Mi Solicitud de Beca</h1>
                <p class="subtitulo">Completa los datos para iniciar tu proceso.</p>

                <?php if ($mensaje_exito): ?>
                    <div class="alert alert-exito"><?php echo $mensaje_exito; ?></div>
                <?php endif; ?>
                <?php if ($mensaje_error): ?>
                    <div class="alert alert-error"><?php echo $mensaje_error; ?></div>
                <?php endif; ?>

                <!-- Mostrar observaciones del admin (HU-12) -->
                <?php if ($solicitud && !empty($solicitud['observaciones_admin'])): ?>
                    <div class="alert alert-error">
                        <strong>Observación del Administrador:</strong><br>
                        <?php echo htmlspecialchars($solicitud['observaciones_admin']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    
                    <!-- SECCIÓN 1: Datos Personales (solo lectura, vienen del usuario) -->
                    <div class="tarjeta" style="margin-bottom: 24px;">
                        <h3 style="color: var(--itca-red); font-size: 15px; font-weight: 700; margin-bottom: 12px;">1. Datos Personales</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px;">
                            <div class="form-group">
                                <label>Nombre Completo</label>
                                <input type="text" value="<?php echo htmlspecialchars($datos_estudiante['nombre_completo']); ?>" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="form-group">
                                <label>Carnet</label>
                                <input type="text" value="<?php echo htmlspecialchars($datos_estudiante['carnet']); ?>" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="form-group">
                                <label>Correo Institucional</label>
                                <input type="email" value="<?php echo htmlspecialchars($datos_estudiante['correo_institucional']); ?>" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="form-group">
                                <label>Teléfono Móvil</label>
                                <input type="text" value="<?php echo htmlspecialchars($datos_estudiante['telefono_movil']); ?>" readonly style="background-color: #f8f9fa;">
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: Información Académica -->
                    <div class="tarjeta" style="margin-bottom: 24px;">
                        <h3 style="color: var(--itca-red); font-size: 15px; font-weight: 700; margin-bottom: 12px;">2. Información Académica</h3>
                        <div class="form-group">
                            <label for="id_especialidad">Especialidad de Interés *</label>
                            <select id="id_especialidad" name="id_especialidad" required>
                                <option value="">-- Selecciona una especialidad --</option>
                                <?php foreach ($especialidades as $esp): ?>
                                    <option value="<?php echo $esp['id']; ?>" 
                                        <?php echo ($solicitud && $solicitud['id_especialidad'] == $esp['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($esp['nombre_institucion'] . ' - ' . $esp['nombre_especialidad']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: Documentos -->
                    <div class="tarjeta" style="margin-bottom: 24px;">
                        <h3 style="color: var(--itca-red); font-size: 15px; font-weight: 700; margin-bottom: 12px;">3. Documentos</h3>
                        <div class="form-group">
                            <label for="documento_dui">Documento de Identidad (DUI) *</label>
                            <input type="file" id="documento_dui" name="documento_dui" accept=".pdf,.jpg,.jpeg,.png">
                            <small style="color: var(--text-muted);">Formatos permitidos: PDF, JPG, PNG. Máximo 5MB.</small>
                        </div>
                        <?php if ($documento_actual): ?>
                            <p style="margin-top: 10px; font-size: 13px;">
                                <strong>Archivo actual:</strong> 
                                <a href="/Sistema-de-Becas---ITCA-FEPADE/public/uploads/<?php echo htmlspecialchars($documento_actual['ruta_archivo']); ?>" target="_blank" style="color: var(--itca-red); font-weight: 600;">
                                    Ver documento subido
                                </a>
                                <span class="badge badge-<?php echo $documento_actual['estado_documento']; ?>" style="margin-left: 5px;">
                                    <?php echo ucfirst($documento_actual['estado_documento']); ?>
                                </span>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- BOTONES DE ACCIÓN -->
                    <div style="text-align: right; margin-top: 20px;">
                        <button type="submit" name="accion" value="borrador" class="btn btn-secundario">Guardar Borrador</button>
                        <button type="submit" name="accion" value="enviar" class="btn btn-primario">Enviar Solicitud</button>
                    </div>

                </form>
            </div>
            
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>
</body>
</html>