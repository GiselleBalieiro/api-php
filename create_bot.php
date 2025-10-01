<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $botToken = $_POST["bot_token"] ?? null;

    if (!$botToken) {
        exit("Erro: informe o bot_token.");
    }

    $webhookUrl = "https://api-php-ff2c9710eabd.herokuapp.com/telegram.php";

    $setWebhook = file_get_contents("https://api.telegram.org/bot$botToken/setWebhook?url=$webhookUrl");

    echo "Webhook configurado: " . $setWebhook;
} else {
    echo "Envie o bot_token";
}
