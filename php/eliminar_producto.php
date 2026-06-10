<?php
// Importa el archivo de configuración con las credenciales de conexión a la base de datos
require_once("../config/config.php");
// Inicia o reanuda la sesión actual para gestionar el control de acceso
session_start();

// Verifica si la sesión no existe o si el usuario carece de privilegios de administrador
if (!isset($_SESSION['sesion_personal']) || $_SESSION['sesion_personal']['super'] != 1) {
    // Redirige al visitante hacia la página de inicio de sesión por falta de autorización
    header("Location: ./iniciar_sesion.php");
    // Finaliza la ejecución del script para bloquear cualquier procesamiento posterior
    exit();
}

// Obtiene y convierte estrictamente a entero el identificador del producto enviado por parámetro GET
$id_producto = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Evalúa si el identificador extraído es un número válido mayor a cero
if ($id_producto > 0) {
    // Establece la conexión con el servidor MySQL mediante los parámetros configurados
    $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
    
    // Comprueba que la conexión a la base de datos se haya realizado exitosamente
    if (!mysqli_connect_errno()) {
        // Ejecuta la instrucción de borrado definitivo del artículo correspondiente en el catálogo
        mysqli_query($con, "DELETE FROM producto WHERE id_producto = $id_producto;");
        // Cierra la conexión activa con la base de datos para liberar los recursos
        mysqli_close($con);
    }
}

// Redirige al administrador de regreso a la interfaz de gestión del inventario
header('Location: ./modificar_productos.php');
// Finaliza la ejecución de forma limpia al terminar el redireccionamiento
exit();
?>