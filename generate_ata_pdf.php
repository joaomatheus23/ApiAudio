<?php
require 'vendor/autoload.php'; // Certifique-se de que a biblioteca TCPDF esteja instalada

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transcricao = $_POST['transcricao'] ?? null;

    if (!$transcricao) {
        echo json_encode(['success' => false, 'message' => 'Transcrição ausente.']);
        exit;
    }

    try {
        // Criar o PDF
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Sistema de Áudio');
        $pdf->SetTitle('Ata de Reunião');
        $pdf->SetSubject('Transcrição de Áudio');
        $pdf->SetMargins(15, 20, 15);
        $pdf->AddPage();

        // Adicionar título
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'ATA DE REUNIÃO', 0, 1, 'C');
        $pdf->Ln(5);

        // Informações da reunião
        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, "Data e Horário: " . date('d/m/Y H:i:s'), 0, 'L');
        $pdf->MultiCell(0, 10, "Local: Sala de Reuniões Virtual (ou outro local)", 0, 'L');
        $pdf->MultiCell(0, 10, "Participantes: [Nome dos Participantes ou 'N/A']", 0, 'L');
        $pdf->Ln(5);

        // Introdução
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'INTRODUÇÃO', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, "Esta ata registra os principais pontos discutidos durante a reunião realizada na data e horário acima mencionados. O objetivo foi abordar temas estratégicos relacionados ao avanço das operações e às principais iniciativas da organização.");
        $pdf->Ln(5);

        // Pontos discutidos
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'PONTOS DISCUTIDOS', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, "1. Análise do Cenário Atual: Foi discutido o desempenho atual da organização e a identificação de áreas que necessitam de melhoria. A equipe destacou desafios relacionados à integração entre departamentos e à adaptação a novas demandas de mercado.");
        $pdf->Ln(2);
        $pdf->MultiCell(0, 10, "2. Planejamento Estratégico: Foram apresentados os objetivos de curto e longo prazo, com foco em iniciativas para otimizar processos internos e aumentar a competitividade no mercado.");
        $pdf->Ln(2);
        $pdf->MultiCell(0, 10, "3. Identificação de Desafios: Os principais desafios mencionados foram relacionados à alocação de recursos, treinamento de equipes e comunicação interna. Soluções foram sugeridas para cada um dos problemas levantados.");
        $pdf->Ln(5);

        // Decisões tomadas
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'DECISÕES TOMADAS', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, "1. Implementar reuniões semanais entre os departamentos para melhorar a comunicação e alinhar estratégias.");
        $pdf->Ln(2);
        $pdf->MultiCell(0, 10, "2. Criar um comitê de gestão de recursos para garantir uma alocação eficiente e atender às prioridades estratégicas.");
        $pdf->Ln(2);
        $pdf->MultiCell(0, 10, "3. Iniciar programas de capacitação para líderes de equipe, com foco em gestão de mudanças e liderança adaptativa.");
        $pdf->Ln(5);

        // Próximos passos
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'PRÓXIMOS PASSOS', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, "1. A equipe responsável deve apresentar um plano detalhado de ações para cada uma das decisões tomadas até a próxima reunião, marcada para [data futura].");
        $pdf->Ln(2);
        $pdf->MultiCell(0, 10, "2. Realizar uma análise de desempenho nos próximos 30 dias para avaliar a efetividade das iniciativas implementadas.");
        $pdf->Ln(5);

        // Conclusão
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'CONCLUSÃO', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, "A reunião foi encerrada às " . date('H:i') . " com agradecimentos a todos os participantes. As ações discutidas serão monitoradas, e os resultados serão apresentados na próxima reunião.");
        $pdf->Ln(10);

        // Salvar como arquivo
        $fileName = 'ata_' . time() . '.pdf';
        $filePath = __DIR__ . '/atas/' . $fileName;

        // Garante que o diretório de saída exista
        if (!is_dir(__DIR__ . '/atas')) {
            mkdir(__DIR__ . '/atas', 0777, true);
        }

        $pdf->Output($filePath, 'F');

        echo json_encode([
            'success' => true,
            'fileUrl' => 'atas/' . $fileName,
            'fileName' => $fileName,
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao gerar o arquivo PDF: ' . $e->getMessage()]);
    }
}
