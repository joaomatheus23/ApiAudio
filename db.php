<?php
// Configurações do banco de dados
$host = 'localhost'; // Servidor do banco de dados
$dbname = 'apiaudio'; // Nome do banco de dados
$username = 'root'; // Usuário do banco de dados
$password = 'root'; // Senha do banco de dados

try {
    // Cria a conexão com o banco de dados usando PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Configura o modo de erro para exceções
} catch (PDOException $e) {
    // Exibe uma mensagem de erro amigável e termina o script
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
