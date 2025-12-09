<?php 
session_start(); 

if (empty($_SESSION["nome"]) || $_SESSION["rol"] != "admin") {
    header("Location: login.php");
    exit();
}

if (isset($_POST["nome"]) || isset($_POST["contrasinal"])){
    if (empty($_POST["nome"]) || empty($_POST["contrasinal"])){
        if (empty($_POST["nome"])){
            $erron = "O nome non pode estar baleiro";
        }
        if (empty($_POST["contrasinal"])){
            $erro = "A contrasinal non pode estar baleira";
        }
    } else {
        $nome = $_POST["nome"];
        $contrasinal = $_POST["contrasinal"];
        $nextcloud_url = "http://nextcloud";
        $apiurl = $nextcloud_url . '/ocs/v2.php/core/getapppassword';
        
        $ch = curl_init($apiurl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $nome . ':' . $contrasinal,
            CURLOPT_HTTPHEADER => [
                'OCS-APIRequest: true',
                'User-Agent: Serteca'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $dom = new DOMDocument();
            @$dom->loadXML($response);
            $xpath = new DOMXPath($dom);
            $apppassword = $xpath->query('//apppassword')->item(0);
            
            if ($apppassword){
                $token = $apppassword->nodeValue;
                
                require("conexion.php");
                
                mysqli_query($conexion, "UPDATE nextcloud_config SET activo = 0");
                
                $insert = "INSERT INTO nextcloud_config (nextcloud_url, nextcloud_user, nextcloud_token, activo) 
                          VALUES ('$nextcloud_url', '$nome', '$token', 1)";
                
                if (mysqli_query($conexion, $insert)){
                    mysqli_close($conexion);
                    header("Location: index.php");
                } else {
                    $erro = "Erro ao gardar na base de datos: " . mysqli_error($conexion);
                }
                
                mysqli_close($conexion);
            } else {
                $erro = "Non se puido xerar o token";
            }
        } else {
            $erron = "Usuario ou contrasinal incorrectos";
            $erro = "Usuario ou contrasinal incorrectos";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="gl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nextcloud - Serteca</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="logo">
            <img src="logo.png" alt="Logo Serteca">
            <h1>Serteca</h1>
            <h2>Usuario de nextcloud</h2>
        </div>
        <div class="login-box">
            <form action="usuario_nextcloud.php" method="post">
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
                    <input type="submit" class="btn btn-primary" value="Iniciar sesión">
                    <a href="index.php" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>