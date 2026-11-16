<?php
declare(strict_types=1);

define('API_REQUEST', true);
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Cache-Control: no-store');

function respond(int $status, array $data): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function fail(int $status, string $message): void
{
    respond($status, ['message' => $message]);
}

function method_is(string $method): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === $method;
}

function require_method(string $method): void
{
    if (!method_is($method)) {
        header('Allow: ' . $method);
        fail(405, 'Método no permitido.');
    }
}

function require_role(string $role): void
{
    if (empty($_SESSION['usuario_id'])) {
        fail(401, 'Tu sesión ha expirado. Inicia sesión nuevamente.');
    }
    if (($_SESSION['rol'] ?? '') !== $role) {
        fail(403, 'No tienes permiso para realizar esta acción.');
    }
}

function require_login(): void
{
    if (empty($_SESSION['usuario_id'])) {
        fail(401, 'Tu sesión ha expirado. Inicia sesión nuevamente.');
    }
}

function require_csrf(): void
{
    $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';
    if (!$expected || !$provided || !hash_equals($expected, $provided)) {
        fail(403, 'La solicitud expiró. Recarga la página e inténtalo nuevamente.');
    }
}

function request_data(): array
{
    if (isset($_POST['route']) || $_POST) {
        return $_POST;
    }
    $body = file_get_contents('php://input');
    if (!$body) {
        return [];
    }
    $data = json_decode($body, true);
    return is_array($data) ? $data : [];
}

function positive_id($value): ?int
{
    $id = filter_var($value, FILTER_VALIDATE_INT);
    return $id !== false && $id > 0 ? $id : null;
}

function check_upload(array $file, array $extensions, int $maxBytes): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return 'No se pudo recibir el archivo. Verifica el tamaño permitido por el servidor.';
    }
    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($extension, $extensions, true)) {
        return 'El formato del archivo no está permitido.';
    }
    if (($file['size'] ?? 0) > $maxBytes) {
        return 'El archivo supera el tamaño máximo permitido.';
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowedMimeTypes = [
        'csv' => ['text/plain', 'text/csv', 'application/csv', 'application/vnd.ms-excel', 'application/octet-stream'],
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
    ];
    if (!in_array($mime, $allowedMimeTypes[$extension] ?? [], true)) {
        return 'El contenido del archivo no coincide con su formato.';
    }
    return null;
}

function finish_import(PDO $pdo, array $file): array
{
    $uploadError = check_upload($file, ['csv'], 10 * 1024 * 1024);
    if ($uploadError) {
        fail(422, $uploadError);
    }

    $handle = fopen($file['tmp_name'], 'r');
    if ($handle === false) {
        fail(422, 'No fue posible abrir el archivo CSV.');
    }

    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($handle);
    }
    $position = ftell($handle);
    $firstLine = fgets($handle);
    if ($firstLine === false) {
        fclose($handle);
        fail(422, 'El archivo CSV está vacío.');
    }
    $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
    fseek($handle, $position);

    $rows = [];
    while (($row = fgetcsv($handle, 10000, $delimiter)) !== false) {
        $normalized = [];
        for ($column = 0; $column < 6; $column++) {
            $normalized[$column] = trim((string)($row[$column] ?? ''));
        }
        $rows[] = $normalized;
    }
    fclose($handle);

    if (count($rows) <= 1) {
        fail(422, 'El archivo no contiene registros de datos para procesar.');
    }

    $inserted = 0;
    $duplicates = 0;
    $errors = 0;
    $log = [];
    $added = [];
    $seenCarnets = [];
    $seenEmails = [];
    $passwordHash = password_hash('123456', PASSWORD_BCRYPT);
    $check = $pdo->prepare('SELECT id FROM usuarios WHERE carnet = ? OR correo_institucional = ? LIMIT 1');
    $insertUser = $pdo->prepare(
        "INSERT INTO usuarios (carnet, nombre_completo, telefono_fijo, telefono_movil, correo_institucional, correo_personal, password, rol)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'estudiante')"
    );
    $insertApplication = $pdo->prepare("INSERT INTO solicitudes (id_usuario, estado) VALUES (?, 'borrador')");

    try {
        $pdo->beginTransaction();
        foreach (array_slice($rows, 1, null, true) as $index => $row) {
            $rowNumber = $index + 1;
            if (!array_filter($row, static fn($value) => $value !== '')) {
                continue;
            }

            [$carnet, $name, $landline, $mobile, $institutionalEmail, $personalEmail] = $row;
            if ($carnet === '' || $name === '' || $institutionalEmail === '') {
                $errors++;
                $log[] = ['row' => $rowNumber, 'carnet' => $carnet ?: '(Sin carnet)', 'type' => 'error', 'message' => 'Faltan campos obligatorios: carnet, nombre completo y correo institucional.'];
                continue;
            }
            if (!filter_var($institutionalEmail, FILTER_VALIDATE_EMAIL)) {
                $errors++;
                $log[] = ['row' => $rowNumber, 'carnet' => $carnet, 'type' => 'error', 'message' => 'El correo institucional no tiene un formato válido.'];
                continue;
            }

            $normalizedCarnet = strtolower($carnet);
            $normalizedEmail = strtolower($institutionalEmail);
            if (isset($seenCarnets[$normalizedCarnet]) || isset($seenEmails[$normalizedEmail])) {
                $duplicates++;
                $log[] = ['row' => $rowNumber, 'carnet' => $carnet, 'type' => 'warning', 'message' => 'Registro duplicado dentro del archivo.'];
                continue;
            }

            $check->execute([$carnet, $institutionalEmail]);
            if ($check->fetchColumn()) {
                $duplicates++;
                $log[] = ['row' => $rowNumber, 'carnet' => $carnet, 'type' => 'warning', 'message' => 'El carnet o correo ya está registrado.'];
                continue;
            }

            $insertUser->execute([
                $carnet,
                $name,
                $landline !== '' ? $landline : null,
                $mobile !== '' ? $mobile : null,
                $institutionalEmail,
                $personalEmail !== '' ? $personalEmail : null,
                $passwordHash,
            ]);
            $userId = (int)$pdo->lastInsertId();
            $insertApplication->execute([$userId]);
            $inserted++;
            $seenCarnets[$normalizedCarnet] = true;
            $seenEmails[$normalizedEmail] = true;
            $added[] = ['carnet' => $carnet, 'name' => $name, 'email' => $institutionalEmail, 'mobile' => $mobile ?: 'N/D'];
        }
        $pdo->commit();
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Bulk import failed: ' . $exception->getMessage());
        fail(500, 'Ocurrió un error de base de datos durante la importación.');
    }

    return [
        'filename' => basename($file['name']),
        'total' => $inserted + $duplicates + $errors,
        'inserted' => $inserted,
        'duplicates' => $duplicates,
        'errors' => $errors,
        'log' => $log,
        'added' => $added,
    ];
}

$route = trim((string)($_GET['route'] ?? ''), '/');
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if (!in_array($route, ['auth/session', 'auth/logout', 'admin/template'], true)) {
    try {
        require_once __DIR__ . '/../../config/database.php';
    } catch (PDOException $exception) {
        error_log('API database connection failed: ' . $exception->getMessage());
        fail(500, 'No fue posible conectar con la base de datos. Verifica que MySQL esté iniciado.');
    }
}

try {
    switch ($route) {
        case 'auth/session':
            require_method('GET');
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            respond(200, [
                'csrfToken' => $_SESSION['csrf_token'],
                'user' => isset($_SESSION['usuario_id']) ? [
                    'id' => (int)$_SESSION['usuario_id'],
                    'carnet' => $_SESSION['carnet'],
                    'name' => $_SESSION['nombre'],
                    'role' => $_SESSION['rol'],
                ] : null,
            ]);

        case 'auth/login':
            require_method('POST');
            $data = request_data();
            require_csrf();
            $carnet = trim((string)($data['carnet'] ?? ''));
            $password = (string)($data['password'] ?? '');
            if ($carnet === '' || $password === '') {
                fail(422, 'Ingresa tu carnet y contraseña.');
            }
            $statement = $pdo->prepare('SELECT id, carnet, nombre_completo, password, rol FROM usuarios WHERE carnet = ? AND activo = 1');
            $statement->execute([$carnet]);
            $user = $statement->fetch();
            if (!$user || !password_verify($password, $user['password'])) {
                fail(401, 'Carnet o contraseña incorrectos.');
            }
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int)$user['id'];
            $_SESSION['carnet'] = $user['carnet'];
            $_SESSION['nombre'] = $user['nombre_completo'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            respond(200, [
                'csrfToken' => $_SESSION['csrf_token'],
                'user' => ['id' => (int)$user['id'], 'carnet' => $user['carnet'], 'name' => $user['nombre_completo'], 'role' => $user['rol']],
            ]);

        case 'auth/logout':
            require_method('POST');
            require_login();
            require_csrf();
            session_unset();
            session_destroy();
            respond(200, ['success' => true]);

        case 'admin/dashboard':
            require_method('GET');
            require_role('administrador');
            respond(200, [
                'total' => (int)$pdo->query('SELECT COUNT(*) FROM solicitudes')->fetchColumn(),
                'inReview' => (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'en_revision'")->fetchColumn(),
                'approved' => (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'aprobada'")->fetchColumn(),
                'selected' => (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado IN ('en_revision', 'aprobada')")->fetchColumn(),
                'beneficiaries' => (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'aprobada'")->fetchColumn(),
                'incomplete' => (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'incompleta'")->fetchColumn(),
                'complete' => (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado IN ('aprobada', 'en_revision')")->fetchColumn(),
            ]);

        case 'admin/requests':
            require_role('administrador');
            if ($method === 'GET') {
                $filter = (string)($_GET['status'] ?? 'todas');
                $allowed = ['todas', 'borrador', 'en_revision', 'aprobada', 'rechazada', 'incompleta'];
                if (!in_array($filter, $allowed, true)) {
                    fail(422, 'El filtro de estado no es válido.');
                }
                $sql = "SELECT s.*, u.nombre_completo, u.carnet, e.nombre_especialidad
                        FROM solicitudes s
                        JOIN usuarios u ON s.id_usuario = u.id
                        LEFT JOIN especialidades e ON s.id_especialidad = e.id";
                if ($filter !== 'todas') {
                    $sql .= ' WHERE s.estado = :status';
                }
                $sql .= ' ORDER BY s.fecha_solicitud DESC';
                $statement = $pdo->prepare($sql);
                if ($filter !== 'todas') {
                    $statement->bindValue(':status', $filter);
                }
                $statement->execute();
                respond(200, [
                    'items' => $statement->fetchAll(),
                    'counts' => [
                        'todas' => (int)$pdo->query('SELECT COUNT(*) FROM solicitudes')->fetchColumn(),
                        'en_revision' => (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'en_revision'")->fetchColumn(),
                        'aprobada' => (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'aprobada'")->fetchColumn(),
                        'rechazada' => (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'rechazada'")->fetchColumn(),
                        'incompleta' => (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'incompleta'")->fetchColumn(),
                        'borrador' => (int)$pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'borrador'")->fetchColumn(),
                    ],
                ]);
            }
            require_method('PUT');
            require_csrf();
            $data = request_data();
            $id = positive_id($data['id'] ?? null);
            $state = (string)($data['state'] ?? '');
            $observation = trim((string)($data['observation'] ?? ''));
            if (!$id || !in_array($state, ['borrador', 'en_revision', 'aprobada', 'rechazada', 'incompleta'], true)) {
                fail(422, 'Los datos enviados no son válidos.');
            }
            $statement = $pdo->prepare('UPDATE solicitudes SET estado = ?, observaciones_admin = ? WHERE id = ?');
            $statement->execute([$state, $observation, $id]);
            if ($statement->rowCount() === 0) {
                $exists = $pdo->prepare('SELECT id FROM solicitudes WHERE id = ?');
                $exists->execute([$id]);
                if (!$exists->fetchColumn()) {
                    fail(404, 'No se encontró la solicitud.');
                }
            }
            if ($observation !== '') {
                $owner = $pdo->prepare('SELECT id_usuario FROM solicitudes WHERE id = ?');
                $owner->execute([$id]);
                $userId = $owner->fetchColumn();
                $notification = $pdo->prepare('INSERT INTO notificaciones (id_usuario, id_solicitud, mensaje) VALUES (?, ?, ?)');
                $notification->execute([$userId, $id, $observation]);
            }
            respond(200, ['success' => true]);

        case 'admin/scholarships':
            require_role('administrador');
            if ($method === 'GET') {
                respond(200, ['items' => $pdo->query('SELECT * FROM becas ORDER BY id DESC')->fetchAll()]);
            }
            require_csrf();
            $data = request_data();
            $id = positive_id($data['id'] ?? $_GET['id'] ?? null);
            if ($method === 'POST') {
                $name = trim((string)($data['name'] ?? ''));
                if ($name === '') {
                    fail(422, 'El nombre de la beca es obligatorio.');
                }
                $statement = $pdo->prepare('INSERT INTO becas (nombre_beca, descripcion) VALUES (?, ?)');
                $statement->execute([$name, trim((string)($data['description'] ?? ''))]);
                respond(201, ['success' => true]);
            }
            if ($method === 'PUT') {
                $name = trim((string)($data['name'] ?? ''));
                if (!$id || $name === '') {
                    fail(422, 'Los datos enviados no son válidos.');
                }
                $statement = $pdo->prepare('UPDATE becas SET nombre_beca = ?, descripcion = ? WHERE id = ?');
                $statement->execute([$name, trim((string)($data['description'] ?? '')), $id]);
                respond(200, ['success' => true]);
            }
            if ($method === 'DELETE') {
                if (!$id) {
                    fail(422, 'Selecciona una beca válida.');
                }
                $check = $pdo->prepare('SELECT COUNT(*) FROM solicitudes WHERE id_beca = ?');
                $check->execute([$id]);
                if ((int)$check->fetchColumn() > 0) {
                    fail(409, 'No se puede eliminar la beca porque tiene solicitudes asociadas.');
                }
                $statement = $pdo->prepare('DELETE FROM becas WHERE id = ?');
                $statement->execute([$id]);
                respond(200, ['success' => true]);
            }
            if ($method === 'PATCH') {
                $statement = $pdo->prepare("UPDATE becas SET estado = IF(estado = 'activa', 'inactiva', 'activa') WHERE id = ?");
                if (!$id) {
                    fail(422, 'Selecciona una beca válida.');
                }
                $statement->execute([$id]);
                respond(200, ['success' => true]);
            }
            require_method('POST, PUT, PATCH, DELETE');

        case 'admin/institutions':
            require_role('administrador');
            if ($method === 'GET') {
                $institutions = $pdo->query('SELECT * FROM instituciones ORDER BY id ASC')->fetchAll();
                $specialties = $pdo->query('SELECT * FROM especialidades ORDER BY nombre_especialidad ASC')->fetchAll();
                respond(200, ['institutions' => $institutions, 'specialties' => $specialties]);
            }
            require_csrf();
            $data = request_data();
            if ($method === 'POST') {
                $name = trim((string)($data['name'] ?? ''));
                if ($name === '') {
                    fail(422, 'El nombre de la institución es obligatorio.');
                }
                $statement = $pdo->prepare('INSERT INTO instituciones (nombre_institucion) VALUES (?)');
                $statement->execute([$name]);
                respond(201, ['success' => true]);
            }
            if ($method === 'PUT') {
                $institutionId = positive_id($data['institutionId'] ?? null);
                $name = trim((string)($data['name'] ?? ''));
                if (!$institutionId || $name === '') {
                    fail(422, 'Selecciona una institución y proporciona el nombre de la especialidad.');
                }
                $statement = $pdo->prepare('INSERT INTO especialidades (id_institucion, nombre_especialidad) VALUES (?, ?)');
                $statement->execute([$institutionId, $name]);
                respond(201, ['success' => true]);
            }
            if ($method === 'DELETE') {
                $specialtyId = positive_id($_GET['id'] ?? null);
                if (!$specialtyId) {
                    fail(422, 'Selecciona una especialidad válida.');
                }
                $statement = $pdo->prepare('DELETE FROM especialidades WHERE id = ?');
                $statement->execute([$specialtyId]);
                respond(200, ['success' => true]);
            }
            require_method('POST, PUT, DELETE');

        case 'admin/selected':
            require_role('administrador');
            if ($method === 'GET') {
                $items = $pdo->query(
                    "SELECT s.id AS id_solicitud, s.id_beca, b.nombre_beca, b.estado AS estado_beca,
                            u.nombre_completo, u.carnet, e.nombre_especialidad, s.estado, s.fecha_solicitud, s.prioridad
                     FROM solicitudes s
                     JOIN usuarios u ON s.id_usuario = u.id
                     LEFT JOIN especialidades e ON s.id_especialidad = e.id
                     LEFT JOIN becas b ON s.id_beca = b.id
                     WHERE s.estado IN ('en_revision', 'aprobada')
                     ORDER BY s.fecha_solicitud DESC"
                )->fetchAll();
                $scholarships = $pdo->query("SELECT id, nombre_beca FROM becas WHERE estado = 'activa' ORDER BY nombre_beca")->fetchAll();
                respond(200, ['items' => $items, 'scholarships' => $scholarships]);
            }
            require_method('PUT');
            require_csrf();
            $data = request_data();
            $applicationId = positive_id($data['applicationId'] ?? null);
            $scholarshipId = ($data['scholarshipId'] ?? '') === '' ? null : positive_id($data['scholarshipId']);
            if (!$applicationId || (($data['scholarshipId'] ?? '') !== '' && !$scholarshipId)) {
                fail(422, 'Los datos enviados no son válidos.');
            }
            $statement = $pdo->prepare("SELECT id, id_beca FROM solicitudes WHERE id = ? AND estado IN ('en_revision', 'aprobada')");
            $statement->execute([$applicationId]);
            $application = $statement->fetch();
            if (!$application) {
                fail(404, 'No se encontró una solicitud gestionable.');
            }
            if ($scholarshipId !== null) {
                $statement = $pdo->prepare('SELECT estado FROM becas WHERE id = ?');
                $statement->execute([$scholarshipId]);
                $scholarshipState = $statement->fetchColumn();
                if ($scholarshipState !== 'activa' && !($scholarshipState === 'inactiva' && (int)$application['id_beca'] === $scholarshipId)) {
                    fail(422, 'Selecciona una beca activa.');
                }
            }
            $statement = $pdo->prepare('UPDATE solicitudes SET id_beca = ? WHERE id = ?');
            $statement->execute([$scholarshipId, $applicationId]);
            respond(200, ['success' => true]);

        case 'admin/beneficiaries':
            require_method('GET');
            require_role('administrador');
            $items = $pdo->query(
                "SELECT s.id AS id_solicitud, u.nombre_completo, u.carnet, u.correo_institucional, u.correo_personal,
                        e.nombre_especialidad, s.fecha_solicitud, b.nombre_beca
                 FROM solicitudes s
                 JOIN usuarios u ON s.id_usuario = u.id
                 LEFT JOIN especialidades e ON s.id_especialidad = e.id
                 LEFT JOIN becas b ON s.id_beca = b.id
                 WHERE s.estado = 'aprobada'
                 ORDER BY s.fecha_solicitud DESC"
            )->fetchAll();
            respond(200, ['items' => $items]);

        case 'admin/import':
            require_method('POST');
            require_role('administrador');
            require_csrf();
            if (!isset($_FILES['file'])) {
                fail(422, 'Selecciona un archivo CSV para cargar.');
            }
            respond(200, finish_import($pdo, $_FILES['file']));

        case 'admin/template':
            require_method('GET');
            require_role('administrador');
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=plantilla_aspirantes_itca.csv');
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Código', 'Nombre Completo', 'Tel. Fijo', 'Tel. Móvil', 'Correo ITCA', 'Correo Personal']);
            fputcsv($output, ['2024002', 'Juan Pérez García', '2440-1234', '7123-9876', 'jperez@itca.edu.sv', 'juan@gmail.com']);
            fputcsv($output, ['2024003', 'Ana Sofía Ramírez', '2440-5678', '7234-8765', 'aramirez@itca.edu.sv', 'ana@gmail.com']);
            fputcsv($output, ['2024004', 'Carlos Eduardo Mendoza', '2440-9988', '7890-1234', 'cmendoza@itca.edu.sv', 'carlos.mendoza@gmail.com']);
            fclose($output);
            exit;

        case 'student/dashboard':
            require_method('GET');
            require_role('estudiante');
            $statement = $pdo->prepare('SELECT * FROM solicitudes WHERE id_usuario = ? ORDER BY fecha_solicitud DESC LIMIT 1');
            $statement->execute([$_SESSION['usuario_id']]);
            $application = $statement->fetch();
            $statement = $pdo->prepare('SELECT * FROM notificaciones WHERE id_usuario = ? AND leida = 0 ORDER BY fecha_creacion DESC');
            $statement->execute([$_SESSION['usuario_id']]);
            respond(200, ['application' => $application ?: null, 'notifications' => $statement->fetchAll()]);

        case 'student/application':
            require_role('estudiante');
            $userId = (int)$_SESSION['usuario_id'];
            if ($method === 'GET') {
                $statement = $pdo->prepare('SELECT * FROM solicitudes WHERE id_usuario = ? ORDER BY id DESC LIMIT 1');
                $statement->execute([$userId]);
                $application = $statement->fetch();
                $document = null;
                if ($application) {
                    $statement = $pdo->prepare("SELECT * FROM documentos WHERE id_solicitud = ? AND tipo_documento = 'dui' LIMIT 1");
                    $statement->execute([$application['id']]);
                    $document = $statement->fetch() ?: null;
                }
                $statement = $pdo->prepare('SELECT id, carnet, nombre_completo, correo_institucional, telefono_movil FROM usuarios WHERE id = ?');
                $statement->execute([$userId]);
                $user = $statement->fetch();
                $specialties = $pdo->query(
                    'SELECT e.*, i.nombre_institucion FROM especialidades e
                     JOIN instituciones i ON e.id_institucion = i.id ORDER BY i.nombre_institucion, e.nombre_especialidad'
                )->fetchAll();
                respond(200, ['user' => $user, 'application' => $application ?: null, 'document' => $document, 'specialties' => $specialties]);
            }
            require_method('POST');
            require_csrf();
            $data = request_data();
            $specialtyId = positive_id($data['id_especialidad'] ?? null);
            if (!$specialtyId) {
                fail(422, 'Debes seleccionar una especialidad de interés.');
            }
            $statement = $pdo->prepare('SELECT id FROM especialidades WHERE id = ?');
            $statement->execute([$specialtyId]);
            if (!$statement->fetchColumn()) {
                fail(422, 'La especialidad seleccionada ya no está disponible.');
            }
            $file = $_FILES['documento_dui'] ?? ['error' => UPLOAD_ERR_NO_FILE];
            $uploadError = check_upload($file, ['pdf', 'jpg', 'jpeg', 'png'], 5 * 1024 * 1024);
            if ($uploadError) {
                fail(422, $uploadError);
            }
            $statement = $pdo->prepare('SELECT id FROM solicitudes WHERE id_usuario = ? ORDER BY id DESC LIMIT 1');
            $statement->execute([$userId]);
            $existingId = $statement->fetchColumn();
            $action = ($data['accion'] ?? 'enviar') === 'borrador' ? 'borrador' : 'en_revision';
            if ($existingId) {
                $statement = $pdo->prepare('UPDATE solicitudes SET id_especialidad = ?, estado = ?, fecha_actualizacion = NOW() WHERE id = ?');
                $statement->execute([$specialtyId, $action, $existingId]);
                $applicationId = (int)$existingId;
            } else {
                $statement = $pdo->prepare('INSERT INTO solicitudes (id_usuario, id_especialidad, estado) VALUES (?, ?, ?)');
                $statement->execute([$userId, $specialtyId, $action]);
                $applicationId = (int)$pdo->lastInsertId();
            }
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = 'dui_' . $userId . '_' . bin2hex(random_bytes(12)) . '.' . $extension;
                $destination = __DIR__ . '/../uploads/' . $filename;
                if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0755, true) && !is_dir(dirname($destination))) {
                    fail(500, 'No fue posible preparar el almacenamiento de documentos.');
                }
                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    fail(500, 'Error al guardar el archivo en el servidor.');
                }
                $pdo->prepare("DELETE FROM documentos WHERE id_solicitud = ? AND tipo_documento = 'dui'")->execute([$applicationId]);
                $statement = $pdo->prepare("INSERT INTO documentos (id_solicitud, tipo_documento, ruta_archivo) VALUES (?, 'dui', ?)");
                $statement->execute([$applicationId, $filename]);
            }
            respond(200, ['success' => true, 'message' => $action === 'borrador' ? 'Borrador guardado exitosamente.' : 'Solicitud enviada a revisión exitosamente.']);

        case 'student/document':
            require_method('GET');
            require_role('estudiante');
            $documentId = positive_id($_GET['id'] ?? null);
            if (!$documentId) {
                fail(422, 'Selecciona un documento válido.');
            }
            $statement = $pdo->prepare(
                'SELECT d.ruta_archivo FROM documentos d
                 JOIN solicitudes s ON d.id_solicitud = s.id
                 WHERE d.id = ? AND s.id_usuario = ?'
            );
            $statement->execute([$documentId, $_SESSION['usuario_id']]);
            $filename = $statement->fetchColumn();
            if (!$filename || basename($filename) !== $filename) {
                fail(404, 'No se encontró el documento.');
            }
            $path = __DIR__ . '/../uploads/' . $filename;
            if (!is_file($path)) {
                fail(404, 'El archivo ya no está disponible.');
            }
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path) ?: 'application/octet-stream';
            header('Content-Type: ' . $mime);
            header('Content-Disposition: inline; filename="' . rawurlencode($filename) . '"');
            header('X-Content-Type-Options: nosniff');
            readfile($path);
            exit;

        default:
            fail(404, 'La ruta solicitada no existe.');
    }
} catch (PDOException $exception) {
    error_log('API database error: ' . $exception->getMessage());
    fail(500, 'Ocurrió un error al procesar la solicitud.');
}
