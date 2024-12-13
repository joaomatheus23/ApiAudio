<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transcricao = $_POST['transcricao'] ?? null;

    // Verifica se a transcrição foi enviada
    if (!$transcricao) {
        echo json_encode(['success' => false, 'message' => 'Transcrição ausente.']);
        exit;
    }

    try {
        // Nome do arquivo e diretório para salvar a ata
        $fileName = "ata_" . time() . ".txt"; // Exemplo: ata_1670812000.txt
        $filePath = __DIR__ . "/atas/" . $fileName;

        // Garante que o diretório exista
        if (!is_dir(__DIR__ . "/atas")) {
            mkdir(__DIR__ . "/atas", 0777, true);
        }

        // Conteúdo da ata
        $content = "ATA DE REUNIÃO\n";
        $content .= "Data: " . date('d/m/Y H:i:s') . "\n";
        $content .= "------------------------------------------\n";
        $content .= $transcricao . "\n";
        $content .= "------------------------------------------\n";
        $content .= "Fim da Ata.\n";

        // Escreve o conteúdo no arquivo
        file_put_contents($filePath, $content);

        // Retorna o sucesso com o caminho do arquivo
        echo json_encode([
            'success' => true,
            'fileUrl' => "atas/" . $fileName, // Caminho relativo para download
            'fileName' => $fileName,
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao gerar a ata: ' . $e->getMessage()]);
    }
}
