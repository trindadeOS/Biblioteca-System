<?php
session_start();

// 1. Importa a conexão com o banco de dados
require_once("../conexao.php");

// 2. Valida o nível de acesso por segurança
if(!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'Bibliotecario'){
    header("Location: ../index.php");
    exit();
}

// 3. Executa a busca dos prazos (Ajustado para buscar a coluna correta de data se necessário)
$sql = "
SELECT NOME, TURMA, TURNO, CURSO, LIVRO,
DATE_ADD(DATA, INTERVAL 7 DAY) AS DATA_PREVISTA
FROM CLIENTES
ORDER BY DATA_PREVISTA ASC
";

$result = $conn->query($sql);

if(!$result){
    die("Erro na consulta ao banco: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prazos de Entrega - Biblioteca</title>
    
    <?php include("../css.php"); ?>

    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f8fafc;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Bibliotecário</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="dashboard.php">Livros</a>
        <a href="dashboard.php">Empréstimos</a>
        <a href="registrar_pedido.php">Criar Pedido</a>
        <a class="active" href="prazos.php">Alunos (Prazos)</a>

        <div class="logout">
            <a href="../logout.php">Sair</a>
        </div>
    </div>

    <div class="main">

        <div class="topbar">
            <div>
                <h1>Prazos de Entrega</h1>
                <p>Controle de datas e devoluções dos alunos</p>
            </div>
            <a href="dashboard.php" class="btn" style="background: #64748b; color: white;">← Voltar ao Painel</a>
        </div>

        <div class="table-box">
            <div style="margin-bottom: 20px;">
                <input type="text" placeholder="Buscar aluno ou livro..." style="max-width: 300px; margin: 0;">
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Turma</th>
                            <th>Turno</th>
                            <th>Curso</th>
                            <th>Livro</th>
                            <th>Data Prevista</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if($result->num_rows > 0){
                            while($row = $result->fetch_assoc()){ 
                                // Formata a data para o padrão brasileiro (dia/mês/ano)
                                $data_formatada = date('d/m/l', strtotime($row['DATA_PREVISTA']));
                                // Se a data falhar por algum motivo, mantemos o valor bruto
                                if($row['DATA_PREVISTA']) {
                                    $data_formatada = date('d/m/Y', strtotime($row['DATA_PREVISTA']));
                                } else {
                                    $data_formatada = "Não definida";
                                }
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['NOME']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['TURMA']); ?></td>
                            <td><?php echo htmlspecialchars($row['TURNO']); ?></td>
                            <td><?php echo htmlspecialchars($row['CURSO']); ?></td>
                            <td><?php echo htmlspecialchars($row['LIVRO']); ?></td>
                            <td style="color: #2563eb; font-weight: 600;"><?php echo $data_formatada; ?></td>
                        </tr>
                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; color:#64748b;'>Nenhum prazo pendente encontrado no momento.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>