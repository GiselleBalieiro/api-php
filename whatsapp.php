<?php

$request = $_SERVER['REQUEST_URI'];

if ($request === '/' || $request === '/index.php') {
    echo "API PHP rodando";
    exit;
}

if ($request === '/webhook' || $request === '/webhook.php') {
    require __DIR__ . '/api-oficial/webhook.php';
    exit;
}

http_response_code(404);
echo "Rota não encontrada: $request";
exit;