<?php
session_start();

// 1. PROTEÇÃO DA PÁGINA: Se não for um aluno logado via Google, expulsa para o login
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'Aluno') {
    header("Location: login.html");
    exit();
}

// 2. CONEXÃO COM O BANCO 
require_once("../conexao.php");

// Resgata o nome do aluno direto da sessão para servir como sugestão inicial
$aluno_nome = $_SESSION['aluno_nome'];

// Captura o nome do livro vindo do clique no catálogo
$livro_selecionado = isset($_GET['livro']) ? $_GET['livro'] : '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){


    $nome = $_POST['nome']; 
    $livro = $_POST['livro'];
    $telefone = $_POST['telefone'];
    
  
    $data_atual = date('Y-m-d'); 

  
    $sql = "INSERT INTO reservas (NOME, LIVRO, TELEFONE, DATA) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if($stmt){
    
        $stmt->bind_param("ssss", $nome, $livro, $telefone, $data_atual);

        if($stmt->execute()){
            $mensagem = "Reserva solicitada com sucesso! Vá ao balcão para retirar.";
            $classe_msg = "msg-sucesso";
        }else{
            $mensagem = "Erro ao registrar pedido: " . $stmt->error;
            $classe_msg = "msg-erro";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Reserva - CETEPES Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        /* Estilização Premium e Limpa exclusiva para a página de Solicitação */
        body {
            background-color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }

        .main {
            max-width: 550px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .topbar h1 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin: 0;
        }

        .btn-voltar {
            display: inline-flex;
            align-items: center;
            padding: 10px 166px;
            background-color: white;
            color: #64748b;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .btn-voltar:hover {
            background-color: #f1f5f9;
            color: #334155;
            transform: translateY(-1px);
        }

        .form-box {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        .form-box h2 {
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 25px;
            color: #1e293b;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 8px;
        }

        .form-box input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            font-family: inherit;
            font-size: 15px;
            color: #0f172a;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .form-box input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .btn-confirmar {
            width: 100%;
            padding: 16px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
        }

        .btn-confirmar:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        /* Alertas de Feedback Fluidos */
        .alert-box {
            padding: 16px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            margin-top: 20px;
            text-align: center;
        }

        .msg-sucesso {
            background-color: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .msg-erro {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>

    <div class="main">

        <div class="topbar">
            <h1>Solicitar Livro</h1>
            <a href="aluno.php" class="btn-voltar">← Voltar</a>
        </div>

        <div class="form-box">
            <h2>Confirme ou ajuste seus dados de entrega</h2>

            <form method="POST">
                <div class="input-group">
                    <label>Seu Nome (Como deseja ser chamado no balcão)</label>
                    <input type="text" name="nome" value="<?php echo htmlspecialchars($aluno_nome); ?>" placeholder="Seu nome completo ou como prefere" required>
                </div>

                <div class="input-group">
                    <div class="input-group">
            <label>Título do Livro Selecionado</label>
            <input type="text" name="livro" value="<?php echo htmlspecialchars($livro_selecionado); ?>" placeholder="Nome do Livro" required readonly onfocus="this.blur()">
</div>
                </div>

                <div class="input-group">
                    <label>Seu Telefone / WhatsApp para contato</label>
                    <input type="text" name="telefone" placeholder="73999999999" required autocomplete="off">
                </div>

                <button type="submit" class="btn-confirmar">
                    Confirmar Reserva na Biblioteca
                </button>
            </form>

            <?php if (isset($mensagem)): ?>
                <div class="alert-box <?php echo $classe_msg; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>