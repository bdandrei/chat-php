<?php
$host = getenv('DB_HOST') ?: 'db';
$db = getenv('DB_NAME') ?: 'cerowait';
$user = getenv('DB_USER') ?: 'chat_user';
$pass = getenv('DB_PASS') ?: '123';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Si falla la conexión, podríamos estar ejecutando localmente sin las variables de entorno de docker,
    // o esperando a que la base de datos inicie.
    // Por ahora, terminar con error.
    die("Error de conexión: " . $e->getMessage());
}
?>