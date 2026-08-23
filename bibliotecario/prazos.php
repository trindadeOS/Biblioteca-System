<?php
session_start();

require_once("../conexao.php");

if(!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'Bibliotecario'){
    header("Location: ../index.php");
    exit();
}

$sql = "
SELECT NOME, TURMA, TURNO, CURSO, LIVRO,
DATE_ADD(DATA, INTERVAL 7 DAY) AS DATA_PREVISTA
FROM emp_pessoal
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
    <title>Prazos de Entrega - CETEPES Digital</title>
    <?php include("../css.php"); ?>
    <style>
        body.theme-estante {
            --bg-body: #1a120c radial-gradient(circle, #2d1e12 0%, #110b07 100%);
            --text-color: #f3f4f6;
            --nav-bg: #28160c;
            --nav-border: #3d2314;
            --brand-color: #fbbf24;
            --card-bg: rgba(20, 15, 10, 0.9);
            --card-border: rgba(251, 191, 36, 0.2);
            --input-bg: rgba(0, 0, 0, 0.4);
            --table-header: #28160c;
        }
        body { background: var(--bg-body) !important; color: var(--text-color) !important; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: var(--nav-bg); border-right: 1px solid var(--nav-border); padding: 20px; display: flex; flex-direction: column; justify-content: space-between; flex-shrink: 0; }
        .sidebar h2 { color: var(--brand-color); font-size: 20px; margin-bottom: 30px; text-align: center; }
        .sidebar a { color: #a1a1aa; text-decoration: none; padding: 12px 15px; display: block; border-radius: 8px; margin-bottom: 8px; font-weight: 600; }
        .sidebar a:hover, .sidebar a.active { background: rgba(251, 191, 36, 0.15); color: var(--brand-color); }
        .main { flex-grow: 1; padding: 30px; overflow-y: auto; }
        .panel-box { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 12px; padding: 20px; margin-bottom: 30px; }
        input { width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--card-border); color: #fff; border-radius: 6px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; margin-top: 10px; }
        th, td { padding: 12px 15px; border-bottom: 1px solid var(--card-border); vertical-align: middle; }
        th { background-color: var(--table-header); color: var(--brand-color); }
        tr:hover { background-color: rgba(251, 191, 36, 0.05); }
    </style>
</head>
<body class="theme-estante">

    <div class="sidebar">
        <div>
            <h2>Bibliotecário</h2>
            <a href="dashboard.php">Dashboard</a>
            <a href="livros.php">Livros</a>
            <a href="emprestimos.php">Empréstimos</a>
            <a href="registrar_pedido.php">Criar Pedido</a>
            <a href="avisos.php">Mural de Avisos</a>
            <a href="prazos.php" class="active">Alunos (Prazos)</a>
        </div>
        <div class="logout"><a href="logout.php" style="color: #ef4444;">Sair</a></div>
    </div>

    <div class="main">
        <h1>Prazos de Entrega</h1>
        <p style="color: #a1a1aa; margin-bottom: 25px;">Controle de datas e devoluções dos alunos</p>

        <div class="panel-box">
            <div style="margin-bottom: 20px;">
                <input type="text" placeholder="Buscar aluno ou livro..." style="max-width: 300px;">
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
                        if($result && $result->num_rows > 0){
                            while($row = $result->fetch_assoc()){ 
                                if(!empty($row['DATA_PREVISTA'])) {
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
                            <td style="color: var(--brand-color); font-weight: 600;"><?php echo $data_formatada; ?></td>
                        </tr>
                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; color:#a1a1aa;'>Nenhum prazo pendente encontrado no momento.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
