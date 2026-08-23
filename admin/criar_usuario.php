<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once("../conexao.php");

if (!isset($_SESSION['tipo']) || strtoupper($_SESSION['tipo']) !== 'ADMIN') {
    header("Location: ../index.php");
    exit();
}

$mensagem = '';
$classe_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nome = $_POST['nome'];
    $senha = $_POST['senha'];
    $cpf = $_POST['cpf'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];

    $sql = "INSERT INTO usuarios (NOME, SENHA, CPF, EMAIL, TELEFONE, TIPO) VALUES (?, ?, ?, ?, ?, 'Bibliotecario')";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erro SQL: " . $conn->error);
    }

    $stmt->bind_param("sssss", $nome, $senha, $cpf, $email, $telefone);

    try {
        if ($stmt->execute()) {
            $admin_id = $_SESSION['id'];

            $auditoria = "INSERT INTO auditoria (Tabela_Afetada, User_Responsavel, Tipo_Operacao) VALUES ('Usuarios', ?, 'INSERT')";
            $stmt2 = $conn->prepare($auditoria);

            if ($stmt2) {
                $stmt2->bind_param("i", $admin_id);
                $stmt2->execute();
            }

            $mensagem = "Usuário criado com sucesso!";
            $classe_msg = "sucesso";
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $mensagem = "Erro: Este CPF já está cadastrado no sistema!";
            $classe_msg = "erro";
        } else {
            $mensagem = "Erro no banco de dados: " . $e->getMessage();
            $classe_msg = "erro";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Usuário - Biblioteca CETEPES</title>
    
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
            max-width: 420px;
        }

        .panel-box h2 {
            margin-top: 0;
            font-size: 18px;
            color: var(--brand-color);
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .form-group label {
            color: var(--brand-color);
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
        }

        .form-group input {
            background-color: var(--input-bg);
            border: 1px solid var(--card-border);
            color: #ffffff;
            width: 100%;
            padding: 10px 12px;
            border-radius: 6px;
            box-sizing: border-box;
            margin-bottom: 14px;
            font-size: 14px;
            outline: none;
        }

        .form-group input:focus {
            border-color: var(--brand-color);
        }

        .btn-submit {
            background-color: var(--brand-color);
            color: #110b07;
            font-weight: 800;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 14px;
            transition: 0.2s;
        }

        .btn-submit:hover {
            opacity: 0.9;
        }

        .alert-box {
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .alert-box.sucesso {
            background-color: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 1px solid #10b981;
        }

        .alert-box.erro {
            background-color: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid #ef4444;
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
            <a href="criar_usuario.php" class="active">Criar Usuário</a>
            <a href="auditoria.php">Ver Auditoria</a>
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
            <h2>Criar Bibliotecário</h2>

            <?php if (!empty($mensagem)): ?>
                <div class="alert-box <?php echo $classe_msg; ?>">
                    <?php echo htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" placeholder="Digite o nome" required>
                </div>
                <div class="form-group">
                    <label>CPF</label>
                    <input type="text" name="cpf" placeholder="000.000.000-00" required>
                </div>
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" placeholder="usuario@email.com" required>
                </div>
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone" placeholder="(00) 00000-0000" required>
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="senha" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-submit">Criar Usuário</button>
            </form>
        </div>
    </div>

</body>
</html>
