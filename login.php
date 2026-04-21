<?php
session_start();
// If already logged in, go to gallery
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Por favor completa todos los campos.';
    } else {
        $stmt = $conn->prepare("SELECT id, nombre_usuario, contrasena_hash FROM usuarios WHERE nombre_usuario = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['contrasena_hash'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['nombre_usuario'];
            header("Location: index.php");
            exit();
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión – Galería Dinámica</title>
    <meta name="description" content="Inicia sesión para acceder a tu galería de fotos dinámica.">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="glass-bg"></div>

    <main class="auth-container">
        <div class="auth-card glass-panel">
            <div class="auth-logo">📷</div>
            <h1>Galería <span>Dinámica</span></h1>
            <p class="auth-subtitle">Inicia sesión para ver y subir fotos</p>

            <?php if ($error): ?>
                <div class="alert error" id="errorAlert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="auth-form" id="loginForm">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" placeholder="Tu nombre de usuario" required autocomplete="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Tu contraseña" required autocomplete="current-password">
                        <button type="button" class="toggle-pw" id="togglePw" title="Mostrar/ocultar">👁️</button>
                    </div>
                </div>
                <button type="submit" class="submit-btn" id="loginBtn">Iniciar Sesión</button>
            </form>

            <p class="auth-footer">¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
        </div>
    </main>

    <script>
        const toggle = document.getElementById('togglePw');
        const pw = document.getElementById('password');
        toggle.addEventListener('click', () => {
            pw.type = pw.type === 'password' ? 'text' : 'password';
        });
    </script>
</body>
</html>
