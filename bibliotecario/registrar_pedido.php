<?php
session_start();

require_once("../conexao.php");

if(!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'Bibliotecario'){
    header("Location: ../index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $nome = $_POST['nome'];
    $serie = $_POST['serie'];
    $turma = $_POST['turma'];
    $turno = $_POST['turno'];
    $curso = $_POST['curso'];
    $livro = $_POST['livro'];
    $telefone = $_POST['telefone'];

    $sql = "INSERT INTO CLIENTES (NOME,SERIE,TURMA,TURNO,CURSO,LIVRO,TELEFONE)
            VALUES (?,?,?,?,?,?,?)";

    $stmt = $conn->prepare($sql);

    if($stmt){
        $stmt->bind_param("sssssss",
            $nome,$serie,$turma,$turno,$curso,$livro,$telefone
        );

        if($stmt->execute()){
            $mensagem = "Pedido registrado com sucesso!";
            $classe_msg = "msg";
        }else{
            $mensagem = "Erro ao registrar pedido: " . $stmt->error;
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
    <title>Registrar Pedido - Biblioteca</title>

    <?php include("../css.php"); ?>
</head>
<body>

    <div class="sidebar">
        <h2>Bibliotecário</h2>
        
        <a href="dashboard.php">Dashboard</a>
        <a href="dashboard.php">Livros</a>
        <a href="dashboard.php">Empréstimos</a>
        
        <a class="active" href="registrar_pedido.php">Criar Pedido</a>
        <a href="prazos.php">Alunos (Prazos)</a>

        <div class="logout">
            <a href="../logout.php">Sair</a>
        </div>
    </div>

    <div class="main">

        <div class="topbar">
            <div>
                <h1>Registrar Pedido</h1>
                <p>Saída de novos livros para alunos</p>
            </div>
            <a href="dashboard.php" class="btn" style="background: #64748b; color: white;">← Voltar ao Painel</a>
        </div>

        <div class="table-box" style="max-width: 600px;">
            <h2 style="margin-bottom: 20px;">Dados do Empréstimo</h2>

            <form method="POST">
                <input type="text" name="nome" placeholder="Nome do aluno" required>
                <input type="text" name="serie" placeholder="Série" required>
                <input type="text" name="turma" placeholder="Turma" required>
                <input type="text" name="turno" placeholder="Turno" required>
                <input type="text" name="curso" placeholder="Curso" required>
                <input type="text" name="livro" placeholder="Nome do Livro" required>
                <input type="text" name="telefone" placeholder="Telefone do Aluno" required>

                <button type="submit" class="btn-blue" style="margin-top: 15px; width: 100%;">
                    Registrar Empréstimo
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