<?php
// Importar de forma remota parámetros de base de datos
require_once("../config/config.php");
// Comenzar bloque de sesión
session_start();
// Comprobar restricción de área para usuarios anónimos
if (!isset($_SESSION['sesion_personal'])) {
    header("Location: ./iniciar_sesion.php");
    exit();
}
// Determina quién está realizando el registro (ID de Usuario actual)
$id_usuario = (int) $_SESSION['sesion_personal']['id'];
// Verifica mediante bandera de los parámetros si el pedido se hace del carrito (por ende debe vaciarse al finalizar)
$vaciar_carrito = isset($_GET['v']) ? (int)$_GET['v'] : 0;

if (!isset($_GET['datos']) || !is_array($_GET['datos'])) {
    // Si no hay datos, no hay nada que comprar.
    header('Location: ../index.php');
    exit();
}

$arreglo=array(); // arreglo de productos con sus cantidad y id pe [0]=1, 2
// Procesa el paso a paso de cada variable por la que el sistema iteró de la lista de productos
foreach ($_GET['datos'] as $value) {
    // Descompone las partes agrupadas por coma
    $subarreglo=explode(",",$value);
    // Valida que el sub-arreglo tiene 2 elementos numéricos
    if (count($subarreglo) == 2 && is_numeric($subarreglo[0]) && is_numeric($subarreglo[1])) {
        // Apila en el arreglo oficial de productos como enteros
        array_push($arreglo, [(int)$subarreglo[0], (int)$subarreglo[1]]);
    }
}

// Si el arreglo está vacío después de la sanitización, salir.
if (empty($arreglo)) {
    header('Location: ./carrito.php');
    exit();
}

// Instancia la conexión MySQL a la tabla general
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
// Valida ausencia de errores en conexión
if (mysqli_connect_errno()) {
    error_log("Fallo al conectar a MySQL: " . mysqli_connect_error());
    header('Location: ./pantalla_de_compra.php?error=1');
    exit();
}

// Usa transacciones para asegurar la integridad de los datos (o todo o nada)
mysqli_autocommit($con, FALSE);
$error_en_transaccion = false;
$stock_error = false; // Bandera específica para errores de stock

// Genera un código de orden único para la agrupación del carrito
$codigo_orden_actual = "ORD-" . date("YmdHis") . "-" . $id_usuario;

// Desglosa uno por uno el arreglo estructurado a guardar
foreach ($arreglo as $valor) {
    $cantidad_seleccionada = $valor[0];
    $id_producto = $valor[1];

    // 1. Actualiza stock de forma atómica y segura para evitar race conditions y overselling
    $stmt_update = mysqli_prepare($con, "UPDATE producto SET cantidad_disponible = cantidad_disponible - ? WHERE id_producto = ? AND cantidad_disponible >= ?");
    mysqli_stmt_bind_param($stmt_update, "iii", $cantidad_seleccionada, $id_producto, $cantidad_seleccionada);
    mysqli_stmt_execute($stmt_update);

    if (mysqli_stmt_affected_rows($stmt_update) == 0) {
        $error_en_transaccion = true; // No había stock suficiente
        $stock_error = true;
        break;
    }
    mysqli_stmt_close($stmt_update);

    // 2. Registra la compra en el historial
    date_default_timezone_set("America/Mexico_City");
    $fecha_actual = date("Y-m-d");
    $stmt_insert = mysqli_prepare($con, "INSERT INTO historial_compras (id_usuario, id_producto, cantidad_comprada, fecha_compra, codigo_orden, estado) VALUES (?, ?, ?, ?, ?, 'En proceso')");
    mysqli_stmt_bind_param($stmt_insert, "iiiss", $id_usuario, $id_producto, $cantidad_seleccionada, $fecha_actual, $codigo_orden_actual);
    if (!mysqli_stmt_execute($stmt_insert)) {
        $error_en_transaccion = true;
        break;
    }
    mysqli_stmt_close($stmt_insert);
}

// 3. Vacia el carrito del usuario si la compra fue exitosa y se indicó
if (!$error_en_transaccion && $vaciar_carrito) {
    // Elimina únicamente los productos específicos que fueron comprados, conservando los no seleccionados
    $stmt_delete = mysqli_prepare($con, "DELETE FROM carrito WHERE id_usuario = ? AND id_producto = ?");
    foreach ($arreglo as $valor) {
        $id_producto_comprado = $valor[1];
        mysqli_stmt_bind_param($stmt_delete, "ii", $id_usuario, $id_producto_comprado);
        if (!mysqli_stmt_execute($stmt_delete)) {
            $error_en_transaccion = true;
            break;
        }
    }
    mysqli_stmt_close($stmt_delete);
}

// Finaliza la transacción: confirmar si todo fue bien, o revertir si hubo un error
if ($error_en_transaccion) {
    mysqli_rollback($con);
    if ($stock_error) {
        header('Location: ./carrito.php?error=stock_insuficiente');
    } else {
        header('Location: ./carrito.php?error=compra_fallida');
    }
} else {
    mysqli_commit($con);
    header('Location: ./historial_individual.php');
}

mysqli_close($con);
exit();
