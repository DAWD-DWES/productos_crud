<?php
if (!(filter_has_var(INPUT_POST, 'pet_modificar') || filter_has_var(INPUT_POST, 'modificar'))) {
    header('Location:index.php');
    die;
}
require_once 'error_handler.php';
require_once 'funciones_bd.php';
$bd = require_once 'conexion.php';

define('NOMBRE_INVALIDO', '**Nombre inválido');
define('NOMBRE_CORTO_INVALIDO', '**Nombre corto inválido');
define('NOMBRE_CORTO_DUPLICADO', '**Nombre corto duplicado');
define('PVP_INVALIDO', '**PVP inválido');
define('DESCRIPCION_INVALIDO', '**Descripción inválida');

define("REGEXP_NOMBRE", "/^[\w\s\-_áéíóúñ.,;:!?'(){}[\]+]{2,100}$/");
define("REGEXP_NOMBRE_CORTO", "/^[\w\s\-_áéíóúñ.,;:!?'(){}[\]]{2,15}$/");
define("REGEXP_DESCRIPCION", "/^[\s\S]*$/");

if (filter_has_var(INPUT_POST, 'modificar')) {
//recogemos los datos del formulario
    $id = filter_input(INPUT_POST, 'id', FILTER_UNSAFE_RAW);
    $nombre = ucwords(trim(filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW)));
    $nombreErr = filter_var($nombre, FILTER_VALIDATE_REGEXP,
                    ['options' => ['regexp' => REGEXP_NOMBRE]]) === false;
    $nombreCorto = strtoupper(trim(filter_input(INPUT_POST, 'nombre_corto', FILTER_UNSAFE_RAW)));
    $nombreCortoErr = filter_var($nombreCorto, FILTER_VALIDATE_REGEXP,
                    ['options' => ['regexp' => REGEXP_NOMBRE_CORTO]]) === false;
    $pvp = filter_input(INPUT_POST, 'pvp', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $pvpErr = filter_var($pvp, FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0]]) === false;
    $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_UNSAFE_RAW));
    $descripcionErr = filter_var($descripcion, FILTER_VALIDATE_REGEXP,
                    ['options' => ['regexp' => REGEXP_DESCRIPCION]]) === false;
    // $descripcionErr = false;
    $familiaCodigo = filter_input(INPUT_POST, 'familia_codigo', FILTER_UNSAFE_RAW);
    $error = array_sum(compact(["nombreErr", "nombreCortoErr", "pvpErr", "descripcionErr"])) > 0;
    if (!$error) {
        try {
            $productoModificado = modificarProducto($bd, $id, $nombre, $nombreCorto, $pvp, $familiaCodigo, $descripcion);
        } catch (PDOException $ex) {
            if ($ex->getcode() == 23000) { // Clave duplicada
                $errorDuplicadoNombreCorto = true;
            }
            error_log("Error al crear el producto " . $ex->getMessage());
            $productoModificado = false;
        }
    }
} else {
    $id = filter_input(INPUT_POST, 'id', FILTER_UNSAFE_RAW);
    try {
        $producto = consultarProductoPorId($bd, $id);
    } catch (PDOException $ex) {
        error_log("Error al recuperar información de producto " . $ex->getMessage());
        $productoEncontrado = false;
    }
}


if (!($productoModificado ?? false)) {
    try {
        $familias = consultarFamilias($bd);
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
        <h3 class="text-center mt-2 fw-bold">Modificar Producto</h3>
        <div class="container mt-3">
            <?php if ($productoModificado ?? false): ?>
                <h3 class="text-center mt-2 fw-bold">Producto modificado con éxito</h3>
                <a href="index.php" class="btn btn-warning">Volver</a>
            <?php elseif (!isset($errorDuplicadoNombreCorto) && !($productoModificado ?? true)): ?>
                <h3 class="text-center mt-2 fw-bold">Ha habido un problema para modificar el producto</h3>
            <?php else: ?>
                <form method="POST" action="<?= "{$_SERVER['PHP_SELF']}" ?>">
                    <div class="row g-3">
                        <div class="col-md-6 align-items-center mb-3">
                            <input type="hidden" name="id" value="<?= $id ?>" >
                            <label for="nombre">Nombre: </label>
                            <input type="text" class="form-control <?= (isset($nombreErr) ? (($nombreErr) ? "is-invalid" : "is-valid") : "") ?>" 
                                   id="nombre" placeholder="Nombre" name="nombre"
                                   value="<?= htmlspecialchars($nombre ?? $producto->nombre ?? '', ENT_NOQUOTES, 'UTF-8') ?>" >
                            <div class="invalid-feedback">
                                <p><?= NOMBRE_INVALIDO ?></p>
                            </div>
                        </div>
                        <div class="col-md-6 align-items-center mb-3">
                            <label for="nombre_corto align-items-center">Nombre Corto</label>
                            <input type="text" class="form-control <?= (isset($nombreCortoErr) ? (($nombreCortoErr || ($errorDuplicadoNombreCorto ?? false)) ? "is-invalid" : "is-valid") : "") ?>"
                                   id="nombre_corto" placeholder="Nombre corto" name="nombre_corto"
                                   value = "<?= htmlspecialchars($nombreCorto ?? $producto->nombre_corto ?? '', ENT_NOQUOTES, 'UTF-8') ?>" >
                            <div class="invalid-feedback">
                                <p><?=
                                    match (true) {
                                        $nombreCortoErr ?? false => NOMBRE_CORTO_INVALIDO,
                                        $errorDuplicadoNombreCorto ?? false => NOMBRE_CORTO_DUPLICADO,
                                        default => ''
                                    }
                                    ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 align-items-center mb-3">
                            <label for="pvp">Precio (€)</label>
                            <input type="text" class="form-control <?= (isset($pvpErr) ? (($pvpErr) ? "is-invalid" : "is-valid") : "") ?>"
                                   id="pvp" placeholder="PVP" name="pvp"
                                   value="<?= htmlspecialchars($pvp ?? $producto->pvp ?? '', ENT_NOQUOTES, 'UTF-8') ?>" >
                            <div class="invalid-feedback">
                                <p><?= PVP_INVALIDO ?></p>
                            </div>
                        </div>
                        <div class="col-md-6 align-items-center mb-3">
                            <label for="familia">Familia: </label>
                            <select class="form-control" name="familia_codigo">
                                <?php foreach ($familias as $familia): ?>
                                    <option value='<?= $familia->cod ?>' <?= ($familia->cod == (isset($producto) ? $producto->familia : $familiaCodigo)) ? "selected" : "" ?>>
                                        <?= $familia->nombre ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-9 align-items-center mb-3">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control <?= (isset($descripcionErr) ? (($descripcionErr) ? "is-invalid" : "is-valid") : "") ?>"
                                      id="descripcion" name="descripcion" placeholder="Descripción" rows="12" >
                                          <?= htmlspecialchars($descripcion ?? $producto->descripcion ?? '', ENT_NOQUOTES, 'UTF-8') ?>
                            </textarea>
                            <div class="invalid-feedback">
                                <p><?= DESCRIPCION_INVALIDO ?></p>
                            </div>
                        </div>
                    </div>
                    <input type="submit" class="btn btn-primary m-3" name="modificar" value="Modificar">
                    <input type="submit" class="btn btn-warning" formaction="index.php" value="Volver" >
                </form>
            <?php endif ?>
        </div>
    </body>
</html>