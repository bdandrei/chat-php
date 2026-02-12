<?php
require_once 'functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize($_POST['username']);
    $password = sanitize($_POST['password']);

    // Validación
    if (empty($username) || empty($password)) {
        flash('msg', 'Por favor rellena todos los campos', 'alert alert-error');
    } else {
        // Comprobar BD
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username']; // Para mostrar
            header('Location: index.php');
            exit;
        } else {
            flash('msg', 'Usuario o contraseña incorrectos', 'alert alert-error');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Premium Chat</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 2rem;">Bienvenido</h2>
        <?php flash('msg'); ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" style="width: 100%;">Entrar</button>
        </form>
        <p style="text-align: center; margin-top: 1rem;">
            ¿No tienes cuenta? <a href="register.php">Regístrate</a>
        </p>
    </div>
</body>

</html>