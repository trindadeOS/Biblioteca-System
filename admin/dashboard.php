<?php
session_start();

// Puxa a conexão com o banco
require_once("../conexao.php");

// Validação de acesso para 'Admin'
if(!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'Admin'){
    header("Location: ../index.php");
    exit();
}

// 1. Contar Total de Usuários Cadastrados
$sql_users = "SELECT COUNT(*) AS total FROM Usuarios";
$res_users = $conn->query($sql_users);
$row_users = $res_users->fetch_assoc();
$total_usuarios = $row_users['total'] ?? 0;

// 2. Contar Total Geral de Auditorias Existentes
$sql_auditoria = "SELECT COUNT(*) AS total FROM Auditoria";
$res_auditoria = $conn->query($sql_auditoria);
$row_auditoria = $res_auditoria->fetch_assoc();
$total_auditorias = $row_auditoria['total'] ?? 0;

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
    <title>Dashboard Admin - Biblioteca</title>
    
    <?php include("../css.php"); ?>
</head>
<body>

    <div class="sidebar">
        <h2>Biblioteca</h2>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="criar_usuario.php">Criar Usuário</a>
        <a href="auditoria.php">Ver Auditoria</a>
        
        <div class="logout">
            <a href="../logout.php">Sair</a>
        </div>
    </div>

    <div class="main">
        
        <div class="topbar">
            <div>
                <h1>Painel Administrativo</h1>
                <p>Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome'] ?? 'Trindade'); ?></p>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Usuários Cadastrados</h3>
                <h2><?php echo $total_usuarios; ?></h2>
            </div>
            
            <div class="card">
                <h3>Total de Auditorias</h3>
                <h2><?php echo $total_auditorias; ?></h2>
            </div>

            <div class="card">
                <h3>Pedidos Pendentes</h3>
                <h2><?php echo $total_pedidos; ?></h2>
            </div>
        </div>

        <div class="table-box">
            <h3>Ações do Administrador</h3>
            <p style="margin-top: 10px; color: #64748b;">Utilize o menu lateral para navegar entre as funções que já estão criadas no seu sistema.</p>
        </div>

    </div>

</body>
</html>