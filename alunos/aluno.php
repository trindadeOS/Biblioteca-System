<?php
session_start();

$caminho_conexao = __DIR__ . "/../conexao.php";
if (file_exists($caminho_conexao)) {
    require_once($caminho_conexao);
} else {
    die("Erro: O arquivo conexao.php não foi encontrado na raiz.");
}

$aluno_id   = $_SESSION['aluno_id'];
$aluno_nome = $_SESSION['aluno_nome'] ?? 'Aluno';



// Validação de acesso restrito
if (!isset($_SESSION['aluno_id']) || empty($_SESSION['aluno_id'])) {
    header("Location: login.php");
    exit();
}

// Verifica se o aluno está logado
if (!isset($_SESSION['aluno_id']) || empty($_SESSION['aluno_id'])) {
    header("Location: login.php");
    exit();
}

// Verifica se o perfil está completo
$id_aluno = $_SESSION['aluno_id'];
$check_perfil = $conn->query("SELECT perfil_completo FROM alunos WHERE ID = $id_aluno");
if ($check_perfil && $dados_aluno = $check_perfil->fetch_assoc()) {
    // Se não completou e a página atual não for a de completar perfil, redireciona
    if (isset($dados_aluno['perfil_completo']) && $dados_aluno['perfil_completo'] == 0) {
        header("Location: completar_perfil.php");
        exit();
    }
}



// Captura mensagens de feedback da URL (?status=sucesso)
$mensagem_feedback = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'cancelado') {
        $mensagem_feedback = 'Solicitação cancelada com sucesso!';
    } elseif ($_GET['status'] === 'reservado') {
        $mensagem_feedback = 'Reserva solicitada com sucesso! Você tem 3 dias para buscar o livro na biblioteca física.';
    }
}

// Consulta avisos do mural divididos por prioridade
$res_avisos_altos = $conn->query("SELECT * FROM avisos WHERE LOWER(prioridade) = 'alta' ORDER BY data_criacao DESC");
$res_avisos_normais = $conn->query("SELECT * FROM avisos WHERE LOWER(prioridade) != 'alta' OR prioridade IS NULL ORDER BY data_criacao DESC LIMIT 10");

// Conta quantas notificações normais existem para exibir no badge da navbar
$total_notificacoes = ($res_avisos_normais) ? $res_avisos_normais->num_rows : 0;

// Consulta solicitações de empréstimos do aluno
$stmt_emprestimos = $conn->prepare("SELECT ID, LIVRO, STATUS, DATA FROM emprestimos WHERE aluno_id = ? ORDER BY DATA DESC");
$stmt_emprestimos->bind_param("i", $aluno_id);
$stmt_emprestimos->execute();
$dados_emprestimos = $stmt_emprestimos->get_result();

// Métricas do Dashboard
$total_solicitacoes = 0;
$total_pendentes = 0;
$total_concluidos = 0;

$emprestimos_array = [];
if ($dados_emprestimos && $dados_emprestimos->num_rows > 0) {
    while($row = $dados_emprestimos->fetch_assoc()) {
        $emprestimos_array[] = $row;
        $total_solicitacoes++;
        $st = strtoupper($row['STATUS']);
        if ($st === 'PENDENTE') $total_pendentes++;
        if ($st === 'CONCLUIDO' || $st === 'CONCLUÍDO') $total_concluidos++;
    }
}

// Consulta livros buscando a sinopse real cadastrada no banco
$dados_livros = $conn->query("SELECT id, titulo, autor, categoria, status, imagem, quantidade, COALESCE(sinopse, 'Nenhuma sinopse cadastrada para este livro.') AS sinopse FROM livros ORDER BY titulo ASC");

$categorias_unicas = [];
$livros_array = [];
if ($dados_livros && $dados_livros->num_rows > 0) {
    while($livro = $dados_livros->fetch_assoc()) {
        $livros_array[] = $livro;
        if (!empty($livro['categoria']) && !in_array($livro['categoria'], $categorias_unicas)) {
            $categorias_unicas[] = $livro['categoria'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espaço do Aluno - CETEPES Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body.theme-estante {
            --bg-body: #1a120c radial-gradient(circle, #2d1e12 0%, #110b07 100%);
            --text-color: #FFFFFF;
            --nav-bg: #28160c;
            --nav-border: #3d2314;
            --brand-color: #fbbf24;
            --card-bg: rgba(20, 15, 10, 0.9);
            --card-border: rgba(251, 191, 36, 0.2);
            --banner-bg: rgba(45, 30, 18, 0.7);
            --banner-border: rgba(217, 119, 6, 0.3);
            --input-bg: rgba(20, 15, 10, 0.9);
            --select-bg: #3d2314;
            --select-text: #fbbf24;
            --modal-bg: #28160c;
            --theme-pill-bg: rgba(0, 0, 0, 0.3);
            --theme-btn-active: #fbbf24;
            --theme-btn-active-text: #1a120c;
        }

        body.theme-dark {
            --bg-body: #0f172a;
            --text-color: #FFFFFF;
            --nav-bg: #1e293b;
            --nav-border: #334155;
            --brand-color: #38bdf8;
            --card-bg: #1e293b;
            --card-border: #334155;
            --banner-bg: #1e293b;
            --banner-border: #334155;
            --input-bg: #1e293b;
            --select-bg: #334155;
            --select-text: #38bdf8;
            --modal-bg: #1e293b;
            --theme-pill-bg: rgba(15, 23, 42, 0.6);
            --theme-btn-active: #38bdf8;
            --theme-btn-active-text: #0f172a;
        }

        body.theme-light {
            --bg-body: #f8fafc;
            --text-color: #000000;
            --nav-bg: #ffffff;
            --nav-border: #e2e8f0;
            --brand-color: #2563eb;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --banner-bg: #ffffff;
            --banner-border: #e2e8f0;
            --input-bg: #ffffff;
            --select-bg: #f1f5f9;
            --select-text: #2563eb;
            --modal-bg: #ffffff;
            --theme-pill-bg: #e2e8f0;
            --theme-btn-active: #2563eb;
            --theme-btn-active-text: #ffffff;
        }

        body {
            background: var(--bg-body) !important;
            color: var(--text-color);
            transition: all 0.3s ease;
            min-height: 100vh;
            margin: 0;
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--nav-bg) !important;
            border-bottom: 1px solid var(--nav-border);
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        }

        .nav-logo { color: var(--brand-color) !important; font-weight: 800; }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .dash-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            padding: 16px;
            border-radius: 12px;
            text-align: center;
        }

        .dash-card h4 { margin: 0; font-size: 12px; opacity: 0.8; text-transform: uppercase; }
        .dash-card .number { font-size: 24px; font-weight: 800; color: var(--brand-color); margin-top: 5px; }

        .alert-feedback {
            background: #10b981;
            color: white;
            padding: 14px 22px;
            border-radius: 50px;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
            transition: opacity 0.4s ease, transform 0.4s ease;
        }

        .alert-feedback.esconder {
            opacity: 0;
            transform: translateY(-10px);
        }

        .alerta-maxima-banner {
            background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%);
            border: 2px solid #f87171;
            color: #fff;
            padding: 16px 20px;
            border-radius: 14px;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4);
            animation: pulse-border 2s infinite;
        }

        @keyframes pulse-border {
            0% { border-color: rgba(248, 113, 113, 0.6); }
            50% { border-color: rgba(248, 113, 113, 1); box-shadow: 0 10px 30px rgba(220, 38, 38, 0.7); }
            100% { border-color: rgba(248, 113, 113, 0.6); }
        }

        .alerta-maxima-header {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #fecaca;
        }

        .btn-sino {
            background: var(--select-bg);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            font-size: 18px;
            transition: all 0.2s;
        }

        .btn-sino:hover {
            background: var(--brand-color);
            color: #000;
        }

        .badge-sino {
            position: absolute;
            top: -3px;
            right: -3px;
            background: #ef4444;
            color: white;
            font-size: 10px;
            font-weight: 800;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--nav-bg);
        }

        .category-chips {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .chip {
            background: var(--select-bg);
            color: var(--text-color);
            border: 1px solid var(--card-border);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .chip.active, .chip:hover {
            background: var(--brand-color);
            color: #000;
        }

        body.theme-estante .estante-container {
            background: #3d2314;
            border: 10px solid #28160c;
            border-radius: 12px;
            padding: 20px;
            box-shadow: inset 0 0 40px rgba(0, 0, 0, 0.9);
        }

        body.theme-estante .card-livro::after {
            content: "";
            position: absolute;
            bottom: -22px; left: -15px; right: -15px; height: 16px;
            background: linear-gradient(180deg, #5c341d 0%, #2b170c 100%);
            border-top: 2px solid #8d502d;
            border-bottom: 2px solid #150b05;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.7);
            border-radius: 3px;
        }

        .grid-livros { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 35px 20px; padding: 10px; }
        .card-livro, .card-emprestimo {
            background: var(--card-bg) !important;
            border: 1px solid var(--card-border) !important;
            color: var(--text-color);
            border-radius: 12px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            cursor: pointer;
        }

        .capa-mockup { width: 100%; height: 230px; background: #000; border-radius: 8px; overflow: hidden; position: relative; display: flex; align-items: center; justify-content: center; }
        .capa-mockup img { width: 100%; height: 100%; object-fit: cover; }
        .livro-titulo { font-size: 15px; font-weight: 700; margin: 10px 0 4px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-color);}
        .tag-categoria { position: absolute; top: 8px; left: 8px; background: rgba(0, 0, 0, 0.75); color: #fff; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: bold; }
        .search-bar { background: var(--input-bg) !important; border: 1px solid var(--card-border) !important; color: var(--text-color) !important; }

        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.75);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999;
            backdrop-filter: blur(4px);
        }

 .modal-card {
    background: var(--card-bg, #1e293b);
    border: 1px solid var(--card-border, #334155);
    border-radius: 16px;
    padding: 24px;
    width: 90%;
    max-width: 450px;
    max-height: 85vh; /* Garante que o modal não passe da altura da tela */
    overflow-y: auto;  /* Adiciona barra de rolagem se o conteúdo for muito grande */
    box-sizing: border-box;
    position: relative;
    color: var(--text-color, #ffffff);
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

/* Caixa específica da sinopse dentro do modal */
#modal-sinopse {
    max-height: 120px;    /* Limita o tamanho máximo da sinopse */
    overflow-y: auto;     /* Permite rolar apenas dentro da sinopse se precisar */
    padding-right: 5px;
    font-size: 13px;
    opacity: 0.9;
    line-height: 1.5;
    margin: 0;
}

        .modal-close {
            position: absolute;
            top: 15px; right: 15px;
            background: none; border: none;
            color: var(--text-color);
            font-size: 20px; cursor: pointer;
        }

        .theme-pill-container {
            display: inline-flex;
            background: var(--theme-pill-bg);
            padding: 3px;
            border-radius: 20px;
            border: 1px solid var(--card-border);
            gap: 2px;
        }

        .theme-pill-btn {
            background: transparent;
            border: none;
            color: var(--text-color);
            padding: 5px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            opacity: 0.6;
            transition: all 0.2s ease;
        }

        .theme-pill-btn:hover {
            opacity: 1;
        }

        .theme-pill-btn.active {
            background: var(--theme-btn-active);
            color: var(--theme-btn-active-text);
            opacity: 1;
            font-weight: 800;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .notificacao-item {
            background: rgba(0, 0, 0, 0.15);
            border-left: 4px solid var(--brand-color);
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .notificacao-item:last-child {
            margin-bottom: 0;
        }
        
        /* RESPONSIVIDADE PARA CELULARES E TABLETS */
@media (max-width: 768px) {
    .navbar {
        flex-direction: column;
        gap: 12px;
        padding: 12px 15px;
        text-align: center;
    }

    .nav-links {
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px !important;
    }

    .dashboard-grid {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .grid-livros {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 20px 10px;
    }

    .card-emprestimo {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 15px;
    }

    .prazo-emprestimo {
        width: 100%;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .modal-card {
        width: 95%;
        padding: 16px;
        margin: 10px;
    }
}
        
    </style>
</head>
<body class="theme-estante">

    <nav class="navbar">
        <div class="nav-logo">CETEPES Digital</div>
        
        <div class="nav-links" style="display: flex; align-items: center; gap: 12px;">
            <!-- BOTÃO DO SINO DE NOTIFICAÇÕES -->
            <button type="button" class="btn-sino" onclick="abrirModalNotificacoes()" title="Ver Notificações">
                🔔
                <?php if ($total_notificacoes > 0): ?>
                    <span id="badge-notificacoes" class="badge-sino"><?php echo $total_notificacoes; ?></span>
                <?php endif; ?>
            </button>

            <div class="theme-pill-container">
                <button type="button" class="theme-pill-btn" data-theme="theme-estante" onclick="alterarTema('theme-estante')">Estante</button>
                <button type="button" class="theme-pill-btn" data-theme="theme-dark" onclick="alterarTema('theme-dark')">Escuro</button>
                <button type="button" class="theme-pill-btn" data-theme="theme-light" onclick="alterarTema('theme-light')">Claro</button>
            </div>

            <button class="nav-btn active" id="btn-catalogo" onclick="mudarAba('catalogo')">Explorar Acervo</button>
            <button class="nav-btn" id="btn-emprestimos" onclick="mudarAba('emprestimos')">Minhas Solicitações</button>
            <a href="logout.php" style="text-decoration: none;"><button class="nav-btn btn-sair">Sair</button></a>
        </div>
    </nav>

    <div class="container" style="padding-top: 20px;">

        <!-- MENSAGEM COM SCRIPT DE REMOÇÃO AUTOMÁTICA -->
        <?php if (!empty($mensagem_feedback)): ?>
            <div id="mensagem-alerta" class="alert-feedback">
                <span> <?php echo $mensagem_feedback; ?></span>
                <button onclick="this.parentElement.remove()" style="background:none; border:none; color:white; cursor:pointer; font-weight:bold;">✕</button>
            </div>

            <script>
                setTimeout(() => {
                    const alerta = document.getElementById('mensagem-alerta');
                    if (alerta) {
                        alerta.classList.add('esconder');
                        setTimeout(() => {
                            alerta.remove();
                            window.history.replaceState({}, document.title, window.location.pathname);
                        }, 400);
                    }
                }, 3500);
            </script>
        <?php endif; ?>

        <!-- BANNER DE PRIORIDADE MÁXIMA (DESTAQUE ABSOLUTO NO TOPO) -->
        <?php if ($res_avisos_altos && $res_avisos_altos->num_rows > 0): ?>
            <?php while ($aviso_alto = $res_avisos_altos->fetch_assoc()): ?>
                <div class="alerta-maxima-banner">
                    <div class="alerta-maxima-header">
                        <span>🚨</span>
                        <span><?php echo htmlspecialchars($aviso_alto['titulo']); ?></span>
                    </div>
                    <p style="margin: 0 0 8px 0; font-size: 14px; line-height: 1.5; color: #fee2e2;">
                        <?php echo nl2br(htmlspecialchars($aviso_alto['mensagem'])); ?>
                    </p>
                    <span style="font-size: 11px; opacity: 0.8; display: block;">
                        Emitido em: <?php echo date('d/m/Y H:i', strtotime($aviso_alto['data_criacao'])); ?>
                    </span>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>

        <div class="dashboard-grid">
            <div class="dash-card">
                <h4>Total Solicitado</h4>
                <div class="number"><?php echo $total_solicitacoes; ?></div>
            </div>
            <div class="dash-card">
                <h4>Pendentes</h4>
                <div class="number" style="color: #fbbf24;"><?php echo $total_pendentes; ?></div>
            </div>
            <div class="dash-card">
                <h4>Concluídos</h4>
                <div class="number" style="color: #10b981;"><?php echo $total_concluidos; ?></div>
            </div>
        </div>

        <section id="aba-catalogo" class="tab-section active">
            <div class="search-wrapper" style="margin-bottom: 12px;">
                <input type="text" id="busca-input" class="search-bar" placeholder="Digite o título ou autor..." onkeyup="filtrarAcervo()">
            </div>

            <div class="category-chips">
                <button class="chip active" onclick="filtrarCategoria('TODAS', this)">Todas</button>
                <?php foreach($categorias_unicas as $cat): ?>
                    <button class="chip" onclick="filtrarCategoria('<?php echo htmlspecialchars($cat); ?>', this)"><?php echo htmlspecialchars($cat); ?></button>
                <?php endforeach; ?>
            </div>

            <div class="estante-container">
                <div class="grid-livros">
                    <?php if (!empty($livros_array)): ?>
                        <?php foreach($livros_array as $livro): 
                            $disponivel = (strtolower($livro['status']) === 'disponivel' && $livro['quantidade'] > 0);
                        ?>
<div class="card-livro" 
     data-categoria="<?php echo htmlspecialchars($livro['categoria'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
     data-titulo="<?php echo htmlspecialchars($livro['titulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
     data-autor="<?php echo htmlspecialchars($livro['autor'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
     data-quantidade="<?php echo htmlspecialchars($livro['quantidade'] ?? 0, ENT_QUOTES, 'UTF-8'); ?>"
     data-status="<?php echo htmlspecialchars($livro['status'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
     data-sinopse="<?php echo htmlspecialchars($livro['sinopse'] ?? 'Sem sinopse disponível.', ENT_QUOTES, 'UTF-8'); ?>"
     onclick="abrirModalPorCard(this)">
    
    <div class="capa-mockup">
                                    <span class="tag-categoria"><?php echo htmlspecialchars($livro['categoria'] ?? 'Geral'); ?></span>
                                    
                                    <?php if(!empty($livro['imagem'])): ?>
                                        <img src="sources/<?php echo htmlspecialchars($livro['imagem']); ?>" alt="Capa">
                                    <?php else: ?>
                                        <span style="font-size: 40px;">📚</span>
                                    <?php endif; ?>
                                </div>
                                
                                <h3 class="livro-titulo" title="<?php echo htmlspecialchars($livro['titulo']); ?>"><?php echo htmlspecialchars($livro['titulo']); ?></h3>
                                <p class="livro-author" style="font-size: 13px; opacity: 0.8; margin-bottom: 10px;"><?php echo htmlspecialchars($livro['autor']); ?></p>
                                
                                <div class="livro-footer">
                                    <?php if($disponivel): ?>
                                        <button class="btn-acao" style="width: 100%;" onclick="event.stopPropagation(); pedirLivro('<?php echo addslashes($livro['titulo']); ?>')">Reservar</button>
                                    <?php else: ?>
                                        <button class="btn-acao" style="width: 100%; background: #4b5563;" disabled>Indisponível</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="opacity: 0.8; grid-column: 1/-1; text-align: center; padding: 20px;">Nenhum livro cadastrado no acervo.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section id="aba-emprestimos" class="tab-section">
            <div class="lista-emprestimos">
                <?php if (!empty($emprestimos_array)): ?>
                    <?php foreach($emprestimos_array as $emp): 
                        $data_formatada = date('d/m/Y H:i', strtotime($emp['DATA']));
                        $status_raw = $emp['STATUS'];
                        $status_upper = strtoupper($status_raw);

                        $badge_style = "background: #374151; color: #f3f4f6;"; 
                        if ($status_upper === 'PENDENTE') {
                            $badge_style = "background: #78350f; color: #fef3c7;";
                        } elseif ($status_upper === 'APROVADO' || $status_upper === 'EMPRESTADO') {
                            $badge_style = "background: #1e3a8a; color: #dbeafe;";
                        } elseif ($status_upper === 'CONCLUIDO' || $status_upper === 'CONCLUÍDO') {
                            $badge_style = "background: #14532d; color: #dcfce7;";
                        } elseif ($status_upper === 'CANCELADO') {
                            $badge_style = "background: #7f1d1d; color: #fee2e2;";
                        }
                    ?>
                        <div class="card-emprestimo" style="padding: 20px; margin-bottom: 12px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; cursor: default;">
                            <div class="info-emprestimo">
                                <h3 style="margin: 0 0 5px 0;"><?php echo htmlspecialchars($emp['LIVRO']); ?></h3>
                                <p style="font-size: 14px; margin: 0; opacity: 0.8;">Solicitado em: <?php echo $data_formatada; ?></p>
                            </div>
                            
                            <div class="prazo-emprestimo" style="display: flex; align-items: center; gap: 12px;">
                                <div>
                                    <strong>Status: </strong>
                                    <span class="badge-status" style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; <?= $badge_style ?>">
                                        <?php echo htmlspecialchars($status_raw); ?>
                                    </span>
                                </div>

                                <?php if ($status_upper === 'PENDENTE'): ?>
                                    <a href="cancelar_reserva.php?id=<?php echo $emp['ID']; ?>" 
                                       onclick="return confirm('Tem certeza que deseja cancelar esta solicitação?');" 
                                       style="padding: 6px 12px; background: #dc2626; color: white; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold;">
                                        Cancelar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; background: rgba(0,0,0,0.1); border-radius: 16px; border: 1px solid rgba(255,255,255,0.1); width: 100%;">
                        <p style="opacity: 0.8; font-weight: 500;">Você não possui solicitações de empréstimo ativas no momento.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </div>

    <!-- MODAL DE DETALHES DO LIVRO -->
    <div id="modal-livro" class="modal-overlay" onclick="fecharModal()">
        <div class="modal-card" onclick="event.stopPropagation()">
            <button class="modal-close" onclick="fecharModal()">✕</button>
            <h2 id="modal-titulo" style="margin-top: 0; color: var(--brand-color);"></h2>
            <p id="modal-autor" style="opacity: 0.8; font-size: 14px; margin-bottom: 15px;"></p>
            <div style="margin-bottom: 15px;">
                <strong>Categoria:</strong> <span id="modal-categoria"></span> | 
                <strong>Estoque:</strong> <span id="modal-quantidade"></span>
            </div>
            <div style="background: rgba(0,0,0,0.2); padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <strong style="display: block; margin-bottom: 5px;">Sinopse:</strong>
                <p id="modal-sinopse" style="font-size: 13px; opacity: 0.9; margin: 0; line-height: 1.5;"></p>
            </div>
            <div id="modal-footer"></div>
        </div>
    </div>

    <!-- MODAL DE NOTIFICAÇÕES (SINO DA NAVBAR) -->
    <div id="modal-notificacoes" class="modal-overlay" onclick="fecharModalNotificacoes()">
        <div class="modal-card" onclick="event.stopPropagation()" style="max-height: 80vh; overflow-y: auto;">
            <button class="modal-close" onclick="fecharModalNotificacoes()">✕</button>
            <h2 style="margin-top: 0; color: var(--brand-color); display: flex; align-items: center; gap: 8px; font-size: 18px;">
                <span>🔔</span> Notificações Recentes
            </h2>
            <div style="margin-top: 15px;">
                <?php if ($res_avisos_normais && $res_avisos_normais->num_rows > 0): ?>
                    <?php while ($aviso_n = $res_avisos_normais->fetch_assoc()): ?>
                        <div class="notificacao-item">
                            <h4 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 700;"><?php echo htmlspecialchars($aviso_n['titulo']); ?></h4>
                            <p style="margin: 0; font-size: 13px; opacity: 0.9; line-height: 1.4;"><?php echo nl2br(htmlspecialchars($aviso_n['mensagem'])); ?></p>
                            <span style="font-size: 10px; opacity: 0.6; display: block; margin-top: 6px;">
                                Recebido em: <?php echo date('d/m/Y H:i', strtotime($aviso_n['data_criacao'])); ?>
                            </span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; opacity: 0.7; padding: 20px 0;">Nenhuma notificação nova no momento.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        let categoriaAtual = 'TODAS';

        function alterarTema(nomeTema) {
            document.body.className = nomeTema;
            localStorage.setItem('tema_aluno', nomeTema);
            
            document.querySelectorAll('.theme-pill-btn').forEach(btn => {
                if(btn.getAttribute('data-theme') === nomeTema) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }

        window.addEventListener('DOMContentLoaded', () => {
            const temaSalvo = localStorage.getItem('tema_aluno') || 'theme-estante';
            alterarTema(temaSalvo);
        });

        function mudarAba(nomeAba) {
            document.querySelectorAll('.tab-section').forEach(sec => sec.classList.remove('active'));
            document.querySelectorAll('.nav-links .nav-btn').forEach(btn => btn.classList.remove('active'));

            if (nomeAba === 'catalogo') {
                document.getElementById('aba-catalogo').classList.add('active');
                document.getElementById('btn-catalogo').classList.add('active');
            } else {
                document.getElementById('aba-emprestimos').classList.add('active');
                document.getElementById('btn-emprestimos').classList.add('active');
            }
        }

        function filtrarCategoria(cat, btn) {
            categoriaAtual = cat;
            document.querySelectorAll('.category-chips .chip').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            filtrarAcervo();
        }

        function filtrarAcervo() {
            let busca = document.getElementById('busca-input').value.toLowerCase();
            let cards = document.querySelectorAll('.card-livro');

            cards.forEach(card => {
                let titulo = card.querySelector('.livro-titulo').innerText.toLowerCase();
                let autor = card.querySelector('.livro-author').innerText.toLowerCase();
                let categoria = card.getAttribute('data-categoria');

                let atendeBusca = titulo.includes(busca) || autor.includes(busca);
                let atendeCategoria = (categoriaAtual === 'TODAS' || categoria === categoriaAtual);

                if(atendeBusca && atendeCategoria) {
                    card.style.display = "flex";
                } else {
                    card.style.display = "none";
                }
            });
        }

        function pedirLivro(nomeLivro) {
            window.location.href = "reserva.php?livro=" + encodeURIComponent(nomeLivro);
        }
        
        function abrirModalPorCard(element) {
            // Cria o objeto livro pegando direto dos atributos data-* do elemento HTML
            const livro = {
                titulo: element.getAttribute('data-titulo'),
                autor: element.getAttribute('data-autor'),
                categoria: element.getAttribute('data-categoria'),
                quantidade: parseInt(element.getAttribute('data-quantidade')) || 0,
                status: element.getAttribute('data-status'),
                sinopse: element.getAttribute('data-sinopse')
            };

            abrirModal(livro);
        }

function abrirModal(livro) {
    console.log("Dados do livro recebidos:", livro); // Olhe o F12 para ver se aparece aqui

    if (!livro) {
        alert("Erro: Dados do livro não encontrados.");
        return;
    }

    document.getElementById('modal-titulo').innerText = livro.titulo || '';
    document.getElementById('modal-autor').innerText = "Autor: " + (livro.autor || 'Não informado');
    document.getElementById('modal-categoria').innerText = livro.categoria || 'Geral';
    document.getElementById('modal-quantidade').innerText = (livro.quantidade !== undefined ? livro.quantidade : 0) + " unidades";
    document.getElementById('modal-sinopse').innerText = livro.sinopse || 'Sem sinopse disponível.';

    const disponivel = (String(livro.status).toLowerCase() === 'disponivel' && Number(livro.quantidade) > 0);
    const footer = document.getElementById('modal-footer');

    if(disponivel) {
        footer.innerHTML = `<button class="btn-acao" style="width: 100%; padding: 10px;" onclick="pedirLivro('${String(livro.titulo).replace(/'/g, "\\'")}')">Reservar Livro Agora</button>`;
    } else {
        footer.innerHTML = `<button class="btn-acao" style="width: 100%; background: #4b5563; padding: 10px;" disabled>Exemplar Indisponível</button>`;
    }

    document.getElementById('modal-livro').style.display = 'flex';
}

        function fecharModal() {
            document.getElementById('modal-livro').style.display = 'none';
        }

        function abrirModalNotificacoes() {
            // Remove o badge (o "1zinho") ao abrir as notificações
            const badge = document.getElementById('badge-notificacoes');
            if (badge) {
                badge.remove();
            }
            
            document.getElementById('modal-notificacoes').style.display = 'flex';
        }

        function fecharModalNotificacoes() {
            document.getElementById('modal-notificacoes').style.display = 'none';
        }
        
        function pedirLivro(tituloLivro) {
            window.location.href = 'reserva.php?livro=' + encodeURIComponent(tituloLivro);
        }
        
    </script>
</body>
</html>