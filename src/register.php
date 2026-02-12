<?php
require_once 'functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize($_POST['username']);
    $password = sanitize($_POST['password']);
    $confirm_password = sanitize($_POST['confirm_password']);

    // Validación
    if (empty($username) || empty($password) || empty($confirm_password)) {
        flash('msg', 'Todos los campos son obligatorios', 'alert alert-error');
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        flash('msg', 'El usuario solo puede contener letras y números', 'alert alert-error');
    } elseif (strlen($password) < 6 || strlen($password) > 30) {
        flash('msg', 'La contraseña debe tener entre 6 y 30 caracteres', 'alert alert-error');
    } elseif ($password !== $confirm_password) {
        flash('msg', 'Las contraseñas no coinciden', 'alert alert-error');
    } else {
        // Comprobar si usuario existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);

        if ($stmt->rowCount() > 0) {
            flash('msg', 'El nombre de usuario ya está en uso', 'alert alert-error');
        } else {
            // Registro
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");

            if ($stmt->execute(['username' => $username, 'password' => $hash])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $username;
                session_write_close(); // Asegurar que la sesión se guarde antes de redirigir
                header('Location: index.php');
                exit;
            } else {
                flash('msg', 'Fallo en el registro', 'alert alert-error');
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
    <title>Cerowait - Registro</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container" style="max-width: 450px; text-align: center;">
        <div class="logo" style="margin-bottom: 2rem;">
            <img src="img/logo.png" alt="Cerowait" style="height: 60px;">
        </div>

        <h2 style="margin-bottom: 2rem;">CREAR CUENTA</h2>

        <?php if (isset($_SESSION['msg'])): ?>
            <div class="<?= $_SESSION['msg_type'] ?>">
                <?= $_SESSION['msg'] ?>
                <?php unset($_SESSION['msg']);
                unset($_SESSION['msg_type']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" style="text-align: left;">
            <div class="form-group">
                <label for="username">USUARIO</label>
                <input type="text" name="username" id="username" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="password">CONTRASEÑA</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="confirm_password">CONFIRMAR CONTRASEÑA</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit" class="btn-primary" style="width: 100%;">REGISTRARSE</button>
        </form>

        <p style="margin-top: 2rem; font-size: 0.9rem; color: var(--text-muted);">
            ¿Ya tienes cuenta? <a href="login.php" style="color: var(--text); font-weight: bold;">Entrar</a>
        </p>
    </div>
</body>

</html>