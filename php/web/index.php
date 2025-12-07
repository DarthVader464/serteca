<?php
session_start();
require("conexion.php");

require_once 'vendor/autoload.php';
use Meilisearch\Client;

if (empty($_SESSION["nome"])) {
    header("Location: login.php");
}

$rol = $_SESSION["rol"];
$nome = $_SESSION["nome"];

$archivos_result = mysqli_query(
    $conexion,
    "SELECT 
        c.nome AS nombre_carpeta,
        c.descripcion AS descripcion_carpeta,
        (SELECT a.nome 
         FROM arquivos a 
         WHERE a.carpeta = c.nome 
         ORDER BY a.fecha_subida DESC 
         LIMIT 1) AS nombre_archivo,
        (SELECT a.fecha_subida 
         FROM arquivos a 
         WHERE a.carpeta = c.nome 
         ORDER BY a.fecha_subida DESC 
         LIMIT 1) AS fecha_subida
    FROM carpetas c
    ORDER BY fecha_subida DESC
    LIMIT 8"
);
?>
<!DOCTYPE html>
<html lang="gl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serteca - Inicio</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="index-page">
    <header class="barra-superior">
        <div class="logo">
            <img src="logo.png" alt="Logo Serteca">
            <h1>Serteca</h1>
        </div>
        <div class="menu-admin">
            <?php if ($rol == 'admin') { ?>
                <a href="usuarios.php" class="btn-header">Usuarios</a>
                <a href="subir_arquivos.php" class="btn-header">Subir Archivos</a>
                <a href="graficos.php" class="btn-header">Gráficos</a>
                <a href="usuario_nextcloud.php" class="btn-header">Nextcloud</a>
            <?php } ?>
        </div>
        <div class="usuario">
            <span><?php echo $nome; ?></span>
            <div class="dropdown">
                <a href="sair.php">Cerrar sesión</a>
            </div>
        </div>
    </header>

    <main class="contenido-principal">
        <form action="index.php" method="post" class="form-buscador">
            <input type="text" name="busqueda" placeholder="Buscar carpetas..." required>
            <button type="submit">Buscar</button>
        </form>

        <?php
        // BÚSQUEDA CON MEILISEARCH
        if (isset($_POST["busqueda"])) {
            $carpetanome = $_POST["busqueda"];
            
            $client = new Client('http://meilisearch:7700', 'masterkey123');
            $index = $client->index('carpetas');
            
            $resultado_busqueda = $index->search($carpetanome, ['limit' => 50]);
            $resultados = $resultado_busqueda->getHits();
            
            $archivos_array = [];
            foreach ($resultados as $hit) {
                $archivos_array[] = [
                    'nombre_carpeta' => $hit['nome'],
                    'descripcion_carpeta' => $hit['descripcion'],
                    'nombre_archivo' => $hit['ultimo_archivo'] ?? null,
                    'fecha_subida' => $hit['fecha_ultimo_archivo'] ?? null
                ];
            }
            
            $archivos_result = (object)['data' => $archivos_array, 'num_rows' => count($archivos_array)];
        }
        ?>

        <section class="listado-archivos">
            <h2>Últimos arquivos subidos</h2>
            <?php
            $tiene_resultados = false;
            if (isset($archivos_result->data)) {
                $tiene_resultados = count($archivos_result->data) > 0;
            } else {
                $tiene_resultados = mysqli_num_rows($archivos_result) > 0;
            }
            
            if ($tiene_resultados) { ?>
                <ul class="encabezados">
                    <li>
                        <span>Cartafol</span>
                        <span>Descripción</span>
                        <span>Último arquivo</span>
                        <span>Data subida</span>
                    </li>
                </ul>
                <ul class="lista-resultados">
                    <?php 
                    if (isset($archivos_result->data)) {
                        foreach ($archivos_result->data as $fila) { ?>
                            <li class="fila">
                                <span><a href="arquivos.php?carpeta=<?php echo urlencode($fila['nombre_carpeta']); ?>" class="btn-secundary"><?php echo htmlspecialchars($fila['nombre_carpeta']); ?></a></span>
                                <span><?php echo htmlspecialchars($fila['descripcion_carpeta']); ?></span>
                                <span><?php echo $fila['nombre_archivo'] ? htmlspecialchars($fila['nombre_archivo']) : '-'; ?></span>
                                <span><?php echo $fila['fecha_subida'] ? $fila['fecha_subida'] : '-'; ?></span>
                            </li>
                        <?php }
                    } else {
                        while ($fila = mysqli_fetch_assoc($archivos_result)) { ?>
                            <li class="fila">
                                <span><a href="arquivos.php?carpeta=<?php echo urlencode($fila['nombre_carpeta']); ?>" class="btn-secundary"><?php echo htmlspecialchars($fila['nombre_carpeta']); ?></a></span>
                                <span><?php echo htmlspecialchars($fila['descripcion_carpeta']); ?></span>
                                <span><?php echo $fila['nombre_archivo'] ? htmlspecialchars($fila['nombre_archivo']) : '-'; ?></span>
                                <span><?php echo $fila['fecha_subida'] ? $fila['fecha_subida'] : '-'; ?></span>
                            </li>
                        <?php }
                    } ?>
                </ul>
            <?php } else { ?>
                <p>Non hai carpetas que mostrar.</p>
            <?php } ?>
        </section>
    </main>
</body>
</html>