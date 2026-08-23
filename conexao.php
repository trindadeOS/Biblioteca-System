<?php
require_once __DIR__ . '/vendor/autoload.php';

// CORREÇÃO: Força o Dotenv a ler a pasta raiz onde o conexao.php está fixado
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST']; 
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASSWORD'];
$db = $_ENV['DB_NAME'];

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
?>
