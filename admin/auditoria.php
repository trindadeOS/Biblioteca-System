<?php
session_start();

// Puxa a conexão com o banco de dados
require_once("../conexao.php");

// Validação de acesso para 'Admin'
if(!isset($_SESSION['tipo']) || $_SESSION['tipo'] != 'Admin'){
    header("Location: ../index.php");
    exit();
}

// Busca todos os registros da tabela Auditoria ordenando pelos mais recentes
$sql_auditoria = "SELECT ID, Tabela_Afetada, User_Responsavel, Tipo_Operacao FROM Auditoria ORDER BY ID DESC";
$res_auditoria = $conn->query($sql_auditoria);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoria do Sistema - Biblioteca</title>
    
    <?php include("../css.php"); ?>

    <style>
        /* Estilos específicos para deixar a tabela bonita e responsiva */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }
        
        th, td {
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        tr:hover {
            background-color: #f8fafc;
        }

        /* Badge estilizada para o tipo de operação */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-insert {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-update {
            background-color: #fef9c3;
            color: #a16207;
        }
        .badge-delete {
            background-color: #fee2e2;
            color: #b91c1c;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Biblioteca</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="criar_usuario.php">Criar Usuário</a>
        <a href="auditoria.php" class="active">Ver Auditoria</a>
        
        <div class="logout">
            <a href="../logout.php">Sair</a>
        </div>
    </div>

    <div class="main">
        
        <div class="topbar">
            <div>
                <h1>Auditoria do Sistema</h1>
                <p>Histórico completo de alterações realizadas no banco de dados</p>
            </div>
        </div>

        <div class="table-box">
            <h3>Registros de Alterações</h3>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tabela Afetada</th>
                            <th>ID Usuário Responsável</th>
                            <th>Tipo de Operação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($res_auditoria && $res_auditoria->num_rows > 0) {
                            while($row = $res_auditoria->fetch_assoc()) {
                                // Define a cor da badge dinamicamente baseada na operação
                                $operacao = strtoupper($row['Tipo_Operacao']);
                                $badge_class = 'badge-insert';
                                if ($operacao == 'UPDATE') $badge_class = 'badge-update';
                                if ($operacao == 'DELETE') $badge_class = 'badge-delete';
                                
                                echo "<tr>";
                                echo "<td># " . htmlspecialchars($row['ID']) . "</td>";
                                echo "<td><strong>" . htmlspecialchars($row['Tabela_Afetada']) . "</strong></td>";
                                echo "<td>Usuário ID: " . htmlspecialchars($row['User_Responsavel']) . "</td>";
                                echo "<td><span class='badge {$badge_class}'>" . htmlspecialchars($operacao) . "</span></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center; color:#64748b;'>Nenhum registro de auditoria encontrado.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>