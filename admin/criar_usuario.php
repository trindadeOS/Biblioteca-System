<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once("../conexao.php");

if(!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'Admin'){
    header("Location: ../index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $nome = $_POST['nome'];
    $senha = $_POST['senha'];
    $cpf = $_POST['cpf'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];

    $sql = "
    INSERT INTO Usuarios
    (NOME,SENHA,CPF,EMAIL,TELEFONE,TIPO)
    VALUES (?,?,?,?,?,'Bibliotecario')
    ";

    $stmt = $conn->prepare($sql);

    if(!$stmt){
        die("Erro SQL: " . $conn->error);
    }

    $stmt->bind_param(
        "sssss",
        $nome,
        $senha,
        $cpf,
        $email,
        $telefone
    );

    try {
        if($stmt->execute()){

            $admin_id = $_SESSION['id'];

            $auditoria = "
            INSERT INTO Auditoria
            (Tabela_Afetada,User_Responsavel,Tipo_Operacao)
            VALUES
            ('Usuarios',?,'INSERT')
            ";

            $stmt2 = $conn->prepare($auditoria);

            if($stmt2){
                $stmt2->bind_param("i",$admin_id);
                $stmt2->execute();
            }

            $mensagem = "Usuário criado com sucesso!";
            $classe_msg = "msg";
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $mensagem = "Erro: Este CPF já está cadastrado no sistema!";
            $classe_msg = "msg-erro";
        } else {
            $mensagem = "Erro no banco de dados: " . $e->getMessage();
            $classe_msg = "msg-erro";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Usuário - Biblioteca</title>
    
    <?php include("../css.php"); ?>
</head>
<body>

    <div class="sidebar">
        <h2>Biblioteca</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="criar_usuario.php" class="active">Criar Usuário</a>
        <a href="auditoria.php">Ver Auditoria</a>
        
        <div class="logout">
            <a href="../logout.php">Sair</a>
        </div>
    </div>

    <div class="main">
        
        <div class="topbar">
            <div>
                <h1>Gerenciamento</h1>
                <p>Cadastrar novos funcionários no sistema</p>
            </div>
            <a href="dashboard.php" class="btn" style="background: #64748b; color: white;">← Voltar ao Dashboard</a>
        </div>

        <div class="table-box" style="max-width: 600px;">
            <h2 style="margin-bottom: 20px;">Criar Bibliotecário</h2>

            <form method="POST">
                <input type="text" name="nome" placeholder="Nome" required>
                <input type="text" name="cpf" placeholder="CPF" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="telefone" placeholder="Telefone" required>
                <input type="password" name="senha" placeholder="Senha" required>

                <button type="submit" class="btn-blue" style="margin-top: 15px; width: 100%;">
                    Criar Usuário
                </button>
            </form>

            <?php
            if(isset($mensagem)){
                $cor = ($classe_msg == "msg-erro") ? "color: #dc2626;" : "color: #16a34a;"; 
                echo "<p style='$cor font-weight:600; margin-top:15px;'>$mensagem</p>";
            }
            ?>
        </div>

    </div>

</body>
</html>