<?php 
session_start();

require("conexion.php");
$query = "SELECT * FROM nextcloud_config WHERE activo = 1 LIMIT 1";
$resultado = mysqli_query($conexion, $query);
if (mysqli_num_rows($resultado) > 0) {
    $config = mysqli_fetch_assoc($resultado);
    $nextcloud_url = $config['nextcloud_url'];
    $nextcloud_user = $config['nextcloud_user'];
    $nextcloud_token = $config['nextcloud_token'];
} else {
    die("Erro: Non hai configuración de Nextcloud <a href='usuario_nextcloud.php'>Configúraa aquí</a>");
}

if (isset($_POST["crear_carpeta"])){
    if (empty($_POST["nome_carpeta"])){
        $errocarpeta = "O nome da carpeta non pode estar baleiro";
    } else {
        $carpetabase = "serteca";
        $nome = preg_replace('/[^a-zA-Z0-9_-]/', '_', trim($_POST["nome_carpeta"]));
        $descripcion = trim($_POST["descripcion_carpeta"]);
        
        $verificar = "SELECT * FROM carpetas WHERE nome = '$nome'";
        $resultado_verificar = mysqli_query($conexion, $verificar);
        
        if (mysqli_num_rows($resultado_verificar) > 0) {
            $errocarpeta = "A carpeta '$nome' xa existe";
        } else {
            $webdav_url = $nextcloud_url . '/remote.php/dav/files/' . $nextcloud_user . '/'. $carpetabase . '/' . $nome;
            $ch = curl_init($webdav_url);
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => "MKCOL",
                CURLOPT_USERPWD => $nextcloud_user . ':' . $nextcloud_token,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Host: nextcloud'],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 201 || $httpCode === 405) {
                // Guardar en BD
                $insert = "INSERT INTO carpetas (nome, descripcion) VALUES ('$nome', '$descripcion')";
                if (mysqli_query($conexion, $insert)) {
                    $exitocarpeta = "Carpeta '$nome' creada correctamente";
                    include("actualizar_meilisearch.php");
                } else {
                    $errocarpeta = "Erro ao gardar na base de datos";
                }
            } else {
                $errocarpeta = "Erro ao crear a carpeta en Nextcloud";
            }
        }
    }
}

if (isset($_POST["subir_arquivo"]) && isset($_FILES["arquivo"])){
    if (empty($_POST["carpeta_destino"])){
        $erroarquivo = "Debes seleccionar unha carpeta";
    } else {
        $carpetabase = "serteca";
        $arquivo = $_FILES["arquivo"];
        $carpeta = $_POST["carpeta_destino"];
        
        if ($arquivo['error'] === UPLOAD_ERR_OK) {
            $nome_original = basename($arquivo['name']);
            $nome_seguro = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nome_original);
            $ruta = $carpetabase . '/' . $carpeta . '/' . $nome_seguro;

            $webdav_url = $nextcloud_url . '/remote.php/dav/files/' . $nextcloud_user . '/' . $ruta;
            $ch = curl_init($webdav_url);
            curl_setopt_array($ch, [
                CURLOPT_PUT => true,
                CURLOPT_USERPWD => $nextcloud_user . ':' . $nextcloud_token,
                CURLOPT_HTTPHEADER => ['Content-Type: application/octet-stream', 'Host: nextcloud'],
                CURLOPT_INFILE => fopen($arquivo['tmp_name'], 'r'),
                CURLOPT_INFILESIZE => filesize($arquivo['tmp_name']),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                // Guardar en BD
                $insert = "INSERT INTO arquivos (nome, ruta, carpeta) VALUES ('$nome_seguro', '$ruta', '$carpeta')";
                mysqli_query($conexion, $insert);
                $exitoarquivo = "Arquivo '$nome_original' subido correctamente";
                include("actualizar_meilisearch.php");
            } else {
                $erroarquivo = "Erro ao subir o arquivo";
            }
        } else {
            $erroarquivo = "Erro ao subir o arquivo";
        }
    }
}

$query_carpetas = "SELECT nome FROM carpetas ORDER BY fecha_creacion DESC";
$resultado_carpetas = mysqli_query($conexion, $query_carpetas);
$carpetas = [];
while ($row = mysqli_fetch_assoc($resultado_carpetas)) {
    $carpetas[] = $row['nome'];
}
?>
<!DOCTYPE html>
<html lang="gl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Arquivos - Serteca</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="logo">
            <h1>Serteca</h1>
            <h2>Xestión de Arquivos</h2>
        </div>
        
        <div class="login-box">
            <h3>📁 Crear novo cartafol</h3>
            <form action="subir_arquivos.php" method="post">
                <div class="form-group <?php echo (!empty($errocarpeta)) ? 'has-error' : ''; ?>">
                    <label>Nome do cartafol</label>
                    <input type="text" name="nome_carpeta" placeholder="meu-cartafol">
                    <span class="error-message"><?php
                    if (isset($errocarpeta)){
                        echo $errocarpeta;
                    }?></span>
                    <?php if (isset($exitocarpeta)){ ?>
                    <span class="error-message" style="color: green;"><?php echo $exitocarpeta; ?></span>
                    <?php } ?>
                </div>
                <div class="form-group">
                    <label>Descripción (opcional)</label>
                    <textarea name="descripcion_carpeta" placeholder="Descripción do cartafol..." rows="3"></textarea>
                </div>
                <div class="buttons">
                    <input type="submit" name="crear_carpeta" class="btn btn-primary" value="Crear cartafol">
                </div>
            </form>
        </div>
        
        <div class="login-box">
            <h3>📤 Subir arquivo</h3>
            <form action="subir_arquivos.php" method="post" enctype="multipart/form-data">
                <div class="form-group <?php echo (!empty($erroarquivo)) ? 'has-error' : ''; ?>">
                    <label>Seleccionar carpeta</label>
                    <input type="text" 
                           name="carpeta_destino" 
                           list="carpetas_lista" 
                           placeholder="Escribe ou selecciona..."
                           autocomplete="off"
                           required>
                    <datalist id="carpetas_lista">
                        <?php foreach ($carpetas as $carpeta){ ?>
                            <option value="<?php echo $carpeta; ?>">
                        <?php } ?>
                    </datalist>
                    <span class="error-message"><?php
                    if (isset($erroarquivo)){
                        echo $erroarquivo;
                    }?></span>
                    <?php if (isset($exitoarquivo)){ ?>
                    <span class="error-message" style="color: green;"><?php echo $exitoarquivo; ?></span>
                    <?php } ?>
                </div>
                <div class="form-group">
                    <label>Seleccionar arquivo</label>
                    <input type="file" name="arquivo" class="boton-examinar" required>
                </div>
                <div class="buttons">
                    <input type="submit" name="subir_arquivo" class="btn btn-primary" value="Subir Arquivo">
                    <a href="index.php" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>