<?php
/**
 * public/login.php
 * Formulario de inicio de sesión (HU-01).
 */

session_start();
require_once '../config/database.php';

// Si el usuario ya está logueado, lo redirigimos a su dashboard
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol'] === 'administrador') {
        header('Location: /Sistema-de-Becas---ITCA-FEPADE/public/admin/dashboard.php');
    } else {
        header('Location: /Sistema-de-Becas---ITCA-FEPADE/public/estudiante/dashboard.php');
    }
    exit();
}

$error = '';

// Procesar el formulario cuando se envía por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carnet = trim($_POST['carnet'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($carnet) || empty($password)) {
        $error = 'Por favor, ingresa tu carnet y contraseña.';
    } else {
        // Buscamos al usuario por su carnet
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE carnet = ? AND activo = 1");
        $stmt->execute([$carnet]);
        $usuario = $stmt->fetch();

        // Verificamos la contraseña usando password_verify
        if ($usuario && password_verify($password, $usuario['password'])) {
            // Credenciales correctas: creamos la sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['carnet'] = $usuario['carnet'];
            $_SESSION['nombre'] = $usuario['nombre_completo'];
            $_SESSION['rol'] = $usuario['rol'];

            // Redirigimos según el rol
            if ($usuario['rol'] === 'administrador') {
                header('Location: /Sistema-de-Becas---ITCA-FEPADE/public/admin/dashboard.php');
            } else {
                header('Location: /Sistema-de-Becas---ITCA-FEPADE/public/estudiante/dashboard.php');
            }
            exit();
        } else {
            $error = 'Carnet o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Becas ITCA</title>
    <style>
        /* Estilos inspirados en la paleta y el diseño institucional ITCA FEPADE */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        body { display: flex; height: 100vh; background-color: #f4f4f4; }
        
        /* Panel Izquierdo (Rojo Institucional) */
        .panel-izquierdo {
            width: 45%;
            background-color: #b22a14;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px;
            position: relative;
            overflow: hidden;
        }
        .panel-izquierdo::before {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 150%; height: 50%;
            background-color: #921a08;
            transform: rotate(-12deg);
            z-index: 0;
            opacity: 0.85;
        }
        .panel-izquierdo h1, .panel-izquierdo p { z-index: 1; position: relative; }
        .panel-izquierdo h1 { font-size: 2.6rem; font-weight: 800; margin-bottom: 12px; letter-spacing: -0.5px; }
        .panel-izquierdo p { font-size: 1.15rem; opacity: 0.95; line-height: 1.5; }

        /* Panel Derecho (Formulario) */
        .panel-derecho {
            width: 55%;
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }
        .formulario-login { width: 100%; max-width: 400px; }
        .formulario-login h2 { color: #1e2022; margin-bottom: 8px; font-size: 1.85rem; font-weight: 700; }
        .formulario-login .subtitulo { color: #6b7280; margin-bottom: 28px; font-size: 0.95rem; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #374151; font-size: 0.9rem; }
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus { border-color: #b22a14; box-shadow: 0 0 0 3px rgba(178, 42, 20, 0.12); }

        .btn-ingresar {
            width: 100%;
            padding: 13px;
            background-color: #b22a14;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, box-shadow 0.2s;
        }
        .btn-ingresar:hover { background-color: #921a08; box-shadow: 0 4px 12px rgba(178, 42, 20, 0.2); }

        .alert-error {
            background-color: #fdeded;
            color: #5f2120;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Panel Izquierdo -->
    <div class="panel-izquierdo">
        <h1>SISTEMA DE<br>GESTIÓN DE BECAS</h1>
        <p>ITCA FEPADE<br><br>Plataforma institucional para la gestión de solicitudes y seguimiento de becas.</p>
    </div>

    <!-- Panel Derecho -->
    <div class="panel-derecho">
        <div class="formulario-login">
            <h2>Iniciar sesión</h2>
            <p class="subtitulo">Accede con tus credenciales institucionales.</p>

            <?php if (!empty($error)): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="carnet">Carnet</label>
                    <input type="text" id="carnet" name="carnet" placeholder="Ingresa tu número de carnet" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                </div>
                <button type="submit" class="btn-ingresar">INGRESAR</button>
            </form>
        </div>
    </div>
</body>
</html>