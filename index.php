<?php
require_once 'error_handler.php';
require_once 'funciones_bd.php';
$bd = require_once 'conexion.php';

try {
    $productos = consultarProductos($bd);
} catch (PDOException $ex) {
    error_log("Error al recuperar los productos " . $ex->getMessage());
    $productos = [];
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
        <title>CRUD Productos</title>
    </head>
    <body class="bg-info">
        <h3 class="text-center mt-2 font-weight-bold">Gestión de Productos</h3>
        <div class="container mt-3">
            <a href="crear.php?pet_crear" class='btn btn-success mt-2 mb-2'>Crear</a>
            <table class="table table-striped table-dark">
                <thead>
                    <tr class="text-center">
                        <th scope="col">Detalle</th>
                        <th scope="col">Codigo</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                        <tr class='text-center'>
                            <td>
                                <a class="btn btn-warning mr2" href="detalle.php?pet_detalle&id=<?= $producto->id ?>">Detalle</a>
                            </td>
                            <td><?= $producto->id ?></td>
                            <td><?= htmlspecialchars($producto->nombre, ENT_NOQUOTES, 'UTF-8') ?></td>
                            <td>
                                <form action="borrar.php" method='POST' class="d-inline">
                                    <input type="submit" class="btn btn-warning m2" value="Actualizar" name="pet_modificar" formaction="modificar.php">
                                    <input type="hidden" name="id" value="<?= $producto->id ?>"> <!-- mandamos el código del producto a borrar -->
                                    <input type="submit" onclick="return confirm('¿Borrar Producto?')" class="btn btn-danger" value="Borrar" name="borrar">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </body>
</html>

