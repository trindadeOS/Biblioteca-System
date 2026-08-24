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

// Salvar/Atualizar dados de configuração
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dias_prazo        = intval($_POST['dias_prazo']);
    $limite_livros     = intval($_POST['limite_livros']);
    $ultramsg_instance = trim($_POST['ultramsg_instance']);
    $ultramsg_token    = trim($_POST['ultramsg_token']);

    $stmt = $conn->prepare("UPDATE configuracoes SET dias_prazo = ?, limite_livros = ?, ultramsg_instance = ?, ultramsg_token = ? WHERE id = 1");
    $stmt->bind_param("iiss", $dias_prazo, $limite_livros, $ultramsg_instance, $ultramsg_token);
    $stmt->execute();

    header("Location: configuracoes.php?status=salvo");
    exit();
}

$res_config = $conn->query("SELECT * FROM configuracoes WHERE id = 1");
$config = ($res_config && $res_config->num_rows > 0) ? $res_config->fetch_assoc() : [];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Biblioteca CETEPES</title>
    
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
        }

        body {
            background: var(--bg-body) !important;
            color: var(--text-color) !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
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
            transition: left 0.3s ease;
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

        .menu-toggle {
            display: none;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            color: var(--brand-color);
            font-size: 18px;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 20px;
            font-weight: bold;
            align-items: center;
            gap: 8px;
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

        .panel-box {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 25px;
            max-width: 600px;
        }

        .panel-box h2 {
            margin-top: 0;
            font-size: 18px;
            color: var(--brand-color);
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            background: var(--input-bg);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            color: #fff;
            margin-top: 6px;
            margin-bottom: 18px;
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

        @media (max-width: 768px) {
            .sidebar {
                position: fixed !important;
                left: -260px !important;
                top: 0 !important;
                height: 100vh !important;
                z-index: 99999 !important;
                width: 250px !important;
                box-shadow: 5px 0 25px rgba(0,0,0,0.9) !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
            }

            .sidebar.active {
                left: 0 !important;
            }

            .menu-toggle {
                display: inline-flex !important;
            }

            .main {
                padding: 15px !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="theme-estante">

    <!-- MENU LATERAL PADRONIZADO -->
    <div class="sidebar" id="sidebar">
        <div>
            <h2>Biblioteca CETEPES</h2>
            <a href="dashboard.php">Dashboard</a>
            <a href="mural.php">Mural de Avisos</a>
            <a href="relatorios.php">Relatórios</a>
            <a href="configuracoes.php" class="active">Configurações</a>
            <a href="criar_usuario.php">Criar Usuário</a>
            <a href="auditoria.php">Ver Auditoria</a>
        </div>
        <div class="logout">
            <a href="logout.php">Sair</a>
        </div>
    </div>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="main">
        <button class="menu-toggle" onclick="toggleSidebar()">
            ☰ Menu
        </button>

        <div class="header-title">
            <h1>Configurações do Sistema</h1>
            <span>Bem-vindo(a), <strong><?php echo htmlspecialchars($_SESSION['nome'] ?? 'Administrador'); ?></strong>!</span>
        </div>

        <?php if (isset($_GET['status'])): ?>
            <p style="color: #10b981; font-weight: bold; margin-bottom: 20px;">✅ Configurações salvas com sucesso!</p>
        <?php endif; ?>

        <div class="panel-box">
            <form method="POST">
                <h2>⚙️ Parâmetros Globais</h2>

                <label><strong>Prazo Padrão de Devolução (em Dias):</strong></label>
                <input type="number" name="dias_prazo" class="form-control" value="<?php echo htmlspecialchars($config['dias_prazo'] ?? 7); ?>" required>

                <label><strong>Limite Máximo de Livros por Aluno:</strong></label>
                <input type="number" name="limite_livros" class="form-control" value="<?php echo htmlspecialchars($config['limite_livros'] ?? 3); ?>" required>

                <h2>📲 API UltraMSG (WhatsApp)</h2>

                <label><strong>ID da Instância:</strong></label>
                <input type="text" name="ultramsg_instance" class="form-control" value="<?php echo htmlspecialchars($config['ultramsg_instance'] ?? 'instance178315'); ?>" required autocomplete="off">

                <label><strong>Token de Acesso:</strong></label>
                <input type="password" name="ultramsg_token" class="form-control" value="<?php echo htmlspecialchars($config['ultramsg_token'] ?? ''); ?>" required>

                <button type="submit" class="btn-salvar">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>
</html>