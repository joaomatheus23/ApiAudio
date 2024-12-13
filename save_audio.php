<?php
require 'db.php';

// Aumentar o limite de memória para evitar erros de alocação
ini_set('memory_limit', '512M'); // Ajustado para 512 MB

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $arquivo = $_POST['arquivo'] ?? null;
    $transcricao = $_POST['transcricao'] ?? null;
    $nome = $_POST['nome'] ?? 'Usuário Desconhecido'; // Captura o nome do usuário ou usa um padrão

    // Verificar os valores recebidos
    if (!$arquivo || !$transcricao) {
        echo json_encode(['success' => false, 'message' => 'Arquivo ou transcrição ausente.']);
        exit;
    }

    try {
        // Função para gerar um token único
        function generateToken($length = 32) {
            return bin2hex(random_bytes($length / 2));
        }

        // Verificar se o arquivo já existe no banco
        $stmt = $pdo->prepare("SELECT id, token FROM audios WHERE arquivo = :arquivo LIMIT 1");
        $stmt->execute([':arquivo' => $arquivo]);
        $audio = $stmt->fetch();

        $token = $audio['token'] ?? generateToken(); // Se não existir, gera um novo token

        if ($audio) {
            // Atualizar a transcrição e o nome do usuário mantendo o token existente
            $updateStmt = $pdo->prepare("UPDATE audios SET transcricao = :transcricao, nome = :nome WHERE arquivo = :arquivo");
            $updateStmt->execute([
                ':transcricao' => $transcricao,
                ':arquivo' => $arquivo,
                ':nome' => $nome,
            ]);

            $message = 'Transcrição e nome atualizados com sucesso.';
        } else {
            // Inserir um novo registro com o token e o nome do usuário
            $insertStmt = $pdo->prepare("INSERT INTO audios (nome, arquivo, transcricao, token) VALUES (:nome, :arquivo, :transcricao, :token)");
            $insertStmt->execute([
                ':nome' => $nome,
                ':arquivo' => $arquivo,
                ':transcricao' => $transcricao,
                ':token' => $token,
            ]);

            $message = 'Novo arquivo, transcrição e nome salvos com sucesso.';
        }

        // Fechar conexões para liberar memória
        $updateStmt = null;
        $insertStmt = null;

        // Excluir arquivos picotados imediatamente após processá-los
        $picotadosDir = __DIR__ . '/picotados';
        $files = glob("$picotadosDir/parte_*.wav"); // Busca todos os arquivos que começam com "parte_"
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file); // Exclui o arquivo
                gc_collect_cycles(); // Libera memória após cada exclusão
            }
        }

        echo json_encode(['success' => true, 'message' => $message, 'token' => $token]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro inesperado: ' . $e->getMessage()]);
    }
}
