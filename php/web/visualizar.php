<?php
session_start();
require("conexion.php");

if (empty($_SESSION["nome"])) {
    die("Non autorizado");
}

if (empty($_GET["id"])) {
    die("ID de arquivo non proporcionado");
}

$id = intval($_GET["id"]);

$query = "SELECT * FROM arquivos WHERE id = $id";
$resultado = mysqli_query($conexion, $query);

if (mysqli_num_rows($resultado) == 0) {
    die("Arquivo non atopado");
}

$arquivo = mysqli_fetch_assoc($resultado);

$query_config = "SELECT * FROM nextcloud_config WHERE activo = 1 LIMIT 1";
$resultado_config = mysqli_query($conexion, $query_config);
$config = mysqli_fetch_assoc($resultado_config);
$nextcloud_url = $config['nextcloud_url'];
$nextcloud_user = $config['nextcloud_user'];
$nextcloud_token = $config['nextcloud_token'];

mysqli_close($conexion);

$webdav_url = $nextcloud_url . '/remote.php/dav/files/' . $nextcloud_user . '/' . $arquivo['ruta'];

$extension = strtolower(pathinfo($arquivo['nome'], PATHINFO_EXTENSION));
$mime_types = [
    'mp4' => 'video/mp4',
    'avi' => 'video/x-msvideo',
    'mkv' => 'video/x-matroska',
    'mov' => 'video/quicktime',
    'wmv' => 'video/x-ms-wmv',
    'flv' => 'video/x-flv',
    'webm' => 'video/webm',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'bmp' => 'image/bmp',
    'pdf' => 'application/pdf',
    'txt' => 'text/plain',
    'mp3' => 'audio/mpeg',
    'wav' => 'audio/wav',
    'ogg' => 'audio/ogg',
    'aac' => 'audio/aac',
    'flac' => 'audio/flac',
    'm4a' => 'audio/mp4',
];

$mime = isset($mime_types[$extension]) ? $mime_types[$extension] : 'application/octet-stream';

$ch = curl_init($webdav_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => $nextcloud_user . ':' . $nextcloud_token,
    CURLOPT_HTTPHEADER => ['Host: nextcloud'],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_FOLLOWLOCATION => true,
]);

$contenido = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && $contenido !== false) {
    $length = strlen($contenido);
    
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . $length);
    header('Accept-Ranges: bytes');
    header('Cache-Control: public, max-age=3600');
    
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    echo $contenido;
    exit();
} else {
    die("Erro ao obter o arquivo de Nextcloud (HTTP: $httpCode)");
}
?>