<?php
require_once 'functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$error = '';
$success = '';

// Gestionar Acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send_message'])) {
        $recipientName = sanitize($_POST['recipient']);
        $messageBody = sanitize($_POST['message']);

        if (empty($recipientName) || empty($messageBody)) {
            $error = "El destinatario y el mensaje no pueden estar vacíos.";
        } else {
            // Buscar destinatario
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->execute(['username' => $recipientName]);
            $recipient = $stmt->fetch();

            if ($recipient) {
                $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (:sender, :receiver, :message)");
                if ($stmt->execute(['sender' => $user_id, 'receiver' => $recipient['id'], 'message' => $messageBody])) {
                    $success = "Mensaje enviado a @$recipientName";
                } else {
                    $error = "Fallo al enviar el mensaje.";
                }
            } else {
                $error = "Usuario @$recipientName no encontrado.";
            }
        }
    }

    if (isset($_POST['action'])) {
        $msgId = (int) $_POST['message_id'];

        // Verificar propiedad
        $check = $pdo->prepare("SELECT id FROM messages WHERE id = :id AND receiver_id = :me");
        $check->execute(['id' => $msgId, 'me' => $user_id]);

        if ($check->rowCount() > 0) {
            if ($_POST['action'] === 'delete') {
                $pdo->prepare("DELETE FROM messages WHERE id = :id")->execute(['id' => $msgId]);
                $success = "Mensaje eliminado.";
            } elseif ($_POST['action'] === 'toggle_read') {
                $pdo->prepare("UPDATE messages SET is_read = NOT is_read WHERE id = :id")->execute(['id' => $msgId]);
            } elseif ($_POST['action'] === 'toggle_favorite') {
                // Comprobar si ya es favorito
                $favCheck = $pdo->prepare("SELECT * FROM favorites WHERE user_id = :user AND message_id = :msg");
                $favCheck->execute(['user' => $user_id, 'msg' => $msgId]);
                if ($favCheck->rowCount() > 0) {
                    $pdo->prepare("DELETE FROM favorites WHERE user_id = :user AND message_id = :msg")->execute(['user' => $user_id, 'msg' => $msgId]);
                } else {
                    $pdo->prepare("INSERT INTO favorites (user_id, message_id) VALUES (:user, :msg)")->execute(['user' => $user_id, 'msg' => $msgId]);
                }
            }
        }
    }
}

// Obtener Mensajes
// Unir con usuarios para remitente y favoritos para estado
$sql = "SELECT m.*, u.username as sender_name, f.message_id as is_favorite 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        LEFT JOIN favorites f ON m.id = f.message_id AND f.user_id = :me_fav 
        WHERE m.receiver_id = :me_recv 
        ORDER BY m.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['me_fav' => $user_id, 'me_recv' => $user_id]);
$messages = $stmt->fetchAll();

// Filtrar Favoritos si es necesario
$showFavorites = isset($_GET['view']) && $_GET['view'] === 'favorites';

// Si se muestran favoritos, filtrar el array de mensajes
if ($showFavorites) {
    $messages = array_filter($messages, function ($m) {
        return !empty($m['is_favorite']);
    });
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerowait Chat</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container">

        <!-- Navbar -->
        <div class="navbar">
            <div class="logo">
                <img src="img/logo.png" alt="Cerowait">
            </div>
            <div class="user-controls">
                <span>@<?= htmlspecialchars($username) ?></span>
                <a href="logout.php" class="logout-link">Salir</a>
            </div>
        </div>

        <!-- Feedback Messages -->
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- Composition Area -->
        <div class="composition-area">
            <h3>NUEVO MENSAJE</h3>
            <form method="POST" class="send-form">
                <div class="form-group">
                    <label>PARA</label>
                    <input type="text" name="recipient" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>MENSAJE</label>
                    <input type="text" name="message" required autocomplete="off">
                </div>
                <div>
                    <button type="submit" name="send_message" class="btn-primary">ENVIAR</button>
                </div>
            </form>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <a href="index.php?view=all" class="tab <?= !$showFavorites ? 'active' : '' ?>">BUZÓN</a>
            <a href="index.php?view=favorites" class="tab <?= $showFavorites ? 'active' : '' ?>">FAVORITOS</a>
        </div>

        <!-- Message List -->
        <div class="message-list">
            <?php if (empty($messages)): ?>
                <p style="text-align: center; color: var(--text-muted); margin-top: 2rem;">No hay mensajes.</p>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message-card <?= $msg['is_read'] ? '' : 'unread' ?>">
                        <div class="message-meta">
                            <span class="sender-name">@<?= htmlspecialchars($msg['sender_name']) ?></span>
                            <span><?= date('M j, H:i', strtotime($msg['created_at'])) ?></span>
                        </div>

                        <div class="message-content">
                            <?= htmlspecialchars($msg['message']) ?>
                        </div>

                        <div class="message-actions">
                            <!-- Toggle Read -->
                            <form method="POST">
                                <input type="hidden" name="action" value="toggle_read">
                                <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                <button type="submit" class="btn-icon"
                                    title="<?= $msg['is_read'] ? 'Marcar como No Leído' : 'Marcar como Leído' ?>">
                                    <?= $msg['is_read'] ? 'No leído' : 'Leído' ?>
                                </button>
                            </form>

                            <!-- Favorite -->
                            <form method="POST">
                                <input type="hidden" name="action" value="toggle_favorite">
                                <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                <button type="submit" class="btn-icon <?= !empty($msg['is_favorite']) ? 'active' : '' ?>"
                                    title="Favorito">
                                    <?= !empty($msg['is_favorite']) ? '★' : '☆' ?>
                                </button>
                            </form>

                            <!-- Delete -->
                            <form method="POST" onsubmit="return confirm('¿Eliminar mensaje?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                <button type="submit" class="btn-icon btn-delete">Eliminar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>