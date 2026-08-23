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

// Deletar aviso
if (isset($_GET['deletar'])) {
    $id_aviso = intval($_GET['deletar']);
    $stmt = $conn->prepare("DELETE FROM avisos WHERE ID = ?");
    $stmt->bind_param("i", $id_aviso);
    $stmt->execute();
    header("Location: mural.php?status=deletado");
    exit();
}

// Criar novo aviso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_aviso'])) {
    $titulo     = trim($_POST['titulo']);
    $mensagem   = trim($_POST['mensagem']);
    $prioridade = $_POST['prioridade'] ?? 'normal';

    $stmt = $conn->prepare("INSERT INTO avisos (titulo, mensagem, prioridade, data_criacao) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $titulo, $mensagem, $prioridade);
    $stmt->execute();
    header("Location: mural.php?status=sucesso");
    exit();
}

$avisos = $conn->query("SELECT * FROM avisos ORDER BY data_criacao DESC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mural de Avisos - Biblioteca CETEPES</title>
    
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
            padding: 20px;
            margin-bottom: 25px;
        }

        .panel-box h2, .panel-box h3 {
            margin-top: 0;
            font-size: 18px;
            color: var(--brand-color);
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            background: var(--input-bg);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            color: #fff;
            margin-bottom: 15px;
            box-sizing: border-box;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--brand-color);
        }

        .btn-salvar {
            background: var(--brand-color);
            color: #1a120c;
            border: none;
            padding: 12px 24px;
            font-weight: 800;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-salvar:hover {
            opacity: 0.9;
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
    </style>
</head>
<body class="theme-estante">

    <!-- MENU LATERAL PADRONIZADO -->
    <div class="sidebar">
        <div>
            <h2>Biblioteca CETEPES</h2>
            <a href="dashboard.php">Dashboard</a>
            <a href="mural.php" class="active">Mural de Avisos</a>
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
            <h1>Mural de Avisos</h1>
            <span>Bem-vindo(a), <strong><?php echo htmlspecialchars($_SESSION['nome'] ?? 'Administrador'); ?></strong>!</span>
        </div>

        <div class="panel-box">
            <h2>📢 Publicar Novo Aviso</h2>
            <form method="POST">
                <input type="hidden" name="novo_aviso" value="1">
                <input type="text" name="titulo" class="form-control" placeholder="Título do aviso" required autocomplete="off">
                <textarea name="mensagem" class="form-control" rows="4" placeholder="Escreva a mensagem para os alunos..." required></textarea>
                <select name="prioridade" class="form-control">
                    <option value="normal">Prioridade Normal</option>
                    <option value="alta">Prioridade Alta (Destaque Red)</option>
                </select>
                <button type="submit" class="btn-salvar">Publicar Comunicado</button>
            </form>
        </div>

        <div class="panel-box">
            <h2>📌 Avisos Publicados</h2>
            <table class="table-responsive">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Título</th>
                        <th>Mensagem</th>
                        <th>Prioridade</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($avisos && $avisos->num_rows > 0): ?>
                        <?php while ($a = $avisos->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($a['data_criacao'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($a['titulo']); ?></strong></td>
                                <td><?php echo htmlspecialchars($a['mensagem']); ?></td>
                                <td>
                                    <span style="color: <?php echo ($a['prioridade'] === 'alta') ? '#ef4444' : '#fbbf24'; ?>; font-weight: bold;">
                                        <?php echo strtoupper($a['prioridade']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="mural.php?deletar=<?php echo $a['ID']; ?>" style="color: #ef4444; font-weight: bold; text-decoration: none;" onclick="return confirm('Deseja excluir este aviso?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; color: #a1a1aa; padding: 20px;">Nenhum aviso cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
