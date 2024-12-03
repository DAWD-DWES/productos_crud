<?php
if (!filter_has_var(INPUT_POST, 'borrar')) {
    header('Location:index.php');
    die;
}

require_once 'error_handler.php';
require_once 'funciones_bd.php';
$bd = require_once 'conexion.php';

$id = filter_input(INPUT_POST, 'id', FILTER_UNSAFE_RAW);

try {
    $productoBorrado = borrarProducto($bd, $id);
} catch (PDOException $ex) {
    error_log("Error al borrar el producto" . $ex->getMessage());
    $productoBorrado = false;
}

$bd = null;
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <!-- css para usar Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" 
              integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
        <title>Borrar Producto</title>
    </head>
    <body class="bg-info">
        <h3 class="text-center mt-2 font-weight-bold">Borrar Producto</h3>
        <div class="container mt-3">
            <h3 class="text-center mt-2 font-weight-bold">
                <?= ($productoBorrado) ? "Producto borrado con éxito" : "Ha habido un problema para borrar el producto" ?>
            </h3>
            <a href="index.php" class="btn btn-warning">Volver</a>
        </div>
    </body>
</html>
