<?php
session_start();
require("conexion.php");

if (empty($_SESSION["nome"]) || $_SESSION["rol"] != 'admin') {
    header("Location: login.php");
    exit();
}

$rol = $_SESSION["rol"];
$nome_usuario = $_SESSION["nome"];

$numRexistros = 8;
$paxina = 1;

if(array_key_exists('pax', $_GET)){
    $paxina = $_GET['pax']; 
}

$resultado_total = mysqli_query($conexion, "SELECT * FROM usuarios");
$totalRexistros = mysqli_num_rows($resultado_total);
$totalPaxinas = ceil($totalRexistros/$numRexistros);

$resultado = mysqli_query($conexion, "SELECT * FROM usuarios LIMIT ".(($paxina-1)*$numRexistros).", $numRexistros");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Serteca</title>
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
            <a href="index.php" class="btn-header">Inicio</a>
            <a href="subir_arquivos.php" class="btn-header">Subir Archivos</a>
            <a href="graficos.php" class="btn-header">Gráficos</a>
            <a href="usuario_nextcloud.php" class="btn-header">Nextcloud</a>
        <?php } ?>
        </div>
        <div class="usuario">
            <span><?php echo $nome_usuario; ?></span>
            <div class="dropdown">
                <a href="sair.php">Cerrar sesión</a>
            </div>
        </div>
    </header>
    
    <main class="contenido-principal">
        <h2>Listado de Usuarios</h2>
        
            <table class="tabla-usuarios">
                <tr>
                    <th>Nome</th>
                    <th>Data creación</th>
                    <th>Rol</th>
                    <th>Cambiar contrasinal</th>
                    <th>Borrar</th>
                </tr>
                <?php while($fila = mysqli_fetch_assoc($resultado)) { ?>
                <tr>
                    <td><?php echo $fila['usuario']; ?></td>
                    <td><?php echo $fila['fecha_creacion']; ?></td>
                    <td><?php echo $fila['rol']; ?></td>
                    <td><a href="cambiar_contrasinal.php?id=<?php echo $fila['id']; ?>" class="btn-secundary">🔑 Cambiar</a></td>
                    <td>
                <?php if($fila['id'] != 1){ ?>
                        <a href="borrar.php?id=<?php echo $fila['id']; ?>" class="btn-secundary" onclick="return confirm('¿Seguro que quieres borrar este usuario?');">🗑️ Borrar</a>
                <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        <div class="barra-inferior">
            <div class="paginacion">
                <?php for($i=0; $i<$totalPaxinas; $i++){ ?>
                    <a href="usuarios.php?pax=<?php echo $i+1; ?>" class="btn-secundary"><?php echo $i+1; ?></a>
                <?php } ?>
            </div>
            
            <div class="botones-accion">
                <a href="engadir_usuario.php" class="btn-header">Engadir usuario</a>
                <a href="index.php" class="btn-header">Volver</a>
            </div>
        </div> 
    </main>
</body>
</html>