<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['parte'])) {
    $parte = $_POST['parte'];
    $filePath = __DIR__ . "/picotados/" . $parte;

    if (!file_exists($filePath)) {
        echo json_encode(['status' => 'error', 'message' => 'Arquivo não encontrado.']);
        exit;
    }

    // Chave da API e URL do Whisper
    $apiKey = 'sk-proj-woyXmHCjV4KTm37EodMZT3BlbkFJqJSEVo1MMMOxAsFoIFuG';
    $url = 'https://api.openai.com/v1/audio/transcriptions';

    $headers = [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: multipart/form-data',
    ];

    $postFields = [
        'file' => new CURLFile($filePath),
        'model' => 'whisper-1',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['text'])) {
            echo json_encode(['status' => 'success', 'text' => $data['text']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Texto não retornado pela API.']);
        }
    } else {
        $error = json_decode($response, true);
        $message = $error['error']['message'] ?? 'Erro desconhecido na transcrição.';
        echo json_encode(['status' => 'error', 'message' => $message]);
    }
    exit;
}
?>
