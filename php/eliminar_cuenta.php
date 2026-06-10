<?php
// Importa el archivo de configuración con las credenciales de conexión a la base de datos
require_once("../config/config.php");
// Inicia o reanuda la sesión actual para gestionar las variables globales del usuario
session_start();

// Verifica si la variable de sesión no existe para confirmar si hay un usuario autenticado
if (!isset($_SESSION['sesion_personal'])) {
    // Redirige al visitante hacia la página de inicio de sesión si carece de credenciales
    header("Location: ./iniciar_sesion.php");
    // Finaliza la ejecución del script para evitar procesamientos no autorizados
    exit();
}

// Extrae el identificador numérico del usuario desde la sesión actual
$id_usuario = (int) $_SESSION['sesion_personal']['id'];

// Establece la conexión con el servidor de base de datos utilizando los parámetros configurados
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);

// Evalúa que la conexión se haya realizado con éxito sin devolver errores
if (!mysqli_connect_errno()) {
    // Construye la consulta para desvincular el identificador del usuario de su historial de compras
    // Asigna el valor nulo (NULL) para mantener intactas las estadísticas financieras generales de la tienda
    $query_historial = "UPDATE historial_compras SET id_usuario = NULL WHERE id_usuario = ?";
    // Prepara la consulta de actualización de forma segura en la base de datos
    if ($stmt_hist = mysqli_prepare($con, $query_historial)) {
        // Enlaza el parámetro entero del identificador del usuario a la sentencia preparada
        mysqli_stmt_bind_param($stmt_hist, "i", $id_usuario);
        // Ejecuta la sentencia para aplicar los cambios sobre el historial
        mysqli_stmt_execute($stmt_hist);
        // Cierra la declaración preparada liberando los recursos asociados
        mysqli_stmt_close($stmt_hist);
    }

    // Construye la consulta de eliminación para borrar de forma definitiva el registro del usuario
    $query_borrar = "DELETE FROM usuario WHERE id_usuario = ?";
    // Prepara la consulta de borrado de forma segura en la base de datos
    if ($stmt_borrar = mysqli_prepare($con, $query_borrar)) {
        // Enlaza el parámetro entero del identificador del usuario a borrar
        mysqli_stmt_bind_param($stmt_borrar, "i", $id_usuario);
        // Ejecuta la sentencia que remueve permanentemente a la persona de la tabla de usuarios
        mysqli_stmt_execute($stmt_borrar);
        // Cierra la declaración de borrado liberando la memoria empleada
        mysqli_stmt_close($stmt_borrar);
    }
    // Cierra la conexión activa con la base de datos
    mysqli_close($con);
}

// Destruye completamente la sesión activa y elimina todos los datos vinculados a ella
session_destroy();
// Redirige al cliente de vuelta hacia la página principal (index) de la tienda
header("Location: ../index.php");
// Finaliza el flujo de ejecución de manera limpia tras realizar la redirección
exit();
?>