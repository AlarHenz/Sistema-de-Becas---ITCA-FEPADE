<?php
/**
 * public/admin/descargar_plantilla.php
 * Genera un archivo CSV de ejemplo (HU-02).
 */
require_once '../../includes/auth.php';
verificar_rol('administrador');

// Nombre del archivo
$nombre_archivo = 'plantilla_aspirantes.csv';

// Encabezados para forzar la descarga
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $nombre_archivo);

// Abrimos la salida estándar de PHP
$output = fopen('php://output', 'w');

// Agregamos el BOM para que Excel lea bien los acentos
fwrite($output, "\xEF\xBB\xBF");

// Escribimos la fila de encabezados
fputcsv($output, ['Código', 'Nombre Completo', 'Tel. Fijo', 'Tel. Móvil', 'Correo ITCA', 'Correo Personal']);

// Escribimos filas de ejemplo
fputcsv($output, ['2024002', 'Juan Pérez García', '2440-1234', '7123-9876', 'jperez@itca.edu.sv', 'juan@gmail.com']);
fputcsv($output, ['2024003', 'Ana Sofía Ramírez', '2440-5678', '7234-8765', 'aramirez@itca.edu.sv', 'ana@gmail.com']);

fclose($output);
exit();
?>