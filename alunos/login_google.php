<?php
session_start();


require_once("../conexao.php"); 



// Verifica se o Google enviou a credencial (Token JWT)
if (!isset($_POST['credential'])) {
    header("Location: login.html");
    exit();
}

$id_token = $_POST['credential'];

// Descodifica o token JWT enviado pelo Google para pegar os dados do aluno
$partes = explode('.', $id_token);
if (count($partes) === 3) {
    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $partes[1])), true);
    
    $email_aluno = isset($payload['email']) ? strtolower($payload['email']) : '';
    $nome_aluno = isset($payload['name']) ? $payload['name'] : '';

    //  VALIDAÇÃO OBRIGATÓRIA DO DOMÍNIO ENOVA BA
    $dominio_enova =  $_ENV['dominio']; 
    
    if (substr($email_aluno, -strlen($dominio_enova)) === $dominio_enova) {
        
        // 🔍 1. Verifica se o aluno já existe na tabela 'alunos'
        // Usando a variável $conn que veio lá do seu '../conexao.php'
        $stmt = $conn->prepare("SELECT * FROM alunos WHERE EMAIL = ?");
        $stmt->bind_param("s", $email_aluno);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            // O aluno já tem registro. Pega os dados para iniciar a sessão, SE o usuario estiver desativado, ocorre o erro.
            $aluno = $resultado->fetch_assoc();
            
            $_SESSION['aluno_id'] = $aluno['ID'];
            $_SESSION['aluno_nome'] = $aluno['NOME'];
            $_SESSION['aluno_email'] = $aluno['EMAIL'];
            $_SESSION['tipo_usuario'] = 'Aluno';
            $_SESSION['status_aluno'] = $aluno['STATUS'];
            if ($_SESSION['status_aluno'] == 'DESATIVADO') {
                die ('Essa conta foi desativada por um administrador.');
            }
            
            header("Location: aluno.php");
            exit();
        } else {
            // 📝 2. PRIMEIRO ACESSO: Regista o aluno automaticamente na tabela 'alunos'
            $status_inicial = "Ativo";
            $senha_fake = password_hash(uniqid(), PASSWORD_DEFAULT);
            
            $inserir = $conn->prepare("INSERT INTO alunos (NOME, EMAIL, SENHA, STATUS) VALUES (?, ?, ?, ?)");
            $inserir->bind_param("ssss", $nome_aluno, $email_aluno, $senha_fake, $status_inicial);
            
            if ($inserir->execute()) {
                $_SESSION['aluno_id'] = $inserir->insert_id;
                $_SESSION['aluno_nome'] = $nome_aluno;
                $_SESSION['aluno_email'] = $email_aluno;
                $_SESSION['tipo_usuario'] = 'Aluno';
                
                header("Location: aluno.php");
                exit();
            } else {
                echo "Erro ao registar o aluno no banco de dados.";
                exit();
            }
        }

    } else {
        echo "<script>
                alert('Acesso Negado! Este sistema é exclusivo para alunos da rede estadual. Use a sua conta enova para fazer login.');
                window.location.href = 'login.html';
              </script>";
        exit();
    }
} else {
    header("Location: login.html");
    exit();
}
?>