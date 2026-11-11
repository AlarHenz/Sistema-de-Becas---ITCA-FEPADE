<?php
// includes/sidebar_estudiante.php
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="logo">
        ITCA FEPADE<br>
        <small>Portal del Estudiante</small>
    </div>
    <nav>
        <a href="/Sistema-de-Becas---ITCA-FEPADE/public/estudiante/dashboard.php" class="<?php echo $pagina_actual == 'dashboard.php' ? 'activo' : ''; ?>">Dashboard</a>
        <a href="/Sistema-de-Becas---ITCA-FEPADE/public/estudiante/solicitud.php" class="<?php echo $pagina_actual == 'solicitud.php' ? 'activo' : ''; ?>">Mi Solicitud</a>
    </nav>
</div>