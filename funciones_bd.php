<?php

/**
 * borrarProducto borra un producto correspondiente a un producto Id de la BD
 * 
 * @param PDO $bd
 * @param string $productoId
 * @return bool
 */
function borrarProducto(PDO $bd, string $productoId): bool {
    $sqlBorrarProducto = "delete from productos where id=:id";
    $stmtBorrarProducto = $bd->prepare($sqlBorrarProducto);
    $resultado = $stmtBorrarProducto->execute([':id' => $productoId]);
    $stmtBorrarProducto = null;
    return ($resultado);
}

/**
 * consultarProductos Obtiene los productos de la BD
 * 
 * @param PDO $bd
 * @return array
 */
function consultarProductos(PDO $bd): array {
    $sqlConsultarProductos = "select id, nombre from productos order by nombre";
    $stmtConsultarProductos = $bd->prepare($sqlConsultarProductos);
    $stmtConsultarProductos->execute();
    $resultado = $stmtConsultarProductos->fetchAll(PDO::FETCH_OBJ);
    $stmtConsultarProductos = null;
    return $resultado;
}

/**
 * consultarProductoPorId Obtiene los datos de un producto concreto de la BD
 * 
 * @param PDO $bd
 * @param string $productoId
 * @return object|false
 */
function consultarProductoPorId(PDO $bd, string $productoId): object|false {
    $sqlConsultarProductoPorId = "select * from productos where id=:i";
    $stmtConsultarProductoPorId = $bd->prepare($sqlConsultarProductoPorId);
    $stmtConsultarProductoPorId->execute([':i' => $productoId]);
    $resultado = $stmtConsultarProductoPorId->fetch(PDO::FETCH_OBJ);
    $stmtConsultarProductoPorId = null;
    return $resultado;
}

/**
 * insertarProducto inserta un producto en la BD
 * 
 * @param PDO $bd
 * @param string $nombre
 * @param string $nombreCorto
 * @param float $pvp
 * @param string $familia
 * @param string $descripcion
 * @return bool
 */
function insertarProducto(PDO $bd, string $nombre, string $nombreCorto, float $pvp,
        string $familia, string $descripcion): bool {
    $sqlInsertarProducto = "insert into productos (nombre, nombre_corto, pvp, familia, descripcion) values(:nombre, :nombre_corto, :pvp, :familia, :descripcion)";
    $stmtInsertarProducto = $bd->prepare($sqlInsertarProducto);
    $resultado = $stmtInsertarProducto->execute([
        ':nombre' => $nombre,
        ':nombre_corto' => $nombreCorto,
        ':pvp' => $pvp,
        ':familia' => $familia,
        ':descripcion' => $descripcion
    ]);
    $stmtInsertarProducto = null;
    return $resultado;
}

/**
 * consultarFamilias Obtiene las familias de la BD
 * 
 * @param PDO $bd
 * @return array
 */
function consultarFamilias(PDO $bd): array {
    $sqlConsultarFamilias = "select * from familias order by nombre";
    $stmtConsultarFamilias = $bd->prepare($sqlConsultarFamilias);
    $stmtConsultarFamilias->execute();
    $resultado = $stmtConsultarFamilias->fetchAll(PDO::FETCH_OBJ);
    $stmtConsultarFamilias = null;
    return $resultado;
}

/**
 * modificarProducto modifica un producto en la BD
 * 
 * @param PDO $bd
 * @param string $nombre
 * @param string $nombreCorto
 * @param float $pvp
 * @param string $familia
 * @param string $descripcion
 * @return bool
 */
function modificarProducto(PDO $bd, string $productoId, string $nombre, string $nombreCorto, float $pvp,
        string $familia, string $descripcion): bool {
    $sqlModificarProducto = "update productos set nombre=:nombre, nombre_corto=:nombre_corto, pvp=:pvp, familia=:familia, descripcion=:descripcion where id=:id";
    $stmtModificarProducto = $bd->prepare($sqlModificarProducto);
    $resultado = $stmtModificarProducto->execute([
        ':nombre' => $nombre,
        ':nombre_corto' => $nombreCorto,
        ':pvp' => $pvp,
        ':familia' => $familia,
        ':descripcion' => $descripcion,
        ':id' => $productoId
    ]);
    $stmtModificarProducto = null;
    return $resultado;
}

/**
 * existeNombreCortoProducto comprueba que el nombre corto no exista
 * 
 * @param PDO $bd
 * @param string $nombreCorto
 * @return bool
 */
function existeNombreCortoProducto(PDO $bd, string $nombreCorto, int $id = null): bool {
    $sqlExisteNombreCortoProducto = "select 1 from productos where nombre_corto = :nombre_corto " . ($id ? 'and id != :id' : '') . " LIMIT 1;";
    $stmtExisteNombreCortoProducto = $bd->prepare($sqlExisteNombreCortoProducto);
    $params = [':nombre_corto' => $nombreCorto];
    if ($id) {
        $params[':id'] = $id;
    }
    $stmtExisteNombreCortoProducto->execute($params);
    $existe = (bool) $stmtExisteNombreCortoProducto->fetch();
    return $existe;
}
