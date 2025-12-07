<?php
session_start();
require("conexion.php");
if (isset($_GET["id"])){
    $_SESSION["id_con"] = $_GET["id"];
}
$id = $_SESSION["id_con"];
$query = "SELECT * FROM usuarios WHERE id = '$id'";
$resultado = @mysqli_query($conexion, $query);
$lina = mysqli_fetch_assoc($resultado);
if (isset($_POST["contrasinal1"]) || isset($_POST["contrasinal2"])){
    if (empty($_POST["contrasinal1"]) || empty($_POST["contrasinal2"])){
            $erro = "As contrasinais non poden estar valeiras";
    }else if ( $_POST["contrasinal1"] != $_POST["contrasinal2"]){
        $erro = "As contrasinais son distintas";
    }else{
        $nome = $lina["usuario"];
        $contrasinal1 = $_POST["contrasinal1"];
        $contrasinal2 = $_POST["contrasinal2"];
        
        $contrasinal = password_hash($contrasinal1,PASSWORD_DEFAULT);

        $query = "UPDATE usuarios set contrasinal = '$contrasinal' where id = '$id'";
        @mysqli_query($conexion, $query);
        header("Location: usuarios.php");
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
            <h2>Cambia a tua contrasinal</h2>
        </div>
        <div class="login-box">
        <form action="cambiar_contrasinal.php" method="post">
            <div class="form-group <?php echo (!empty($erro)) ? 'has-error' : ''; ?>">
                <label>Nova contrasinal</label>
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
                <input type="submit" class="btn btn-default" value="Actualizar">
                <a href="usuarios.php" class="btn btn-default" >Cancelar</a>
                <br>
            </div>
        </form>
    </div>  


</body>
</html>