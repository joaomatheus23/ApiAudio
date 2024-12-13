<?php
set_time_limit(0);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $part = $_POST['part'] ?? null; // Receber o nome da parte do áudio
    $filePath = __DIR__ . "/picotados/$part";

    // Verifica se o arquivo foi enviado corretamente
    if (!$part || !file_exists($filePath)) {
        echo json_encode(['status' => 'error', 'message' => "Arquivo não encontrado: $part"]);
        exit;
    }

    // Configurações da API Whisper
    $apiKey = 'sk-s-4A59MAYfzW1o0ZCg24-dRuJCEtZ-QwT9XTPy8m2nT3BlbkFJv0A1z-_k6hzZmO_0NpWEviqb6utPjn1Z4wkgui00IA';
    $url = 'https://api.openai.com/v1/audio/transcriptions';

    // Configuração do cabeçalho da requisição
    $headers = [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: multipart/form-data',
    ];

    // Prepara os dados para envio
    $postFields = [
        'file' => new CURLFile($filePath),
        'model' => 'whisper-1',
    ];

    // Inicializa o cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

    // Executa a requisição e captura a resposta
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Verifica o status da resposta
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        echo json_encode(['status' => 'success', 'text' => $data['text']]);
    } else {
        $error = json_decode($response, true);
        $errorMessage = $error['error']['message'] ?? 'Erro desconhecido na transcrição.';
        echo json_encode(['status' => 'error', 'message' => $errorMessage]);
    }
}
