<?php
session_start();

// Conexão segura utilizando caminho absoluto
$caminho_conexao = __DIR__ . "/../conexao.php";
if (file_exists($caminho_conexao)) {
    require_once($caminho_conexao);
} else {
    die("Erro: O arquivo conexao.php não foi encontrado na raiz.");
}

// Redireciona para a dashboard apenas se o aluno já estiver logado
if (isset($_SESSION['aluno_id']) && !empty($_SESSION['aluno_id'])) {
    header('Location: aluno.php');
    exit();
}

// Processa o envio do formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = trim($_POST['senha'] ?? '');

    if (!empty($usuario) && !empty($senha)) {
        // Alterado para trazer também o campo 'perfil_completo'
        $stmt = $conn->prepare("SELECT ID, NOME, EMAIL, SENHA, STATUS, perfil_completo FROM alunos WHERE EMAIL = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($aluno = $result->fetch_assoc()) {
                if (strtoupper($aluno['STATUS']) !== 'ATIVO') {
                    $_SESSION['erro_login'] = "Sua conta está inativa!";
                } else {
                    // Validação de senha por texto simples ou hash
                    $senha_correta = password_verify($senha, $aluno['SENHA']) || ($senha === $aluno['SENHA']);

                    if ($senha_correta) {
                        $_SESSION['aluno_id']      = $aluno['ID'];
                        $_SESSION['aluno_nome']    = $aluno['NOME'];
                        $_SESSION['aluno_email']   = $aluno['EMAIL'];
                        $_SESSION['tipo_usuario'] = 'Aluno';

                        // VERIFICAÇÃO DO PERFIL COMPLETO:
                        // Se o campo perfil_completo for 0 ou nulo, manda preencher primeiro
                        if (!isset($aluno['perfil_completo']) || $aluno['perfil_completo'] == 0) {
                            header("Location: completar_perfil.php");
                            exit();
                        }

                        header("Location: aluno.php");
                        exit();
                    } else {
                        $_SESSION['erro_login'] = "Senha incorreta!";
                    }
                }
            } else {
                $_SESSION['erro_login'] = "E-mail não cadastrado!";
            }
        } else {
            $_SESSION['erro_login'] = "Erro na conexão com a base de dados!";
        }
    } else {
        $_SESSION['erro_login'] = "Por favor, preencha todos os campos!";
    }

    header("Location: login.php");
    exit();
}

$mensagem_erro = $_SESSION['erro_login'] ?? '';
unset($_SESSION['erro_login']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login do Aluno - CETEPES Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <h1>Área do Aluno</h1>
            <p>Logue com seu email institucional para acessar nosso acervo digital.</p>
        </div>

        <div class="error-message" id="error-box" style="<?= !empty($mensagem_erro) ? 'display: block;' : 'display: none;' ?>">
            <?= htmlspecialchars($mensagem_erro) ?>
        </div>

        <form id="loginForm" action="login.php" method="POST">
            <div class="form-group">
                <label for="usuario">E-mail</label>
                <input type="email" id="usuario" name="usuario" placeholder="exemplo@escola.com" required>
            </div>

            <div class="form-group">
                <label for="senha">Palavra-passe</label>
                <input type="password" id="senha" name="senha" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-estudante">Acessar Espaço</button>

            <div class="google-login-area" style="margin-top: 25px; padding-top: 20px; border-top: 2px solid var(--borda, #eee);">
                <p style="font-size: 13px; color: #666; margin-bottom: 15px; font-weight: 600;">Ou acesse com a sua conta Google</p> 
                
                <div id="g_id_onload"
                     data-client_id="115550235490-qp1sunedndr3que32fingf3pkn89h2mv.apps.googleusercontent.com"
                     data-context="signin"
                     data-ux_mode="redirect"
                     data-login_uri="https://bibliotecacetepes.free.nf/alunos/login_google.php"
                     data-auto_prompt="false">
                </div>

                <div style="display: flex; justify-content: center;">
                    <div class="g_id_signin" data-type="standard" data-shape="pill" data-theme="outline" data-size="large"></div>
                </div>
            </div>
        </form>
    </div>

</body>
</html>