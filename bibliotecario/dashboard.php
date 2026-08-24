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

// 1. Contar Total de Alunos Cadastrados
$sql_alunos = "SELECT COUNT(*) AS total FROM alunos";
$res_alunos = $conn->query($sql_alunos);
$total_alunos = ($res_alunos) ? $res_alunos->fetch_assoc()['total'] : 0;

// 2. Contar Total de Livros cadastrados no acervo
$sql_livros = "SELECT COUNT(*) AS total FROM livros";
$res_livros = $conn->query($sql_livros);
$total_livros = ($res_livros) ? $res_livros->fetch_assoc()['total'] : 0;

// 3. Contar Total de Empréstimos / Pedidos
$sql_pedidos = "SELECT COUNT(*) AS total FROM emprestimos";
$res_pedidos = $conn->query($sql_pedidos);
$total_pedidos = ($res_pedidos) ? $res_pedidos->fetch_assoc()['total'] : 0;

// 4. Contar Empréstimos Pendentes
$sql_pendentes = "SELECT COUNT(*) AS total FROM emprestimos WHERE UPPER(STATUS) = 'PENDENTE'";
$res_pendentes = $conn->query($sql_pendentes);
$total_pendentes = ($res_pendentes) ? $res_pendentes->fetch_assoc()['total'] : 0;

// 5. Listar os últimos pedidos pendentes
$sql_ultimos_pendentes = "SELECT * FROM emprestimos WHERE UPPER(STATUS) = 'PENDENTE' ORDER BY ID DESC LIMIT 5";
$res_ultimos = $conn->query($sql_ultimos_pendentes);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Bibliotecário - CETEPES Digital</title>
    
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

        body {
            background: var(--bg-body) !important;
            color: var(--text-color) !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            display: flex;
            min-height: 100vh;
            flex-direction: row;
        }

        /* Sidebar padrão (Desktop) */
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

        /* Botão Hamburguer para Mobile */
        .menu-toggle {
            display: none;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            color: var(--brand-color);
            font-size: 16px;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .main {
            flex-grow: 1;
            padding: 30px;
            box-sizing: border-box;
            overflow-y: auto;
            width: 100%;
        }

        .header-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-title h1 {
            color: var(--brand-color);
            margin: 0;
            font-size: 26px;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card-stat {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .card-stat h3 {
            margin: 0;
            font-size: 13px;
            color: #a1a1aa;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-stat .value {
            font-size: 28px;
            font-weight: 800;
            color: var(--brand-color);
            margin-top: 10px;
        }

        .panel-box {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-sizing: border-box;
        }

        .panel-box h3 {
            margin-top: 0;
            font-size: 18px;
            color: var(--brand-color);
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .btn-atalho {
            display: inline-block;
            background: var(--brand-color);
            color: #1a120c;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: opacity 0.2s;
        }

        .btn-atalho:hover {
            opacity: 0.9;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
            white-space: nowrap;
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--card-border);
        }

        th {
            background-color: var(--table-header);
            color: var(--brand-color);
            font-weight: 700;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        /* Responsividade para Telas Menores (Celulares) */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed !important;
                left: -260px !important;
                top: 0 !important;
                height: 100% !important;
                z-index: 99999 !important;
                width: 250px !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                transition: left 0.3s ease !important;
                box-shadow: 5px 0 25px rgba(0,0,0,0.8) !important;
            }

            .sidebar.ativo {
                left: 0 !important;
            }

            .menu-toggle {
                display: inline-flex !important;
            }

            .main {
                padding: 15px !important;
                width: 100% !important;
            }

            .header-title h1 {
                font-size: 22px !important;
            }
        }
    </style>
</head>
<body class="theme-estante">

    <div class="sidebar" id="sidebar">
        <div>
            <h2>Bibliotecário</h2>
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="livros.php">Livros</a>
            <a href="emprestimos.php">Empréstimos</a>
            <a href="registrar_pedido.php">Criar Pedido</a>
            <a href="avisos.php">Mural de Avisos</a>
            <a href="prazos.php">Alunos (Prazos)</a>
        </div>
        <div class="logout">
            <a href="logout.php">Sair</a>
        </div>
    </div>

    <div class="main">
        <!-- Botão para abrir o menu no mobile -->
        <button class="menu-toggle" onclick="toggleMenu()">
            ☰ Menu
        </button>

        <div class="header-title">
            <h1>Painel do Bibliotecário</h1>
            <span>Bem-vindo(a), <strong><?php echo htmlspecialchars($_SESSION['nome'] ?? 'Operador'); ?></strong>!</span>
        </div>

        <div class="cards-grid">
            <div class="card-stat">
                <h3>Alunos Cadastrados</h3>
                <div class="value"><?php echo $total_alunos; ?></div>
            </div>
            <div class="card-stat">
                <h3>Total de Livros</h3>
                <div class="value"><?php echo $total_livros; ?></div>
            </div>
            <div class="card-stat">
                <h3>Total de Empréstimos</h3>
                <div class="value"><?php echo $total_pedidos; ?></div>
            </div>
            <div class="card-stat">
                <h3>Pedidos Pendentes</h3>
                <div class="value" style="color: #f59e0b;"><?php echo $total_pendentes; ?></div>
            </div>
        </div>

        <div class="panel-box">
            <h3>Últimas Solicitações Pendentes</h3>
            <?php if ($res_ultimos && $res_ultimos->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Livro Solicitado</th>
                                <th>Data do Pedido</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $res_ultimos->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['NOME']); ?></td>
                                    <td><?php echo htmlspecialchars($row['LIVRO']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['DATA'])); ?></td>
                                    <td>
                                        <a href="emprestimos.php" style="color: var(--brand-color); text-decoration: none; font-weight: 700;">Gerenciar &rarr;</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: #a1a1aa; margin: 0; font-size: 14px;">Nenhuma solicitação pendente no momento. Tudo em dia! 🎉</p>
            <?php endif; ?>
        </div>

        <div class="panel-box">
            <h3>Ações Rápidas do Operador</h3>
            <p style="margin-top: 10px; color: #a1a1aa; font-size: 14px;">Utilize o menu lateral para gerenciar o acervo de livros, aprovar solicitações dos alunos, cadastrar avisos de prioridade ou criar novos pedidos manualmente.</p>
            
            <div style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 10px;">
                <a href="registrar_pedido.php" class="btn-atalho" style="margin: 0;">➕ Novo Pedido</a>
                <a href="avisos.php" class="btn-atalho" style="background: #a855f7; color: #fff; margin: 0;">📢 Gerenciar Avisos</a>
                <a href="prazos.php" class="btn-atalho" style="background: #10b981; color: #fff; margin: 0;">⏱️ Ver Prazos de Alunos</a>
            </div>
        </div>

    </div>

    <script>
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('ativo');
        }
    </script>

</body>
</html>