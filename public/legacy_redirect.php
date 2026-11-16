<?php
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$publicPath = preg_replace('~/(?:admin|estudiante)/[^/]+$~', '', $scriptPath);
if ($publicPath === $scriptPath) {
    $publicPath = rtrim(str_replace('\\', '/', dirname($scriptPath)), '/');
}

$routes = [
    '/admin/dashboard.php' => '#/admin',
    '/admin/solicitudes.php' => '#/admin/solicitudes',
    '/admin/seleccionados.php' => '#/admin/seleccionados',
    '/admin/becados.php' => '#/admin/becarios',
    '/admin/carga_masiva.php' => '#/admin/carga-masiva',
    '/admin/catalogo_becas.php' => '#/admin/becas',
    '/admin/instituciones.php' => '#/admin/instituciones',
    '/estudiante/dashboard.php' => '#/student',
    '/estudiante/solicitud.php' => '#/student/solicitud',
];

$requestPath = preg_replace('~^' . preg_quote($publicPath, '~') . '~', '', $scriptPath);
if ($requestPath === '/admin/descargar_plantilla.php') {
    header('Location: ' . $publicPath . '/api/index.php?route=admin/template');
    exit;
}

$route = $routes[$requestPath] ?? '#/login';
if ($requestPath === '/admin/solicitudes.php' && isset($_GET['estado'])) {
    $route .= '?status=' . rawurlencode((string)$_GET['estado']);
}

header('Location: ' . $publicPath . '/app/' . $route, true, 302);
exit;
