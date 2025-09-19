<?php
require_once "db.php";
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = getenv('JWT_SECRET_KEY');

header("Content-Type: application/json");

$allowed_origins = [
    'https://agent-5mygpia1j-gisellebalieiros-projects.vercel.app',
    'https://agent-gules-alpha.vercel.app',
    'http://localhost:5173'
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && !isset($_GET['register'])) {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $payload = [
            "user_id" => $user['id'],
            "exp" => time() + 3600
        ];
        $jwt = JWT::encode($payload, $secretKey, 'HS256');

        echo json_encode([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'token' => $jwt,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Conta não existe ou senha incorreta.']);
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['register'])) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data) && isset($data['name'], $data['email'], $data['password'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO user (name, email, password) VALUES (:name, :email, :password)");
            $stmt->execute([
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT)
            ]);

            echo json_encode([
                "success" => true,
                "message" => "Usuário inserido com sucesso"
            ]);
        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Erro ao inserir usuário: " . $error->getMessage()
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Dados obrigatórios não enviados"
        ]);
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        echo json_encode(["success" => false, "message" => "Token ausente"]);
        exit;
    }

    $authHeader = trim(str_replace("Bearer", "", $headers['Authorization']));

    try {
        $decoded = JWT::decode($authHeader, new Key($secretKey, 'HS256'));
        $user_id = $decoded->user_id;

        $stmt = $pdo->prepare("SELECT id, name, email FROM user WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode([
                "success" => true,
                "user" => $user
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Usuário não encontrado"
            ]);
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Token inválido: " . $e->getMessage()
        ]);
    }
    exit;
}
