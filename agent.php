<?php

$allowed_origins = [
    'https://agent-5mygpia1j-gisellebalieiros-projects.vercel.app',
    'https://agent-gules-alpha.vercel.app',
    'http://localhost:5173'
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Access-Control-Allow-Credentials: true"); 
}

header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");

session_set_cookie_params([
    'lifetime' => 0, 
    'path' => '/',
    'domain' => 'api-php-ff2c9710eabd.herokuapp.com',
    'secure' => true, 
    'httponly' => true,
    'samesite' => 'None'
]);
session_start();
require_once "db.php";

$user_id = $_SESSION['user_id'] ?? null;

// UPDATE (POST com update=1)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update'])) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $_GET['id'] ?? null;

    if ($id && !empty($data)) {
        try {
            $fields = [];
            $params = [":id" => $id];

            foreach ($data as $key => $value) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }

            if (empty($fields)) {
                throw new Exception("Nenhum campo válido enviado para atualização");
            }

            $sql = "UPDATE agent SET " . implode(", ", $fields) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            $stmt->execute();

            echo json_encode([
                "success" => true,
                "message" => "Agente atualizado com sucesso"
            ]);
        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Erro ao atualizar agente: " . $error->getMessage()
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Dados insuficientes para atualizar"
        ]);
    }
    exit;
}

// INSERT (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data)) {
        try {
            $columns = [];
            $placeholders = [];
            $params = [];

            foreach ($data as $key => $value) {
                $columns[] = $key;
                $placeholders[] = ":$key";
                $params[":$key"] = $value;
            }

            $sql = "INSERT INTO agent (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
            $stmt = $pdo->prepare($sql);

            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            $stmt->execute();

            echo json_encode([
                "success" => true,
                "message" => "Agente inserido com sucesso"
            ]);
        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Erro ao inserir agente: " . $error->getMessage()
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Nenhum dado enviado para inserção"
        ]);
    }
    exit;
}

// GET por id
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && $user_id) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM agent WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $agent = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => $agent
        ]);
    } catch (Exception $error) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Erro ao buscar agente: " . $error->getMessage()
        ]);
    }
    exit;
}

// GET todos 
if ($user_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM agent WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => $agents
        ]);
    } catch (Exception $error) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Erro ao buscar agentes: " . $error->getMessage()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Erro de conexão com o banco: " . $e->getMessage()
        ]);
    }
    exit;
} else {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Usuário não autenticado"
    ]);
    exit;
}