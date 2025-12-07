<?php

require("conexion.php");
require_once 'vendor/autoload.php';
use Meilisearch\Client;

// Conectar a Meilisearch
$client = new Client('http://meilisearch:7700', 'masterkey123');
$index = $client->index('carpetas');

$resultado = mysqli_query(
    $conexion,
    "SELECT 
        c.id,
        c.nome,
        c.descripcion,
        c.fecha_creacion,
        (SELECT a.nome 
         FROM arquivos a 
         WHERE a.carpeta = c.nome 
         ORDER BY a.fecha_subida DESC 
         LIMIT 1) AS ultimo_archivo,
        (SELECT a.fecha_subida 
         FROM arquivos a 
         WHERE a.carpeta = c.nome 
         ORDER BY a.fecha_subida DESC 
         LIMIT 1) AS fecha_ultimo_archivo
    FROM carpetas c"
);

$carpetas = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $carpetas[] = [
        'id' => (int)$fila['id'],
        'nome' => $fila['nome'],
        'descripcion' => $fila['descripcion'],
        'fecha_creacion' => $fila['fecha_creacion'],
        'ultimo_archivo' => $fila['ultimo_archivo'],
        'fecha_ultimo_archivo' => $fila['fecha_ultimo_archivo']
    ];
}

$index->deleteAllDocuments();

if (!empty($carpetas)) {
    $index->addDocuments($carpetas);
}

$index->updateSearchableAttributes(['nome', 'descripcion']);
?>