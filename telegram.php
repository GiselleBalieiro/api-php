<?php

require_once "db.php";

$update = json_decode(file_get_contents("php://input"), true);

if (!$update || !isset($update["message"])) {
    http_response_code(400);
    exit("Nenhum dado recebido");
}

$chatId = $update["message"]["chat"]["id"];
$text   = $update["message"]["text"];

$iaId = isset($_GET['ia_id']) ? $_GET['ia_id'] : null;
$botToken = $update['bot_token'] ?? null;

$apiUrl = "https://ia-rag-api.vercel.app/perguntar"; 

function getJwtTokenFromHeader() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        exit("Token não enviado");
    }
    $authHeader = $headers['Authorization'];
    list($type, $token) = explode(" ", $authHeader);
    if (strcasecmp($type, "Bearer") != 0) {
        http_response_code(401);
        exit("Formato de token inválido");
    }
    return $token;
}

$jwtToken = getJwtTokenFromHeader();

$postData = [
    "pergunta" => $text,
    "id" => $iaId
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
    // "Authorization: Bearer " . $jwtToken
]);

$response = curl_exec($ch);
if ($response === false) {
    $respostaIA = "Erro ao chamar a IA: " . curl_error($ch);
} else {
    $decoded = json_decode($response, true);
    $respostaIA = $decoded["resposta"] ?? "Não entendi a resposta da IA.";
}
curl_close($ch);

$url = "https://api.telegram.org/bot$botToken/sendMessage";

$data = [
    "chat_id" => $chatId,
    "text"    => $respostaIA
];

$options = [
    "http" => [
        "header"  => "Content-type: application/json\r\n",
        "method"  => "POST",
        "content" => json_encode($data)
    ]
];

file_get_contents($url, false, stream_context_create($options));  
