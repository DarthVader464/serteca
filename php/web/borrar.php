<?php
    require("conexion.php");
    if (!empty($_GET["id"])){
        $id = $_GET["id"];
        $query="DELETE FROM usuarios where id='$id'";
        @mysqli_query($conexion, $query);
    }
    header("Location: usuarios.php");
?>
</body>
</html>
