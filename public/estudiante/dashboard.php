<?php
/**
 * public/estudiante/dashboard.php
 * Portal principal del estudiante (HU-12).
 */
require_once '../../includes/auth.php';
verificar_rol('estudiante'); // Solo estudiantes pueden ver esto

require_once '../../config/database.php';

$id_usuario = $_SESSION['usuario_id'];

// Obtenemos la última solicitud del estudiante (si existe)
$stmt = $pdo->prepare("SELECT * FROM solicitudes WHERE id_usuario = ? ORDER BY fecha_solicitud DESC LIMIT 1");
$stmt->execute([$id_usuario]);
$solicitud = $stmt->fetch();

// Obtenemos las notificaciones no leídas
$stmt_notif = $pdo->prepare("SELECT * FROM notificaciones WHERE id_usuario = ? AND leida = 0 ORDER BY fecha_creacion DESC");
$stmt_notif->execute([$id_usuario]);
$notificaciones = $stmt_notif->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Portal - ITCA FEPADE</title>
    <link rel="stylesheet" href="/Sistema-de-Becas---ITCA-FEPADE/public/css/style.css">
</head>
<body>
    <div class="layout">
        <?php include '../../includes/sidebar_estudiante.php'; ?>
        
        <div class="main-content">
            <?php include '../../includes/header.php'; ?>
            
            <div class="contenido">
                <h1 class="page-title">Bienvenido(a), <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
                <p class="subtitulo">Este es el resumen de tu solicitud de beca.</p>

                <?php if ($solicitud): ?>
                    <div class="tarjetas-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                        <div class="tarjeta">
                            <h3 style="color: var(--itca-red); font-size: 15px; font-weight: 700; margin-bottom: 12px;">Estado de tu solicitud</h3>
                            <p style="margin-bottom: 8px;">Estado actual: 
                                <span class="badge badge-<?php echo str_replace('_', '', $solicitud['estado']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $solicitud['estado'])); ?>
                                </span>
                            </p>
                            <p style="color: var(--text-muted); font-size: 13px;">Fecha: <?php echo date('d/m/Y', strtotime($solicitud['fecha_solicitud'])); ?></p>
                            <a href="solicitud.php" class="btn btn-primario" style="margin-top: 15px; width: fit-content;">Ver / Continuar Solicitud</a>
                        </div>

                        <div class="tarjeta">
                            <h3 style="color: var(--itca-red); font-size: 15px; font-weight: 700; margin-bottom: 12px;">Notificaciones</h3>
                            <?php if (count($notificaciones) > 0): ?>
                                <ul style="list-style: none; padding-left: 0;">
                                    <?php foreach ($notificaciones as $notif): ?>
                                        <li style="padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                                            <span style="font-weight: 500;"><?php echo htmlspecialchars($notif['mensaje']); ?></span>
                                            <br><small style="color: var(--text-muted); font-size: 11px;"><?php echo date('d/m/Y H:i', strtotime($notif['fecha_creacion'])); ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p style="color: var(--text-muted);">No tienes notificaciones pendientes.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="tarjeta" style="text-align: center; padding: 40px;">
                        <h3 style="color: var(--itca-red); margin-bottom: 10px;">No tienes ninguna solicitud activa</h3>
                        <p style="margin-bottom: 20px; color: var(--text-muted);">Comienza tu proceso de solicitud de beca ahora mismo.</p>
                        <a href="solicitud.php" class="btn btn-primario" style="display: inline-block;">Iniciar Solicitud</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>
</body>
</html>