<?php
session_start();
require("conexion.php");

if (isset($_POST["nome"]) || isset($_POST["contrasinal1"]) || isset($_POST["contrasinal2"]) ){
    if (empty($_POST["nome"]) || empty($_POST["contrasinal1"]) || empty($_POST["contrasinal2"])){
        if (empty($_POST["nome"])){
            $erron = "O nome non pode estar valeiro";
        }
        if (empty($_POST["contrasinal1"]) || empty($_POST["contrasinal2"])){
            $erro = "As contrasinais non poden estar valeiras";
        }
    }else{
        $nome = $_POST["nome"];
        $contrasinal1 = $_POST["contrasinal1"];
        $contrasinal2 = $_POST["contrasinal2"];
        if (isset($_POST["rol"])){
            $rol = "admin";
        } else{
            $rol = "usuario";
        }
    
        if ($contrasinal1 == $contrasinal2){
            require("conexion.php");
            $query = "SELECT * FROM usuarios where usuario = '$nome'";
            $resultado = @mysqli_query($conexion, $query);
            
            if (mysqli_num_rows($resultado) > 0){
                $erron = "Este usuario xa existe";
            }
            else{
                $contrasinal = password_hash($contrasinal1, PASSWORD_DEFAULT);
                $insert = "INSERT into usuarios(usuario, contrasinal, rol) values('$nome', '$contrasinal', '$rol')";
                @mysqli_query($conexion, $insert);
                header("Location: usuarios.php");
            }
        }else{
            $erro= "As contrasinais non poden ser distintas";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="logo">
            <img src="logo.png" alt="Logo Serteca">
            <h1>Serteca</h1>
            <h2>Engade un novo usuario</h2>
        </div>
        <div class="login-box">
        <form action="engadir_usuario.php" method="post">
        <div class="form-group-row">
            <div class="form-group <?php echo (!empty($erron)) ? 'has-error' : ''; ?>">
                <label>Usuario</label>
                <input type="text" class="form-control" name="nome">
                <span class="error-message"><?php
                if (isset($erron)){
                echo $erron;
                }?></span>
            </div>
            <div class="checkbox-group">
                <label>Admin</label>
                <input type="checkbox" name="rol" value="1">
            </div>
        </div>
            <div class="form-group <?php echo (!empty($erro)) ? 'has-error' : ''; ?>">
                <label>Contrasinal</label>
                <input type="password" class="form-control" name="contrasinal1">
                <span class="error-message"><?php
                if (isset($erro)){
                echo $erro;
                }?></span>
            </div>
            <div class="form-group <?php echo (!empty($erro)) ? 'has-error' : ''; ?>">
                <label>Confirmar contrasinal</label>
                <input type="password" class="form-control" name="contrasinal2">
                <span class="error-message"><?php
                if (isset($erro)){
                echo $erro;
                }?></span>
            </div>
            <div class="buttons">
                <input type="submit" class="btn btn-primary">
                <a href="usuarios.php" class="btn btn-default" >Cancelar</a>
                <br>

            </div>
        </form>
    </div>  


</body>
</html>