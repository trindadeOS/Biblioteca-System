<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("../conexao.php");

// Validação de acesso do Administrador
if (!isset($_SESSION['tipo']) || strtoupper($_SESSION['tipo']) !== 'ADMIN') {
    header("Location: ../index.php");
    exit();
}

// -----------------------------------------------------------------------------
// METRICAS PARA OS CARDS SUPERIORES
// -----------------------------------------------------------------------------

// Total de Livros Cadastrados
$res_livros = $conn->query("SELECT COUNT(*) as total FROM livros");
$total_livros = ($res_livros) ? $res_livros->fetch_assoc()['total'] : 0;

// Total de Empréstimos Ativos
$res_emp = $conn->query("SELECT COUNT(*) as total FROM emp_pessoal");
$total_emprestimos = ($res_emp) ? $res_emp->fetch_assoc()['total'] : 0;

// Total de Alunos Cadastrados
$res_alunos = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE UPPER(TIPO) != 'ADMIN'");
$total_alunos = ($res_alunos) ? $res_alunos->fetch_assoc()['total'] : 0;

// Livro Mais Popular
$res_top = $conn->query("SELECT LIVRO, COUNT(*) as total FROM emprestimos GROUP BY LIVRO ORDER BY total DESC LIMIT 1");
$livro_top = ($res_top && $res_top->num_rows > 0) ? $res_top->fetch_assoc()['LIVRO'] : "Nenhum registro";

// Empréstimos em Atraso (para a tabela do painel)
$res_atrasados = $conn->query("
    SELECT NOME, LIVRO, TELEFONE, DATA 
    FROM emp_pessoal
    WHERE DATEDIFF(DATA, NOW()) < 0 
    ORDER BY DATA ASC
");

// Avisos Recentes (para o feed lateral)
$res_avisos = $conn->query("SELECT * FROM avisos ORDER BY data_criacao DESC LIMIT 4");

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Administrador - Biblioteca CETEPES</title>
    
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
        }

        /* Sidebar Styling */
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

        /* Main Content */
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

        /* Cards Row */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
            font-size: 14px;
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

        /* Dashboard Layout Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        .panel-box {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 20px;
        }

        .panel-box h2 {
            margin-top: 0;
            font-size: 18px;
            color: var(--brand-color);
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        /* Tables */
        .table-responsive {
            width: 100%;
            border-collapse: collapse;
        }

        .table-responsive th {
            background: var(--table-header);
            color: var(--brand-color);
            text-align: left;
            padding: 10px;
            font-size: 13px;
        }

        .table-responsive td {
            padding: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 14px;
        }

        /* Aviso Card */
        .aviso-item {
            background: rgba(0,0,0,0.3);
            border-left: 3px solid var(--brand-color);
            padding: 10px 12px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .aviso-item.alta {
            border-left-color: #ef4444;
        }

        .aviso-item h4 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #fff;
        }

        .aviso-item p {
            margin: 0;
            font-size: 12px;
            color: #a1a1aa;
        }
    </style>
</head>
<body class="theme-estante">

    <!-- MENU LATERAL -->
    <div class="sidebar">
        <div>
            <h2>Biblioteca CETEPES</h2>
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="mural.php">Mural de Avisos</a>
            <a href="relatorios.php">Relatórios</a>
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
            <h1>Painel de Controle</h1>
            <span>Bem-vindo(a), <strong><?php echo htmlspecialchars($_SESSION['nome'] ?? 'Administrador'); ?></strong>!</span>
        </div>

        <!-- CARDS DE METRICAS -->
        <div class="cards-grid">
            <div class="card-stat">
                <h3>Acervo de Livros</h3>
                <div class="value"><?php echo $total_livros; ?></div>
            </div>
            <div class="card-stat">
                <h3>Empréstimos Ativos</h3>
                <div class="value"><?php echo $total_emprestimos; ?></div>
            </div>
            <div class="card-stat">
                <h3>Alunos Cadastrados</h3>
                <div class="value"><?php echo $total_alunos; ?></div>
            </div>
            <div class="card-stat">
                <h3>Mais Solicitado</h3>
                <div class="value" style="font-size: 16px; margin-top: 15px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                    <?php echo htmlspecialchars($livro_top); ?>
                </div>
            </div>
        </div>

        <!-- CONTEUDO DIVIDIDO EM 2 COLUNAS -->
        <div class="dashboard-grid">
            
            <!-- COLUNA ESQUERDA: DEVOLUÇÕES EM ATRASO -->
            <div class="panel-box">
                <h2> Empréstimos Atrasados (Atenção)</h2>
                <table class="table-responsive">
                    <thead>
                        <tr>
                            <th>Aluno</th>
                            <th>Livro</th>
                            <th>Data Limite</th>
                            <th>Contato</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_atrasados && $res_atrasados->num_rows > 0): ?>
                            <?php while ($row = $res_atrasados->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['NOME']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['LIVRO']); ?></td>
                                    <td style="color: #ef4444; font-weight: bold;">
                                        <?php echo date('d/m/Y', strtotime($row['DATA'])); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['TELEFONE'] ?? 'Sem fone'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #10b981; padding: 20px;">
                                    ✅ Nenhuma devolução pendente/atrasada no momento!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- COLUNA DIREITA: AVISOS DO MURAL DA BIBLIOTECA -->
            <div class="panel-box">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h2 style="margin: 0; border: none; padding: 0;">📌 Mural de Avisos</h2>
                    <a href="mural.php" style="color: var(--brand-color); font-size: 12px; text-decoration: none;">Gerenciar →</a>
                </div>
                <hr style="border-color: var(--card-border); margin-bottom: 15px;">

                <?php if ($res_avisos && $res_avisos->num_rows > 0): ?>
                    <?php while ($aviso = $res_avisos->fetch_assoc()): ?>
                        <div class="aviso-item <?php echo ($aviso['prioridade'] === 'alta') ? 'alta' : ''; ?>">
                            <h4><?php echo htmlspecialchars($aviso['titulo']); ?></h4>
                            <p><?php echo htmlspecialchars(mb_strimwidth($aviso['mensagem'], 0, 75, "...")); ?></p>
                            <span style="font-size: 10px; color: #64748b;">
                                <?php echo date('d/m/Y H:i', strtotime($aviso['data_criacao'])); ?>
                            </span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="font-size: 13px; color: #a1a1aa; text-align: center; padding: 20px 0;">
                        Nenhum comunicado cadastrado.
                    </p>
                <?php endif; ?>
            </div>

        </div>
    </div>

</body>
</html>
