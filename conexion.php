<?php

$driver      = getenv('DB_DRIVER') ?: 'mysql';   // 'mysql' o 'pgsql'
$host        = getenv('DB_HOST')   ?: '127.0.0.1';
$puerto      = getenv('DB_PORT')   ?: ($driver === 'pgsql' ? '5432' : '3306');
$baseDeDatos = getenv('DB_NAME')   ?: 'biblio_express';
$usuario     = getenv('DB_USER')   ?: ($driver === 'pgsql' ? 'postgres' : 'root');
$clave       = getenv('DB_PASS')   ?: '';        // En XAMPP la clave de root está vacía

// ---------- Cadena de conexión DSN----------
$dsn = "$driver:host=$host;port=$puerto;dbname=$baseDeDatos";
if ($driver === 'mysql') {
    $dsn .= ';charset=utf8mb4';
}

// ---------- Conexion con manejo de errores por excepciones ----------
try {
    $pdo = new PDO($dsn, $usuario, $clave, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // los errores lanzan excepciones
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // filas como arreglo asociativo
        PDO::ATTR_EMULATE_PREPARES   => false,                  // consultas preparadas reales
    ]);
} catch (PDOException $e) {
    // El detalle tecnico va al registro del servidor nunca a la pantalla
    // asi no se exponen usuario clave ni datos del servidor
    error_log('BiblioExpress - error de conexión: ' . $e->getMessage());
    http_response_code(500);
    exit('No se pudo conectar con la base de datos. Revisa la configuración en conexion.php.');
}
