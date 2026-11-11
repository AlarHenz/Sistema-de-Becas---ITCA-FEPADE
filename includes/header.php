<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$rol = ucfirst($_SESSION['rol'] ?? 'Administrador');
?>
<div class="header">
    <div class="titulo-sistema">
        ITCA FEPADE
    </div>
    <div class="usuario-info">
        <span><?php echo htmlspecialchars($rol); ?></span>
        <a href="/Sistema-de-Becas---ITCA-FEPADE/public/logout.php" class="btn-logout">Cerrar sesión</a>
    </div>
</div>
