<?php
session_start();
require("conexion.php");

if (empty($_SESSION["nome"]) || $_SESSION["rol"] != "admin") {
    header("Location: login.php");
    exit();
}

$rol = $_SESSION["rol"];
$nome = $_SESSION["nome"];

mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="gl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficos - Serteca</title>
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
                <a href="subir_arquivos.php" class="btn-header">Subir Arquivos</a>
                <a href="index.php" class="btn-header">Volver</a>
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

    <main class="graficos-container">
        <h1>📊 Dashboard de Gráficos</h1>
        <div class="seccion-graficos">
            <h2>🖥️ Sistema</h2>
            <div class="graficos-grid">
                <div class="grafico-card">
                    <div class="grafico-titulo">Node Exporter</div>
                    <iframe src="http://192.168.0.108:3000/d-solo/rYdddlPWk/node-exporter-full?orgId=1&timezone=browser&var-ds_prometheus=ef6e556g2gkxsc&var-job=node-exporter&var-nodename=bf91dc78c25b&var-node=node-exporter:9100&refresh=1m&panelId=panel-323&__feature.dashboardSceneSolo=true" 
                        width="450" height="200" frameborder="0"></iframe>
                </div>
                <div class="grafico-card">
                    <div class="grafico-titulo">Uso de CPU</div>
                    <iframe src="http://192.168.0.108:3000/d-solo/rYdddlPWk/node-exporter-full?orgId=1&timezone=browser&var-ds_prometheus=ef6e556g2gkxsc&var-job=node-exporter&var-nodename=bf91dc78c25b&var-node=node-exporter:9100&refresh=1m&panelId=panel-20&__feature.dashboardSceneSolo=true" 
                        width="450" height="200" frameborder="0"></iframe>
                </div>
                
                <div class="grafico-card">
                    <div class="grafico-titulo">Memoria RAM</div>
                    <iframe src="http://192.168.0.108:3000/d-solo/rYdddlPWk/node-exporter-full?orgId=1&timezone=browser&var-ds_prometheus=ef6e556g2gkxsc&var-job=node-exporter&var-nodename=bf91dc78c25b&var-node=node-exporter:9100&refresh=1m&panelId=panel-16&__feature.dashboardSceneSolo=true" 
                        width="450" height="200" frameborder="0"></iframe>
                </div>
                
                <div class="grafico-card">
                    <div class="grafico-titulo">Sys load</div>
                    <iframe src="http://192.168.0.108:3000/d-solo/rYdddlPWk/node-exporter-full?orgId=1&timezone=browser&var-ds_prometheus=ef6e556g2gkxsc&var-job=node-exporter&var-nodename=bf91dc78c25b&var-node=node-exporter:9100&refresh=1m&panelId=panel-155&__feature.dashboardSceneSolo=true" 
                        width="450" height="200" frameborder="0"></iframe>
                </div>

                <div class="grafico-card">
                    <div class="grafico-titulo">Swam</div>
                    <iframe src="http://192.168.0.108:3000/d-solo/rYdddlPWk/node-exporter-full?orgId=1&timezone=browser&var-ds_prometheus=ef6e556g2gkxsc&var-job=node-exporter&var-nodename=bf91dc78c25b&var-node=node-exporter:9100&refresh=1m&panelId=panel-21&__feature.dashboardSceneSolo=true" 
                        width="450" height="200" frameborder="0"></iframe>
                </div>

                <div class="grafico-card">
                    <div class="grafico-titulo">CPU cores</div>
                    <iframe src="http://192.168.0.108:3000/d-solo/rYdddlPWk/node-exporter-full?orgId=1&timezone=browser&var-ds_prometheus=ef6e556g2gkxsc&var-job=node-exporter&var-nodename=bf91dc78c25b&var-node=node-exporter:9100&refresh=1m&panelId=panel-14&__feature.dashboardSceneSolo=true" 
                        width="450" height="200" frameborder="0"></iframe>
                </div>

                <div class="grafico-card">
                    <div class="grafico-titulo">Ram total</div>
                    <iframe src="http://192.168.0.108:3000/d-solo/rYdddlPWk/node-exporter-full?orgId=1&timezone=browser&var-ds_prometheus=ef6e556g2gkxsc&var-job=node-exporter&var-nodename=bf91dc78c25b&var-node=node-exporter:9100&refresh=1m&panelId=panel-75&__feature.dashboardSceneSolo=true" 
                        width="450" height="200" frameborder="0"></iframe>
                </div>

                <div class="grafico-card">
                    <div class="grafico-titulo">Swam total</div>
                    <iframe src="http://192.168.0.108:3000/d-solo/rYdddlPWk/node-exporter-full?orgId=1&timezone=browser&var-ds_prometheus=ef6e556g2gkxsc&var-job=node-exporter&var-nodename=bf91dc78c25b&var-node=node-exporter:9100&refresh=1m&panelId=panel-18&__feature.dashboardSceneSolo=true" 
                        width="450" height="200" frameborder="0"></iframe>
                </div>

                <div class="grafico-card">
                    <div class="grafico-titulo">Uptime</div>
                    <iframe src="http://192.168.0.108:3000/d-solo/rYdddlPWk/node-exporter-full?orgId=1&timezone=browser&var-ds_prometheus=ef6e556g2gkxsc&var-job=node-exporter&var-nodename=bf91dc78c25b&var-node=node-exporter:9100&refresh=1m&panelId=panel-15&__feature.dashboardSceneSolo=true" 
                        width="450" height="200" frameborder="0"></iframe>
                </div>

                <div class="grafico-card">
                    <div class="grafico-titulo">Uso CPU</div>
                    <iframe src="http://192.168.0.108:3000/d-solo/rYdddlPWk/node-exporter-full?orgId=1&timezone=browser&var-ds_prometheus=ef6e556g2gkxsc&var-job=node-exporter&var-nodename=bf91dc78c25b&var-node=node-exporter:9100&refresh=1m&panelId=panel-77&__feature.dashboardSceneSolo=true" 
                        width="450" height="200" frameborder="0"></iframe>
                </div>

                <div class="grafico-card">
                    <div class="grafico-titulo">Uso memoria</div>
                    <iframe src="http://192.168.0.108:3000/d-solo/rYdddlPWk/node-exporter-full?orgId=1&timezone=browser&var-ds_prometheus=ef6e556g2gkxsc&var-job=node-exporter&var-nodename=bf91dc78c25b&var-node=node-exporter:9100&refresh=1m&panelId=panel-78&__feature.dashboardSceneSolo=true" 
                        width="450" height="200" frameborder="0"></iframe>
                </div>

                <div class="grafico-card">
                    <div class="grafico-titulo">Uso red</div>
                    <iframe src="http://192.168.0.108:3000/d-solo/rYdddlPWk/node-exporter-full?orgId=1&timezone=browser&var-ds_prometheus=ef6e556g2gkxsc&var-job=node-exporter&var-nodename=bf91dc78c25b&var-node=node-exporter:9100&refresh=1m&panelId=panel-74&__feature.dashboardSceneSolo=true" 
                        width="450" height="200" frameborder="0"></iframe>
                </div>

                <div class="grafico-card">
                    <div class="grafico-titulo">Uso disco</div>
                    <iframe src="http://192.168.0.108:3000/d-solo/rYdddlPWk/node-exporter-full?orgId=1&timezone=browser&var-ds_prometheus=ef6e556g2gkxsc&var-job=node-exporter&var-nodename=bf91dc78c25b&var-node=node-exporter:9100&refresh=1m&panelId=panel-152&__feature.dashboardSceneSolo=true" 
                        width="450" height="200" frameborder="0"></iframe>
                </div>

                <div class="grafico-card">
                    <div class="grafico-titulo">Lectura escritura disco</div>
                    <iframe src="http://192.168.0.108:3000/d-solo/rYdddlPWk/node-exporter-full?orgId=1&timezone=browser&var-ds_prometheus=ef6e556g2gkxsc&var-job=node-exporter&var-nodename=bf91dc78c25b&var-node=node-exporter:9100&refresh=1m&panelId=panel-9&__feature.dashboardSceneSolo=true" 
                        width="450" height="200" frameborder="0"></iframe>
                </div>

            </div>
        </div>

        
    </main>
</body>
</html>
