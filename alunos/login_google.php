<?php
session_start();

$caminho_conexao = __DIR__ . "/../conexao.php";
if (file_exists($caminho_conexao)) {
    require_once($caminho_conexao);
} else {
    die("Erro: O arquivo conexao.php não foi encontrado na raiz.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['credential'])) {
    $id_token = $_POST['credential'];
    
    // Consulta token via endpoint do Google
    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;
    $response = @file_get_contents($url);
    $data = json_decode($response, true);

    if (isset($data['email'])) {
        $email = $data['email'];
        $nome  = $data['name'] ?? 'Aluno';

        // Verifica existência do e-mail no MySQL
        $stmt = $conn->prepare("SELECT ID, NOME, EMAIL, STATUS FROM alunos WHERE EMAIL = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($aluno = $res->fetch_assoc()) {
            if (strtoupper($aluno['STATUS']) !== 'ATIVO') {
                $_SESSION['erro_login'] = "Sua conta está inativa!";
                header("Location: login.php");
                exit();
            }
            $aluno_id = $aluno['ID'];
            $aluno_nome = $aluno['NOME'];
        } else {
            // Cria usuário caso seja o primeiro acesso via Google
            $stmt_ins = $conn->prepare("INSERT INTO alunos (NOME, EMAIL, SENHA, STATUS) VALUES (?, ?, '', 'ATIVO')");
            $stmt_ins->bind_param("ss", $nome, $email);
            $stmt_ins->execute();
            $aluno_id = $stmt_ins->insert_id;
            $aluno_nome = $nome;
        }

        $_SESSION['aluno_id']     = $aluno_id;
        $_SESSION['aluno_nome']   = $aluno_nome;
        $_SESSION['aluno_email']  = $email;
        $_SESSION['tipo_usuario'] = 'Aluno';

        header("Location: aluno.php");
        exit();
    }
}

$_SESSION['erro_login'] = "Falha ao autenticar com o Google.";
header("Location: login.php");
exit();
?>