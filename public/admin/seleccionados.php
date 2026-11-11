<?php
/**
 * public/admin/seleccionados.php
 * Listado de aspirantes seleccionados (HU-05).
 */
require_once '../../includes/auth.php';
verificar_rol('administrador');

require_once '../../config/database.php';

// Mostramos los aprobados o en revisión (los "seleccionados" para el proceso)
$sql = "SELECT s.id AS id_solicitud, u.nombre_completo, u.carnet, e.nombre_especialidad, s.estado, s.fecha_solicitud, s.prioridad
        FROM solicitudes s
        JOIN usuarios u ON s.id_usuario = u.id
        LEFT JOIN especialidades e ON s.id_especialidad = e.id
        WHERE s.estado IN ('en_revision', 'aprobada')
        ORDER BY s.fecha_solicitud DESC";

$stmt = $pdo->query($sql);
$seleccionados = $stmt->fetchAll();
$total_seleccionados = count($seleccionados);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seleccionados - Admin</title>
    <link rel="stylesheet" href="/Sistema-de-Becas---ITCA-FEPADE/public/css/style.css">
</head>
<body>
    <div class="layout">
        <?php include '../../includes/sidebar_admin.php'; ?>
        
        <div class="main-content">
            <?php include '../../includes/header.php'; ?>
            
            <div class="contenido">
                <h1>Listado de Aspirantes Seleccionados</h1>
                <p class="subtitulo">Aspirantes en revisión o aprobados (Total: <strong><?php echo $total_seleccionados; ?></strong>)</p>

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
                                            <!-- Botones de acción simulados (para la HU-05) -->
                                            <button onclick="alert('Funcionalidad de ver detalles en desarrollo.')" class="btn btn-secundario" style="padding: 4px 8px; font-size: 11px;">Ver</button>
                                            <a href="solicitudes.php" class="btn btn-primario" style="padding: 4px 8px; font-size: 11px;">Gestionar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 20px;">No hay aspirantes seleccionados por el momento.</td>
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