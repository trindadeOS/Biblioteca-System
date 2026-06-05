<?php
session_start();

if(isset($_SESSION['tipo'])){

    if($_SESSION['tipo'] == 'Admin'){
        header("Location: admin/dashboard.php");
        exit();
    }

    if($_SESSION['tipo'] == 'Bibliotecario'){
        header("Location: bibliotecario/dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca - Login</title>
    
    <?php include("css.php"); ?>
    
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f4f7fb;
            padding: 20px;
        }
    </style>
</head>
<body>

    <div class="login-box" style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 420px; text-align: center;">
        
        <h1 style="font-size: 26px; font-weight: 700; color: #111827; margin-bottom: 10px;">Biblioteca CETEPES</h1>
        <p style="color: #64748b; font-size: 14px; margin-bottom: 30px;">Faça login para acessar o painel</p>

        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit" class="btn-blue" style="width: 100%; margin-top: 10px;">Entrar</button>
        </form>
        
    </div>

</body>
</html>