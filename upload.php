<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';

// Tamaño máximo permitido: 10 MB
define('MAX_FILE_SIZE', 10 * 1024 * 1024);

function uploadError(string $msg, string $detail = ''): void {
    $full = htmlspecialchars($msg);
    $det  = $detail ? '<br><small style="color:#fca5a5">' . htmlspecialchars($detail) . '</small>' : '';
    echo <<<HTML
    <!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
    <title>Error de subida</title>
    <style>
        body{font-family:sans-serif;background:#0f172a;color:#f8fafc;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
        .box{background:rgba(239,68,68,.15);border:1px solid #ef4444;border-radius:16px;padding:2rem 3rem;text-align:center;max-width:500px}
        h2{color:#f87171;margin-bottom:.5rem}
        a{color:#60a5fa;margin-top:1.5rem;display:inline-block;text-decoration:none}
    </style></head><body>
    <div class="box">
        <h2>⚠️ Error al subir la imagen</h2>
        <p>{$full}{$det}</p>
        <a href="index.php">← Volver a la galería</a>
    </div></body></html>
    HTML;
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['photo'])) {
    header("Location: index.php");
    exit();
}

$file = $_FILES['photo'];

// --- 1. Errores nativos de PHP al subir ---
$phpErrors = [
    UPLOAD_ERR_INI_SIZE   => 'El archivo supera el límite definido en php.ini (upload_max_filesize).',
    UPLOAD_ERR_FORM_SIZE  => 'El archivo supera el límite definido en el formulario HTML.',
    UPLOAD_ERR_PARTIAL    => 'El archivo se subió de forma parcial. Inténtalo de nuevo.',
    UPLOAD_ERR_NO_FILE    => 'No se seleccionó ningún archivo.',
    UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal del servidor. Contacta al administrador.',
    UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en disco. Verifica los permisos del servidor.',
    UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP bloqueó la subida del archivo.',
];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $msg = $phpErrors[$file['error']] ?? "Error desconocido (código {$file['error']}).";
    uploadError($msg);
}

// --- 2. Validar que sea un upload real (seguridad) ---
if (!is_uploaded_file($file['tmp_name'])) {
    uploadError('El archivo no proviene de un formulario válido. Posible intento de ataque.');
}

// --- 3. Tamaño máximo custom ---
if ($file['size'] === 0) {
    uploadError('El archivo está vacío (0 bytes).');
}
if ($file['size'] > MAX_FILE_SIZE) {
    $mb = round($file['size'] / 1024 / 1024, 2);
    uploadError("El archivo pesa {$mb} MB. El máximo permitido es " . (MAX_FILE_SIZE / 1024 / 1024) . ' MB.');
}

// --- 4. Validar extensión ---
$allowedExt  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$fileName    = basename($file['name']);
$ext         = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt)) {
    uploadError(
        "Extensión «.{$ext}» no permitida.",
        'Solo se aceptan: ' . implode(', ', $allowedExt)
    );
}

// --- 5. Validar MIME type real (no confiar solo en la extensión) ---
$allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo       = finfo_open(FILEINFO_MIME_TYPE);
$mimeType    = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMime)) {
    uploadError(
        "El contenido del archivo no es una imagen válida.",
        "MIME detectado: {$mimeType}. Se esperaba una imagen JPEG, PNG, GIF o WebP."
    );
}

// --- 6. Crear carpeta destino si no existe ---
$uploadDir = 'fotos/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        uploadError(
            'No se pudo crear la carpeta de destino «fotos/».',
            'Verifica que Apache tenga permisos de escritura en el directorio del proyecto.'
        );
    }
}

// --- 7. Verificar que la carpeta es escribible ---
if (!is_writable($uploadDir)) {
    uploadError(
        'La carpeta «fotos/» existe pero no tiene permisos de escritura.',
        'En el servidor ejecuta: sudo chown -R apache:apache fotos/ && sudo chmod 755 fotos/'
    );
}

// --- 8. Mover el archivo ---
$safeName       = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $fileName);
$targetFilePath = $uploadDir . $safeName;

if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
    uploadError(
        'No se pudo mover el archivo al destino final.',
        "Ruta destino: {$targetFilePath} — Verifica permisos y espacio en disco."
    );
}

// --- 9. Guardar en base de datos ---
$stmt = $conn->prepare("INSERT INTO fotos (nombre_archivo, ruta_archivo) VALUES (?, ?)");
if (!$stmt) {
    // Si no se pudo preparar, borrar el archivo ya subido
    @unlink($targetFilePath);
    uploadError(
        'Error al preparar la consulta en la base de datos.',
        'MySQL dijo: ' . $conn->error
    );
}

$stmt->bind_param("ss", $fileName, $targetFilePath);

if (!$stmt->execute()) {
    @unlink($targetFilePath); // Limpiar archivo huérfano
    uploadError(
        'Error al guardar el registro en la base de datos.',
        'MySQL dijo: ' . $stmt->error
    );
}

header("Location: index.php?status=success");
exit();
?>
