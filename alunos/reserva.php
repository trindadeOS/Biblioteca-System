<?php
session_start();

// Habilita exibição de erros para evitar tela 500 sem diagnóstico
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['aluno_id']) || empty($_SESSION['aluno_id'])) {
    header("Location: login.php");
    exit();
}

$caminho_conexao = __DIR__ . "/../conexao.php";
if (file_exists($caminho_conexao)) {
    require_once($caminho_conexao);
} else {
    die("Erro: O arquivo conexao.php não foi encontrado.");
}

$aluno_id   = $_SESSION['aluno_id'];
$aluno_nome = $_SESSION['aluno_nome'] ?? 'Aluno';
$livro_titulo = $_GET['livro'] ?? '';

// -----------------------------------------------------------------------------
// BUSCA O LIMITE DINÂMICO CONFIGURADO PELO ADMINISTRADOR
// -----------------------------------------------------------------------------
$res_config = $conn->query("SELECT limite_livros FROM configuracoes WHERE id = 1");
$config = ($res_config && $res_config->num_rows > 0) ? $res_config->fetch_assoc() : null;
$limite_maximo = isset($config['limite_livros']) ? (int)$config['limite_livros'] : 3;

// Busca as informações completas do livro no banco (incluindo o ID e quantidade)
$detalhes_livro = null;
if (!empty($livro_titulo)) {
    $stmt_livro = $conn->prepare("SELECT id, titulo, autor, categoria, imagem, quantidade, COALESCE(sinopse, 'Sem sinopse disponível.') AS sinopse FROM livros WHERE titulo = ? LIMIT 1");
    if ($stmt_livro) {
        $stmt_livro->bind_param("s", $livro_titulo);
        $stmt_livro->execute();
        $res = $stmt_livro->get_result();
        if ($res && $res->num_rows > 0) {
            $detalhes_livro = $res->fetch_assoc();
        }
    }
}

$erro = '';
$sucesso_reserva = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $livro_post = trim($_POST['livro'] ?? '');
    $aceitou_termos = isset($_POST['aceito_termos']);

    // 1. Verifica quantidade de livros pendentes ou emprestados atualmente por este aluno
    $qtd_atual = 0;
    $stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM emprestimos WHERE aluno_id = ? AND STATUS IN ('PENDENTE', 'EMPRESTADO')");
    if ($stmt_count) {
        $stmt_count->bind_param("i", $aluno_id);
        $stmt_count->execute();
        $res_count = $stmt_count->get_result()->fetch_assoc();
        $qtd_atual = (int) ($res_count['total'] ?? 0);
    }

    if ($qtd_atual >= $limite_maximo) {
        $erro = "Você já possui {$limite_maximo} solicitações ativas (pendentes ou emprestadas). Limite máximo atingido!";
    } elseif (!$aceitou_termos) {
        $erro = "Você precisa declarar estar ciente das regras antes de confirmar.";
    } elseif (!empty($livro_post)) {
        
        // Verifica novamente se o livro tem estoque antes de registrar a reserva
        $stmt_estoque = $conn->prepare("SELECT id, quantidade FROM livros WHERE titulo = ? LIMIT 1");
        $stmt_estoque->bind_param("s", $livro_post);
        $stmt_estoque->execute();
        $res_est = $stmt_estoque->get_result();
        
        if ($res_est && $res_est->num_rows > 0) {
            $dados_est = $res_est->fetch_assoc();
            $livro_db_id = $dados_est['id'];
            $qtd_disponivel = (int)$dados_est['quantidade'];
            $stmt_estoque->close();

            if ($qtd_disponivel > 0) {
                // Inicia transação para garantir que a inserção do empréstimo e a baixa no estoque ocorram juntas
                $conn->begin_transaction();

                try {
                    $data_atual = date('Y-m-d H:i:s');
                    $status_pendente = 'PENDENTE';
                    $telefone_vazio = '';

                    // 2. Insere na tabela emprestimos
                    $stmt = $conn->prepare("INSERT INTO emprestimos (aluno_id, NOME, TELEFONE, LIVRO, STATUS, DATA) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("isssss", $aluno_id, $aluno_nome, $telefone_vazio, $livro_post, $status_pendente, $data_atual);
                    $stmt->execute();
                    $stmt->close();

                    // 3. Dá baixa de 1 unidade no estoque do livro
                    $stmt_baixa = $conn->prepare("UPDATE livros SET quantidade = quantidade - 1 WHERE id = ?");
                    $stmt_baixa->bind_param("i", $livro_db_id);
                    $stmt_baixa->execute();
                    $stmt_baixa->close();

                    // Confirma a transação
                    $conn->commit();
                    $sucesso_reserva = true;

                } catch (Exception $e) {
                    $conn->rollback();
                    $erro = "Erro ao processar a reserva: " . $e->getMessage();
                }

            } else {
                $erro = "Desculpe, este livro acabou de esgotar no estoque!";
            }
        } else {
            $erro = "Livro não encontrado no acervo.";
        }

    } else {
        $erro = "Selecione ou informe um livro válido.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Reserva - CETEPES Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body.theme-estante {
            --bg-body: #1a120c radial-gradient(circle, #2d1e12 0%, #110b07 100%);
            --text-color: #f3f4f6;
            --nav-bg: #28160c;
            --nav-border: #3d2314;
            --brand-color: #fbbf24;
            --card-bg: rgba(20, 15, 10, 0.9);
            --card-border: rgba(251, 191, 36, 0.2);
            --input-bg: rgba(20, 15, 10, 0.9);
            --box-info: rgba(0, 0, 0, 0.3);
        }

        body.theme-dark {
            --bg-body: #0f172a;
            --text-color: #f8fafc;
            --nav-bg: #1e293b;
            --nav-border: #334155;
            --brand-color: #38bdf8;
            --card-bg: #1e293b;
            --card-border: #334155;
            --input-bg: #1e293b;
            --box-info: rgba(0, 0, 0, 0.2);
        }

        body.theme-light {
            --bg-body: #f8fafc;
            --text-color: #0f172a;
            --nav-bg: #ffffff;
            --nav-border: #e2e8f0;
            --brand-color: #2563eb;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --input-bg: #ffffff;
            --box-info: #f1f5f9;
        }

        body {
            background: var(--bg-body) !important;
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            box-sizing: border-box;
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            width: 100vw;
            background: var(--nav-bg) !important;
            border-bottom: 1px solid var(--nav-border);
            padding: 14px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
            box-sizing: border-box;
        }

        .nav-logo { color: var(--brand-color) !important; font-weight: 800; font-size: 18px; }

        .reserva-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .reserva-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 30px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
        }

        .reserva-card h2 {
            margin-top: 0;
            color: var(--brand-color);
            font-size: 22px;
            text-align: center;
            margin-bottom: 20px;
        }

        .detalhes-container {
            display: flex;
            gap: 20px;
            background: var(--box-info);
            padding: 16px;
            border-radius: 12px;
            border: 1px solid var(--card-border);
            margin-bottom: 20px;
            align-items: flex-start;
        }

        .capa-reserva {
            width: 100px;
            height: 140px;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .capa-reserva img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .info-livro {
            flex: 1;
        }

        .info-livro h3 {
            margin: 0 0 6px 0;
            font-size: 17px;
            color: var(--text-color);
        }

        .info-livro p {
            margin: 0 0 6px 0;
            font-size: 13px;
            opacity: 0.8;
        }

        .sinopse-box {
            margin-top: 8px;
            font-size: 12px;
            line-height: 1.4;
            opacity: 0.85;
            max-height: 70px;
            overflow-y: auto;
        }

        .aluno-box {
            background: var(--box-info);
            padding: 12px 16px;
            border-radius: 8px;
            border-left: 4px solid var(--brand-color);
            margin-bottom: 15px;
            font-size: 13px;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--box-info);
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid var(--card-border);
            margin-bottom: 20px;
            cursor: pointer;
            font-size: 13px;
        }

        .checkbox-container input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--brand-color);
            cursor: pointer;
        }

        .btn-container {
            display: flex;
            gap: 12px;
            margin-top: 15px;
        }

        .btn-confirmar {
            flex: 1;
            padding: 12px;
            background: var(--brand-color);
            color: #000;
            border: none;
            border-radius: 8px;
            font-weight: 800;
            font-size: 14px;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.2s;
        }

        .btn-confirmar:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none;
        }

        .btn-confirmar:not(:disabled):hover {
            transform: translateY(-2px);
        }

        .btn-cancelar {
            flex: 1;
            padding: 12px;
            background: rgba(255,255,255,0.08);
            color: var(--text-color);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
            text-decoration: none;
        }

        .nav-btn-back {
            background: transparent;
            border: 1px solid var(--card-border);
            color: var(--text-color);
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease-out forwards;
        }

        .popup-card {
            background: #1e293b;
            border: 1px solid #10b981;
            border-radius: 16px;
            padding: 24px 32px;
            text-align: center;
            color: #ffffff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            max-width: 400px;
            width: 90%;
            animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        .popup-icon {
            font-size: 45px;
            margin-bottom: 10px;
        }

        .popup-card h3 {
            margin: 0 0 8px 0;
            color: #10b981;
            font-size: 20px;
        }

        .popup-card p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes popIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        /* RESPONSIVIDADE PARA CELULARES E TABLETS */
@media (max-width: 768px) {
    .navbar {
        padding: 12px 16px;
    }

    .reserva-wrapper {
        padding: 20px 10px;
    }

    .reserva-card {
        padding: 20px 16px;
        width: 100%;
        border-radius: 12px;
    }

    .detalhes-container {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 15px;
    }

    .capa-reserva {
        width: 120px;
        height: 170px;
    }

    .info-livro {
        width: 100%;
    }

    .sinopse-box {
        max-height: 90px;
        text-align: left;
    }

    .btn-container {
        flex-direction: column-reverse;
        gap: 10px;
    }

    .btn-confirmar, .btn-cancelar {
        width: 100%;
    }
}
        
    </style>
</head>
<body class="theme-estante">

    <?php if ($sucesso_reserva): ?>
        <div class="popup-overlay" id="popupSucesso">
            <div class="popup-card">
                <div class="popup-icon">✅</div>
                <h3>Reserva Solicitada!</h3>
                <p>Estoque atualizado e solicitação gravada com sucesso.<br>Redirecionando para o acervo...</p>
            </div>
        </div>
        <script>
            setTimeout(() => {
                window.location.href = 'aluno.php?status=reservado';
            }, 2500);
        </script>
    <?php endif; ?>

    <nav class="navbar">
        <div class="nav-logo">CETEPES Digital</div>
        <div>
            <a href="aluno.php" style="text-decoration: none;"><button class="nav-btn-back">← Voltar ao Acervo</button></a>
        </div>
    </nav>

    <div class="reserva-wrapper">
        <div class="reserva-card">
            <h2>Confirmar Solicitação de Reserva</h2>
            
            <?php if (!empty($erro)): ?>
                <div style="background: #ef4444; color: white; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; text-align: center; font-weight: 600;">
                     <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>

            <div class="detalhes-container">
                <div class="capa-reserva">
                    <?php if(!empty($detalhes_livro['imagem'])): ?>
                        <img src="sources/<?php echo htmlspecialchars($detalhes_livro['imagem']); ?>" alt="Capa">
                    <?php else: ?>
                        <span style="font-size: 35px;">📚</span>
                    <?php endif; ?>
                </div>

                <div class="info-livro">
                    <h3><?php echo htmlspecialchars($detalhes_livro['titulo'] ?? $livro_titulo); ?></h3>
                    <p><strong>Autor:</strong> <?php echo htmlspecialchars($detalhes_livro['autor'] ?? 'Não informado'); ?></p>
                    <p><strong>Disponível:</strong> <?php echo isset($detalhes_livro['quantidade']) ? $detalhes_livro['quantidade'] : 0; ?> un.</p>
                    <div class="sinopse-box">
                        <strong>Sinopse:</strong> <?php echo htmlspecialchars($detalhes_livro['sinopse'] ?? ''); ?>
                    </div>
                </div>
            </div>

            <div class="aluno-box">
                <strong>Solicitante:</strong> <?php echo htmlspecialchars($aluno_nome); ?><br>
                <span style="font-size: 11px; opacity: 0.8;">
                    A reserva baixará 1 unidade do estoque e ficará com status <strong>PENDENTE</strong>.<br><br>
                    <strong>Limite:</strong> Máximo de <?php echo $limite_maximo; ?> reservas/empréstimos por aluno.
                </span>
            </div>

            <form method="POST">
                <input type="hidden" name="livro" value="<?php echo htmlspecialchars($livro_titulo); ?>">

                <label class="checkbox-container">
                    <input type="checkbox" id="checkTermos" name="aceito_termos" onchange="toggleBotaoReserva()">
                    <span>Li e estou ciente das regras de reserva.</span>
                </label>

                <div class="btn-container">
                    <a href="aluno.php" class="btn-cancelar">Cancelar</a>
                    <button type="submit" id="btnSubmit" class="btn-confirmar" disabled>Confirmar Reserva</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleBotaoReserva() {
            const checkbox = document.getElementById('checkTermos');
            const btnSubmit = document.getElementById('btnSubmit');
            btnSubmit.disabled = !checkbox.checked;
        }

        window.addEventListener('DOMContentLoaded', () => {
            const temaSalvo = localStorage.getItem('tema_aluno') || 'theme-estante';
            document.body.className = temaSalvo;
        });
    </script>
</body>
</html>
