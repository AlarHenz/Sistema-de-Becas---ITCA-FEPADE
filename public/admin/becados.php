<?php
/**
 * public/admin/becados.php
 * Gestión de becados y exportación (HU-06).
 */
require_once '../../includes/auth.php';
verificar_rol('administrador');

require_once '../../config/database.php';

// Obtenemos a los estudiantes que tienen una solicitud APROBADA
// (Para que sea más realista, también deberían tener una beca asignada, pero 
// como no hemos hecho la asignación de becas aún, mostraremos los aprobados).
$sql = "SELECT s.id AS id_solicitud, u.nombre_completo, u.carnet, u.correo_institucional, u.correo_personal, 
               e.nombre_especialidad, s.fecha_solicitud, b.nombre_beca
        FROM solicitudes s
        JOIN usuarios u ON s.id_usuario = u.id
        LEFT JOIN especialidades e ON s.id_especialidad = e.id
        LEFT JOIN becas b ON s.id_beca = b.id
        WHERE s.estado = 'aprobada'
        ORDER BY s.fecha_solicitud DESC";

$stmt = $pdo->query($sql);
$becados = $stmt->fetchAll();
$total_becados = count($becados);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Becarios - ITCA FEPADE</title>
    <link rel="stylesheet" href="/Sistema-de-Becas---ITCA-FEPADE/public/css/style.css">
</head>
<body>
    <div class="layout">
        <?php include '../../includes/sidebar_admin.php'; ?>
        
        <div class="main-content">
            <?php include '../../includes/header.php'; ?>
            
            <div class="contenido">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h1 class="page-title" style="margin-bottom: 4px;">Aspirantes con Beca (Becarios)</h1>
                        <p class="subtitulo" style="margin-bottom: 0;">Total de becados aprobados: <strong><?php echo $total_becados; ?></strong></p>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <!-- Botones de exportación -->
                        <button onclick="exportarExcel()" class="btn btn-primario">Generar Excel</button>
                        <button onclick="generarListaCorreos()" class="btn btn-secundario">Generar Lista de Correos</button>
                    </div>
                </div>

                <div class="tabla-container">
                    <table id="tabla-becados">
                        <thead>
                            <tr>
                                <th>ID Solicitud</th>
                                <th>Estudiante</th>
                                <th>Carnet</th>
                                <th>Correo ITCA</th>
                                <th>Especialidad</th>
                                <th>Beca Asignada</th>
                                <th>Fecha Aprobación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($total_becados > 0): ?>
                                <?php foreach ($becados as $b): ?>
                                    <tr>
                                        <td>#<?php echo $b['id_solicitud']; ?></td>
                                        <td><?php echo htmlspecialchars($b['nombre_completo']); ?></td>
                                        <td><?php echo htmlspecialchars($b['carnet']); ?></td>
                                        <td><?php echo htmlspecialchars($b['correo_institucional']); ?></td>
                                        <td><?php echo htmlspecialchars($b['nombre_especialidad'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($b['nombre_beca'] ?? 'Sin asignar'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($b['fecha_solicitud'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px;">No hay estudiantes con beca aprobada aún.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>

    <!-- Scripts para la exportación (HU-06) -->
    <script>
        // 1. Función para exportar la tabla a un archivo CSV (compatible con Excel)
        function exportarExcel() {
            const tabla = document.getElementById('tabla-becados');
            let csvContent = "";
            
            // Extraer encabezados
            const encabezados = [];
            tabla.querySelectorAll('thead th').forEach(th => encabezados.push(`"${th.innerText}"`));
            csvContent += encabezados.join(",") + "\n";

            // Extraer filas
            tabla.querySelectorAll('tbody tr').forEach(fila => {
                const celdas = [];
                fila.querySelectorAll('td').forEach(td => celdas.push(`"${td.innerText}"`));
                if(celdas.length > 0) csvContent += celdas.join(",") + "\n";
            });

            // Crear el archivo y descargarlo
            const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' }); // \uFEFF es el BOM para que Excel lea bien los acentos
            const url = URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.setAttribute("href", url);
            link.setAttribute("download", "listado_becados.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // 2. Función para generar la lista de correos (mailto)
        function generarListaCorreos() {
            const correos = [];
            // Buscamos la columna de correo (índice 3) en cada fila
            document.querySelectorAll('#tabla-becados tbody tr').forEach(fila => {
                const celdas = fila.querySelectorAll('td');
                if (celdas.length > 3) {
                    const correo = celdas[3].innerText.trim();
                    if (correo && correo !== 'N/A') {
                        correos.push(correo);
                    }
                }
            });

            if (correos.length === 0) {
                alert("No hay correos para generar.");
                return;
            }

            // Creamos el enlace mailto con copia oculta (BCC) para no exponer los correos entre sí
            const mailtoLink = `mailto:?bcc=${correos.join(',')}&subject=Notificación de Beca ITCA-FEPADE`;
            
            // Abrimos el cliente de correo
            window.location.href = mailtoLink;
        }
    </script>
</body>
</html>