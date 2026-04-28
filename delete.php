<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];

// Obtener la ruta del archivo antes de borrar
$stmt = $conn->prepare("SELECT ruta_archivo FROM fotos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$foto = $result->fetch_assoc();

if (!$foto) {
    header("Location: index.php");
    exit();
}

// 1. Borrar el archivo físico
$rutaArchivo = $foto['ruta_archivo'];
if (file_exists($rutaArchivo)) {
    unlink($rutaArchivo);
}

// 2. Borrar el registro de la base de datos
$stmt = $conn->prepare("DELETE FROM fotos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: index.php?status=deleted");
exit();
?>
