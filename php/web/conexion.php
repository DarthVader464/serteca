<?php
//variables de conexión a la bd

$usuariobd = "admin";
$conexionbd = "mysql_db";
$contrasinalbd = "abc123.";
$bd = "proxecto_db";
$conexion = @mysqli_connect("$conexionbd","$usuariobd","$contrasinalbd","$bd");

if (!$conexion)
{
    echo "Error ".mysqli_connect_errno()." na conexión: ".mysqli_connect_error();
}

$query = "SELECT * FROM usuarios where usuario = 'admin'";
$resultado = @mysqli_query($conexion, $query);
            
if (mysqli_num_rows($resultado) == 0){
    $contrasinal = "abc123.";
    $contrasinal = password_hash($contrasinal, PASSWORD_DEFAULT);
    $insert = "INSERT into usuarios(usuario, contrasinal, rol) values('admin', '$contrasinal', 'admin')";
    @mysqli_query($conexion, $insert);
}

?>