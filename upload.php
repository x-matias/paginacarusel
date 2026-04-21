<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $uploadDir = 'fotos/';
    
    // Crear la carpeta si no existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = basename($_FILES['photo']['name']);
    // Añadir timestamp para evitar archivos duplicados
    $targetFilePath = $uploadDir . time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $fileName);
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Permitir ciertos formatos de imagen
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
    
    if (in_array($fileType, $allowTypes)) {
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath)) {
            // Insertar metadata en base de datos
            $stmt = $conn->prepare("INSERT INTO fotos (nombre_archivo, ruta_archivo) VALUES (?, ?)");
            $stmt->bind_param("ss", $fileName, $targetFilePath);
            
            if ($stmt->execute()) {
                header("Location: index.php?status=success");
                exit();
            } else {
                echo "Error en base de datos.";
            }
        } else {
            echo "Error al subir el archivo físico.";
        }
    } else {
        echo "Solo se permiten archivos JPG, JPEG, PNG, GIF y WEBP.";
    }
} else {
    header("Location: index.php");
    exit();
}
?>
