<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
require_once "db.php";


// UPDATE (POST com update=1)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update'])) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $_GET['id'] ?? null;

    if ($id && !empty($data['agent'])) {
        try {
            $stmt = $pdo->prepare("UPDATE agent SET agent = :agent, personality = :personality, training = :training, status = :status WHERE id = :id");
            $stmt->bindParam(':agent', $data['agent']);
            $stmt->bindParam(':personality', $data['personality']);
            $stmt->bindParam(':training', $data['training']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':id', $id);
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

    if (!empty($data['agent'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO agent (agent, personality, training, status) VALUES (:agent, :personality, :training, :status)");
            $stmt->bindParam(':agent', $data['agent']);
            $stmt->bindParam(':personality', $data['personality']);
            $stmt->bindParam(':training', $data['training']);
            $stmt->bindParam(':status', $data['status']);
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
            "message" => "Nome do agente não enviado"
        ]);
    }
    exit;
}

// GET por ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM agent WHERE id = :id");
        $stmt->bindParam(':id', $id);
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
try {
    $stmt = $pdo->query("SELECT * FROM agent");
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
}