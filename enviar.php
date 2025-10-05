<?php

require_once __DIR__ . '/../../db.php';

$token = $_ENV['WHATSAPP_TOKEN'];
$phoneNumberId = $_ENV['PHONE_NUMBER_ID'];

$input = json_decode(file_get_contents("php://input"), true);
$number = $input['number'] ?? null;
$message = $input['message'] ?? null;

if (!$number || !$message) {
    http_response_code(400);
    echo json_encode(["error" => "Número e mensagem obrigatórios"]);
    exit;
}

$url = "https://graph.facebook.com/v20.0/{$phoneNumberId}/messages";

$data = [
    "messaging_product" => "whatsapp",
    "to" => $number,
    "type" => "text",
    "text" => ["body" => $message]
];

$options = [
    "http" => [
        "header"  => "Authorization: Bearer {$token}\r\n" .
                     "Content-Type: application/json\r\n",
        "method"  => "POST",
        "content" => json_encode($data),
    ],
];

$context  = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === false) {
    http_response_code(500);
    echo json_encode(["error" => "Erro ao enviar mensagem"]);
    exit;
}

echo $response;