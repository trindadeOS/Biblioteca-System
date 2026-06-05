<?php
session_start();

// 1. PROTEÇÃO DA PÁGINA: Se não for um aluno logado via Google, expulsa para o login
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'Aluno') {
    header("Location: login.html");
    exit();
}

// 2. CONEXÃO COM O BANCO (Voltando para a pasta raiz onde está o conexao.php)
require_once("../conexao.php");

// Resgatando as variáveis da sessão do aluno
$aluno_nome = $_SESSION['aluno_nome'];

// 🔍 CONSULTA 1: LISTAR OS EMPRÉSTIMOS DO ALUNO LOGADO (Tabela CLIENTES)
$stmt_emprestimos = $conn->prepare("SELECT LIVRO, DATA, TURMA, TURNO FROM CLIENTES WHERE NOME = ? ORDER BY DATA DESC");
$stmt_emprestimos->bind_param("s", $aluno_nome);
$stmt_emprestimos->execute();
$dados_emprestimos = $stmt_emprestimos->get_result();

// 📚 CONSULTA 2: LISTAR O ACERVO DE LIVROS DISPONÍVEIS (Tabela livros)
$query_livros = "SELECT * FROM livros ORDER BY titulo ASC";
$dados_livros = $conn->query($query_livros);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espaço do Aluno - Biblioteca CETEPES</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        /* Garante que as imagens reais dos livros fiquem perfeitas no mockup */
        .capa-mockup img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 16px;
            position: absolute;
            top: 0;
            left: 0;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-logo">CETEPES Digital</div>
        <div class="nav-links">
            <button class="nav-btn active" id="btn-catalogo" onclick="mudarAba('catalogo')">Explorar Acervo</button>
            <button class="nav-btn" id="btn-emprestimos" onclick="mudarAba('emprestimos')">Minhas Leituras</button>
            <a href="login.html" style="text-decoration: none;"><button class="nav-btn btn-sair">Sair</button></a>
        </div>
    </nav>

    <div class="container">

        <div class="welcome-banner">
            <h1>E aí, <?php echo explode(' ', $aluno_nome)[0]; ?>! 👋</h1>
            <p>Pronto para a sua próxima leitura? Pesquise livros disponíveis e acompanhe seus prazos.</p>
        </div>

        <section id="aba-catalogo" class="tab-section active">
            <div class="search-wrapper">
                <input type="text" id="busca-input" class="search-bar" placeholder="Digite o título ou autor..." onkeyup="filtrarAcervo()">
            </div>

            <div class="grid-livros">
                <?php 
                if ($dados_livros && $dados_livros->num_rows > 0):
                    $contador = 1;
                    while($livro = $dados_livros->fetch_assoc()): 
                        $classe_capa = "capa-" . ($contador % 3 + 1);
                        $contador++;
                        
                        $disponivel = (strtolower($livro['status']) == 'disponivel' || $livro['status'] == 1);
                ?>
                        <div class="card-livro">
                            <div class="capa-mockup <?php echo $classe_capa; ?>" style="position: relative;">
                                <span class="tag-categoria" style="z-index: 2;"><?php echo htmlspecialchars($livro['categoria'] ?? 'Geral'); ?></span>
                                
                                <?php if(!empty($livro['imagem'])): ?>
                                    <img src="sources/<?php echo $livro['imagem']; ?>" alt="Capa de <?php echo htmlspecialchars($livro['titulo']); ?>">
                                <?php else: ?>
                                    <span style="font-size: 32px;">📖</span>
                                <?php endif; ?>
                            </div>
                            
                            <h3 class="livro-titulo"><?php echo htmlspecialchars($livro['titulo']); ?></h3>
                            <p class="livro-author"><?php echo htmlspecialchars($livro['autor']); ?></p>
                            
                            <div class="livro-footer">
                                <?php if($disponivel): ?>
                                    <span class="badge-status status-livre">Disponível</span>
                                    <button class="btn-acao" onclick="pedirLivro('<?php echo addslashes($livro['titulo']); ?>')">Alugar</button>
                                <?php else: ?>
                                    <span class="badge-status status-ocupado">Alugado</span>
                                    <button class="btn-acao" disabled>Alugar</button>
                                <?php endif; ?>
                            </div>
                        </div>
                <?php 
                    endwhile; 
                else:
                ?>
                    <p style="color: var(--texto-mutado); grid-column: 1/-1; text-align: center; padding: 20px;">Nenhum livro disponível no catálogo no momento.</p>
                <?php endif; ?>
            </div>
        </section>

        <section id="aba-emprestimos" class="tab-section">
            <div class="lista-emprestimos">
                <?php 
                if ($dados_emprestimos && $dados_emprestimos->num_rows > 0):
                    while($emprestimo = $dados_emprestimos->fetch_assoc()): 
                        $data_retirada_formatada = date('d/m/Y', strtotime($emprestimo['DATA']));
                        
                        // Calcula o prazo limite (14 dias após a retirada)
                        $prazo_timestamp = strtotime($emprestimo['DATA'] . ' + 14 days');
                        $data_entrega_formatada = date('d/m/Y', $prazo_timestamp);
                        
                        // Cálculo regressivo de dias restantes
                        $dias_restantes = ceil(($prazo_timestamp - time()) / (60 * 60 * 24));
                ?>
                        <div class="card-emprestimo">
                            <div class="info-emprestimo">
                                <h3><?php echo htmlspecialchars($emprestimo['LIVRO']); ?></h3>
                                <p>Retirado da estante em: <?php echo $data_retirada_formatada; ?></p>
                                <small style="color: var(--texto-mutado); display: block; margin-top: 4px;">Turma: <?php echo htmlspecialchars($emprestimo['TURMA']); ?></small>
                            </div>
                            <div class="prazo-emprestimo">
                                <div class="prazo-data">Entrega: <?php echo $data_entrega_formatada; ?></div>
                                
                                <?php if ($dias_restantes > 0): ?>
                                    <div class="prazo-countdown">Restam <?php echo $dias_restantes; ?> dias</div>
                                <?php else: ?>
                                    <div class="prazo-countdown" style="background: #ffe2e2; color: #ef4444;">Prazo Excedido 🚨</div>
                                <?php endif; ?>
                            </div>
                        </div>
                <?php 
                    endwhile; 
                else:
                ?>
                    <div style="text-align: center; padding: 40px; background: white; border-radius: 24px; border: 1px solid var(--borda); width: 100%;">
                        <p style="color: var(--texto-mutado); font-weight: 500;">Você não possui nenhum livro alugado no momento.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </div>

    <script>
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

        function filtrarAcervo() {
            let busca = document.getElementById('busca-input').value.toLowerCase();
            let cards = document.querySelectorAll('.card-livro');

            cards.forEach(card => {
                let titulo = card.querySelector('.livro-titulo').innerText.toLowerCase();
                let autor = card.querySelector('.livro-author').innerText.toLowerCase();

                if(titulo.includes(busca) || autor.includes(busca)) {
                    card.style.display = "flex";
                } else {
                    card.style.display = "none";
                }
            });
        }

        function pedirLivro(nomeLivro) {
            window.location.href = "reserva.php?livro=" + encodeURIComponent(nomeLivro);
        }
    </script>
</body>
</html>