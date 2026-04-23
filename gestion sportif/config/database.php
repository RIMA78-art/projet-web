<?php

declare(strict_types=1);

$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'gestion_sport';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $password, $options);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET $charset COLLATE {$charset}_unicode_ci");
    $pdo->exec("USE `$database`");
    $pdo->exec("CREATE TABLE IF NOT EXISTS programme (
        id_programme INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        type VARCHAR(50) NOT NULL,
        duree INT NOT NULL,
        niveau VARCHAR(50) NOT NULL,
        description TEXT NOT NULL,
        calories INT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (PDOException $exception) {
    echo '<h1>Erreur de connexion à la base de données</h1>';
    echo '<p>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}

return $pdo;
