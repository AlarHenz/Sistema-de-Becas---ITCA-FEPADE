<?php
/**
 * public/index.php
 * Punto de entrada del sistema.
 */

session_start();

// Si ya hay una sesión activa, redirigimos según el rol
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol'] === 'administrador') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: estudiante/dashboard.php');
    }
    exit();
}

// Si no hay sesión, al login
header('Location: login.php');
exit();
?>