<?php
// includes/header.php
// Asegurarse de que la sesión esté iniciada para acceder a $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<div class="header">
    <div class="titulo-sistema">
        SISTEMA DE GESTIÓN DE BECAS - ITCA FEPADE
    </div>
    <div class="usuario-info">
        <span><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Invitado'); ?></span>
        <span>| <?php echo ucfirst($_SESSION['rol'] ?? ''); ?></span>
        <a href="/Sistema-de-Becas---ITCA-FEPADE/public/logout.php">Cerrar Sesión</a>
    </div>
</div>