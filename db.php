<?php

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }
}

$host = $_ENV["DATABASE_HOST"] ?? getenv('DATABASE_HOST');
$dbname = $_ENV["DATABASE"] ?? getenv('DATABASE');
$username = $_ENV["DATABASE_USERNAME"] ?? getenv('DATABASE_USERNAME');
$password = $_ENV["DATABASE_PASSWORD"] ?? getenv('DATABASE_PASSWORD');
$port = $_ENV["DATABASE_PORT"] ?? getenv('DATABASE_PORT');

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, 
        PDO::MYSQL_ATTR_SSL_CA => null 
    ]);
    
    echo "Conexão realizada com sucesso!";
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
