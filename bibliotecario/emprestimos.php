<?php
session_start();

$caminho_conexao = __DIR__ . "/../conexao.php";
if (file_exists($caminho_conexao)) {
    require_once($caminho_conexao);
} else {
    die("Erro: O arquivo conexao.php não foi encontrado.");
}

if(!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'Bibliotecario'){
    header("Location: ../index.php");
    exit();
}

// Alterar Status do Empréstimo
if (isset($_GET['acao']) && isset($_GET['id'])) {
    $id_emp = intval($_GET['id']);
    $nova_status = $_GET['acao'] == 'concluir' ? 'CONCLUIDO' : 'Cancelado';
    
    $stmt = $conn->prepare("UPDATE emprestimos SET STATUS = ? WHERE ID = ?");
    $stmt->bind_param("si", $nova_status, $id_emp);
    $stmt->execute();
    $stmt->close();
    header("Location: emprestimos.php");
    exit();
}

$sql = "SELECT * FROM emprestimos ORDER BY ID DESC";
$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Empréstimos - CETEPES Digital</title>
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
            --table-header: #28160c;
        }
        body { background: var(--bg-body) !important; color: var(--text-color) !important; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: var(--nav-bg); border-right: 1px solid var(--nav-border); padding: 20px; display: flex; flex-direction: column; justify-content: space-between; flex-shrink: 0; }
        .sidebar h2 { color: var(--brand-color); font-size: 20px; margin-bottom: 30px; text-align: center; }
        .sidebar a { color: #a1a1aa; text-decoration: none; padding: 12px 15px; display: block; border-radius: 8px; margin-bottom: 8px; font-weight: 600; }
        .sidebar a:hover, .sidebar a.active { background: rgba(251, 191, 36, 0.15); color: var(--brand-color); }
        .main { flex-grow: 1; padding: 30px; overflow-y: auto; }
        .panel-box { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 12px; padding: 20px; margin-bottom: 30px; }
        .panel-box h3 { color: var(--brand-color); margin-top: 0; border-bottom: 1px solid var(--card-border); padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; margin-top: 10px; }
        th, td { padding: 12px 15px; border-bottom: 1px solid var(--card-border); }
        th { background-color: var(--table-header); color: var(--brand-color); }
        .btn-acao { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; margin-right: 5px; }
        .btn-concluir { background: #10b981; color: #fff; }
        .btn-cancelar { background: #ef4444; color: #fff; }
    </style>
</head>
<body class="theme-estante">
    <div class="sidebar">
        <div>
            <h2>Bibliotecário</h2>
            <a href="dashboard.php">Dashboard</a>
            <a href="livros.php">Livros</a>
            <a href="emprestimos.php" class="active">Empréstimos</a>
            <a href="registrar_pedido.php">Criar Pedido</a>
            <a href="avisos.php">Mural de Avisos</a>
            <a href="prazos.php">Alunos (Prazos)</a>
        </div>
        <div class="logout"><a href="logout.php" style="color: #ef4444;">Sair</a></div>
    </div>

    <div class="main">
        <h1>Controle de Empréstimos e Solicitações</h1>

        <div class="panel-box">
            <h3>Lista de Solicitações</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Aluno</th>
                        <th>Livro</th>
                        <th>Telefone</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                        <?php while($row = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['ID']; ?></td>
                                <td><?php echo htmlspecialchars($row['NOME']); ?></td>
                                <td><?php echo htmlspecialchars($row['LIVRO']); ?></td>
                                <td><?php echo htmlspecialchars($row['TELEFONE']); ?></td>
                                <td>
                                    <span style="color: <?php echo ($row['STATUS'] == 'PENDENTE') ? '#f59e0b' : (($row['STATUS'] == 'CONCLUIDO') ? '#10b981' : '#ef4444'); ?>; font-weight: bold;">
                                        <?php echo $row['STATUS']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['DATA'])); ?></td>
                                <td>
                                    <a href="emprestimos.php?acao=concluir&id=<?php echo $row['ID']; ?>" class="btn-acao btn-concluir">Concluir</a>
                                    <a href="emprestimos.php?acao=cancelar&id=<?php echo $row['ID']; ?>" class="btn-acao btn-cancelar">Cancelar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7">Nenhum empréstimo registrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
