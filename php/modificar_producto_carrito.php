<?php
/* 1. IMPORTACIÓN DE CONFIGURACIÓN Y SESIÓN */

// Importa el archivo de configuración con las credenciales de la base de datos
require_once("../config/config.php");
// Inicia o reanuda la sesión actual para gestionar el control de acceso del usuario
session_start();

/* 2. SEGURIDAD: Prevenir acceso no autorizado */

// Verifica si la variable de sesión no existe para detectar usuarios sin autenticar
if (!isset($_SESSION['sesion_personal'])) {
    // Redirige al visitante hacia la página de inicio de sesión si carece de credenciales
    header("Location: ./iniciar_sesion.php");
    // Finaliza la ejecución del script para evitar procesamientos no autorizados tras la redirección
    exit();
}

/* 3. CAPTURA Y VALIDACIÓN DE PARÁMETROS GET */

// Captura y sanitiza la acción solicitada (1 para sumar, 0 para restar), asignando -1 si es inválida
$signo = isset($_GET['signo']) ? (int)$_GET['signo'] : -1;
// Captura y sanitiza el identificador único del carrito convirtiéndolo a entero
$id_carrito = isset($_GET['id_carrito']) ? (int)$_GET['id_carrito'] : 0;
// Captura y sanitiza el inventario físico disponible del producto en el catálogo
$disponibles = isset($_GET['disp']) ? (int)$_GET['disp'] : 0;
// Captura y sanitiza la cantidad de unidades que actualmente posee el usuario en su carrito
$cantidad = isset($_GET['cant']) ? (int)$_GET['cant'] : 0;
// Extrae el identificador numérico del usuario directamente desde la sesión actual
$id_usuario = (int)$_SESSION['sesion_personal']['id'];

// Evalúa que los parámetros obligatorios posean valores lógicos y permitidos
if ($id_carrito <= 0 || ($signo !== 0 && $signo !== 1)) {
    // Redirige de vuelta al carrito en caso de detectar manipulación indebida en los parámetros
    header('Location: carrito.php');
    // Interrumpe la ejecución del script para proteger la integridad de los datos
    exit();
}

// Evalúa si la intención de suma excede el stock físicamente disponible en la tienda
if ((($cantidad + 1) > $disponibles) && ($signo == 1)) {
    // Redirige al carrito adjuntando un parámetro de error por límite de inventario superado
    header('Location: carrito.php?error=stock');
    // Detiene la ejecución para prevenir sobreventa del artículo
    exit();
}

/* 4. CONEXIÓN Y PROCESAMIENTO EN BASE DE DATOS */

// Establece la conexión con el servidor MySQL mediante los parámetros configurados
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
// Evalúa si ocurrió algún error durante el intento de conexión a la base de datos
if (mysqli_connect_errno()) {
    // Registra internamente el error de conexión en el servidor sin exponer detalles al cliente
    error_log("Fallo al conectar a MySQL: " . mysqli_connect_error());
    // Redirige al usuario hacia el carrito notificando sobre un fallo general del sistema
    header('Location: ./carrito.php?error=dberror');
    // Finaliza el flujo de ejecución debido a la imposibilidad de operar en la base de datos
    exit();
}

// Comprueba si la acción solicitada es restar y la cantidad resultante equivaldría a cero
if ((($cantidad - 1) == 0) && ($signo == 0)) {
    // Construye la consulta de borrado asegurando que el artículo coincida y pertenezca al usuario
    $query = "DELETE FROM carrito WHERE id_carrito = ? AND id_usuario = ?";
    // Prepara la instrucción SQL de eliminación de forma segura
    if ($stmt = mysqli_prepare($con, $query)) {
        // Enlaza los identificadores del carrito y usuario como parámetros enteros a la sentencia
        mysqli_stmt_bind_param($stmt, "ii", $id_carrito, $id_usuario);
        // Ejecuta la sentencia que descarta permanentemente el registro de la tabla temporal
        mysqli_stmt_execute($stmt);
        // Cierra la declaración preparada liberando los recursos de la base de datos
        mysqli_stmt_close($stmt);
    }
} else {
    // Calcula el nuevo volumen asignando un incremento si el signo es 1, o un decremento si es 0
    $nueva_cantidad = ($signo == 1) ? ($cantidad + 1) : ($cantidad - 1);
    // Construye la consulta de actualización limitando el impacto al usuario autenticado
    $query = "UPDATE carrito SET cantidad_seleccionada = ? WHERE id_carrito = ? AND id_usuario = ?";
    // Prepara la instrucción SQL de modificación de forma segura
    if ($stmt = mysqli_prepare($con, $query)) {
        // Enlaza la nueva cantidad calculada y las llaves de identificación a la sentencia
        mysqli_stmt_bind_param($stmt, "iii", $nueva_cantidad, $id_carrito, $id_usuario);
        // Ejecuta la modificación reescribiendo la cantidad almacenada en el carrito
        mysqli_stmt_execute($stmt);
        // Cierra la declaración de actualización liberando el espacio en memoria
        mysqli_stmt_close($stmt);
    }
}

/* 5. CIERRE DE CONEXIÓN Y REDIRECCIÓN */

// Cierra la conexión activa con la base de datos
mysqli_close($con);
// Retorna al usuario a la interfaz del carrito de compras para que visualice los cambios
header('Location: ./carrito.php');
// Garantiza una terminación limpia del proceso general
exit();