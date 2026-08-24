<?php
session_start();
require_once("../conexao.php");

// CORREÇÃO: Aceita qualquer uma das duas variáveis de sessão que os arquivos de login usam
$tipo_atual = $_SESSION['tipo'] ?? $_SESSION['tipo_usuario'] ?? '';

// CORREÇÃO: Pega o ID corretamente da sessão correta (aluno_id)
$id_aluno = $_SESSION['aluno_id'] ?? $_SESSION['id'] ?? null;

if (empty($id_aluno) || $tipo_atual !== 'Aluno') {
    header("Location: ../index.php");
    exit();
}

// Se o formulário foi enviado, atualiza os dados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $turma = trim($_POST['turma'] ?? '');
    $turno = trim($_POST['turno'] ?? '');
    $curso = trim($_POST['curso'] ?? '');

    // Corrigido para atualizar usando a variável correta $id_aluno e marcando perfil_completo = 1
    $stmt = $conn->prepare("UPDATE alunos SET NOME = ?, TURMA = ?, TURNO = ?, CURSO = ?, perfil_completo = 1 WHERE ID = ?");
    
    if ($stmt) {
        $stmt->bind_param("ssssi", $nome, $turma, $turno, $curso, $id_aluno);
        
        if ($stmt->execute()) {
            $_SESSION['aluno_nome'] = $nome; // Atualiza o nome na sessão do aluno
            header("Location: aluno.php"); // Redireciona para o painel correto
            exit();
        } else {
            $erro = "Erro ao atualizar os dados no banco: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $erro = "Erro na preparação da consulta: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar Perfil - CETEPES Digital</title>
    <?php include("../css.php"); ?>
    <style>
        body { 
            background: #1a120c; 
            color: #f3f4f6; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .box { 
            background: rgba(20, 15, 10, 0.9); 
            border: 1px solid rgba(251, 191, 36, 0.2); 
            padding: 30px; 
            border-radius: 12px; 
            width: 100%; 
            max-width: 400px; 
            box-sizing: border-box;
        }
        h2 { color: #fbbf24; text-align: center; margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-size: 14px; color: #a1a1aa; }
        input, select { 
            width: 100%; 
            padding: 10px; 
            margin-bottom: 15px; 
            background: rgba(0, 0, 0, 0.4); 
            border: 1px solid rgba(251, 191, 36, 0.2); 
            color: #fff; 
            border-radius: 6px; 
            box-sizing: border-box; 
        }
        button { 
            width: 100%; 
            padding: 12px; 
            background: #fbbf24; 
            border: none; 
            color: #1a120c; 
            font-weight: bold; 
            border-radius: 6px; 
            cursor: pointer; 
        }
        button:hover { background: #f59e0b; }
        .erro { color: #ef4444; font-size: 13px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Completar Cadastro</h2>
        <p style="font-size: 13px; color: #a1a1aa; text-align: center; margin-bottom: 20px;">
            Por favor, preencha seus dados reais antes de continuar.
        </p>

        <?php if(isset($erro)): ?>
            <div class="erro"><?php echo $erro; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Nome Completo:</label>
            <input type="text" name="nome" required placeholder="Seu nome real">

            <label>Turma:</label>
            <input type="text" name="turma" required placeholder="Ex: 3º Ano, Turma A">

            <label>Turno:</label>
            <select name="turno" required>
                <option value="">Selecione o turno</option>
                <option value="Matutino">Matutino</option>
                <option value="Vespertino">Vespertino</option>
                <option value="Noturno">Noturno</option>
            </select>

            <label>Curso:</label>
            <input type="text" name="curso" required placeholder="Ex: Informática, Enfermagem">

            <button type="submit">Salvar e Continuar</button>
        </form>
    </div>
</body>
</html>