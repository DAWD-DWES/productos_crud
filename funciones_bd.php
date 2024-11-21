<?php
/**
 * borrarProducto borra un producto correspondiente a un producto Id de la BD
 * 
 * @param PDO $bd
 * @param string $productoId
 * @return bool
 */

function borrarProducto(PDO $bd, string $productoId): bool {
    $borrarProducto = "delete from productos where id=:id";
    $stmtBorrarProducto = $bd->prepare($borrarProducto);
    $resultado = $stmtBorrarProducto->execute([':id' => $productoId]);
    $stmtBorrarProducto = null;
    return ($resultado);
}

/**
 * consultaProductos Obtiene los productos de la BD
 * 
 * @param PDO $bd
 * @return array|null
 */

function consultaProductos(PDO $bd): ?array {
    $consultaProductos = "select id, nombre from productos order by nombre";
    $stmtConsultaProductos = $bd->prepare($consultaProductos);
    $stmtConsultaProductos->execute();
    $resultado = $stmtConsultaProductos->fetchAll(PDO::FETCH_OBJ);
    $stmtConsultaProductos = null;
    return $resultado;
}

/**
 * consultaProductoPorId Obtiene los datos de un producto concreto de la BD
 * 
 * @param PDO $bd
 * @param string $productoId
 * @return object|null
 */

function consultaProductoPorId(PDO $bd, string $productoId): ?object {
    $consulta = "select * from productos where id=:i";
    $stmtConsultaProductoPorId = $bd->prepare($consulta);
    $stmtConsultaProductoPorId->execute([':i' => $productoId]);
    $resultado = $stmtConsultaProductoPorId->fetch(PDO::FETCH_OBJ);
    $stmtConsultaProductoPorId = null;
    return $resultado;
}

/**
 * insertaProducto inserta un producto en la BD
 * 
 * @param PDO $bd
 * @param string $nombre
 * @param string $nombreCorto
 * @param float $pvp
 * @param string $familia
 * @param string $descripcion
 * @return bool
 */

function insertaProducto(PDO $bd, string $nombre, string $nombreCorto, float $pvp, string $familia, string $descripcion): bool {
    $insertaProducto = "insert into productos (nombre, nombre_corto, pvp, familia, descripcion) values(:nombre, :nombre_corto, :pvp, :familia, :descripcion)";
    $stmtInsertaProducto = $bd->prepare($insertaProducto);
    $resultado = $stmtInsertaProducto->execute([
        ':nombre' => $nombre,
        ':nombre_corto' => $nombreCorto,
        ':pvp' => $pvp,
        ':familia' => $familia,
        ':descripcion' => $descripcion
    ]);
    $stmtInsertaProducto = null;
    return $resultado;
}

/**
 * consultaFamilias Obtiene las familias de la BD
 * 
 * @param PDO $bd
 * @return type
 */

function consultaFamilias(PDO $bd) {
    $consultaFamilias = "select cod, nombre from familias order by nombre";
    $stmtObtenerFamilias = $bd->prepare($consultaFamilias);
    $stmtObtenerFamilias->execute();
    $resultado = $stmtObtenerFamilias->fetchAll(PDO::FETCH_OBJ);
    $stmtObtenerFamilias = null;
    return $resultado;
}

/**
 * modificaProducto modifica un producto en la BD
 * 
 * @param PDO $bd
 * @param string $nombre
 * @param string $nombreCorto
 * @param float $pvp
 * @param string $familia
 * @param string $descripcion
 * @return bool
 */

function modificaProducto(PDO $bd, string $productoId, string $nombre, string $nombreCorto, float $pvp, string $familia, string $descripcion): bool {
    $modificaProducto = "update productos set nombre=:nombre, nombre_corto=:nombre_corto, pvp=:pvp, familia=:familia, descripcion=:descripcion where id=:id";
    $stmtModificaProducto = $bd->prepare($modificaProducto);
    $resultado = $stmtModificaProducto->execute([
        ':nombre' => $nombre,
        ':nombre_corto' => $nombreCorto,
        ':pvp' => $pvp,
        ':familia' => $familia,
        ':descripcion' => $descripcion,
        ':id' => $productoId
    ]);
    $stmtModificaProducto = null;
    return $resultado;
}
