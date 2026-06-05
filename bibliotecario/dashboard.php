<?php
session_start();

// Puxa a conexão com o banco
require_once("../conexao.php");

// Validação de acesso para 'Bibliotecario'
if(!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'Bibliotecario'){
    header("Location: ../index.php");
    exit();
}

// 1. Contar Total de Usuários Cadastrados
$sql_users = "SELECT COUNT(*) AS total FROM Usuarios";
$res_users = $conn->query($sql_users);
$row_users = $res_users->fetch_assoc();
$total_usuarios = $row_users['total'] ?? 0;

// 2. Contar Total de Livros distintos na tabela CLIENTES
$sql_livros = "SELECT COUNT(DISTINCT LIVRO) AS total FROM CLIENTES";
$res_livros = $conn->query($sql_livros);
$row_livros = $res_livros->fetch_assoc();
$total_livros = $row_livros['total'] ?? 0;

// 3. Contar Pedidos Pendentes/Totais
$sql_pedidos = "SELECT COUNT(*) AS total FROM CLIENTES";
$res_pedidos = $conn->query($sql_pedidos);
$row_pedidos = $res_pedidos->fetch_assoc();
$total_pedidos = $row_pedidos['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Bibliotecário</title>
    
    <?php include("../css.php"); ?>
</head>
<body>

    <div class="sidebar">
        <h2>Bibliotecário</h2>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="">Livros</a>
        <a href="">Empréstimos</a>
        <a href="registrar_pedido.php">Criar Pedido</a>
        <a href="prazos.php">Alunos (Prazos)</a>
        
        <div class="logout">
            <a href="../logout.php">Sair</a>
        </div>
    </div>

    <div class="main">
        
        <div class="topbar">
            <div>
                <h1>Painel do Bibliotecário</h1>
                <p>Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome'] ?? 'Operador'); ?></p>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Usuários Cadastrados</h3>
                <h2><?php echo $total_usuarios; ?></h2>
            </div>
            
            <div class="card">
                <h3>Total de Livros</h3>
                <h2><?php echo $total_livros; ?></h2>
            </div>

            <div class="card">
                <h3>Prazos</h3>
                <h2><?php echo $total_pedidos; ?></h2>
            </div>
        </div>

        <div class="table-box">
            <h3>Ações do Operador</h3>
            <p style="margin-top: 10px; color: #64748b;">Utilize o menu lateral para navegar entre as funções que já estão criadas no seu sistema.</p>
        </div>

    </div>

</body>
</html>