<?php
session_start();

if (!isset($_SESSION['aluno_id']) || empty($_SESSION['aluno_id'])) {
    header("Location: login.php");
    exit();
}

$caminho_conexao = __DIR__ . "/../conexao.php";
if (file_exists($caminho_conexao)) {
    require_once($caminho_conexao);
} else {
    die("Erro: O arquivo conexao.php não foi encontrado na raiz.");
}

$aluno_id = $_SESSION['aluno_id'];
$id_emprestimo = $_GET['id'] ?? null;

if ($id_emprestimo) {
    // 1. Busca os dados do empréstimo para saber qual livro foi solicitado e garantir que pertence ao aluno e está PENDENTE
    $stmt_busca = $conn->prepare("SELECT LIVRO FROM emprestimos WHERE ID = ? AND aluno_id = ? AND STATUS = 'PENDENTE'");
    $stmt_busca->bind_param("ii", $id_emprestimo, $aluno_id);
    $stmt_busca->execute();
    $res = $stmt_busca->get_result();

    if ($res && $res->num_rows > 0) {
        $dados = $res->fetch_assoc();
        $titulo_livro = $dados['LIVRO'];
        $stmt_busca->close();

        // Inicia a transação para garantir que a alteração de status e a devolução do estoque ocorram juntas
        $conn->begin_transaction();

        try {
            // 2. Altera o status para 'CANCELADO'
            $stmt_update = $conn->prepare("UPDATE emprestimos SET STATUS = 'CANCELADO' WHERE ID = ? AND aluno_id = ?");
            $stmt_update->bind_param("ii", $id_emprestimo, $aluno_id);
            $stmt_update->execute();
            $stmt_update->close();

            // 3. Devolve +1 unidade no estoque do livro correspondente
            $stmt_estoque = $conn->prepare("UPDATE livros SET quantidade = quantidade + 1 WHERE titulo = ?");
            $stmt_estoque->bind_param("s", $titulo_livro);
            $stmt_estoque->execute();
            $stmt_estoque->close();

            // Confirma as alterações no banco de dados
            $conn->commit();

        } catch (Exception $e) {
            // Se houver qualquer erro, desfaz as alterações
            $conn->rollback();
        }
    }
}

header("Location: aluno.php?status=cancelado");
exit();
?>
