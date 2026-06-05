<?php

// 1. Carrega o sistema de arquivos do Composer
require_once __DIR__ . '/vendor/autoload.php';

// 2. Carrega as variáveis do arquivo .env para o PHP
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASSWORD'];
$db = $_ENV['DB_NAME'];

$conn = new mysqli($host,$user,$pass,$db);

if($conn->connect_error){
    die("Erro de conexão");
}
?>