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

$mensagem = "";

// Cadastro de novo aviso
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar_aviso'])) {
    $titulo = trim($_POST['titulo']);
    $conteudo = trim($_POST['mensagem']);
    $prioridade = trim($_POST['prioridade']);
    $data_criacao = date('Y-m-d H:i:s');

    if (!empty($titulo) && !empty($conteudo)) {
        $stmt = $conn->prepare("INSERT INTO avisos (titulo, mensagem, prioridade, data_criacao) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $titulo, $conteudo, $prioridade, $data_criacao);
        if ($stmt->execute()) {
            $mensagem = "<div style='color: #10b981; margin-bottom: 15px;'>Aviso cadastrado com sucesso!</div>";
        } else {
            $mensagem = "<div style='color: #ef4444; margin-bottom: 15px;'>Erro ao cadastrar aviso.</div>";
        }
        $stmt->close();
    } else {
        $mensagem = "<div style='color: #f59e0b; margin-bottom: 15px;'>Preencha todos os campos obrigatórios.</div>";
    }
}

// Excluir aviso se solicitado
if (isset($_GET['excluir'])) {
    $id_aviso = intval($_GET['excluir']);
    $stmt = $conn->prepare("DELETE FROM avisos WHERE ID = ?");
    $stmt->bind_param("i", $id_aviso);
    $stmt->execute();
    $stmt->close();
    header("Location: avisos.php");
    exit();
}

// Buscar avisos cadastrados
$sql = "SELECT * FROM avisos ORDER BY ID DESC";
$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mural de Avisos - CETEPES Digital</title>
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
        .panel-box h3 { 
            color: var(--brand-color); 
            margin-top: 0; 
            border-bottom: 1px solid var(--card-border); 
            padding-bottom: 10px; 
        }
        input, select, textarea { 
            width: 100%; 
            padding: 10px; 
            margin-top: 5px; 
            margin-bottom: 15px; 
            background: var(--input-bg); 
            border: 1px solid var(--card-border); 
            color: #fff; 
            border-radius: 6px; 
            box-sizing: border-box; 
        }
        button[type="submit"] { 
            background: var(--brand-color); 
            color: #1a120c; 
            border: none; 
            padding: 10px 20px; 
            font-weight: 700; 
            border-radius: 6px; 
            cursor: pointer; 
        }
        button[type="submit"]:hover { opacity: 0.9; }

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
        }
        th { 
            background-color: var(--table-header); 
            color: var(--brand-color); 
        }
        .btn-excluir { 
            background: #ef4444; 
            color: #fff; 
            padding: 6px 12px; 
            border-radius: 4px; 
            text-decoration: none; 
            font-size: 12px; 
            font-weight: bold; 
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
            <a href="avisos.php" class="active">Mural de Avisos</a>
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

        <h1>Mural de Avisos</h1>
        <?php echo $mensagem; ?>

        <div class="panel-box">
            <h3>Criar Novo Aviso</h3>
            <form method="POST">
                <label>Título do Aviso:</label>
                <input type="text" name="titulo" required>
                
                <label>Prioridade:</label>
                <select name="prioridade">
                    <option value="normal">Normal</option>
                    <option value="importante">Importante</option>
                    <option value="urgente">Urgente</option>
                </select>

                <label>Mensagem:</label>
                <textarea name="mensagem" rows="4" required></textarea>
                
                <button type="submit" name="cadastrar_aviso">Publicar Aviso</button>
            </form>
        </div>

        <div class="panel-box">
            <h3>Avisos Publicados</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Prioridade</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado && $resultado->num_rows > 0): ?>
                            <?php while($row = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['ID']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['titulo']); ?></strong><br><small style="color: #a1a1aa;"><?php echo htmlspecialchars($row['mensagem']); ?></small></td>
                                    <td>
                                        <span style="color: <?php echo ($row['prioridade'] == 'urgente') ? '#ef4444' : (($row['prioridade'] == 'importante') ? '#f59e0b' : '#38bdf8'); ?>; font-weight: bold; text-transform: uppercase;">
                                            <?php echo $row['prioridade']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['data_criacao'])); ?></td>
                                    <td>
                                        <a href="avisos.php?excluir=<?php echo $row['ID']; ?>" class="btn-excluir" onclick="return confirm('Deseja realmente excluir este aviso?');">Excluir</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5">Nenhum aviso cadastrado no momento.</td></tr>
                        <?php endif; ?>
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
    </script>
</body>
</html>