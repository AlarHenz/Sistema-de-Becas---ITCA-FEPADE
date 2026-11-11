<?php
/**
 * public/admin/dashboard.php
 * Panel de control del administrador (HU-03).
 * Diseñado según la maqueta institucional ITCA FEPADE.
 */
require_once '../../includes/auth.php';
verificar_rol('administrador');

require_once '../../config/database.php';

// Consultas para los contadores y métricas
$total_solicitudes = 0;
$en_revision = 0;
$aprobadas = 0;
$total_seleccionados = 0;
$total_becados = 0;
$total_incompletas = 0;
$total_completas = 0;

try {
    $total_solicitudes = (int)$pdo->query("SELECT COUNT(*) FROM solicitudes")->fetchColumn();
    $en_revision = (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'en_revision'")->fetchColumn();
    $aprobadas = (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'aprobada'")->fetchColumn();
    $total_seleccionados = (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado IN ('en_revision', 'aprobada')")->fetchColumn();
    $total_becados = (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'aprobada'")->fetchColumn();
    $total_incompletas = (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'incompleta'")->fetchColumn();
    $total_completas = (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado IN ('aprobada', 'en_revision')")->fetchColumn();
} catch (Exception $e) {
    // Si la base de datos no está disponible, los contadores quedan en 0
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Gestión - ITCA FEPADE</title>
    <link rel="stylesheet" href="/Sistema-de-Becas---ITCA-FEPADE/public/css/style.css">
</head>
<body>
    <div class="layout">
        <?php include '../../includes/sidebar_admin.php'; ?>
        
        <div class="main-content">
            <?php include '../../includes/header.php'; ?>
            
            <div class="contenido">
                <h1 class="page-title">Panel de gestión</h1>
                
                <h2 class="section-title">Accesos rápidos</h2>
                <div class="grid-accesos-rapidos">
                    <a href="solicitudes.php" class="tarjeta-acceso">
                        <div>
                            <span class="tarjeta-titulo">Solicitudes</span>
                            <span class="tarjeta-numero"><?php echo $total_solicitudes; ?></span>
                        </div>
                        <span class="tarjeta-enlace">Ver módulo &rarr;</span>
                    </a>

                    <a href="solicitudes.php?estado=en_revision" class="tarjeta-acceso">
                        <div>
                            <span class="tarjeta-titulo">Solicitudes pendientes</span>
                            <span class="tarjeta-numero"><?php echo $en_revision; ?></span>
                        </div>
                        <span class="tarjeta-enlace">Ver módulo &rarr;</span>
                    </a>

                    <a href="seleccionados.php" class="tarjeta-acceso">
                        <div>
                            <span class="tarjeta-titulo">Seleccionados</span>
                            <span class="tarjeta-numero"><?php echo $total_seleccionados; ?></span>
                        </div>
                        <span class="tarjeta-enlace">Ver módulo &rarr;</span>
                    </a>

                    <a href="becados.php" class="tarjeta-acceso">
                        <div>
                            <span class="tarjeta-titulo">Becarios</span>
                            <span class="tarjeta-numero"><?php echo $total_becados; ?></span>
                        </div>
                        <span class="tarjeta-enlace">Ver módulo &rarr;</span>
                    </a>

                    <a href="solicitudes.php?estado=incompleta" class="tarjeta-acceso">
                        <div>
                            <span class="tarjeta-titulo">Solicitudes incompletas</span>
                            <span class="tarjeta-numero"><?php echo $total_incompletas; ?></span>
                        </div>
                        <span class="tarjeta-enlace">Ver módulo &rarr;</span>
                    </a>
                </div>

                <!-- Bloque de Estadísticas -->
                <div class="card-estadisticas">
                    <div class="estadisticas-header">
                        <div>
                            <h3 class="estadisticas-titulo">Estadísticas</h3>
                            <p class="estadisticas-subtitulo">Los contadores se actualizan al cambiar el año.</p>
                        </div>
                        <div class="filtro-ano">
                            <label for="filtro_ano">Año</label>
                            <select id="filtro_ano" class="select-ano">
                                <option value="2026" selected>2026</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid-estadisticas-cajas">
                        <div class="caja-estadistica">
                            <div class="caja-numero"><?php echo $total_solicitudes; ?></div>
                            <div class="caja-etiqueta">Solicitudes recibidas</div>
                        </div>

                        <div class="caja-estadistica">
                            <div class="caja-numero"><?php echo $total_completas; ?></div>
                            <div class="caja-etiqueta">Completas</div>
                        </div>

                        <div class="caja-estadistica">
                            <div class="caja-numero"><?php echo $total_incompletas; ?></div>
                            <div class="caja-etiqueta">Incompletas</div>
                        </div>

                        <div class="caja-estadistica">
                            <div class="caja-numero"><?php echo $total_seleccionados; ?></div>
                            <div class="caja-etiqueta">Seleccionados</div>
                        </div>

                        <div class="caja-estadistica">
                            <div class="caja-numero"><?php echo $total_becados; ?></div>
                            <div class="caja-etiqueta">Becarios</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>
</body>
</html>
