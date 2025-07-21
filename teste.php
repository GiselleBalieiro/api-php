<?php
require_once 'db.php'; // seu arquivo de conexão com PDO

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($tables) {
        echo "Conexão OK. Tabelas encontradas:<br>";
        foreach ($tables as $table) {
            echo "- $table<br>";
        }
    } else {
        echo "Conexão OK, mas sem tabelas.";
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
