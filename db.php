<?php


// // Carrega variáveis de ambiente, se estiver em ambiente local com .env
// if (file_exists(__DIR__ . '/vendor/autoload.php')) {
//     require __DIR__ . '/vendor/autoload.php';
//     if (class_exists('Dotenv\Dotenv')) {
//         $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
//         $dotenv->load();
//     }
// }

// Alternativa para Railway, Heroku ou servidores que usam env diretamente
$host = $_ENV["DATABASE_HOST"] ?? getenv('DATABASE_HOST');
$dbname = $_ENV["DATABASE"] ?? getenv('DATABASE');
$username = $_ENV["DATABASE_USERNAME"] ?? getenv('DATABASE_USERNAME');
$password = $_ENV["DATABASE_PASSWORD"] ?? getenv('DATABASE_PASSWORD');

var_dump($host, $dbname, $username, $password);
exit;

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::MYSQL_ATTR_SSL_CA => "/etc/ssl/certs/ca-certificates.crt", // funciona no Railway e outros containers
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>
