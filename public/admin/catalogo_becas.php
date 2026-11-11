<?php
/**
 * public/admin/catalogo_becas.php
 * Gestión del catálogo de becas (HU-07).
 */
require_once '../../includes/auth.php';
verificar_rol('administrador');

require_once '../../config/database.php';

$mensaje_exito = '';
$mensaje_error = '';

// --- 1. Procesar acciones ---

// Agregar nueva beca
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_agregar'])) {
    $nombre = trim($_POST['nombre_beca'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (empty($nombre)) {
        $mensaje_error = 'El nombre de la beca es obligatorio.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO becas (nombre_beca, descripcion) VALUES (?, ?)");
        if ($stmt->execute([$nombre, $descripcion])) {
            $mensaje_exito = 'Beca agregada correctamente.';
        } else {
            $mensaje_error = 'Error al agregar la beca.';
        }
    }
}

// Editar beca existente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_editar'])) {
    $id = $_POST['id_beca'] ?? null;
    $nombre = trim($_POST['nombre_beca'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($id && !empty($nombre)) {
        $stmt = $pdo->prepare("UPDATE becas SET nombre_beca = ?, descripcion = ? WHERE id = ?");
        if ($stmt->execute([$nombre, $descripcion, $id])) {
            $mensaje_exito = 'Beca actualizada correctamente.';
        } else {
            $mensaje_error = 'Error al actualizar la beca.';
        }
    } else {
        $mensaje_error = 'Datos inválidos para editar.';
    }
}

// Cambiar estado (activar/desactivar)
if (isset($_GET['toggle_estado'])) {
    $id = $_GET['toggle_estado'];
    $stmt = $pdo->prepare("UPDATE becas SET estado = IF(estado = 'activa', 'inactiva', 'activa') WHERE id = ?");
    if ($stmt->execute([$id])) {
        $mensaje_exito = 'Estado de la beca actualizado.';
    }
}

// Eliminar beca
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    // Verificamos si tiene solicitudes asociadas para no romper la integridad
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM solicitudes WHERE id_beca = ?");
    $stmt_check->execute([$id]);
    if ($stmt_check->fetchColumn() > 0) {
        $mensaje_error = 'No se puede eliminar la beca porque tiene solicitudes asociadas.';
    } else {
        $stmt = $pdo->prepare("DELETE FROM becas WHERE id = ?");
        if ($stmt->execute([$id])) {
            $mensaje_exito = 'Beca eliminada correctamente.';
        } else {
            $mensaje_error = 'Error al eliminar la beca.';
        }
    }
}

// --- 2. Obtener listado de becas ---
$becas = $pdo->query("SELECT * FROM becas ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Becas - ITCA FEPADE</title>
    <link rel="stylesheet" href="/Sistema-de-Becas---ITCA-FEPADE/public/css/style.css">
    <style>
        .panel-edicion-beca {
            background-color: #f9fafb;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            margin-top: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .panel-edicion-beca input, .panel-edicion-beca textarea {
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
        .panel-edicion-beca input:focus, .panel-edicion-beca textarea:focus {
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
                <h1 class="page-title">Catálogo de Becas</h1>
                <p class="subtitulo">Administra los tipos de beca disponibles en el sistema.</p>

                <?php if ($mensaje_exito): ?>
                    <div class="alert alert-exito"><?php echo $mensaje_exito; ?></div>
                <?php endif; ?>
                <?php if ($mensaje_error): ?>
                    <div class="alert alert-error"><?php echo $mensaje_error; ?></div>
                <?php endif; ?>

                <!-- Formulario para agregar nueva beca -->
                <div class="tarjeta" style="margin-bottom: 24px;">
                    <h3 style="color: var(--itca-red); font-size: 15px; font-weight: 700; margin-bottom: 12px;">Agregar Nueva Beca</h3>
                    <form method="POST" action="" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                        <input type="hidden" name="accion_agregar" value="1">
                        <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                            <label>Nombre de la Beca *</label>
                            <input type="text" name="nombre_beca" placeholder="Ej: Beca Excelencia" required>
                        </div>
                        <div class="form-group" style="flex: 2; min-width: 250px; margin-bottom: 0;">
                            <label>Descripción (opcional)</label>
                            <input type="text" name="descripcion" placeholder="Breve descripción">
                        </div>
                        <button type="submit" class="btn btn-primario">Agregar Beca</button>
                    </form>
                </div>

                <!-- Tabla de becas -->
                <div class="tabla-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre de la Beca</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($becas) > 0): ?>
                                <?php foreach ($becas as $beca): ?>
                                    <tr>
                                        <td>#<?php echo $beca['id']; ?></td>
                                        <td><?php echo htmlspecialchars($beca['nombre_beca']); ?></td>
                                        <td><?php echo htmlspecialchars($beca['descripcion'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $beca['estado'] == 'activa' ? 'badge-aprobada' : 'badge-borrador'; ?>">
                                                <?php echo ucfirst($beca['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button onclick="toggleEdicionBeca(<?php echo $beca['id']; ?>)" class="btn btn-secundario" style="padding: 4px 8px; font-size: 11px;">Editar</button>
                                            <a href="?toggle_estado=<?php echo $beca['id']; ?>" class="btn btn-secundario" style="padding: 4px 8px; font-size: 11px;">
                                                <?php echo $beca['estado'] == 'activa' ? 'Desactivar' : 'Activar'; ?>
                                            </a>
                                            <a href="?eliminar=<?php echo $beca['id']; ?>" class="btn btn-primario" style="padding: 4px 8px; font-size: 11px;" onclick="return confirm('¿Seguro que deseas eliminar esta beca?');">Eliminar</a>

                                            <!-- Panel de edición oculto -->
                                            <div id="edicion-beca-<?php echo $beca['id']; ?>" class="panel-edicion-beca" style="display: none;">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="accion_editar" value="1">
                                                    <input type="hidden" name="id_beca" value="<?php echo $beca['id']; ?>">
                                                    <label style="font-size: 11px; font-weight: bold;">Nombre:</label>
                                                    <input type="text" name="nombre_beca" value="<?php echo htmlspecialchars($beca['nombre_beca']); ?>" style="width: 100%; padding: 4px; margin-bottom: 5px;" required>
                                                    <label style="font-size: 11px; font-weight: bold;">Descripción:</label>
                                                    <input type="text" name="descripcion" value="<?php echo htmlspecialchars($beca['descripcion'] ?? ''); ?>" style="width: 100%; padding: 4px; margin-bottom: 5px;">
                                                    <button type="submit" class="btn btn-primario" style="padding: 4px 8px; font-size: 11px;">Guardar Cambios</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px;">No hay becas registradas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>

    <script>
        function toggleEdicionBeca(id) {
            const panel = document.getElementById('edicion-beca-' + id);
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>