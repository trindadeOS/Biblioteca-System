<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("conexao.php");

// Se o formulário foi enviado via POST, processa o login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (empty($email) || empty($senha)) {
        echo "<script>alert('Por favor, preencha todos os campos!'); window.location.href='index.php';</script>";
        exit();
    }

    $sql = "SELECT * FROM usuarios WHERE EMAIL = ? AND SENHA = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erro no prepare: " . $conn->error);
    }

    $stmt->bind_param("ss", $email, $senha);

    if (!$stmt->execute()) {
        die("Erro na execução: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        // Definição das sessões
        $_SESSION['id']         = $usuario['ID'];
        $_SESSION['aluno_id']   = $usuario['ID'];
        $_SESSION['nome']       = $usuario['NOME'];
        $_SESSION['aluno_nome'] = $usuario['NOME'];
        $_SESSION['tipo']       = $usuario['TIPO'];

        // Redirecionamento pós-login seguro
        $tipo_usuario = strtoupper(trim($usuario['TIPO'] ?? ''));

        if ($tipo_usuario === 'ADMIN') {
            header("Location: admin/dashboard.php");
            exit();
        } elseif ($tipo_usuario === 'BIBLIOTECARIO' || $tipo_usuario === 'BIBLIOTECÁRIO') {
            header("Location: bibliotecario/dashboard.php");
            exit();
        } else {
            header("Location: aluno/aluno.php");
            exit();
        }

    } else {
        echo "<script>alert('E-mail ou senha incorretos!'); window.location.href='index.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca CETEPES - Login</title>
    
    <?php include("css.php"); ?>
    
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #1a120c radial-gradient(circle, #2d1e12 0%, #110b07 100%);
            padding: 20px;
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #f3f4f6;
        }

        .login-box {
            background: rgba(20, 15, 10, 0.9);
            border: 1px solid rgba(251, 191, 36, 0.2);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-box h1 {
            font-size: 24px;
            font-weight: 800;
            color: #fbbf24;
            margin-bottom: 8px;
        }

        .login-box p {
            color: #a1a1aa;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            border-color: #fbbf24;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #fbbf24;
            color: #1a120c;
            border: none;
            border-radius: 10px;
            font-weight: 800;
            font-size: 15px;
            cursor: pointer;
            transition: opacity 0.2s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

    <div class="login-box">
        <h1>Biblioteca CETEPES</h1>
        <p>Faça login para acessar a plataforma</p>

        <!-- O formulário envia os dados para ele mesmo (index.php) -->
        <form action="index.php" method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="E-mail do usuário" required autocomplete="email">
            </div>
            
            <div class="form-group">
                <input type="password" name="senha" placeholder="Sua senha" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-submit">Entrar no Sistema</button>
        </form>
    </div>

</body>
</html>