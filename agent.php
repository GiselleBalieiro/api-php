<?php
require_once "db.php";
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


$secretKey = getenv('JWT_SECRET_KEY');

$allowed_origins = [
    'https://agent-5mygpia1j-gisellebalieiros-projects.vercel.app',
    'https://agent-gules-alpha.vercel.app',
    'http://localhost:5173'
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Access-Control-Allow-Credentials: true");
}

header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");


function getUserIdFromToken($secretKey) {
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Token não enviado"]);
        exit;
    }

    $authHeader = $headers['Authorization'];
    list($type, $token) = explode(" ", $authHeader);

    if (strcasecmp($type, "Bearer") != 0) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Formato de token inválido"]);
        exit;
    }

    try {
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        return $decoded->user_id ?? null;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Token inválido: " . $e->getMessage()]);
        exit;
    }
}

$user_id = getUserIdFromToken($secretKey);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update'])) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $_GET['id'] ?? null;

    if ($id && !empty($data)) {
        try {
            $fields = [];
            $params = [":id" => $id, ":user_id" => $user_id];

            foreach ($data as $key => $value) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }

            $sql = "UPDATE agent SET " . implode(", ", $fields) . " WHERE id = :id AND user_id = :user_id";
            $stmt = $pdo->prepare($sql);

            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            $stmt->execute();

            echo json_encode(["success" => true, "message" => "Agente atualizado com sucesso"]);
        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Erro ao atualizar agente: " . $error->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Dados insuficientes para atualizar"]);
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data)) {
        try {
            $data['user_id'] = $user_id;

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

            echo json_encode(["success" => true, "message" => "Agente inserido com sucesso"]);
        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Erro ao inserir agente: " . $error->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Nenhum dado enviado para inserção"]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM agent WHERE id = :id AND user_id = :user_id");
        $stmt->execute([":id" => $id, ":user_id" => $user_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "success" => true,
                "message" => "Agente deletado com sucesso"
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "message" => "Agente não encontrado ou não pertence ao usuário"
            ]);
        }
    } catch (Exception $error) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Erro ao deletar agente: " . $error->getMessage()
        ]);
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM agent WHERE id = :id AND user_id = :user_id");
        $stmt->execute([":id" => $id, ":user_id" => $user_id]);
        $agent = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "data" => $agent]);
    } catch (Exception $error) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Erro ao buscar agente: " . $error->getMessage()]);
    }
    exit;
}


try {
    $stmt = $pdo->prepare("SELECT * FROM agent WHERE user_id = :user_id");
    $stmt->execute([":user_id" => $user_id]);
    $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $agents]);
} catch (Exception $error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erro ao buscar agentes: " . $error->getMessage()]);
}
