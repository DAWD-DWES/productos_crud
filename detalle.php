<?php
if (!filter_has_var(INPUT_GET, 'pet_detalle')) {
    header('Location:index.php');
    die;
}

require_once 'error_handler.php';
require_once 'funciones_bd.php';
$bd = require_once 'conexion.php';

$id = filter_input(INPUT_GET, 'id', FILTER_UNSAFE_RAW);

try {
    $producto = consultarProductoPorId($bd, $id);
} catch (PDOException $ex) {
    error_log("Error al recuperar información de producto " . $ex->getMessage());
    $productoNoEncontrado = true;
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
        <title>Detalle Producto</title>
    </head>
    <body class="bg-info">
        <h3 class="text-center mt-2 font-weight-bold">Detalle Producto</h3>
        <div class="container mt-3">
            <?php if (!($productoEncontrado ?? true)): ?>
                <h3 class="text-center mt-2 font-weight-bold">Producto no encontrado</h3>
            <?php else: ?>
                <div class="card text-white bg-info mt-5 mx-auto">
                    <div class="card-header text-center text-weight-bold">
                        <?= $producto->nombre ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-center"><?= "Codigo: {$producto->id}" ?></h5>
                        <p class="card-text"><b>Nombre: </b><?= htmlspecialchars($producto->nombre, ENT_NOQUOTES, 'UTF-8') ?></p>
                        <p class="card-text"><b>Nombre Corto: </b> <?= htmlspecialchars($producto->nombre_corto, ENT_NOQUOTES, 'UTF-8') ?></p>
                        <p class="card-text"><b>Codigo Familia: </b><?= htmlspecialchars($producto->familia, ENT_NOQUOTES, 'UTF-8') ?></p>
                        <p class="card-text"><b>PVP (€): </b><?= htmlspecialchars($producto->pvp, ENT_NOQUOTES, 'UTF-8') ?></p>
                        <p class="card-text"><b>Descripción: </b><?= htmlspecialchars($producto->descripcion, ENT_NOQUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
            <?php endif ?>
            <div class="container mt-5 text-center">
                <a href="index.php" class="btn btn-warning">Volver</a>
            </div>
        </div>
    </body>
</html>

