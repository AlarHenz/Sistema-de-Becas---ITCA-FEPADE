<?php
/**
 * includes/auth.php
 * Middleware de autenticación y control de acceso por roles.
 * RUTA BASE: /Sistema-de-Becas---ITCA-FEPADE/
 */

// Iniciamos la sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si el usuario está logueado.
 * Si no lo está, redirige al login.
 */
function verificar_login() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /Sistema-de-Becas---ITCA-FEPADE/public/login.php');
        exit();
    }
}

/**
 * Verifica si el usuario tiene un rol específico.
 * @param string $rol_requerido 'administrador' o 'estudiante'
 */
function verificar_rol($rol_requerido) {
    verificar_login(); // Primero asegura que esté logueado
    
    if ($_SESSION['rol'] !== $rol_requerido) {
        // Si no tiene el rol, lo mandamos a su dashboard correspondiente
        if ($_SESSION['rol'] === 'administrador') {
            header('Location: /Sistema-de-Becas---ITCA-FEPADE/public/admin/dashboard.php');
        } else {
            header('Location: /Sistema-de-Becas---ITCA-FEPADE/public/estudiante/dashboard.php');
        }
        exit();
    }
}
?>