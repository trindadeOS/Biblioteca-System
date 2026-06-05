<?php

session_start();
require_once("conexao.php");

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "
    SELECT *
    FROM Usuarios
    WHERE EMAIL = ?
    AND SENHA = ?
    ";

    $stmt = $conn->prepare($sql);

    if(!$stmt){
        die("Erro prepare: " . $conn->error);
    }

    $stmt->bind_param("ss",$email,$senha);

    if(!$stmt->execute()){
        die("Erro execute: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if($result->num_rows > 0){

        $usuario = $result->fetch_assoc();

        $_SESSION['id'] = $usuario['ID'];
        $_SESSION['nome'] = $usuario['NOME'];
        $_SESSION['tipo'] = $usuario['TIPO'];

        // ==========================================================
        // CONFIGURAÇÕES DA SUA GATEWAY ULTRAMSG
        // ==========================================================
        $ultramsg_instance = "instance178315"; // Mantida sua instância
        $ultramsg_token = "qnt8fmss6rk6oohy";   // Mantido seu Token

        // ----------------------------------------------------------
        // 🚀 AUTOMAÇÃO DE PRAZOS CORRIGIDA (TABELA: CLIENTES)
        // ----------------------------------------------------------
        // CORREÇÃO NO SQL: Filtra livros onde a data de entrega seja menor ou igual a 3 dias a partir de hoje
        // Isso inclui os próximos 3 dias E todos os livros que já estão atrasados no sistema e com AVISO_ENVIADO = 0
        $sql_prazos = "
            SELECT ID, NOME, LIVRO, TELEFONE, DATA 
            FROM CLIENTES 
            WHERE DATEDIFF(DATA, NOW()) <= 3 
              AND AVISO_ENVIADO = 0
        ";
        
        $res_prazos = $conn->query($sql_prazos);

        if($res_prazos && $res_prazos->num_rows > 0) {
            
            // Inicializa o cURL para envio
            $curl = curl_init();
            
            // Define o fuso horário correto para a comparação exata de datas
            date_default_timezone_set('America/Bahia');
            $hoje = new DateTime(date('Y-m-d'));

            while($aluno = $res_prazos->fetch_assoc()) {
                
                // Pula o registro caso não tenha telefone cadastrado
                if(empty($aluno['TELEFONE'])) {
                    continue;
                }

                $data_banco = new DateTime($aluno['DATA']);
                
                // Calcula a diferença real de dias (considerando valores negativos para atrasos)
                $diff_em_dias = (int)$hoje->diff($data_banco)->format("%r%a");

                // Texto amigável dinâmico baseado na situação real do prazo
                if($diff_em_dias < 0) {
                    $dias_atraso = abs($diff_em_dias);
                    $tempo_texto = "está *ATRASADO há {$dias_atraso} " . ($dias_atraso == 1 ? "dia" : "dias") . "*! 🚨";
                } elseif($diff_em_dias == 0) {
                    $tempo_texto = "vence *HOJE*! 🚨";
                } elseif($diff_em_dias == 1) {
                    $tempo_texto = "resta apenas *1 dia*";
                } else {
                    $tempo_texto = "restam apenas *{$diff_em_dias} dias*";
                }

                // Limpa o número guardado no banco (remove traços ou espaços)
                $numero_aluno = preg_replace('/[^0-9]/', '', $aluno['TELEFONE']);
                
                // Garante o código do país '55' na frente
                if(strlen($numero_aluno) <= 11) {
                    $numero_aluno = "55" . $numero_aluno;
                }

                // Mensagem personalizada utilizando os dados reais do seu banco
                $mensagem_aluno = "Olá, *{$aluno['NOME']}*! 👋\n\n";
                $mensagem_aluno .= "Passando para lembrar sobre o prazo de devolução do livro *\"{$aluno['LIVRO']}\"* que pegou emprestado.\n\n";
                $mensagem_aluno .= "⏳ Atenção: Seu prazo {$tempo_texto}\n";
                $mensagem_aluno .= "📅 *Data limite para entrega:* " . $data_banco->format('d/m/Y') . "\n\n";
                $mensagem_aluno .= "Por favor, compareça à Biblioteca CETEPES para renovar ou devolver o livro o quanto antes. Obrigado! 📚✨";

                $params = array(
                    'token' => $ultramsg_token,
                    'to' => $numero_aluno,
                    'body' => $mensagem_aluno
                );

                // Configura e envia via cURL para a UltraMsg
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://api.ultramsg.com/{$ultramsg_instance}/messages/chat",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => http_build_query($params),
                    CURLOPT_HTTPHEADER => array("content-type: application/x-www-form-urlencoded"),
                ));
                
                curl_exec($curl);

                // 🔄 Atualiza o status no banco para 1, evitando reenvios incômodos
                $id_registro = $aluno['ID'];
                $conn->query("UPDATE CLIENTES SET AVISO_ENVIADO = 1 WHERE ID = $id_registro");
            }
            
            curl_close($curl);
        }
        // ==========================================================

        // Encaminha para o painel correspondente
        if($usuario['TIPO'] == 'Admin'){
            header("Location: admin/dashboard.php");
            exit();
        }else{
            header("Location: bibliotecario/dashboard.php");
            exit();
        }

    }else{
        echo "Email ou senha inválidos.";
    }
}
?>