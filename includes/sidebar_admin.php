<?php
// includes/sidebar_admin.php
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="logo">
        ITCA FEPADE<br>
        <small>Panel Administrativo</small>
    </div>
    <nav>
        <a href="/Sistema-de-Becas---ITCA-FEPADE/public/admin/dashboard.php" class="<?php echo $pagina_actual == 'dashboard.php' ? 'activo' : ''; ?>">Dashboard</a>
        <a href="/Sistema-de-Becas---ITCA-FEPADE/public/admin/solicitudes.php" class="<?php echo $pagina_actual == 'solicitudes.php' ? 'activo' : ''; ?>">Solicitudes</a>
        <a href="/Sistema-de-Becas---ITCA-FEPADE/public/admin/carga_masiva.php" class="<?php echo $pagina_actual == 'carga_masiva.php' ? 'activo' : ''; ?>">Carga Masiva</a>
        <a href="/Sistema-de-Becas---ITCA-FEPADE/public/admin/seleccionados.php" class="<?php echo $pagina_actual == 'seleccionados.php' ? 'activo' : ''; ?>">Seleccionados</a>
        <a href="/Sistema-de-Becas---ITCA-FEPADE/public/admin/becados.php" class="<?php echo $pagina_actual == 'becados.php' ? 'activo' : ''; ?>">Becados</a>
        <a href="/Sistema-de-Becas---ITCA-FEPADE/public/admin/catalogo_becas.php" class="<?php echo $pagina_actual == 'catalogo_becas.php' ? 'activo' : ''; ?>">Catálogo de Becas</a>
        <a href="/Sistema-de-Becas---ITCA-FEPADE/public/admin/instituciones.php" class="<?php echo $pagina_actual == 'instituciones.php' ? 'activo' : ''; ?>">Instituciones</a>
    </nav>
</div>