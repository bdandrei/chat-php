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
                    $success = "¡Mensaje enviado correctamente a @$recipientName!";
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
if ($showFavorites) {
    $messages = array_filter($messages, function ($m) {
        return !empty($m['is_favorite']);
    });
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Premium Chat</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .unread-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: var(--unread);
            border-radius: 50%;
            margin-right: 0.5rem;
        }
    </style>
</head>

<body>
    <div class="container" style="max-width: 1000px;">
        <div class="navbar">
            <div class="logo">PremiumChat</div>
            <div>
                <span>Hola, <strong>@<?= htmlspecialchars($username) ?></strong></span>
                <a href="logout.php" style="margin-left: 1rem; color: var(--secondary);">Cerrar Sesión</a>
            </div>
        </div>

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

        <!-- Send Message Form -->
        <div style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 15px; margin-bottom: 2rem;">
            <h3>Enviar Mensaje</h3>
            <form method="POST"
                style="display: grid; grid-template-columns: 1fr 3fr auto; gap: 1rem; align-items: end;">
                <div>
                    <label>Para (Usuario)</label>
                    <input type="text" name="recipient" placeholder="nombre de usuario" required>
                </div>
                <div>
                    <label>Mensaje</label>
                    <input type="text" name="message" placeholder="Escribe tu mensaje..." required>
                </div>
                <div>
                    <button type="submit" name="send_message">Enviar</button>
                </div>
            </form>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3>Buzón de Entrada</h3>
            <div>
                <a href="index.php" class="<?= !$showFavorites ? 'active' : '' ?>"
                    style="margin-right: 1rem; font-weight: bold; color: <?= !$showFavorites ? 'var(--primary)' : 'var(--text-muted)' ?>">Todos</a>
                <a href="index.php?view=favorites" class="<?= $showFavorites ? 'active' : '' ?>"
                    style="font-weight: bold; color: <?= $showFavorites ? 'var(--primary)' : 'var(--text-muted)' ?>">Favoritos
                    ★</a>
            </div>
        </div>

        <div class="message-list">
            <?php if (empty($messages)): ?>
                <p style="text-align: center; color: var(--text-muted);">No hay mensajes.</p>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message-card <?= $msg['is_read'] ? '' : 'unread' ?>">
                        <div class="message-meta">
                            <span class="sender-name">@
                                <?= htmlspecialchars($msg['sender_name']) ?>
                            </span>
                            <span>
                                <?= date('M j, Y h:i A', strtotime($msg['created_at'])) ?>
                            </span>
                        </div>
                        <p style="margin: 0.5rem 0; font-size: 1.1rem;">
                            <?= htmlspecialchars($msg['message']) ?>
                        </p>

                        <div class="message-actions">
                            <!-- Toggle Read -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="toggle_read">
                                <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                <button type="submit" class="btn-sm"
                                    title="<?= $msg['is_read'] ? 'Marcar como No Leído' : 'Marcar como Leído' ?>">
                                    <?= $msg['is_read'] ? 'Marcar No Leído' : 'Marcar Leído' ?>
                                </button>
                            </form>

                            <!-- Favorite -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="toggle_favorite">
                                <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                <button type="submit" class="btn-sm btn-favorite <?= $msg['is_favorite'] ? 'active' : '' ?>"
                                    title="Alternar Favorito">
                                    ★
                                </button>
                            </form>

                            <!-- Delete -->
                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                <button type="submit" class="btn-sm btn-danger">Borrar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>