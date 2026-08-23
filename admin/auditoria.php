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

// Query com JOIN para trazer o Nome do Usuário Responsável
$sql_auditoria = "
    SELECT 
        a.ID, 
        a.Tabela_Afetada, 
        a.User_Responsavel, 
        a.Tipo_Operacao,
        a.data_acao,
        u.NOME as Nome_Usuario
    FROM auditoria a
    LEFT JOIN usuarios u ON a.User_Responsavel = u.ID
    ORDER BY a.ID DESC
";

$res_auditoria = $conn->query($sql_auditoria);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoria do Sistema - Biblioteca CETEPES</title>
    
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
            padding: 24px;
        }

        .panel-box h2 {
            margin-top: 0;
            font-size: 18px;
            color: var(--brand-color);
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .table-responsive {
            width: 100%;
            border-collapse: collapse;
        }

        .table-responsive th {
            background: var(--table-header);
            color: var(--brand-color);
            text-align: left;
            padding: 12px 14px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--card-border);
        }

        .table-responsive td {
            padding: 12px 14px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 14px;
        }

        .table-responsive tbody tr:hover {
            background: rgba(251, 191, 36, 0.03);
        }

        /* Estilização de Badges */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .badge-insert { 
            background: rgba(16, 185, 129, 0.15); 
            color: #10b981; 
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-update { 
            background: rgba(251, 191, 36, 0.15); 
            color: #fbbf24; 
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        .badge-delete { 
            background: rgba(239, 68, 68, 0.15); 
            color: #ef4444; 
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-info .name {
            color: #f3f4f6;
            font-weight: 600;
        }

        .user-info .id {
            color: #71717a;
            font-size: 11px;
        }
    </style>
</head>
<body class="theme-estante">

    <div class="sidebar">
        <div>
            <h2>Biblioteca CETEPES</h2>
            <a href="dashboard.php">Dashboard</a>
            <a href="mural.php">Mural de Avisos</a>
            <a href="relatorios.php">Relatórios</a>
            <a href="configuracoes.php">Configurações</a>
            <a href="criar_usuario.php">Criar Usuário</a>
            <a href="auditoria.php" class="active">Ver Auditoria</a>
        </div>
        <div class="logout">
            <a href="logout.php">Sair</a>
        </div>
    </div>

    <div class="main">
        <div class="header-title">
            <h1>Painel de Controle</h1>
            <span>Bem-vindo(a), <strong><?php echo htmlspecialchars($_SESSION['nome'] ?? 'Administrador'); ?></strong>!</span>
        </div>

        <div class="panel-box">
            <h2>📜 Histórico de Auditoria do Sistema</h2>
            
            <table class="table-responsive">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Tabela Afetada</th>
                        <th>Usuário Responsável</th>
                        <th>Operação</th>
                        <th>Data / Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($res_auditoria && $res_auditoria->num_rows > 0) {
                        while($row = $res_auditoria->fetch_assoc()) {
                            $operacao = strtoupper($row['Tipo_Operacao']);
                            $badge_class = 'badge-insert';
                            if ($operacao == 'UPDATE') $badge_class = 'badge-update';
                            if ($operacao == 'DELETE') $badge_class = 'badge-delete';
                            
                            $nome_usuario = $row['Nome_Usuario'] ?? 'Usuário Removido';
                            $data_formatada = isset($row['data_acao']) ? date('d/m/Y H:i:s', strtotime($row['data_acao'])) : 'N/A';

                            echo "<tr>";
                            echo "<td style='color: #71717a; font-weight: 600;'>#" . htmlspecialchars($row['ID']) . "</td>";
                            echo "<td><strong style='color: var(--brand-color);'>" . htmlspecialchars($row['Tabela_Afetada']) . "</strong></td>";
                            echo "<td>
                                    <div class='user-info'>
                                        <span class='name'>" . htmlspecialchars($nome_usuario) . "</span>
                                        <span class='id'>ID responsável: #" . htmlspecialchars($row['User_Responsavel']) . "</span>
                                    </div>
                                  </td>";
                            echo "<td><span class='badge {$badge_class}'>" . htmlspecialchars($operacao) . "</span></td>";
                            echo "<td style='color: #a1a1aa; font-size: 13px;'>" . $data_formatada . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; color:#a1a1aa; padding: 24px;'>Nenhum registro de auditoria foi encontrado.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>