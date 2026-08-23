<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("../conexao.php");

if (!isset($_SESSION['tipo']) || strtoupper($_SESSION['tipo']) !== 'ADMIN') {
    header("Location: ../index.php");
    exit();
}

$mais_lidos = $conn->query("SELECT LIVRO, COUNT(*) as total FROM emprestimos GROUP BY LIVRO ORDER BY total DESC LIMIT 5");
$atrasados  = $conn->query("SELECT * FROM emp_pessoal WHERE DATEDIFF(DATA, NOW()) < 0");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Biblioteca CETEPES</title>
    
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

        body {
            background: var(--bg-body) !important;
            color: var(--text-color) !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: var(--nav-bg);
            border-right: 1px solid var(--nav-border);
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
            flex-shrink: 0;
        }

        .sidebar h2 {
            color: var(--brand-color);
            font-size: 20px;
            margin-bottom: 30px;
            text-align: center;
        }

        .sidebar a {
            color: #a1a1aa;
            text-decoration: none;
            padding: 12px 15px;
            display: block;
            border-radius: 8px;
            margin-bottom: 8px;
            font-weight: 600;
            transition: 0.2s;
        }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(251, 191, 36, 0.15);
            color: var(--brand-color);
        }

        .sidebar .logout a {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

        .main {
            flex-grow: 1;
            padding: 30px;
            box-sizing: border-box;
            overflow-y: auto;
        }

        .header-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header-title h1 {
            color: var(--brand-color);
            margin: 0;
            font-size: 26px;
        }

        .panel-box {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .panel-box h2 {
            margin-top: 0;
            font-size: 18px;
            color: var(--brand-color);
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .btn-print {
            background: var(--brand-color);
            color: #1a120c;
            border: none;
            padding: 10px 20px;
            font-weight: 800;
            border-radius: 8px;
            cursor: pointer;
        }

        .table-responsive {
            width: 100%;
            border-collapse: collapse;
        }

        .table-responsive th {
            background: var(--table-header);
            color: var(--brand-color);
            text-align: left;
            padding: 12px;
            font-size: 14px;
        }

        .table-responsive td {
            padding: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 14px;
        }

        @media print {
            .sidebar, .btn-print { display: none !important; }
            .main { padding: 0 !important; width: 100% !important; }
            body { background: #fff !important; color: #000 !important; }
        }
    </style>
</head>
<body class="theme-estante">

    <!-- MENU LATERAL PADRONIZADO -->
    <div class="sidebar">
        <div>
            <h2>Biblioteca CETEPES</h2>
            <a href="dashboard.php">Dashboard</a>
            <a href="mural.php">Mural de Avisos</a>
            <a href="relatorios.php" class="active">Relatórios</a>
            <a href="configuracoes.php">Configurações</a>
            <a href="criar_usuario.php">Criar Usuário</a>
            <a href="auditoria.php">Ver Auditoria</a>
        </div>
        <div class="logout">
            <a href="logout.php">Sair</a>
        </div>
    </div>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="main">
        <div class="header-title">
            <h1>Relatórios Consolidados</h1>
            <button onclick="window.print()" class="btn-print">🖨️ Imprimir Relatório</button>
        </div>

        <div class="panel-box">
            <h2>🏆 Top 5 - Livros Mais Solicitados</h2>
            <ul style="padding-left: 20px; margin: 0;">
                <?php if ($mais_lidos && $mais_lidos->num_rows > 0): ?>
                    <?php while ($l = $mais_lidos->fetch_assoc()): ?>
                        <li style="margin-bottom: 10px;">
                            <strong style="color: var(--brand-color);"><?php echo htmlspecialchars($l['LIVRO']); ?></strong> — <?php echo $l['total']; ?> empréstimos
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li style="color: #a1a1aa;">Nenhum histórico de empréstimo registrado.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="panel-box">
            <h2 style="color: #ef4444;">🚨 Empréstimos Atrasados</h2>
            <table class="table-responsive">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Livro</th>
                        <th>Telefone</th>
                        <th>Data Limite</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($atrasados && $atrasados->num_rows > 0): ?>
                        <?php while ($at = $atrasados->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($at['NOME']); ?></strong></td>
                                <td><?php echo htmlspecialchars($at['LIVRO']); ?></td>
                                <td><?php echo htmlspecialchars($at['TELEFONE'] ?? 'Sem fone'); ?></td>
                                <td style="color: #ef4444; font-weight: bold;"><?php echo date('d/m/Y', strtotime($at['DATA'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align: center; color: #10b981; padding: 20px;">✅ Nenhum atraso pendente no momento!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
