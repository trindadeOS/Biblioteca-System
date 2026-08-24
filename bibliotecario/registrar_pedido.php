<?php
session_start();
require_once("../conexao.php");

if(!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'Bibliotecario'){
    header("Location: ../index.php");
    exit();
}

$mensagem = "";
$classe_msg = "";

// Buscar livros disponíveis para preencher o select
$sql_livros = "SELECT id, titulo, quantidade FROM livros WHERE quantidade > 0 ORDER BY titulo ASC";
$resultado_livros = $conn->query($sql_livros);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $nome = trim($_POST['nome']);
    $serie = trim($_POST['serie']);
    $turma = trim($_POST['turma']);
    $turno = trim($_POST['turno']);
    $curso = trim($_POST['curso']);
    $livro_id = intval($_POST['livro_id']);
    $telefone = trim($_POST['telefone']);

    // 1. Buscar o título do livro selecionado pelo ID para salvar na tabela emp_pessoal
    $stmt_busca = $conn->prepare("SELECT titulo, quantidade FROM livros WHERE id = ?");
    $stmt_busca->bind_param("i", $livro_id);
    $stmt_busca->execute();
    $res_busca = $stmt_busca->get_result();
    
    if($res_busca->num_rows > 0) {
        $dados_livro = $res_busca->fetch_assoc();
        $nome_livro = $dados_livro['titulo'];
        $qtd_atual = $dados_livro['quantidade'];
        $stmt_busca->close();

        if ($qtd_atual > 0) {
            // Inicia uma transação para garantir que ambas as operações ocorram com sucesso
            $conn->begin_transaction();

            try {
                // 2. Inserir na tabela de empréstimo pessoal
                $sql_insere = "INSERT INTO emp_pessoal (NOME, SERIE, TURMA, TURNO, CURSO, LIVRO, TELEFONE, DATA) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt_insere = $conn->prepare($sql_insere);
                $stmt_insere->bind_param("sssssss", $nome, $serie, $turma, $turno, $curso, $nome_livro, $telefone);
                $stmt_insere->execute();
                $stmt_insere->close();

                // 3. Atualizar o estoque do livro (subtrair 1)
                $sql_baixa = "UPDATE livros SET quantidade = quantidade - 1 WHERE id = ?";
                $stmt_baixa = $conn->prepare($sql_baixa);
                $stmt_baixa->bind_param("i", $livro_id);
                $stmt_baixa->execute();
                $stmt_baixa->close();

                // Confirma as alterações no banco
                $conn->commit();
                
                $mensagem = "Pedido registrado com sucesso e estoque atualizado!";
                $classe_msg = "msg";
                
                // Recarrega a lista de livros disponíveis
                $resultado_livros = $conn->query($sql_livros);

            } catch (Exception $e) {
                // Se der erro, desfaz as alterações
                $conn->rollback();
                $mensagem = "Erro ao processar o empréstimo: " . $e->getMessage();
                $classe_msg = "msg-erro";
            }
        } else {
            $mensagem = "Este livro está esgotado no estoque!";
            $classe_msg = "msg-erro";
        }
    } else {
        $mensagem = "Livro selecionado inválido.";
        $classe_msg = "msg-erro";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Pedido - CETEPES Digital</title>
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
            max-width: 600px; 
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
        select option { background: #1a120c; color: #fff; }
        button[type="submit"] { 
            background: var(--brand-color); 
            color: #1a120c; 
            border: none; 
            padding: 10px 20px; 
            font-weight: 700; 
            border-radius: 6px; 
            cursor: pointer; 
            width: 100%; 
            margin-top: 15px; 
        }
        button[type="submit"]:hover { opacity: 0.9; }

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
            <a href="registrar_pedido.php" class="active">Criar Pedido</a>
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

        <h1>Registrar Pedido Presencial</h1>
        <p style="color: #a1a1aa; margin-bottom: 25px;">Saída de livros com baixa automática no estoque</p>

        <div class="panel-box">
            <h3>Dados do Empréstimo</h3>

            <form method="POST">
                <label>Nome do Aluno:</label>
                <input type="text" name="nome" placeholder="Nome completo" required>
                
                <label>Série:</label>
                <input type="text" name="serie" placeholder="Ex: 2º Ano" required>
                
                <label>Turma:</label>
                <input type="text" name="turma" placeholder="Ex: A" required>
                
                <label>Turno:</label>
                <input type="text" name="turno" placeholder="Ex: Matutino" required>
                
                <label>Curso:</label>
                <input type="text" name="curso" placeholder="Ex: Informática" required>
                
                <label>Selecione o Livro no Acervo:</label>
                <select name="livro_id" required>
                    <option value="">-- Escolha um livro disponível --</option>
                    <?php if ($resultado_livros && $resultado_livros->num_rows > 0): ?>
                        <?php while($l = $resultado_livros->fetch_assoc()): ?>
                            <option value="<?php echo $l['id']; ?>">
                                <?php echo htmlspecialchars($l['titulo']); ?> (Disponíveis: <?php echo $l['quantidade']; ?>)
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="">Nenhum livro disponível no momento</option>
                    <?php endif; ?>
                </select>
                
                <label>Telefone do Aluno:</label>
                <input type="text" name="telefone" placeholder="(00) 00000-0000" required>

                <button type="submit">Registrar Empréstimo e Dar Baixa</button>
            </form>

            <?php
            if(!empty($mensagem)){
                $cor = ($classe_msg == "msg-erro") ? "color: #ef4444;" : "color: #10b981;"; 
                echo "<p style='$cor font-weight:600; margin-top:15px;'>$mensagem</p>";
            }
            ?>
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