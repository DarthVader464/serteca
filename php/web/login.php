<?php 
session_start(); 

if (isset($_POST["nome"]) || isset($_POST["contrasinal"])){
    if (empty($_POST["nome"]) || empty($_POST["contrasinal"])){
        if (empty($_POST["nome"])){
            $erron = "O nome non pode estar baleiro";
        }
        if (empty($_POST["contrasinal"])){
            $erro = "A contrasinal non pode estar baleira";
        }
    } else {
        $nome = trim($_POST["nome"]);
        $contrasinal = trim($_POST["contrasinal"]);
        require("conexion.php");
        
        $query = "SELECT * FROM usuarios WHERE usuario = '$nome'";
        $resultado = @mysqli_query($conexion, $query);
        
        if (mysqli_num_rows($resultado) > 0){
            $lina = mysqli_fetch_assoc($resultado);
            $contrasinaltabla = $lina["contrasinal"];
            
            if (password_verify($contrasinal, $contrasinaltabla)){
                $_SESSION["id"] = $lina["id"];
                $_SESSION["nome"] = $lina["usuario"];
                $_SESSION["rol"] = $lina["rol"];
                header("Location: index.php");
            } else {
                $erro = "Contrasinal incorrecto";
            }
        } else {
            $erron = "O usuario non existe";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="gl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Serteca</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="logo">
            <img src="logo.png" alt="Logo Serteca">
            <h1>Serteca</h1>
        </div>
        <div class="login-box">
            <form action="login.php" method="post">
                <div class="form-group <?php echo (!empty($erron)) ? 'has-error' : ''; ?>">
                    <label>Usuario</label>
                    <input type="text" name="nome">
                    <span class="error-message"><?php
                    if (isset($erron)){
                    echo $erron;
                    }?></span>
                </div>    
                <div class="form-group <?php echo (!empty($erro)) ? 'has-error' : ''; ?>">
                    <label>Contrasinal</label>
                    <input type="password" name="contrasinal">
                    <span class="error-message"><?php
                    if (isset($erro)){
                    echo $erro;
                    }?></span>
                </div>
                <div class="buttons">
                    <input type="submit" class="btn btn-primary" value="Iniciar Sesión">
                    <input type="reset" class="btn btn-default" value="Limpar">
                </div>
            </form>
        </div>
    </div>
</body>
</html>