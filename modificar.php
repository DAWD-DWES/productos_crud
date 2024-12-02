<?php
if (!(filter_has_var(INPUT_POST, 'pet_modificar') || filter_has_var(INPUT_POST, 'modificar'))) {
    header('Location:index.php');
    die;
}
require_once 'error_handler.php';
require_once 'funciones_bd.php';
$bd = require_once 'conexion.php';

if (filter_has_var(INPUT_POST, 'modificar')) {
    $nombre = ucwords(trim(filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW)));
    $nombreErr = filter_var($nombre, FILTER_VALIDATE_REGEXP,
                    ['options' => ['regexp' => "/^[\w\s\-_]{2,100}$/"]]) === false;
    $nombreCorto = strtoupper(trim(filter_input(INPUT_POST, 'nombre_corto', FILTER_UNSAFE_RAW)));
    $nombreCortoErr = filter_var($nombreCorto, FILTER_VALIDATE_REGEXP,
                    ['options' => ['regexp' => "/^[a-zA-Z0-9]{2,15}$/"]]) === false;
    $pvp = filter_input(INPUT_POST, 'pvp', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $pvpErr = filter_var($pvp, FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0]]) === false;
    $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_UNSAFE_RAW));
    $familiaCodigo = filter_input(INPUT_POST, 'familia_codigo', FILTER_UNSAFE_RAW);
    $id = filter_input(INPUT_POST, 'id', FILTER_UNSAFE_RAW);
    $error = array_sum(compact(["nombreErr", "nombreCortoErr", "pvpErr"])) > 0;
    if (!$error) {
        try {
            $productoModificado = modificaProducto($bd, $id, $nombre, $nombreCorto, $pvp, $familiaCodigo, $descripcion);
        } catch (PDOException $ex) {
            if ($ex->getcode() == 23000) {
                $errorDuplicadoNombreCorto = true;
            }
            error_log("Error al modificar el producto " . $ex->getMessage());
            $productoModificado = false;
        }
    }
} else {
    $id = filter_input(INPUT_POST, 'id', FILTER_UNSAFE_RAW);
    try {
        $producto = consultaProductoPorId($bd, $id);
    } catch (PDOException $ex) {
        error_log("Error al recuperar información de producto " . $ex->getMessage());
        $productoNoEncontrado = true;
    }
}


if (!(isset($productoModificado) && $productoModificado)) {
    try {
        $familias = consultaFamilias($bd);
    } catch (PDOException $ex) {
        error_log("Error al recuperar información de familias " . $ex->getMessage());
        $familias = [];
    }
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
        <title>Modificar Producto</title>
    </head>
    <body class="bg-info">
        <h3 class="text-center mt-2 font-weight-bold">Modificar Producto</h3>
        <div class="container mt-3">
            <?php if ($productoModificado ?? false): ?>
                <h3 class="text-center mt-2 font-weight-bold">Producto modificado con éxito</h3>
                <a href="index.php" class="btn btn-warning">Volver</a>
            <?php elseif (!($productoModificado ?? true)): ?>
                <h3 class="text-center mt-2 font-weight-bold">Ha habido un problema para modificar el producto</h3>
                <a href="index.php" class="btn btn-warning">Volver</a>
            <?php elseif ($productoNoEncontrado ?? false) : ?>
                <h3 class="text-center mt-2 font-weight-bold">Ha habido un problema para encontrar el producto que se quiere modificar</h3>
                <a href="index.php" class="btn btn-warning">Volver</a>
            <?php else: ?>
                <form method="POST" action="<?= "{$_SERVER['PHP_SELF']}" ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="hidden" name="id" value="<?= $id ?>" >
                            <label for="nombre">Nombre: </label>
                            <input type="text" class="form-control <?= (isset($nombreErr) ? ($nombreErr ? "is-invalid" : "is-valid") : "") ?>" 
                                   id="nombre" placeholder="Nombre" name="nombre"
                                   value="<?= htmlspecialchars($nombre ?? $producto->nombre ?? '', ENT_NOQUOTES, 'UTF-8') ?>" >
                            <div class="col-sm-10 invalid-feedback">
                                <p>Introduce nombre correcto</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="nombre_corto">Nombre Corto: </label>
                            <input type="text" class="form-control <?= (isset($nombreCortoErr) ? (($nombreCortoErr) ? "is-invalid" : "is-valid") : (isset($errorDuplicadoNombreCorto) ? "in-invalid" : "")) ?>"
                                   id="nombre_corto" value = "<?= htmlspecialchars($nombreCorto ?? $producto->nombre_corto ?? '', ENT_NOQUOTES, 'UTF-8') ?>" name="nombre_corto" >
                            <div class="col-sm-10 invalid-feedback">
                                <p><?= isset($errorDuplicadoNombreCorto) ? "Nombre corto duplicado" : "Introduce nombre corto correcto" ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="pvp">Precio (€): </label>
                            <input type="text" class="form-control <?= (isset($pvpErr) ? ($pvpErr ? "is-invalid" : "is-valid") : "") ?>"
                                   id="pvp" value='<?= htmlspecialchars($pvp ?? $producto->pvp ?? '', ENT_NOQUOTES, 'UTF-8') ?>' name="pvp" >
                            <div class="col-sm-10 invalid-feedback">
                                <p>Introduce un precio correcto</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="familia">Familia: </label>
                            <select class="form-control" name="familia_codigo">
                                <?php foreach ($familias as $familia): ?>
                                    <option value='<?= $familia->cod ?>' <?= ($familia->cod == (isset($producto) ? $producto->familia : $familiaCodigo)) ? "selected" : "" ?>><?= $familia->nombre ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9">
                            <label for="descripcion">Descripción: </label>
                            <textarea class="form-control" name="descripcion" id="descripcion" rows="12">
                                <?= htmlspecialchars($descripcion ?? $producto->descripcion ?? '', ENT_NOQUOTES, 'UTF-8') ?>
                            </textarea>
                        </div>
                    </div>
                    <input type="submit" class="btn btn-primary m-3" name="modificar" value="Modificar">
                    <input type="submit" class="btn btn-warning" formaction="index.php" value="Volver" >
                </form>
            <?php endif ?>
        </div>
    </body>
</html>