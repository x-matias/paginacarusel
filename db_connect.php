<?php
// --- Cargar variables del archivo .env ---
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // ignorar comentarios
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

$host       = $_ENV['DB_HOST']  ?? 'localhost';
$usuario    = $_ENV['DB_USER']  ?? 'root';
$contrasena = $_ENV['DB_PASS']  ?? '';
$baseDatos  = $_ENV['DB_NAME']  ?? 'carousel_db';

// --- Paso 1: Conectar sin seleccionar base de datos ---
$conn = new mysqli($host, $usuario, $contrasena);

if ($conn->connect_error) {
    die("Error de conexión al servidor MySQL: " . $conn->connect_error);
}

// --- Paso 2: Crear la base de datos si no existe ---
$sql = "CREATE DATABASE IF NOT EXISTS `$baseDatos` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$conn->query($sql)) {
    die("Error al crear la base de datos: " . $conn->error);
}

// --- Paso 3: Seleccionar la base de datos ---
if (!$conn->select_db($baseDatos)) {
    die("Error al seleccionar la base de datos: " . $conn->error);
}

// --- Paso 4: Crear tabla de usuarios si no existe ---
$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
    contrasena_hash VARCHAR(255) NOT NULL,
    creado_en    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$conn->query($sql)) {
    die("Error al crear la tabla 'usuarios': " . $conn->error);
}

// --- Paso 5: Crear tabla de fotos si no existe ---
$sql = "CREATE TABLE IF NOT EXISTS fotos (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo   VARCHAR(255) NOT NULL,
    subido_en    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$conn->query($sql)) {
    die("Error al crear la tabla 'fotos': " . $conn->error);
}
?>