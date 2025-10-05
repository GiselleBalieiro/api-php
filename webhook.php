<?php

require_once __DIR__ . '/db.php';

$verify_token = $_ENV['VERIFY_TOKEN'];
$whatsapp_token = $_ENV['WHATSAPP_TOKEN'];
$phoneNumberId = $_ENV['PHONE_NUMBER_ID'];
$ia_api_url = $_ENV['IA_API_URL'];

// verifica webhook cadastrado no META
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode = $_GET['hub_mode'] ?? $_GET['hub.mode'] ?? null;
    $token = $_GET['hub_verify_token'] ?? $_GET['hub.verify_token'] ?? null;
    $challenge = $_GET['hub_challenge'] ?? $_GET['hub.challenge'] ?? null;

    if ($mode === 'subscribe' && $token === $verify_token) {
        echo $challenge;
        exit;
    } else {
        http_response_code(403);
        exit;
    }
}


// recebe mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data['entry'][0]['changes'][0]['value']['messages'][0])) {
        $message = $data['entry'][0]['changes'][0]['value']['messages'][0];
        $from = $message['from']; 
        $text = $message['text']['body'] ?? '';

        file_put_contents("log.txt", "Mensagem de $from: $text\n", FILE_APPEND);
        $payloadIA = [
          "pergunta" => $text,
          "id" =>  $agentId ?? 1
        ];

        $chIA = curl_init($ia_api_url);
        curl_setopt($chIA, CURLOPT_POSTFIELDS, json_encode($payloadIA));
        curl_setopt($chIA, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($chIA, CURLOPT_RETURNTRANSFER, true);
        $respostaIA = curl_exec($chIA);
        curl_close($chIA);

        $resposta = json_decode($respostaIA, true)['resposta'] ?? 'Sem resposta da IA';

        $url = "https://graph.facebook.com/v20.0/{$phoneNumberId}/messages";

        $payload = [
            "messaging_product" => "whatsapp",
            "to" => $from,
            "text" => ["body" => $resposta]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $whatsapp_token",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
    }

    http_response_code(200);
    echo "EVENT_RECEIVED";
}