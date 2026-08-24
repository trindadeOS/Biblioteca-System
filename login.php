<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("conexao.php");

// Se a página for acessada via GET (sem envio de formulário), redireciona de volta para a index
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

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

    // ==========================================================
    // AUTOMAÇÃO ULTRAMSG DESATIVADO
    // ==========================================================
  //  $ultramsg_instance = "instance178315"; 
 //   $ultramsg_token    = "qnt8fmss6rk6oohy"; 

 //   $sql_prazos = "
        SELECT ID, NOME, LIVRO, TELEFONE, DATA 
        FROM clientes 
        WHERE DATEDIFF(DATA, NOW()) <= 3 
          AND AVISO_ENVIADO = 0
    ";
    
   // $res_prazos = $conn->query($sql_prazos);

  //  if ($res_prazos && $res_prazos->num_rows > 0) {
   //     $curl = curl_init();
   //     date_default_timezone_set('America/Bahia');
    //    $hoje = new DateTime(date('Y-m-d'));
//
    //    while ($aluno = $res_prazos->fetch_assoc()) {
    //        if (empty($aluno['TELEFONE'])) continue;

      ///      $data_banco = new DateTime($aluno['DATA']);
      //      $diff_em_dias = (int)$hoje->diff($data_banco)->format("%r%a");

       //     if ($diff_em_dias < 0) {
       //         $dias_atraso = abs($diff_em_dias);
       //         $tempo_texto = "está *ATRASADO há {$dias_atraso} " . ($dias_atraso == 1 ? "dia" : "dias") . "*! 🚨";
       //     } elseif ($diff_em_dias == 0) {
        //        $tempo_texto = "vence *HOJE*! 🚨";
        //    } elseif ($diff_em_dias == 1) {
        //        $tempo_texto = "resta apenas *1 dia*";
        //    } else {
        //        $tempo_texto = "restam apenas *{$diff_em_dias} dias*";
        //    }
//
       //     $numero_aluno = preg_replace('/[^0-9]/', '', $aluno['TELEFONE']);
         //   if (strlen($numero_aluno) <= 11) {
          //      $numero_aluno = "55" . $numero_aluno;
            }

           // $mensagem_aluno  = "Olá, *{$aluno['NOME']}*! 👋\n\n";
         //   $mensagem_aluno .= "Passando para lembrar sobre o prazo de devolução do livro *\"{$aluno['LIVRO']}\"* que pegou emprestado.\n\n";
      //      $mensagem_aluno .= "⏳ Atenção: Seu prazo {$tempo_texto}\n";
       //     $mensagem_aluno .= "📅 *Data limite para entrega:* " . $data_banco->format('d/m/Y') . "\n\n";
       //     $mensagem_aluno .= "Por favor, compareça à Biblioteca CETEPES para renovar ou devolver o livro o quanto antes. Obrigado! 📚✨";

       //     $params = array(
      //          'token' => $ultramsg_token,
      //          'to'    => $numero_aluno,
       //         'body'  => $mensagem_aluno
        //    );

         //   curl_setopt_array($curl, array(
                //CURLOPT_URL            => "https://api.ultramsg.com/{$ultramsg_instance}/messages/chat",
            //    CURLOPT_RETURNTRANSFER => true,
            //    CURLOPT_CUSTOMREQUEST  => "POST",
           //     CURLOPT_POSTFIELDS     => http_build_query($params),
         //       CURLOPT_HTTPHEADER     => array("content-type: application/x-www-form-urlencoded"),
      //      ));
      //      
      //      curl_exec($curl);
      //      $id_registro = $aluno['ID'];
    //        $conn->query("UPDATE clientes SET AVISO_ENVIADO = 1 WHERE ID = $id_registro");
   //     }
   //     curl_close($curl);
 //   }

    // ==========================================================
    // REDIRECIONAMENTO PÓS-LOGIN DEFINITIVO
    // ==========================================================
    $tipo_usuario = strtoupper(trim($usuario['TIPO'] ?? ''));

    if ($tipo_usuario === 'ADMIN') {
        header("Location: admin/dashboard.php");
        exit();
    } elseif ($tipo_usuario === 'BIBLIOTECARIO' || $tipo_usuario === 'BIBLIOTECÁRIO') {
        header("Location: bibliotecario/dashboard.php");
        exit();
    } else {
        // Altere para "alunos/aluno.php" caso sua pasta utilize o 's' no plural
        header("Location: aluno/aluno.php");
        exit();
    }

} else {
    echo "<script>alert('E-mail ou senha incorretos!'); window.location.href='index.php';</script>";
    exit();
}
?>