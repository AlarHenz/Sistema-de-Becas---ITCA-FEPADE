<?php
/**
 * public/admin/instituciones.php
 * Gestión de instituciones y especialidades (HU-08).
 */
require_once '../../includes/auth.php';
verificar_rol('administrador');

require_once '../../config/database.php';

$mensaje_exito = '';
$mensaje_error = '';

// --- 1. Procesar acciones ---

// Agregar nueva institución
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_agregar_institucion'])) {
    $nombre = trim($_POST['nombre_institucion'] ?? '');
    if (empty($nombre)) {
        $mensaje_error = 'El nombre de la institución es obligatorio.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO instituciones (nombre_institucion) VALUES (?)");
        if ($stmt->execute([$nombre])) {
            $mensaje_exito = 'Institución agregada correctamente.';
        } else {
            $mensaje_error = 'Error al agregar la institución.';
        }
    }
}

// Agregar nueva especialidad a una institución
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_agregar_especialidad'])) {
    $id_institucion = $_POST['id_institucion'] ?? null;
    $nombre = trim($_POST['nombre_especialidad'] ?? '');
    if (empty($nombre) || !$id_institucion) {
        $mensaje_error = 'El nombre de la especialidad es obligatorio.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO especialidades (id_institucion, nombre_especialidad) VALUES (?, ?)");
        if ($stmt->execute([$id_institucion, $nombre])) {
            $mensaje_exito = 'Especialidad agregada correctamente.';
        } else {
            $mensaje_error = 'Error al agregar la especialidad.';
        }
    }
}

// Eliminar especialidad
if (isset($_GET['eliminar_especialidad'])) {
    $id = $_GET['eliminar_especialidad'];
    $stmt = $pdo->prepare("DELETE FROM especialidades WHERE id = ?");
    if ($stmt->execute([$id])) {
        $mensaje_exito = 'Especialidad eliminada.';
    }
}

// --- 2. Obtener datos ---
$instituciones = $pdo->query("SELECT * FROM instituciones ORDER BY id ASC")->fetchAll();

// Si se seleccionó una institución para gestionar sus especialidades
$id_institucion_seleccionada = $_GET['gestionar'] ?? null;
$especialidades = [];
$institucion_actual = null;

if ($id_institucion_seleccionada) {
    $stmt = $pdo->prepare("SELECT * FROM instituciones WHERE id = ?");
    $stmt->execute([$id_institucion_seleccionada]);
    $institucion_actual = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT * FROM especialidades WHERE id_institucion = ? ORDER BY nombre_especialidad ASC");
    $stmt->execute([$id_institucion_seleccionada]);
    $especialidades = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instituciones y Especialidades - Admin</title>
    <link rel="stylesheet" href="/Sistema-de-Becas---ITCA-FEPADE/public/css/style.css">
</head>
<body>
    <div class="layout">
        <?php include '../../includes/sidebar_admin.php'; ?>
        
        <div class="main-content">
            <?php include '../../includes/header.php'; ?>
            
            <div class="contenido">
                <h1>Instituciones y Especialidades</h1>
                <p class="subtitulo">Configura las instituciones y sus especialidades académicas.</p>

                <?php if ($mensaje_exito): ?>
                    <div class="alert alert-exito"><?php echo $mensaje_exito; ?></div>
                <?php endif; ?>
                <?php if ($mensaje_error): ?>
                    <div class="alert alert-error"><?php echo $mensaje_error; ?></div>
                <?php endif; ?>

                <!-- Formulario para agregar institución -->
                <div class="tarjeta" style="margin-bottom: 20px; border-top-color: #FFCC00;">
                    <h3 style="color: #B30000; margin-bottom: 10px;">Agregar Nueva Institución</h3>
                    <form method="POST" action="" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                        <input type="hidden" name="accion_agregar_institucion" value="1">
                        <div class="form-group" style="flex: 1; min-width: 250px; margin-bottom: 0;">
                            <label>Nombre de la Institución *</label>
                            <input type="text" name="nombre_institucion" placeholder="Ej: ITCA FEPADE - Santa Ana" required>
                        </div>
                        <button type="submit" class="btn btn-primario">Agregar Institución</button>
                    </form>
                </div>

                <!-- Listado de Instituciones -->
                <div class="tabla-container" style="margin-bottom: 25px;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Institución</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($instituciones as $inst): ?>
                                <tr>
                                    <td>#<?php echo $inst['id']; ?></td>
                                    <td><?php echo htmlspecialchars($inst['nombre_institucion']); ?></td>
                                    <td>
                                        <a href="?gestionar=<?php echo $inst['id']; ?>" class="btn btn-secundario" style="padding: 4px 8px; font-size: 11px;">Gestionar Especialidades</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Panel de gestión de especialidades (aparece si se seleccionó una institución) -->
                <?php if ($institucion_actual): ?>
                    <div class="tarjeta" style="border-top-color: #B30000;">
                        <h3 style="color: #B30000; margin-bottom: 10px;">
                            Especialidades de: <?php echo htmlspecialchars($institucion_actual['nombre_institucion']); ?>
                        </h3>

                        <!-- Formulario para agregar especialidad a la institución actual -->
                        <form method="POST" action="" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 15px;">
                            <input type="hidden" name="accion_agregar_especialidad" value="1">
                            <input type="hidden" name="id_institucion" value="<?php echo $institucion_actual['id']; ?>">
                            <div class="form-group" style="flex: 1; min-width: 250px; margin-bottom: 0;">
                                <label>Nueva Especialidad</label>
                                <input type="text" name="nombre_especialidad" placeholder="Ej: Ingeniería en Sistemas" required>
                            </div>
                            <button type="submit" class="btn btn-primario">Agregar Especialidad</button>
                        </form>

                        <!-- Tabla de especialidades de la institución -->
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Especialidad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($especialidades) > 0): ?>
                                    <?php foreach ($especialidades as $esp): ?>
                                        <tr>
                                            <td>#<?php echo $esp['id']; ?></td>
                                            <td><?php echo htmlspecialchars($esp['nombre_especialidad']); ?></td>
                                            <td>
                                                <a href="?gestionar=<?php echo $institucion_actual['id']; ?>&eliminar_especialidad=<?php echo $esp['id']; ?>" class="btn btn-primario" style="padding: 4px 8px; font-size: 11px;" onclick="return confirm('¿Eliminar esta especialidad?');">Eliminar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 15px;">Esta institución no tiene especialidades registradas aún.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </div>
            
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>
</body>
</html>