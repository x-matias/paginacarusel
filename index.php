<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';
$userId = (int)$_SESSION['user_id'];
$stmt   = $conn->prepare("SELECT * FROM fotos WHERE user_id = ? ORDER BY subido_en DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$photos = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $photos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Photo Gallery</title>
    <!-- Modern Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="glass-bg"></div>

    <main class="container">
        <header>
            <div class="header-top">
                <span class="user-badge">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
            <h1>Galería <span>Dinámica</span></h1>
            <p>Visualiza y sube tus fotos de forma espectacular</p>
        </header>

        <!-- Carrusel Section -->
        <section class="carousel-section">
            <div class="carousel-wrapper">
                <?php if (count($photos) > 0): ?>
                    <div class="carousel-track" id="carouselTrack">
                        <?php foreach ($photos as $index => $photo): ?>
                            <div class="carousel-slide <?= $index === 0 ? 'current-slide' : '' ?>">
                                <img src="<?= htmlspecialchars($photo['ruta_archivo']) ?>" alt="<?= htmlspecialchars($photo['nombre_archivo']) ?>" />
                                <div class="image-overlay">
                                    <p class="image-title"><?= htmlspecialchars($photo['nombre_archivo']) ?></p>
                                    <a href="delete.php?id=<?= $photo['id'] ?>" class="delete-btn" onclick="return confirm('¿Eliminar esta foto?')" title="Eliminar foto">🗑️</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Botones de Control -->
                    <button class="carousel-btn prev-btn" id="prevBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <button class="carousel-btn next-btn" id="nextBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                    </button>

                    <!-- Indicadores -->
                    <div class="carousel-nav">
                        <?php foreach ($photos as $index => $photo): ?>
                            <button class="carousel-indicator <?= $index === 0 ? 'current-indicator' : '' ?>" data-index="<?= $index ?>"></button>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📷</div>
                        <h3>¡No hay fotos aún!</h3>
                        <p>Usa el formulario de abajo para subir tu primera foto.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Upload Section (Glassmorphism) -->
        <section class="upload-section glass-panel">
            <h2>Subir Nueva Foto</h2>
            
            <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="alert success">¡Foto subida con éxito!</div>
            <?php endif; ?>
            <?php if(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
                <div class="alert error">Foto eliminada correctamente.</div>
            <?php endif; ?>

            <form action="upload.php" method="POST" enctype="multipart/form-data" class="upload-form">
                <div class="file-drop-area" id="dropArea">
                    <span class="file-msg">Arrastra y suelta tu imagen aquí o</span>
                    <input type="file" name="photo" id="photoInput" accept="image/*" required>
                    <label for="photoInput" class="fake-btn">Explorar Archivos</label>
                </div>
                <div id="previewContainer" class="preview-container hidden">
                    <img id="imagePreview" src="" alt="Preview">
                </div>
                <button type="submit" class="submit-btn">Subir a la Galería</button>
            </form>
        </section>
    </main>

    <script src="script.js"></script>
</body>
</html>
