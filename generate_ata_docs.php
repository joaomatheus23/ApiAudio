<?php
require 'vendor/autoload.php'; // Certifique-se de ter o PHPWord instalado

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transcricao = $_POST['transcricao'] ?? null;

    if (!$transcricao) {
        echo json_encode(['success' => false, 'message' => 'Transcrição ausente.']);
        exit;
    }

    try {
        // Criar o documento Word
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Adicionar título
        $section->addText(
            "ATA DE REUNIÃO",
            ['bold' => true, 'size' => 18],
            ['alignment' => 'center']
        );
        $section->addTextBreak(1);

        // Adicionar informações da reunião
        $section->addText("Data e Horário: " . date('d/m/Y H:i:s'), ['size' => 12]);
        $section->addText("Local: Sala de Reuniões Virtual (ou outro local)", ['size' => 12]);
        $section->addText("Participantes: [Nome dos Participantes ou 'N/A']", ['size' => 12]);
        $section->addTextBreak(1);

        // Adicionar introdução
        $section->addText(
            "INTRODUÇÃO",
            ['bold' => true, 'size' => 14]
        );
        $section->addText(
            "Esta ata registra os principais pontos discutidos durante a reunião realizada na data e horário acima mencionados. O objetivo foi abordar temas estratégicos relacionados ao avanço das operações e às principais iniciativas da organização.",
            ['size' => 12]
        );
        $section->addTextBreak(1);

        // Adicionar pontos discutidos
        $section->addText(
            "PONTOS DISCUTIDOS",
            ['bold' => true, 'size' => 14]
        );
        $section->addText(
            "1. Análise do Cenário Atual: Foi discutido o desempenho atual da organização e a identificação de áreas que necessitam de melhoria. A equipe destacou desafios relacionados à integração entre departamentos e à adaptação a novas demandas de mercado.",
            ['size' => 12]
        );
        $section->addText(
            "2. Planejamento Estratégico: Foram apresentados os objetivos de curto e longo prazo, com foco em iniciativas para otimizar processos internos e aumentar a competitividade no mercado.",
            ['size' => 12]
        );
        $section->addText(
            "3. Identificação de Desafios: Os principais desafios mencionados foram relacionados à alocação de recursos, treinamento de equipes e comunicação interna. Soluções foram sugeridas para cada um dos problemas levantados.",
            ['size' => 12]
        );
        $section->addTextBreak(1);

        // Adicionar decisões tomadas
        $section->addText(
            "DECISÕES TOMADAS",
            ['bold' => true, 'size' => 14]
        );
        $section->addText(
            "1. Implementar reuniões semanais entre os departamentos para melhorar a comunicação e alinhar estratégias.",
            ['size' => 12]
        );
        $section->addText(
            "2. Criar um comitê de gestão de recursos para garantir uma alocação eficiente e atender às prioridades estratégicas.",
            ['size' => 12]
        );
        $section->addText(
            "3. Iniciar programas de capacitação para líderes de equipe, com foco em gestão de mudanças e liderança adaptativa.",
            ['size' => 12]
        );
        $section->addTextBreak(1);

        // Adicionar próximos passos
        $section->addText(
            "PRÓXIMOS PASSOS",
            ['bold' => true, 'size' => 14]
        );
        $section->addText(
            "1. A equipe responsável deve apresentar um plano detalhado de ações para cada uma das decisões tomadas até a próxima reunião, marcada para [data futura].",
            ['size' => 12]
        );
        $section->addText(
            "2. Realizar uma análise de desempenho nos próximos 30 dias para avaliar a efetividade das iniciativas implementadas.",
            ['size' => 12]
        );
        $section->addTextBreak(1);

        // Adicionar conclusão
        $section->addText(
            "CONCLUSÃO",
            ['bold' => true, 'size' => 14]
        );
        $section->addText(
            "A reunião foi encerrada às " . date('H:i') . " com agradecimentos a todos os participantes. As ações discutidas serão monitoradas, e os resultados serão apresentados na próxima reunião.",
            ['size' => 12]
        );

        // Salvar como DOCX
        $fileName = 'ata_' . time() . '.docx';
        $filePath = __DIR__ . '/atas/' . $fileName;

        // Garante que o diretório de saída exista
        if (!is_dir(__DIR__ . '/atas')) {
            mkdir(__DIR__ . '/atas', 0777, true);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($filePath);

        echo json_encode([
            'success' => true,
            'fileUrl' => 'atas/' . $fileName,
            'fileName' => $fileName,
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao gerar o arquivo DOCX: ' . $e->getMessage()]);
    }
}
