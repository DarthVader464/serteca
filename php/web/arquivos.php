<?php
session_start();
require("conexion.php");

if (empty($_SESSION["nome"])) {
    header("Location: login.php");
    exit();
}

if (empty($_GET["carpeta"])) {
    header("Location: index.php");
    exit();
}

$rol = $_SESSION["rol"];
$carpeta = $_GET["carpeta"];

// DESCARGAR ARCHIVO
if (isset($_GET["descargar_arquivo"])) {
    $id_descargar = $_GET["descargar_arquivo"];
    
    $query_arquivo = "SELECT * FROM arquivos WHERE id = $id_descargar";
    $resultado_arquivo = mysqli_query($conexion, $query_arquivo);
    $arquivo_descargar = mysqli_fetch_assoc($resultado_arquivo);
    
    if ($arquivo_descargar) {
        $query_config = "SELECT * FROM nextcloud_config WHERE activo = 1";
        $resultado_config = mysqli_query($conexion, $query_config);
        $config = mysqli_fetch_assoc($resultado_config);
        
        $webdav_url = $config['nextcloud_url'] . '/remote.php/dav/files/' . $config['nextcloud_user'] . '/' . $arquivo_descargar['ruta'];
        $ch = curl_init($webdav_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $config['nextcloud_user'] . ':' . $config['nextcloud_token'],
            CURLOPT_HTTPHEADER => ['Host: nextcloud'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $contenido = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $contenido !== false) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $arquivo_descargar['nome'] . '"');
            header('Content-Length: ' . strlen($contenido));
            echo $contenido;
        }
    }
}

// ELIMINAR ARCHIVO
if (isset($_GET["eliminar_arquivo"]) && $rol == 'admin') {
    $id_eliminar = $_GET["eliminar_arquivo"];
    
    $query_arquivo = "SELECT * FROM arquivos WHERE id = $id_eliminar";
    $resultado_arquivo = mysqli_query($conexion, $query_arquivo);
    $arquivo_eliminar = mysqli_fetch_assoc($resultado_arquivo);
    
    if ($arquivo_eliminar) {
        $query_config = "SELECT * FROM nextcloud_config WHERE activo = 1";
        $resultado_config = mysqli_query($conexion, $query_config);
        $config = mysqli_fetch_assoc($resultado_config);
        
        $webdav_url = $config['nextcloud_url'] . '/remote.php/dav/files/' . $config['nextcloud_user'] . '/' . $arquivo_eliminar['ruta'];
        $ch = curl_init($webdav_url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_USERPWD => $config['nextcloud_user'] . ':' . $config['nextcloud_token'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Host: nextcloud'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        curl_exec($ch);
        curl_close($ch);
        
        mysqli_query($conexion, "DELETE FROM arquivos WHERE id = $id_eliminar");
    }
    
    include("actualizar_meilisearch.php");
    header("Location: arquivos.php?carpeta=" . urlencode($carpeta));
}

// ELIMINAR CARPETA
if (isset($_GET["eliminar_carpeta"]) && $rol == 'admin') {
    $query_config = "SELECT * FROM nextcloud_config WHERE activo = 1";
    $resultado_config = mysqli_query($conexion, $query_config);
    $config = mysqli_fetch_assoc($resultado_config);
    
    $webdav_url = $config['nextcloud_url'] . '/remote.php/dav/files/' . $config['nextcloud_user'] . '/serteca/' . $carpeta;
    $ch = curl_init($webdav_url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_USERPWD => $config['nextcloud_user'] . ':' . $config['nextcloud_token'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Host: nextcloud'],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);
    curl_exec($ch);
    curl_close($ch);
    
    mysqli_query($conexion, "DELETE FROM arquivos WHERE carpeta = '$carpeta'");
    
    mysqli_query($conexion, "DELETE FROM carpetas WHERE nome = '$carpeta'");
    
    mysqli_close($conexion);
    include("actualizar_meilisearch.php");
    header("Location: index.php");
}

$query_carpeta = "SELECT * FROM carpetas WHERE nome = '$carpeta'";
$resultado_carpeta = mysqli_query($conexion, $query_carpeta);
$info_carpeta = mysqli_fetch_assoc($resultado_carpeta);

$query_config = "SELECT * FROM nextcloud_config WHERE activo = 1 LIMIT 1";
$resultado_config = mysqli_query($conexion, $query_config);
$config = mysqli_fetch_assoc($resultado_config);
$nextcloud_url = $config['nextcloud_url'];
$nextcloud_user = $config['nextcloud_user'];
$nextcloud_token = $config['nextcloud_token'];

$query_arquivos = "SELECT * FROM arquivos WHERE carpeta = '$carpeta' ORDER BY fecha_subida DESC";
$resultado_arquivos = mysqli_query($conexion, $query_arquivos);

$videos = [];
$audios = [];
$documentos = [];
$outros = [];

while ($arquivo = mysqli_fetch_assoc($resultado_arquivos)) {
    $extension = strtolower(pathinfo($arquivo['nome'], PATHINFO_EXTENSION));
    
    if (in_array($extension, ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm'])) {
        $videos[] = $arquivo;
    }
    elseif (in_array($extension, ['mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a'])) {
        $audios[] = $arquivo;
    }
    elseif (in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'odt', 'ods', 'png', 'jpg', 'jpeg', 'gif', 'bmp'])) {
        $documentos[] = $arquivo;
    }
    else {
        $outros[] = $arquivo;
    }
}

mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="gl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($info_carpeta['nome']); ?> - Serteca</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container-arquivo">
        <div class="header-carpeta">
            <h2>📂 <?php echo ($info_carpeta['nome']); ?></h2>
            <div>
                <a href="index.php" class="btn-header">Volver</a>
                <?php if ($rol == 'admin') { ?>
                    <a href="arquivos.php?carpeta=<?php echo urlencode($carpeta); ?>&eliminar_carpeta=1" 
                       class="btn-eliminar" 
                       onclick="return confirm('¿Seguro que queres eliminar a carpeta \'<?php echo addslashes($info_carpeta['nome']); ?>\' e TODOS os seus arquivos?\n\n⚠️ ESTA ACCIÓN NON SE PODE DESFACER.');">
                        🗑️ Eliminar Carpeta
                    </a>
                <?php } ?>

            </div>
        </div>
        
        <?php if (!empty($info_carpeta['descripcion'])) { ?>
        <div class="descripcion-carpeta">
            <?php echo nl2br(($info_carpeta['descripcion'])); ?>
        </div>
        <?php } ?>
        
        <?php if (count($videos) == 0 && count($audios) == 0 && count($documentos) == 0 && count($outros) == 0) { ?>
            <p class="no-arquivos">Non hai arquivos nesta carpeta</p>
        <?php } ?>
        
        <!-- VIDEOS -->
        <?php if (count($videos) > 0) { ?>
        <div class="seccion-arquivos">
            <h3>🎬 Vídeos (<?php echo count($videos); ?>)</h3>
            <ul class="lista-arquivos">
                <?php foreach ($videos as $video) { 
                    $extension = strtolower(pathinfo($video['nome'], PATHINFO_EXTENSION));
                    $url_visualizar = "visualizar.php?id=" . $video['id'];
                ?>
                <li class="item-arquivo">
                    <div class="info-arquivo">
                        <strong><?php echo ($video['nome']); ?></strong>
                        <small><?php echo date('d/m/Y H:i', strtotime($video['fecha_subida'])); ?></small>
                        
                        <div class="visor-archivo">
                            <video controls>
                                <source src="<?php echo $url_visualizar; ?>" type="video/<?php echo $extension; ?>">
                                O teu navegador non soporta vídeo HTML5.
                            </video>
                        </div>
                    </div>
                    <div  class="botones-arquivo">
                        <a href="arquivos.php?carpeta=<?php echo urlencode($carpeta); ?>&descargar_arquivo=<?php echo $video['id']; ?>" class="btn-secundary">📥 Descargar</a>
                        <?php if ($rol == 'admin') { ?>
                            <a href="arquivos.php?carpeta=<?php echo urlencode($carpeta); ?>&eliminar_arquivo=<?php echo $video['id']; ?>" 
                               class="btn-eliminar" 
                               onclick="return confirm('¿Seguro que queres eliminar o arquivo \'<?php echo addslashes($video['nome']); ?>\'?\n\nEsta acción non se pode desfacer.');">
                                🗑️ Eliminar
                            </a>
                        <?php } ?>
                    </div>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
        
        <!-- AUDIOS -->
        <?php if (count($audios) > 0) { ?>
        <div class="seccion-arquivos">
            <h3>🎵 Audios (<?php echo count($audios); ?>)</h3>
            <ul class="lista-arquivos">
                <?php foreach ($audios as $audio) { 
                    $extension = strtolower(pathinfo($audio['nome'], PATHINFO_EXTENSION));
                    $url_visualizar = "visualizar.php?id=" . $audio['id'];
                ?>
                <li class="item-arquivo">
                    <div class="info-arquivo">
                        <strong><?php echo ($audio['nome']); ?></strong>
                        <small><?php echo date('d/m/Y H:i', strtotime($audio['fecha_subida'])); ?></small>
                        
                        <div class="visor-archivo">
                            <audio controls>
                                <source src="<?php echo $url_visualizar; ?>" type="audio/<?php echo $extension; ?>">
                                O teu navegador non soporta audio HTML5.
                            </audio>
                        </div>
                    </div>
                    <div  class="botones-arquivo">
                        <a href="arquivos.php?carpeta=<?php echo urlencode($carpeta); ?>&descargar_arquivo=<?php echo $audio['id']; ?>" class="btn-secundary">📥 Descargar</a>
                        <?php if ($rol == 'admin') { ?>
                            <a href="arquivos.php?carpeta=<?php echo urlencode($carpeta); ?>&eliminar_arquivo=<?php echo $audio['id']; ?>" 
                               class="btn-eliminar" 
                               onclick="return confirm('¿Seguro que queres eliminar o arquivo \'<?php echo addslashes($audio['nome']); ?>\'?\n\nEsta acción non se pode desfacer.');">
                                🗑️ Eliminar
                            </a>
                        <?php } ?>
                    </div>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
        
        <!-- DOCUMENTOS -->
        <?php if (count($documentos) > 0) { ?>
        <div class="seccion-arquivos">
            <h3>📄 Documentos (<?php echo count($documentos); ?>)</h3>
            <ul class="lista-arquivos">
                <?php foreach ($documentos as $doc) { 
                    $extension = strtolower(pathinfo($doc['nome'], PATHINFO_EXTENSION));
                    $url_visualizar = "visualizar.php?id=" . $doc['id'];
                ?>
                <li class="item-arquivo">
                    <div class="info-arquivo">
                        <strong><?php echo ($doc['nome']); ?></strong>
                        <small><?php echo date('d/m/Y H:i', strtotime($doc['fecha_subida'])); ?></small>
                        
                        <div class="visor-archivo">
                            <?php if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) { ?>
                                <img src="<?php echo $url_visualizar; ?>" alt="<?php echo $doc['nome']; ?>">
                                
                            <?php } elseif ($extension === 'pdf') { ?>
                                <iframe src="<?php echo $url_visualizar; ?>"></iframe>
                                
                            <?php } elseif ($extension === 'txt') { ?>
                                <?php
                                $ch = curl_init($nextcloud_url . '/remote.php/dav/files/' . $nextcloud_user . '/' . $doc['ruta']);
                                curl_setopt_array($ch, [
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_USERPWD => $nextcloud_user . ':' . $nextcloud_token,
                                    CURLOPT_HTTPHEADER => ['Host: nextcloud'],
                                    CURLOPT_SSL_VERIFYPEER => false,
                                    CURLOPT_SSL_VERIFYHOST => 0,
                                ]);
                                $contenido = curl_exec($ch);
                                curl_close($ch);
                                ?>
                                <pre><?php echo htmlspecialchars($contenido); ?></pre>
                                
                            <?php } else { ?>
                                <p style="padding: 20px; text-align: center; color: #0082c9;">
                                    ℹ️ Previsualización non dispoñible para este tipo de arquivo.<br>
                                    Descarga o arquivo para velo.
                                </p>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="botones-arquivo">
                        <a href="arquivos.php?carpeta=<?php echo urlencode($carpeta); ?>&descargar_arquivo=<?php echo $doc['id']; ?>" class="btn-secundary">📥 Descargar</a>
                        <?php if ($rol == 'admin') { ?>
                            <a href="arquivos.php?carpeta=<?php echo urlencode($carpeta); ?>&eliminar_arquivo=<?php echo $doc['id']; ?>" 
                               class="btn-eliminar" 
                               onclick="return confirm('¿Seguro que queres eliminar o arquivo \'<?php echo addslashes($doc['nome']); ?>\'?\n\nEsta acción non se pode desfacer.');">
                                🗑️ Eliminar
                            </a>
                        <?php } ?>
                    </div>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
        
        <!-- OUTROS ARCHIVOS -->
        <?php if (count($outros) > 0) { ?>
        <div class="seccion-arquivos">
            <h3>📦 Outros arquivos (<?php echo count($outros); ?>)</h3>
            <ul class="lista-arquivos">
                <?php foreach ($outros as $outro) { 
                    $url_visualizar = "visualizar.php?id=" . $outro['id'];
                ?>
                <li class="item-arquivo">
                    <div class="info-arquivo">
                        <strong><?php echo ($outro['nome']); ?></strong>
                        <small><?php echo date('d/m/Y H:i', strtotime($outro['fecha_subida'])); ?></small>
                        
                        <div class="visor-archivo">
                            <p style="padding: 20px; text-align: center; color: #0082c9;">
                                ℹ️ Previsualización non dispoñible para este tipo de arquivo.<br>
                                Descarga o arquivo para velo.
                            </p>
                        </div>
                    </div>
                    <div  class="botones-arquivo">
                        <a href="arquivos.php?carpeta=<?php echo urlencode($carpeta); ?>&descargar_arquivo=<?php echo $outro['id']; ?>" class="btn-secundary">📥 Descargar</a>
                        <?php if ($rol == 'admin') { ?>
                            <a href="arquivos.php?carpeta=<?php echo urlencode($carpeta); ?>&eliminar_arquivo=<?php echo $outro['id']; ?>" 
                               class="btn-eliminar" 
                               onclick="return confirm('¿Seguro que queres eliminar o arquivo \'<?php echo addslashes($outro['nome']); ?>\'?\n\nEsta acción non se pode desfacer.');">
                                🗑️ Eliminar
                            </a>
                        <?php } ?>
                    </div>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
    </div>
</body>
</html>