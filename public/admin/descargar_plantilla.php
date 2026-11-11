<?php
/**
 * public/admin/descargar_plantilla.php
 * Genera y descarga el archivo CSV de plantilla para la carga masiva.
 */
require_once '../../includes/auth.php';
verificar_rol('administrador');

$filas = [
    ['Código', 'Nombre Completo', 'Tel. Fijo', 'Tel. Móvil', 'Correo ITCA', 'Correo Personal'],
    ['2024002', 'Juan Pérez García', '2440-1234', '7123-9876', 'jperez@itca.edu.sv', 'juan@gmail.com'],
    ['2024003', 'Ana Sofía Ramírez', '2440-5678', '7234-8765', 'aramirez@itca.edu.sv', 'ana@gmail.com'],
    ['2024004', 'Carlos Eduardo Mendoza', '2440-9988', '7890-1234', 'cmendoza@itca.edu.sv', 'carlos.mendoza@gmail.com']
];

$nombre_archivo = 'plantilla_aspirantes_itca.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $nombre_archivo);

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF"); // BOM UTF-8

foreach ($filas as $fila) {
    fputcsv($output, $fila);
}

fclose($output);
exit();