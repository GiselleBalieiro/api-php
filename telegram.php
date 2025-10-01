<?php
require_once "db.php";

$update = json_decode(file_get_contents("php://input"), true);

if (!$update || !isset($update["message"])) {
    http_response_code(400);
    exit("Nenhum dado recebido");
}

$chatId = $update["message"]["chat"]["id"];
$text   = $update["message"]["text"];

$agentId = $_GET['agent_id'] ?? null;
if (!$agentId) {
    http_response_code(400);
    exit("agent_id não informado");
}

$stmt = $pdo->prepare("SELECT token_bot, id FROM agent WHERE id = ?");
$stmt->execute([$agentId]);
$agent = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agent) {
    http_response_code(404);
    exit("Agente não encontrado");
}

$botToken = $agent['token_bot'];
$iaId     = $agent['id'];

$apiUrl = "https://ia-rag-api.vercel.app/perguntar"; 

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
