<?php
if (!(filter_has_var(INPUT_GET, 'pet_crear') || filter_has_var(INPUT_POST, 'crear'))) {
    header('Location:index.php');
    die;
}
require_once 'error_handler.php';
require_once 'funciones_bd.php';
$bd = require_once 'conexion.php';

if (isset($_REQUEST['crear'])) {
//recogemos los datos del formulario
    $nombre = ucwords(trim(filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW)));
    $nombreValido = $nombre && (filter_var($nombre, FILTER_VALIDATE_REGEXP,
                    ['options' => ['regexp' => "/^[\w\s\-_]{2,100}$/"]]) !== false);
    $nombreCorto = strtoupper(trim(filter_input(INPUT_POST, 'nombre_corto', FILTER_UNSAFE_RAW)));
    $nombreCortoValido = $nombreCorto && (filter_var($nombreCorto, FILTER_VALIDATE_REGEXP,
                    ['options' => ['regexp' => "/^[a-zA-Z0-9]{2,15}$/"]]) !== false);
    $pvp = filter_input(INPUT_POST, 'pvp', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $pvpValido = $pvp && (filter_var($pvp, FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0]]) !== false);
    $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_UNSAFE_RAW));
    $familiaCodigo = filter_input(INPUT_POST, 'familia_codigo', FILTER_UNSAFE_RAW);
    if ($nombreValido === false || $nombreCortoValido === false || $pvpValido === false) {
        $error = true;
    } else {
        try {
            $productoInsertado = insertaProducto($bd, $nombre, $nombreCorto, $pvp, $familiaCodigo, $descripcion);
        } catch (PDOException $ex) {
            if ($ex->getcode() == 23000) { // Clave duplicada
                $errorDuplicadoNombreCorto = true;
            }
            error_log("Error al crear el producto " . $ex->getMessage());
            $productoInsertado = false;
        }
    }
}
if (!(isset($productoInsertado) && $productoInsertado)) {
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
        <title>Crear Producto</title>
    </head>
    <body class="bg-info">
        <h3 class="text-center mt-2 font-weight-bold">Crear Producto</h3>
        <div class="container mt-3">
            <?php if (isset($productoInsertado) && $productoInsertado): ?>
                <h3 class="text-center mt-2 font-weight-bold">Producto creado con éxito</h3>
                <a href="index.php" class="btn btn-warning">Volver</a>
            <?php elseif (isset($productoInsertado) && !$productoInsertado && !isset($errorDuplicadoNombreCorto)): ?>
                <h3 class="text-center mt-2 font-weight-bold">Ha habido un problema para crear el producto</h3>
                <a href="index.php" class="btn btn-warning">Volver</a>
            <?php else: ?>
                <form name="crear" method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre: </label>
                            <input type="text" class="form-control <?= (isset($nombreValido) ? ($nombreValido ? "is-valid" : "is-invalid") : "") ?>" id="nombre" placeholder="Nombre"
                                   name="nombre" value="<?= (isset($nombre) ? htmlspecialchars($nombre, ENT_NOQUOTES, 'UTF-8') : '') ?>">
                            <div class="col-sm-10 invalid-feedback">
                                <p>Introduce nombre correcto</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="nombre_corto" class="form-label">Nombre Corto: </label>
                            <input type="text" class="form-control <?= (isset($errorDuplicadoNombreCorto) ? "is-invalid" : (isset($nombreCortoValido) ? ($nombreCortoValido ? "is-valid" : "is-invalid") : "")) ?>" id="nombre_corto" placeholder="Nombre Corto"
                                   name="nombre_corto" value="<?= (isset($nombreCorto) ? htmlspecialchars($nombreCorto, ENT_NOQUOTES, 'UTF-8') : '') ?>">
                            <div class="col-sm-10 invalid-feedback">
                                <p><?= (isset($errorDuplicadoNombreCorto) && $errorDuplicadoNombreCorto) ? "Nombre corto duplicado" : "Introduce nombre corto correcto" ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="pvp" class="form-label">Precio (€): </label>
                            <input type="number" class="form-control <?= (isset($pvpValido) ? ($pvpValido ? "is-valid" : "is-invalid") : "") ?>" id="pvp" placeholder="Precio (€)"
                                   name="pvp" min="0" step="0.01" value="<?= (isset($pvp) ? htmlspecialchars($pvp, ENT_NOQUOTES, 'UTF-8') : '') ?>">
                            <div class="col-sm-10 invalid-feedback">
                                <p>Introduce un precio correcto</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="familia" class="form-label">Familia: </label>
                            <select id="familia" class="form-control" name="familia_codigo">
                                <?php foreach ($familias as $familia): ?>
                                    <option value='<?= $familia->cod ?>' <?= (isset($familiaCodigo) && $familia->cod == $familiaCodigo) ? "selected" : "" ?>><?= $familia->nombre ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-9">
                            <label for="descripcion" class="form-label">Descripción: </label>
                            <textarea class="form-control" name="descripcion" id="d" rows="12"> <?= (isset($descripcion) ? htmlspecialchars($descripcion, ENT_NOQUOTES, 'UTF-8') : '') ?></textarea>
                        </div>
                    </div>
                    <input type="submit" class="btn btn-primary m-3" name="crear" value="Crear">
                    <input type="reset" value="Limpiar" class="btn btn-success m-3" onclick="this.querySelectorAll('input[type=text]').forEach(function (input, i) {
                                    input.value = '';
                                })">
                    <!-- <input type="reset" value="Limpiar" class="btn btn-success mr-3"> -->
                    <a href="index.php" class="btn btn-warning">Volver</a>
                </form>
            <?php endif ?>
        </div>
    </body>
</html>


