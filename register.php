<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require 'db_connect.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (empty($username) || empty($password) || empty($password2)) {
        $error = 'Por favor completa todos los campos.';
    } elseif (strlen($username) < 3) {
        $error = 'El nombre de usuario debe tener al menos 3 caracteres.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Ese nombre de usuario ya está en uso. Elige otro.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt2 = $conn->prepare("INSERT INTO usuarios (nombre_usuario, contrasena_hash) VALUES (?, ?)");
            $stmt2->bind_param("ss", $username, $hash);

            if ($stmt2->execute()) {
                $success = '¡Cuenta creada con éxito! Ahora puedes iniciar sesión.';
            } else {
                $error = 'Error al crear la cuenta. Inténtalo de nuevo.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta – Galería Dinámica</title>
    <meta name="description" content="Crea tu cuenta y comienza a compartir fotos en la galería dinámica.">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="glass-bg"></div>

    <main class="auth-container">
        <div class="auth-card glass-panel">
            <div class="auth-logo">🚀</div>
            <h1>Crear <span>Cuenta</span></h1>
            <p class="auth-subtitle">Únete y empieza a subir tus fotos</p>

            <?php if ($error): ?>
                <div class="alert error" id="errorAlert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert success" id="successAlert"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="auth-form" id="registerForm">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" placeholder="Mínimo 3 caracteres" required autocomplete="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required autocomplete="new-password">
                        <button type="button" class="toggle-pw" id="togglePw" title="Mostrar/ocultar">👁️</button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password2">Confirmar Contraseña</label>
                    <input type="password" id="password2" name="password2" placeholder="Repite tu contraseña" required autocomplete="new-password">
                </div>
                <button type="submit" class="submit-btn" id="registerBtn">Crear Cuenta</button>
            </form>

            <p class="auth-footer">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
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
