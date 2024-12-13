<?php
set_time_limit(0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputFile = $_FILES['file']['tmp_name']; // Arquivo temporário enviado
    $outputDir = 'picotados'; // Diretório onde os cortes serão salvos
    $duration = 30; // Duração de cada parte em segundos

    // Verifica se o arquivo foi enviado corretamente
    if (!file_exists($inputFile)) {
        echo json_encode(['status' => 'error', 'message' => 'Arquivo de entrada não encontrado.']);
        exit;
    }

    // Certifica-se de que o diretório de saída existe
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0777, true);
    }

    // Determina a duração total do arquivo usando o caminho absoluto para ffprobe
    $command = "/opt/homebrew/bin/ffprobe -i " . escapeshellarg($inputFile) . " -show_entries format=duration -v quiet -of csv=\"p=0\" 2>&1";
    $output = shell_exec($command);
    $durationTotal = trim($output);

    // Verifica se conseguiu obter a duração
    if (!is_numeric($durationTotal)) {
        echo json_encode(['status' => 'error', 'message' => 'Não foi possível determinar a duração do arquivo. Verifique se o arquivo é válido.']);
        exit;
    }

    $start = 0; // Ponto de início para cortar
    $part = 1; // Contador para os cortes
    $parts = []; // Lista de partes criadas

    // Divide o arquivo em partes e converte para um formato suportado pela API Whisper
    while ($start < $durationTotal) {
        $outputFile = "$outputDir/parte_$part.wav"; // Salvar em formato WAV
        $command = "/opt/homebrew/bin/ffmpeg -i " . escapeshellarg($inputFile) . " -ss $start -t $duration -c:a pcm_s16le -ar 16000 " . escapeshellarg($outputFile) . " 2>&1";
        shell_exec($command);

        // Verifica se o arquivo foi gerado com sucesso
        if (file_exists($outputFile)) {
            $parts[] = "parte_$part.wav";
        }

        $start += $duration; // Incrementa o início para o próximo corte
        $part++; // Incrementa o contador de partes
    }

    // Retorna a lista de partes geradas
    echo json_encode(['status' => 'success', 'parts' => $parts]);
    exit;
}
?>
