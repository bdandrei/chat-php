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
    <title>Registro - Premium Chat</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 2rem;">Únete a nosotros</h2>
        <?php flash('msg'); ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Usuario (Letras y Números)</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña (6-30 caracteres)</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit" style="width: 100%;">Registrarse</button>
        </form>
        <p style="text-align: center; margin-top: 1rem;">
            ¿Ya tienes cuenta? <a href="login.php">Entrar</a>
        </p>
    </div>
</body>

</html>