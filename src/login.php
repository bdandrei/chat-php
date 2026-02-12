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
            session_write_close();
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
    <title>Cerowait - Iniciar Sesión</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container" style="max-width: 400px; text-align: center;">
        <div class="logo" style="margin-bottom: 2rem;">
            <img src="img/logo.png" alt="Cerowait" style="height: 60px;">
        </div>

        <h2 style="margin-bottom: 2rem;">INICIAR SESIÓN</h2>

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
            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="password">CONTRASEÑA</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="btn-primary" style="width: 100%;">ENTRAR</button>
        </form>

        <p style="margin-top: 2rem; font-size: 0.9rem; color: var(--text-muted);">
            ¿No tienes cuenta? <a href="register.php" style="color: var(--text); font-weight: bold;">Regístrate</a>
        </p>
    </div>
</body>

</html>