<?php
session_start();
require_once("../conexao.php");

if(!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'Bibliotecario'){
    header("Location: ../index.php");
    exit();
}

// -----------------------------------------------------------------------------
// LÓGICA DE RENOVAÇÃO DO PRAZO (Hoje + 7 dias)
// -----------------------------------------------------------------------------
if (isset($_GET['acao']) && $_GET['acao'] == 'renovar' && isset($_GET['id']) && isset($_GET['origem'])) {
    $id = intval($_GET['id']);
    $origem = $_GET['origem'];

    if ($origem === 'Pessoal') {
        $stmt = $conn->prepare("UPDATE emp_pessoal SET DATA = NOW() WHERE ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($origem === 'Online') {
        $stmt = $conn->prepare("UPDATE emprestimos SET DATA = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: prazos.php?sucesso=1");
    exit();
}

// Consulta unificada corrigida (sem usar aluno_id em emp_pessoal)
$sql = "
SELECT 
    ID AS id,
    NOME, 
    TURMA, 
    TURNO, 
    CURSO, 
    LIVRO, 
    DATE_ADD(DATA, INTERVAL 7 DAY) AS DATA_PREVISTA, 
    'Pessoal' AS ORIGEM 
FROM emp_pessoal

UNION ALL

SELECT 
    emprestimos.id,
    COALESCE(alunos.NOME, emprestimos.NOME, 'Aluno não identificado') AS NOME, 
    COALESCE(alunos.TURMA, 'Não informada') AS TURMA, 
    COALESCE(alunos.TURNO, 'Não informado') AS TURNO, 
    COALESCE(alunos.CURSO, 'Não informado') AS CURSO, 
    emprestimos.LIVRO AS LIVRO, 
    DATE_ADD(emprestimos.DATA, INTERVAL 7 DAY) AS DATA_PREVISTA, 
    'Online' AS ORIGEM 
FROM emprestimos 
LEFT JOIN alunos ON emprestimos.aluno_id = alunos.id
WHERE emprestimos.STATUS = 'CONCLUIDO'

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
        body { 
            background: var(--bg-body) !important; 
            color: var(--text-color) !important; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; 
            display: flex; 
            min-height: 100vh; 
            flex-direction: row;
        }
        .sidebar { 
            width: 250px; 
            background: var(--nav-bg); 
            border-right: 1px solid var(--nav-border); 
            padding: 20px; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
            flex-shrink: 0; 
            box-sizing: border-box;
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
        }
        .sidebar a:hover, .sidebar a.active { 
            background: rgba(251, 191, 36, 0.15); 
            color: var(--brand-color); 
        }
        .sidebar .logout a {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

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
            overflow-y: auto; 
            width: 100%;
            box-sizing: border-box;
        }
        .panel-box { 
            background: var(--card-bg); 
            border: 1px solid var(--card-border); 
            border-radius: 12px; 
            padding: 20px; 
            margin-bottom: 30px; 
            box-sizing: border-box;
        }
        input { 
            width: 100%; 
            padding: 10px; 
            background: var(--input-bg); 
            border: 1px solid var(--card-border); 
            color: #fff; 
            border-radius: 6px; 
            box-sizing: border-box; 
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
            margin-top: 10px; 
            white-space: nowrap;
        }
        th, td { 
            padding: 12px 15px; 
            border-bottom: 1px solid var(--card-border); 
            vertical-align: middle; 
        }
        th { 
            background-color: var(--table-header); 
            color: var(--brand-color); 
        }
        tr:hover { 
            background-color: rgba(251, 191, 36, 0.05); 
        }

        .badge-tipo {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
            margin-top: 3px;
        }
        .badge-pessoal { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
        .badge-online { background: rgba(16, 185, 129, 0.2); color: #34d399; }

        .btn-renovar {
            background-color: rgba(251, 191, 36, 0.2);
            color: var(--brand-color);
            border: 1px solid var(--card-border);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: 0.2s;
        }
        .btn-renovar:hover {
            background-color: var(--brand-color);
            color: #1a120c;
        }

        .alert-sucesso {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid #10b981;
            color: #34d399;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

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

            h1 {
                font-size: 22px !important;
            }
        }
    </style>
</head>
<body class="theme-estante">

    <div class="sidebar" id="sidebar">
        <div>
            <h2>Bibliotecário</h2>
            <a href="dashboard.php">Dashboard</a>
            <a href="livros.php">Livros</a>
            <a href="emprestimos.php">Empréstimos</a>
            <a href="registrar_pedido.php">Criar Pedido</a>
            <a href="avisos.php">Mural de Avisos</a>
            <a href="prazos.php" class="active">Alunos (Prazos)</a>
        </div>
        <div class="logout">
            <a href="logout.php">Sair</a>
        </div>
    </div>

    <div class="main">
        <button class="menu-toggle" onclick="toggleMenu()">
            ☰ Menu
        </button>

        <h1>Prazos de Entrega</h1>
        <p style="color: #a1a1aa; margin-bottom: 25px;">Controle unificado de datas e renovações de empréstimos</p>

        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1): ?>
            <div class="alert-sucesso">✅ Prazo renovado com sucesso por mais 7 dias!</div>
        <?php endif; ?>

        <div class="panel-box">
            <div style="margin-bottom: 20px;">
                <input type="text" id="filtroTabela" placeholder="Buscar aluno ou livro..." style="max-width: 300px;" onkeyup="filtrarTabela()">
            </div>

            <div class="table-responsive">
                <table id="tabelaPrazos">
                    <thead>
                        <tr>
                            <th>Nome / Tipo</th>
                            <th>Turma</th>
                            <th>Turno</th>
                            <th>Curso</th>
                            <th>Livro</th>
                            <th>Data Prevista</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if($result && $result->num_rows > 0){
                            while($row = $result->fetch_assoc()){ 
                                if(!empty($row['DATA_PREVISTA'])) {
                                    $data_formatada = date('d/m/Y', strtotime($row['DATA_PREVISTA']));
                                    $is_atrasado = strtotime($row['DATA_PREVISTA']) < time();
                                } else {
                                    $data_formatada = "Não definida";
                                    $is_atrasado = false;
                                }
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['NOME']); ?></strong><br>
                                <?php if(strtolower($row['ORIGEM']) === 'pessoal'): ?>
                                    <span class="badge-tipo badge-pessoal">Físico (Pessoal)</span>
                                <?php else: ?>
                                    <span class="badge-tipo badge-online">Online</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['TURMA']); ?></td>
                            <td><?php echo htmlspecialchars($row['TURNO']); ?></td>
                            <td><?php echo htmlspecialchars($row['CURSO']); ?></td>
                            <td><?php echo htmlspecialchars($row['LIVRO']); ?></td>
                            <td style="color: <?php echo $is_atrasado ? '#ef4444' : 'var(--brand-color)'; ?>; font-weight: 600;">
                                <?php echo $data_formatada; ?> <?php echo $is_atrasado ? '⚠️' : ''; ?>
                            </td>
                            <td>
                                <a href="prazos.php?acao=renovar&id=<?php echo $row['id']; ?>&origem=<?php echo $row['ORIGEM']; ?>" class="btn-renovar" onclick="return confirm('Deseja realmente adicionar +7 dias ao prazo deste livro?');">
                                    🔄 Renovar (+7d)
                                </a>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='7' style='text-align:center; color:#a1a1aa;'>Nenhum prazo pendente encontrado no momento.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('ativo');
        }

        function filtrarTabela() {
            let input = document.getElementById("filtroTabela");
            let filter = input.value.toLowerCase();
            let table = document.getElementById("tabelaPrazos");
            let tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let tdNome = tr[i].getElementsByTagName("td")[0];
                let tdLivro = tr[i].getElementsByTagName("td")[4];
                if (tdNome || tdLivro) {
                    let txtNome = tdNome.textContent || tdNome.innerText;
                    let txtLivro = tdLivro.textContent || tdLivro.innerText;
                    if (txtNome.toLowerCase().indexOf(filter) > -1 || txtLivro.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</body>
</html>