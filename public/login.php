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
        /* Estilos básicos inspirados en la paleta y el diseño de referencia */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { display: flex; height: 100vh; }
        
        /* Panel Izquierdo (Rojo) */
        .panel-izquierdo {
            width: 45%;
            background-color: #B30000; /* Rojo institucional */
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        /* Simulación del diseño diagonal de la imagen */
        .panel-izquierdo::before {
            content: '';
            position: absolute;
            bottom: -50px; left: -50px;
            width: 150%; height: 50%;
            background-color: #8B0000;
            transform: rotate(-15deg);
            z-index: 0;
        }
        .panel-izquierdo h1, .panel-izquierdo p { z-index: 1; position: relative; }
        .panel-izquierdo h1 { font-size: 2.5rem; margin-bottom: 10px; }
        .panel-izquierdo p { font-size: 1.1rem; opacity: 0.9; }

        /* Panel Derecho (Formulario) */
        .panel-derecho {
            width: 55%;
            background-color: #F8F9FA;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }
        .formulario-login { width: 100%; max-width: 400px; }
        .formulario-login h2 { color: #333; margin-bottom: 10px; font-size: 1.8rem; }
        .formulario-login .subtitulo { color: #666; margin-bottom: 30px; font-size: 0.95rem; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #444; font-size: 0.9rem; }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-group input:focus { outline: none; border-color: #B30000; }

        .btn-ingresar {
            width: 100%;
            padding: 14px;
            background-color: #B30000;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-ingresar:hover { background-color: #8B0000; }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
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