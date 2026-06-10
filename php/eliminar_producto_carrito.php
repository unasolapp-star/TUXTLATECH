<?php
// Importa el archivo de configuración con las credenciales de la base de datos
require_once("../config/config.php");
// Inicia o reanuda la sesión actual para gestionar las variables globales del usuario
session_start();

// Verifica si la variable de sesión no existe para detectar usuarios sin autenticar
if (!isset($_SESSION['sesion_personal'])) {
    // Redirige al visitante hacia la página de inicio de sesión si carece de credenciales
    header("Location: ./iniciar_sesion.php");
    // Finaliza la ejecución del script para evitar procesamientos no autorizados tras la redirección
    exit();
}

// Verifica que el parámetro identificador del carrito haya sido enviado y tenga un formato numérico
if (!isset($_GET['id_carrito']) || !is_numeric($_GET['id_carrito'])) {
    // Redirige al usuario de vuelta al carrito de compras si no se cumple la validación del identificador
    header('Location: ./carrito.php');
    // Finaliza la ejecución del script por falta de parámetros válidos
    exit();
}

// Captura y convierte estrictamente a entero el identificador del carrito desde el parámetro GET
$id_carrito = (int) $_GET['id_carrito'];
// Extrae el identificador numérico del usuario desde la sesión actual
$id_usuario = (int) $_SESSION['sesion_personal']['id'];

// Establece la conexión con el servidor MySQL mediante los parámetros configurados
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
    
// Evalúa si ocurrió algún error durante el intento de conexión a la base de datos
if (mysqli_connect_errno()) {
    // Registra internamente el error de conexión en el servidor sin exponer detalles al usuario final
    error_log("Fallo al conectar a MySQL: " . mysqli_connect_error());
    // Redirige al carrito de compras adjuntando un parámetro de error en la URL
    header('Location: ./carrito.php?error=dberror');
    // Finaliza la ejecución del script debido al fallo de conexión
    exit();
}

// Construye la consulta de eliminación asegurando que el artículo pertenezca al usuario actual
$query = "DELETE FROM carrito WHERE id_carrito = ? AND id_usuario = ?";

// Prepara la consulta de borrado de forma segura en la base de datos
if ($stmt = mysqli_prepare($con, $query)) {
    // Enlaza los parámetros enteros correspondientes al carrito y al usuario en la sentencia preparada
    mysqli_stmt_bind_param($stmt, "ii", $id_carrito, $id_usuario);
    // Ejecuta la sentencia para remover permanentemente el artículo seleccionado
    mysqli_stmt_execute($stmt);
    // Cierra la declaración preparada liberando los recursos asociados
    mysqli_stmt_close($stmt);
}

// Cierra la conexión activa con la base de datos
mysqli_close($con);

// Redirige al usuario de vuelta hacia la interfaz actualizada del carrito de compras
header('Location: ./carrito.php');
// Finaliza el flujo de ejecución de manera limpia tras realizar la redirección
exit();