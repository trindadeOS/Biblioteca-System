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

// Cadastro de novo livro com imagem
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar_livro'])) {
    $titulo = trim($_POST['titulo']);
    $autor = trim($_POST['autor']);
    $categoria = trim($_POST['categoria']);
    $quantidade = intval($_POST['quantidade']);
    $sinopse = trim($_POST['sinopse']);
    $nome_imagem = "";

    // Tratamento do upload da imagem para a pasta alunos/sources/
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        // Pega o nome original do arquivo enviado
        $nome_imagem = basename($_FILES['imagem']['name']);
        
        // Caminho correto apontando para a pasta do aluno/sources na raiz
        $pasta_destino = __DIR__ . "/../alunos/sources/";
        
        if (!is_dir($pasta_destino)) {
            mkdir($pasta_destino, 0777, true);
        }
        
        move_uploaded_file($_FILES['imagem']['tmp_name'], $pasta_destino . $nome_imagem);
    }

    if (!empty($titulo) && !empty($autor)) {
        $stmt = $conn->prepare("INSERT INTO livros (titulo, autor, categoria, quantidade, sinopse, imagem, status) VALUES (?, ?, ?, ?, ?, ?, 'Disponivel')");
        $stmt->bind_param("sssiss", $titulo, $autor, $categoria, $quantidade, $sinopse, $nome_imagem);
        
        if ($stmt->execute()) {
            $mensagem = "<div style='color: #10b981; margin-bottom: 15px;'>Livro e capa cadastrados com sucesso!</div>";
        } else {
            $mensagem = "<div style='color: #ef4444; margin-bottom: 15px;'>Erro ao cadastrar livro no banco.</div>";
        }
        $stmt->close();
    } else {
        $mensagem = "<div style='color: #f59e0b; margin-bottom: 15px;'>Preencha os campos obrigatórios.</div>";
    }
}

// Buscar livros do banco
$sql = "SELECT * FROM livros ORDER BY id DESC";
$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Livros - CETEPES Digital</title>
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
        .panel-box h3 { color: var(--brand-color); margin-top: 0; border-bottom: 1px solid var(--card-border); padding-bottom: 10px; }
        input, select, textarea { width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; background: var(--input-bg); border: 1px solid var(--card-border); color: #fff; border-radius: 6px; box-sizing: border-box; }
        input[type="file"] { padding: 8px; cursor: pointer; }
        button { background: var(--brand-color); color: #1a120c; border: none; padding: 10px 20px; font-weight: 700; border-radius: 6px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; margin-top: 10px; }
        th, td { padding: 12px 15px; border-bottom: 1px solid var(--card-border); vertical-align: middle; }
        th { background-color: var(--table-header); color: var(--brand-color); }
        .img-capa { width: 40px; height: 55px; object-fit: cover; border-radius: 4px; border: 1px solid var(--card-border); }
    </style>
</head>
<body class="theme-estante">
    <div class="sidebar">
        <div>
            <h2>Bibliotecário</h2>
            <a href="dashboard.php">Dashboard</a>
            <a href="livros.php" class="active">Livros</a>
            <a href="emprestimos.php">Empréstimos</a>
            <a href="registrar_pedido.php">Criar Pedido</a>
            <a href="avisos.php">Mural de Avisos</a>
            <a href="prazos.php">Alunos (Prazos)</a>
        </div>
        <div class="logout"><a href="logout.php" style="color: #ef4444;">Sair</a></div>
    </div>

    <div class="main">
        <h1>Gerenciamento de Acervo</h1>
        <?php echo $mensagem; ?>

        <div class="panel-box">
            <h3>Cadastrar Novo Livro</h3>
            <form method="POST" enctype="multipart/form-data">
                <label>Título:</label>
                <input type="text" name="titulo" required>
                
                <label>Autor:</label>
                <input type="text" name="autor" required>
                
                <label>Categoria:</label>
                <input type="text" name="categoria" value="Geral">
                
                <label>Quantidade em Estoque:</label>
                <input type="number" name="quantidade" value="1" min="0" required>
                
                <label>Capa do Livro (Imagem):</label>
                <input type="file" name="imagem" accept="image/*">

                <label>Sinopse:</label>
                <textarea name="sinopse" rows="3"></textarea>
                
                <button type="submit" name="cadastrar_livro">Salvar Livro</button>
            </form>
        </div>

        <div class="panel-box">
            <h3>Livros Cadastrados</h3>
            <table>
                <thead>
                    <tr>
                        <th>Capa</th>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Categoria</th>
                        <th>Qtd</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                        <?php while($row = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($row['imagem'])): ?>
                                        <img src="../alunos/sources/<?php echo htmlspecialchars($row['imagem']); ?>" alt="Capa" class="img-capa">
                                    <?php else: ?>
                                        <div style="width: 40px; height: 55px; background: rgba(255,255,255,0.05); border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #71717a;">Sem foto</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($row['autor']); ?></td>
                                <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                                <td><?php echo $row['quantidade']; ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7">Nenhum livro cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
