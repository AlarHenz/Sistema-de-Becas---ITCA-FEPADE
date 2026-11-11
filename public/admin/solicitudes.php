<?php
/**
 * public/admin/solicitudes.php
 * Gestión de solicitudes (HU-04, HU-12).
 */
require_once '../../includes/auth.php';
verificar_rol('administrador');

require_once '../../config/database.php';

$mensaje_exito = '';
$mensaje_error = '';

// --- 1. Procesar acciones (cambiar estado u observación) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_actualizar'])) {
    $id_solicitud = $_POST['id_solicitud'] ?? null;
    $nuevo_estado = $_POST['estado'] ?? null;
    $observaciones = trim($_POST['observaciones'] ?? '');

    if ($id_solicitud && $nuevo_estado) {
        $stmt = $pdo->prepare("UPDATE solicitudes SET estado = ?, observaciones_admin = ? WHERE id = ?");
        if ($stmt->execute([$nuevo_estado, $observaciones, $id_solicitud])) {
            $mensaje_exito = 'Solicitud actualizada correctamente.';
            
            // Si hay observaciones, creamos una notificación para el estudiante (HU-12)
            if (!empty($observaciones)) {
                // Buscamos el id del usuario dueño de la solicitud
                $stmt_user = $pdo->prepare("SELECT id_usuario FROM solicitudes WHERE id = ?");
                $stmt_user->execute([$id_solicitud]);
                $id_user = $stmt_user->fetchColumn();
                
                $stmt_notif = $pdo->prepare("INSERT INTO notificaciones (id_usuario, id_solicitud, mensaje) VALUES (?, ?, ?)");
                $stmt_notif->execute([$id_user, $id_solicitud, $observaciones]);
            }
        } else {
            $mensaje_error = 'Error al actualizar la solicitud.';
        }
    }
}

// --- 2. Obtener solicitudes (con filtro por estado si se envía) ---
$filtro_estado = $_GET['estado'] ?? 'todas';

$sql = "SELECT s.*, u.nombre_completo, u.carnet, e.nombre_especialidad 
        FROM solicitudes s
        JOIN usuarios u ON s.id_usuario = u.id
        LEFT JOIN especialidades e ON s.id_especialidad = e.id";

if ($filtro_estado !== 'todas') {
    $sql .= " WHERE s.estado = :estado";
}
$sql .= " ORDER BY s.fecha_solicitud DESC";

$stmt = $pdo->prepare($sql);
if ($filtro_estado !== 'todas') {
    $stmt->bindParam(':estado', $filtro_estado);
}
$stmt->execute();
$solicitudes = $stmt->fetchAll();

// Contadores para las pestañas
$total_todas = $pdo->query("SELECT COUNT(*) FROM solicitudes")->fetchColumn();
$total_revision = $pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'en_revision'")->fetchColumn();
$total_aprobadas = $pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'aprobada'")->fetchColumn();
$total_rechazadas = $pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'rechazada'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Solicitudes - ITCA FEPADE</title>
    <link rel="stylesheet" href="/Sistema-de-Becas---ITCA-FEPADE/public/css/style.css">
    <style>
        .panel-edicion {
            background-color: #f9fafb;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            margin-top: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .panel-edicion select, .panel-edicion textarea {
            width: 100%;
            margin-bottom: 8px;
            padding: 7px 10px;
            font-size: 12.5px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background-color: #ffffff;
            color: #374151;
            outline: none;
        }
        .panel-edicion select:focus, .panel-edicion textarea:focus {
            border-color: #b22a14;
        }
    </style>
</head>
<body>
    <div class="layout">
        <?php include '../../includes/sidebar_admin.php'; ?>
        
        <div class="main-content">
            <?php include '../../includes/header.php'; ?>
            
            <div class="contenido">
                <h1>Gestión de Solicitudes</h1>
                <p class="subtitulo">Administra las solicitudes de beca de los estudiantes.</p>

                <?php if ($mensaje_exito): ?>
                    <div class="alert alert-exito"><?php echo $mensaje_exito; ?></div>
                <?php endif; ?>
                <?php if ($mensaje_error): ?>
                    <div class="alert alert-error"><?php echo $mensaje_error; ?></div>
                <?php endif; ?>

                <!-- Pestañas / Filtros -->
                <div style="margin-bottom: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="solicitudes.php?estado=todas" class="btn <?php echo $filtro_estado == 'todas' ? 'btn-primario' : 'btn-secundario'; ?>">
                        Todas (<?php echo $total_todas; ?>)
                    </a>
                    <a href="solicitudes.php?estado=en_revision" class="btn <?php echo $filtro_estado == 'en_revision' ? 'btn-primario' : 'btn-secundario'; ?>">
                        En Revisión (<?php echo $total_revision; ?>)
                    </a>
                    <a href="solicitudes.php?estado=aprobada" class="btn <?php echo $filtro_estado == 'aprobada' ? 'btn-primario' : 'btn-secundario'; ?>">
                        Aprobadas (<?php echo $total_aprobadas; ?>)
                    </a>
                    <a href="solicitudes.php?estado=rechazada" class="btn <?php echo $filtro_estado == 'rechazada' ? 'btn-primario' : 'btn-secundario'; ?>">
                        Rechazadas (<?php echo $total_rechazadas; ?>)
                    </a>
                </div>

                <!-- Tabla de Solicitudes -->
                <div class="tabla-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Estudiante</th>
                                <th>Carnet</th>
                                <th>Especialidad</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($solicitudes) > 0): ?>
                                <?php foreach ($solicitudes as $sol): ?>
                                    <tr>
                                        <td>#<?php echo $sol['id']; ?></td>
                                        <td><?php echo htmlspecialchars($sol['nombre_completo']); ?></td>
                                        <td><?php echo htmlspecialchars($sol['carnet']); ?></td>
                                        <td><?php echo htmlspecialchars($sol['nombre_especialidad'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($sol['fecha_solicitud'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo str_replace('_', '', $sol['estado']); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $sol['estado'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- Botón para desplegar el panel de edición -->
                                            <button onclick="toggleEdicion(<?php echo $sol['id']; ?>)" class="btn btn-secundario" style="padding: 5px 10px; font-size: 11px;">Gestionar</button>
                                            
                                            <!-- Panel de edición oculto -->
                                            <div id="edicion-<?php echo $sol['id']; ?>" class="panel-edicion" style="display: none;">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="accion_actualizar" value="1">
                                                    <input type="hidden" name="id_solicitud" value="<?php echo $sol['id']; ?>">
                                                    
                                                    <label style="font-size: 11px; font-weight: bold;">Estado:</label>
                                                    <select name="estado">
                                                        <option value="borrador" <?php echo $sol['estado'] == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                                                        <option value="en_revision" <?php echo $sol['estado'] == 'en_revision' ? 'selected' : ''; ?>>En Revisión</option>
                                                        <option value="aprobada" <?php echo $sol['estado'] == 'aprobada' ? 'selected' : ''; ?>>Aprobada</option>
                                                        <option value="rechazada" <?php echo $sol['estado'] == 'rechazada' ? 'selected' : ''; ?>>Rechazada</option>
                                                        <option value="incompleta" <?php echo $sol['estado'] == 'incompleta' ? 'selected' : ''; ?>>Incompleta</option>
                                                    </select>
                                                    
                                                    <label style="font-size: 11px; font-weight: bold;">Observaciones (se envían al estudiante):</label>
                                                    <textarea name="observaciones" rows="2"><?php echo htmlspecialchars($sol['observaciones_admin'] ?? ''); ?></textarea>
                                                    
                                                    <button type="submit" class="btn btn-primario" style="padding: 5px 10px; font-size: 11px; margin-top: 5px;">Guardar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px;">No hay solicitudes para mostrar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>

    <!-- Script para mostrar/ocultar el panel de edición -->
    <script>
        function toggleEdicion(id) {
            const panel = document.getElementById('edicion-' + id);
            if (panel.style.display === 'none') {
                panel.style.display = 'block';
            } else {
                panel.style.display = 'none';
            }
        }
    </script>
</body>
</html>