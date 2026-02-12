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
    // If connection fails, we might be running locally without docker env vars set properly,
    // or waiting for DB to start.
    // For now, die with error.
    die("Connection failed: " . $e->getMessage());
}
?>