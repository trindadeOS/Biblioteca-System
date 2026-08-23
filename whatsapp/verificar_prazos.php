<?php

require_once("../conexao.php");

$sql = "
SELECT *
FROM CLIENTES
WHERE AVISO_ENVIADO = 0
AND TELEFONE IS NOT NULL
AND TELEFONE != ''
";

$result = $conn->query($sql);

while($row = $result->fetch_assoc()){

    $id = $row['ID'];

    $telefone = "55" . preg_replace('/[^0-9]/', '', $row['TELEFONE']);

    $mensagem = "Olá {$row['NOME']}, faltam 2 dias para devolver o livro '{$row['LIVRO']}'.";

    $params = array(
        'token' => 'tokenaqui',
        'to' => $telefone,
        'body' => $mensagem
    );

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.ultramsg.com/instance178315/messages/chat",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => http_build_query($params),
        CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded"
        ),
    ));

    $response = curl_exec($curl);

    $err = curl_error($curl);

    curl_close($curl);

    echo $response;

    if(!$err){

        $update = "
        UPDATE CLIENTES
        SET AVISO_ENVIADO = 1
        WHERE ID = $id
        ";

        $conn->query($update);
    }
}
?>