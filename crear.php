<?php
if (!(filter_has_var(INPUT_GET, 'pet_crear') || filter_has_var(INPUT_POST, 'crear'))) {
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

if (filter_has_var(INPUT_POST, 'crear')) {
//recogemos los datos del formulario
    $nombre = ucwords(trim(filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW)));
    $nombreErr = filter_var($nombre, FILTER_VALIDATE_REGEXP,
                    ['options' => ['regexp' => "/^[\w\s\-_áéíóúñ]{2,100}$/"]]) === false;
    $nombreCorto = strtoupper(trim(filter_input(INPUT_POST, 'nombre_corto', FILTER_UNSAFE_RAW)));
    $nombreCortoErr = filter_var($nombreCorto, FILTER_VALIDATE_REGEXP,
                    ['options' => ['regexp' => "/^[a-zA-Z0-9áéíóúñ]{2,15}$/"]]) === false;
    $errorDuplicadoNombreCorto = false;
    if (!$nombreCortoErr) {
        try {
            $errorDuplicadoNombreCorto = existeNombreCortoProducto($bd, $nombreCorto);
        } catch (PDOException $ex) {
            error_log("Error al comprobar el nombre corto " . $ex->getMessage());
            $errorDuplicadoNombreCorto = true;
        }
    }
    $pvp = filter_input(INPUT_POST, 'pvp', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $pvpErr = filter_var($pvp, FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0]]) === false;
    $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_UNSAFE_RAW));
    $descripcionErr = filter_var($descripcion, FILTER_VALIDATE_REGEXP,
                    ['options' => ['regexp' => "/^[\w\s\-_áéíóúñ]{0,200}$/"]]) === false;
    $familiaCodigo = filter_input(INPUT_POST, 'familia_codigo', FILTER_UNSAFE_RAW);
    $error = array_sum(compact(["nombreErr", "nombreCortoErr", "pvpErr", "descripcionErr", "errorDuplicadoNombreCorto"])) > 0;
    if (!$error) {
        try {
            $productoInsertado = insertarProducto($bd, $nombre, $nombreCorto, $pvp, $familiaCodigo, $descripcion);
        } catch (PDOException $ex) {
            error_log("Error al crear el producto " . $ex->getMessage());
            $productoInsertado = false;
        }
    }
}
if ($error ?? true) {
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
        <title>Crear Producto</title>
    </head>
    <body class="bg-info">
        <h3 class="text-center mt-2 fw-bold">Crear Producto</h3>
        <div class="container mt-3">
            <?php if ($productoInsertado ?? false): ?>
                <h3 class="text-center mt-2 fw-bold">Producto creado con éxito</h3>
                <a href="index.php" class="btn btn-warning">Volver</a>
            <?php elseif (!($productoInsertado ?? true)): ?>
                <h3 class="text-center mt-2 fw-bold">Ha habido un problema para crear el producto</h3>
            <?php else: ?>
                <form name="crear" method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
                    <div class="row g-3">
                        <div class="col-md-6 align-items-center mb-3">
                            <label for="nombre" class="form-label">Nombre: </label>
                            <input type="text" class="form-control <?= (isset($nombreErr) ? (($nombreErr) ? "is-invalid" : "is-valid") : "") ?>" 
                                   id="nombre" placeholder="Nombre" name="nombre" 
                                   value="<?= htmlspecialchars($nombre ?? '', ENT_NOQUOTES, 'UTF-8') ?>">
                            <div class="invalid-feedback">
                                <p><?= NOMBRE_INVALIDO ?></p>
                            </div>
                        </div>
                        <div class="col-md-6 align-items-center mb-3">
                            <label for="nombre_corto" class="form-label">Nombre Corto: </label>
                            <input type="text" class="form-control <?= (isset($nombreCortoErr) ? (($errorDuplicadoNombreCorto ?? $nombreCortoErr ?? false) ? "is-invalid" : "is-valid") : "") ?>" 
                                   id="nombre_corto" placeholder="Nombre Corto"
                                   name="nombre_corto" value="<?= htmlspecialchars($nombreCorto ?? '', ENT_NOQUOTES, 'UTF-8') ?>">
                            <div class="invalid-feedback">
                                <p><?= isset($errorDuplicadoNombreCorto) ? NOMBRE_CORTO_DUPLICADO : (($nombreCortoErr ?? true) ? NOMBRE_CORTO_INVALIDO : '') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 align-items-center mb-3">
                            <label for="pvp" class="form-label">Precio (€): </label>
                            <input type="number" class="form-control <?= (isset($pvpErr) ? ($pvpErr ? "is-invalid" : "is-valid") : "") ?>" id="pvp" placeholder="Precio (€)"
                                   name="pvp" min="0" step="0.01" value="<?= htmlspecialchars($pvp ?? '', ENT_NOQUOTES, 'UTF-8') ?>">
                            <div class="invalid-feedback">
                                <p><?= PVP_INVALIDO ?></p>
                            </div>
                        </div>
                        <div class="col-md-6 align-items-center mb-3">
                            <label for="familia" class="form-label">Familia: </label>
                            <select id="familia" class="form-control" name="familia_codigo">
                                <?php foreach ($familias as $familia): ?>
                                    <option value='<?= $familia->cod ?>' 
                                            <?= (isset($familiaCodigo) && $familia->cod == $familiaCodigo) ? "selected" : "" ?>>
                                        <?= $familia->nombre ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-9">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control <?= (isset($descripcionErr) ? (($descripcionErr) ? "is-invalid" : "is-valid") : "") ?>"
                                      id="descripcion" name="descripcion" placeholder="Descripción" rows="12" >
                                          <?= htmlspecialchars($descripcion ?? '', ENT_NOQUOTES, 'UTF-8') ?>
                            </textarea>
                            <div class="invalid-feedback">
                                <p><?= DESCRIPCION_INVALIDO ?></p>
                            </div>
                        </div>
                    </div>
                    <input type="submit" class="btn btn-primary m-3" name="crear" value="Crear">
                    <input type="reset" value="Limpiar" class="btn btn-success me-3">
                    <a href="index.php" class="btn btn-warning">Volver</a>
                </form>
            <?php endif ?>
        </div>
    </body>
</html>
