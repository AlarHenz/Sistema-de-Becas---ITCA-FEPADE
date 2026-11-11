<?php
/**
 * public/admin/dashboard.php
 * Panel de control del administrador (HU-03).
 */
require_once '../../includes/auth.php';
verificar_rol('administrador'); // Solo admins pueden ver esto

require_once '../../config/database.php';

// Consultas rápidas para los contadores (ejemplos funcionales)
// Total de solicitudes
$total_solicitudes = $pdo->query("SELECT COUNT(*) FROM solicitudes")->fetchColumn();
// Solicitudes en revisión
$en_revision = $pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'en_revision'")->fetchColumn();
// Solicitudes aprobadas
$aprobadas = $pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'aprobada'")->fetchColumn();
// Total de becas activas
$total_becas = $pdo->query("SELECT COUNT(*) FROM becas WHERE estado = 'activa'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador - Sistema de Becas</title>
    <link rel="stylesheet" href="/Sistema-de-Becas---ITCA-FEPADE/public/css/style.css">
</head>
<body>
    <div class="layout">
        <?php include '../../includes/sidebar_admin.php'; ?>
        
        <div class="main-content">
            <?php include '../../includes/header.php'; ?>
            
            <div class="contenido">
                <h1>Panel de Administración</h1>
                <p class="subtitulo">Resumen general del sistema de becas.</p>

                <div class="tarjetas-grid">
                    <a href="solicitudes.php" class="tarjeta">
                        <span class="numero"><?php echo $total_solicitudes; ?></span>
                        <span class="etiqueta">Total de Solicitudes</span>
                    </a>
                    <a href="solicitudes.php" class="tarjeta">
                        <span class="numero"><?php echo $en_revision; ?></span>
                        <span class="etiqueta">En Revisión</span>
                    </a>
                    <a href="solicitudes.php" class="tarjeta">
                        <span class="numero"><?php echo $aprobadas; ?></span>
                        <span class="etiqueta">Aprobadas</span>
                    </a>
                    <a href="catalogo_becas.php" class="tarjeta">
                        <span class="numero"><?php echo $total_becas; ?></span>
                        <span class="etiqueta">Becas Activas</span>
                    </a>
                </div>

                <h2 style="color: #B30000; font-size: 18px; margin-bottom: 10px;">Accesos Rápidos</h2>
                <div class="tarjetas-grid">
                    <a href="solicitudes.php" class="tarjeta">Gestionar Solicitudes</a>
                    <a href="catalogo_becas.php" class="tarjeta">Catálogo de Becas</a>
                    <a href="instituciones.php" class="tarjeta">Instituciones y Especialidades</a>
                    <a href="becados.php" class="tarjeta">Ver Becados</a>
                </div>
            </div>
            
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>
</body>
</html>