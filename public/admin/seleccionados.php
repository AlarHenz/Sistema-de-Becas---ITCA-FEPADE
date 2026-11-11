<?php
/**
 * public/admin/seleccionados.php
 * Listado de aspirantes seleccionados (HU-05).
 */
require_once '../../includes/auth.php';
verificar_rol('administrador');

require_once '../../config/database.php';

$mensaje_exito = '';
$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_asignar_beca'])) {
    $id_solicitud = filter_input(INPUT_POST, 'id_solicitud', FILTER_VALIDATE_INT);
    $id_beca = ($_POST['id_beca'] ?? '') === '' ? null : filter_var($_POST['id_beca'], FILTER_VALIDATE_INT);

    if (!$id_solicitud || ($id_beca !== null && !$id_beca)) {
        $mensaje_error = 'Los datos enviados no son válidos.';
    } else {
        $stmt = $pdo->prepare("SELECT id, id_beca FROM solicitudes WHERE id = ? AND estado IN ('en_revision', 'aprobada')");
        $stmt->execute([$id_solicitud]);
        $solicitud = $stmt->fetch();

        if (!$solicitud) {
            $mensaje_error = 'No se encontró una solicitud gestionable.';
        } elseif ($id_beca !== null) {
            $stmt = $pdo->prepare("SELECT estado FROM becas WHERE id = ?");
            $stmt->execute([$id_beca]);
            $estado_beca = $stmt->fetchColumn();

            if ($estado_beca !== 'activa' && !($estado_beca === 'inactiva' && (int)$solicitud['id_beca'] === $id_beca)) {
                $mensaje_error = 'Selecciona una beca activa.';
            }
        }

        if ($mensaje_error === '') {
            $stmt = $pdo->prepare("UPDATE solicitudes SET id_beca = ? WHERE id = ?");
            $stmt->execute([$id_beca, $id_solicitud]);
            $mensaje_exito = 'La beca asignada se actualizó correctamente.';
        }
    }
}

// Mostramos los aprobados o en revisión (los "seleccionados" para el proceso)
$sql = "SELECT s.id AS id_solicitud, s.id_beca, b.nombre_beca, b.estado AS estado_beca,
               u.nombre_completo, u.carnet, e.nombre_especialidad, s.estado, s.fecha_solicitud, s.prioridad
        FROM solicitudes s
        JOIN usuarios u ON s.id_usuario = u.id
        LEFT JOIN especialidades e ON s.id_especialidad = e.id
        LEFT JOIN becas b ON s.id_beca = b.id
        WHERE s.estado IN ('en_revision', 'aprobada')
        ORDER BY s.fecha_solicitud DESC";

$stmt = $pdo->query($sql);
$seleccionados = $stmt->fetchAll();
$total_seleccionados = count($seleccionados);
$becas = $pdo->query("SELECT id, nombre_beca FROM becas WHERE estado = 'activa' ORDER BY nombre_beca")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionados - ITCA FEPADE</title>
    <link rel="stylesheet" href="/Sistema-de-Becas---ITCA-FEPADE/public/css/style.css">
</head>
<body>
    <div class="layout">
        <?php include '../../includes/sidebar_admin.php'; ?>
        
        <div class="main-content">
            <?php include '../../includes/header.php'; ?>
            
            <div class="contenido">
                <h1 class="page-title">Listado de Aspirantes Seleccionados</h1>
                <p class="subtitulo">Aspirantes en revisión o aprobados (Total: <strong><?php echo $total_seleccionados; ?></strong>)</p>

                <?php if ($mensaje_exito): ?>
                    <div class="alert alert-exito"><?php echo htmlspecialchars($mensaje_exito); ?></div>
                <?php endif; ?>
                <?php if ($mensaje_error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($mensaje_error); ?></div>
                <?php endif; ?>

                <div class="tabla-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Estudiante</th>
                                <th>Carnet</th>
                                <th>Especialidad</th>
                                <th>Fecha Solicitud</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Beca asignada</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($total_seleccionados > 0): ?>
                                <?php foreach ($seleccionados as $sel): ?>
                                    <tr>
                                        <td>#<?php echo $sel['id_solicitud']; ?></td>
                                        <td><?php echo htmlspecialchars($sel['nombre_completo']); ?></td>
                                        <td><?php echo htmlspecialchars($sel['carnet']); ?></td>
                                        <td><?php echo htmlspecialchars($sel['nombre_especialidad'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($sel['fecha_solicitud'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo $sel['prioridad'] == 'alta' ? 'badge-rechazada' : ($sel['prioridad'] == 'media' ? 'badge-revision' : 'badge-aprobada'); ?>">
                                                <?php echo ucfirst($sel['prioridad'] ?? 'media'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo str_replace('_', '', $sel['estado']); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $sel['estado'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($sel['nombre_beca']): ?>
                                                <?php echo htmlspecialchars($sel['nombre_beca']); ?>
                                                <?php if ($sel['estado_beca'] === 'inactiva'): ?>
                                                    <small>(inactiva)</small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                Sin asignar
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <details>
                                                <summary class="btn btn-primario" style="display: inline-block; padding: 4px 8px; font-size: 11px; cursor: pointer;">Gestionar</summary>
                                                <form method="POST" action="seleccionados.php" style="min-width: 220px; padding: 10px;">
                                                    <input type="hidden" name="accion_asignar_beca" value="1">
                                                    <input type="hidden" name="id_solicitud" value="<?php echo (int)$sel['id_solicitud']; ?>">
                                                    <label for="beca-<?php echo (int)$sel['id_solicitud']; ?>">Beca</label>
                                                    <select id="beca-<?php echo (int)$sel['id_solicitud']; ?>" name="id_beca">
                                                        <option value="">Sin asignar</option>
                                                        <?php if ($sel['estado_beca'] === 'inactiva'): ?>
                                                            <option value="<?php echo (int)$sel['id_beca']; ?>" selected>
                                                                <?php echo htmlspecialchars($sel['nombre_beca']); ?> (inactiva)
                                                            </option>
                                                        <?php endif; ?>
                                                        <?php foreach ($becas as $beca): ?>
                                                            <option value="<?php echo (int)$beca['id']; ?>" <?php echo (int)$sel['id_beca'] === (int)$beca['id'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($beca['nombre_beca']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="btn btn-secundario" style="margin-top: 8px;">Guardar beca</button>
                                                </form>
                                            </details>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 20px;">No hay aspirantes seleccionados por el momento.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>
</body>
</html>