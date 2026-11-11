<?php
/**
 * config/database.php
 * Conexión a la base de datos usando PDO.
 */

// --- Configuración de la Base de Datos ---
// Ajusta estos valores si tu XAMPP tiene otra configuración.
$host = 'localhost';
$dbname = 'sistema_becas_itca';
$username = 'root';       // Usuario por defecto de XAMPP
$password = '';           // Contraseña por defecto de XAMPP (vacía)
$charset = 'utf8mb4';

// --- DSN (Data Source Name) ---
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// --- Opciones de PDO para mayor seguridad y manejo de errores ---
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en errores
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve arrays asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa sentencias preparadas reales
];

// --- Intentar la conexión ---
try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Si falla, detenemos la ejecución y mostramos un mensaje genérico
    // (No mostrar detalles del error en producción por seguridad).
    die("Error de conexión a la base de datos. Por favor, verifica que XAMPP y MySQL estén activos.");
}
?>