<?php
// Importa el archivo de configuración que contiene las credenciales de la base de datos
require_once("../config/config.php");
// Inicia o reanuda la sesión actual para tener acceso a las variables globales del usuario
session_start();
// Verifica si la variable de sesión personal está ausente, indicando que el usuario no está autenticado
if (!isset($_SESSION['sesion_personal'])) {
    // Redirige el flujo de la aplicación hacia la página de inicio de sesión
    header("Location: ./iniciar_sesion.php");
    // Detiene la ejecución del script para evitar que se procese código adicional de forma no autorizada
    exit;
}

// Captura el parámetro GET correspondiente al ID del producto y lo convierte estrictamente a un valor entero
$id_producto = (int)$_GET["id_producto"];
// Captura el parámetro GET correspondiente a la cantidad deseada y lo convierte estrictamente a un valor entero
$cantidad_seleccionada = (int)$_GET["cantidad"];
// Extrae el ID del usuario directamente desde la información almacenada en la sesión actual y lo convierte a entero
$id_usuario = (int)$_SESSION['sesion_personal']['id'];

// Establece la conexión con la base de datos utilizando los parámetros previamente importados
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
    
// Verifica si ocurrió algún error durante el intento de conexión a la base de datos
if (mysqli_connect_errno()) {
    // Finaliza la ejecución del script y muestra un mensaje descriptivo con el error de conexión
    die("Fallo al conectar a MySQL: " . mysqli_connect_error());
}

// Construye la consulta SQL para verificar si el usuario ya tiene este producto específico en su carrito
$query_check = "SELECT id_carrito, cantidad_seleccionada FROM carrito WHERE id_producto = $id_producto AND id_usuario = $id_usuario";
// Ejecuta la consulta de verificación sobre la base de datos
$res_check = mysqli_query($con, $query_check);

// Evalúa si la consulta de comprobación fue exitosa y si devolvió al menos un registro coincidente
if ($res_check && mysqli_num_rows($res_check) > 0) {
    // Extrae la fila de resultados obtenida interpretándola como un arreglo asociativo
    $row = mysqli_fetch_assoc($res_check);
    // Calcula la suma de la cantidad previamente almacenada y la nueva cantidad solicitada
    $nueva_cantidad = $row['cantidad_seleccionada'] + $cantidad_seleccionada;
    // Ejecuta una consulta de actualización para reflejar la nueva cantidad acumulada en el carrito
    mysqli_query($con, "UPDATE carrito SET cantidad_seleccionada = $nueva_cantidad WHERE id_carrito = " . $row['id_carrito']);
} else {
    // Ejecuta una consulta de inserción para registrar el producto como un nuevo elemento temporal en el carrito
    mysqli_query($con, "INSERT INTO carrito (id_producto, id_usuario, cantidad_seleccionada) VALUES ($id_producto, $id_usuario, $cantidad_seleccionada)");
}

// Cierra la conexión activa con la base de datos para liberar recursos
mysqli_close($con);
// Redirige al usuario hacia la página principal visual de su carrito de compras
header('Location: ./carrito.php');
// Termina la ejecución del script de manera limpia tras la redirección
exit;
?>
