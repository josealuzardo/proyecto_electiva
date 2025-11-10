<?php
// filepath: /home/aluzardo/code/docker/php/luzardo/config.php
// Ajusta valores según tu entorno Docker
$db_host = '172.17.0.2';
$db_name = 'vzlaaventura_db';
$db_user = 'root';
$db_pass = 'manager';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo "DB connection error.";
    exit;
}