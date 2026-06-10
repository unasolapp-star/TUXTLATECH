<?php
/* 1. IMPORTACIÓN DE CONFIGURACIÓN Y SESIÓN */

// Importa el archivo de configuración con las credenciales de la base de datos
require_once("../config/config.php");
// Inicia o reanuda la sesión actual para el manejo de variables globales
session_start();

/* 2. SEGURIDAD: Prevenir acceso no autorizado */

// Verifica si la variable de sesión no existe para detectar usuarios sin autenticar
if (!isset($_SESSION['sesion_personal'])) {
    // Redirige al visitante hacia la página de inicio de sesión
    header("Location: ./iniciar_sesion.php");
    // Finaliza la ejecución del script para evitar que continúe el procesamiento
    exit;
}

/* 3. LIMPIEZA DE VARIABLES DE SESIÓN TEMPORALES */

// Elimina la variable temporal de la sesión para liberar memoria y evitar posibles conflictos
unset($_SESSION['sesion_personal']['id_producto']);

/* 4. RECOLECCIÓN DE DATOS DEL FORMULARIO */

// Captura el nombre del nuevo producto enviado mediante el método POST
$nombre_producto      = $_POST['nombre_producto'];
// Captura la descripción detallada del nuevo producto
$descripcion_producto = $_POST['descripcion_producto'];
// Captura la cantidad de unidades en existencia iniciales del producto
$cantidad_disponible  = $_POST['cantidad_disponible'];
// Captura el precio unitario establecido para el producto
$precio_producto      = $_POST['precio_producto'];
// Captura el nombre del fabricante o marca del producto
$fabricante           = $_POST['fabricante'];
// Captura el país o lugar de origen del producto
$origen               = $_POST['origen'];
// Captura la categoría a la cual pertenecerá el producto
$categoria            = $_POST['categoria'];

/* 5. CONEXIÓN A LA BASE DE DATOS */

// Establece la conexión con el servidor MySQL mediante los parámetros configurados
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
// Evalúa si ocurrió algún error durante el intento de conexión
if (mysqli_connect_errno()) {
    // Interrumpe la ejecución del script y muestra el mensaje de fallo de conexión
    die("Fallo al conectar a MySQL: " . mysqli_connect_error());
}

/* 6. INSERCIÓN DEL PRODUCTO */

// Construye la consulta de inserción integrando los valores capturados en los campos correspondientes
$query = "INSERT INTO producto 
          (nombre_producto, descripcion_producto, cantidad_disponible, precio_producto, fabricante, origen, categoria)
          VALUES 
          ('$nombre_producto', '$descripcion_producto', $cantidad_disponible, $precio_producto, '$fabricante', '$origen', '$categoria')";
// Ejecuta la consulta de inserción sobre la base de datos para registrar el nuevo artículo
mysqli_query($con, $query);

/* 7. OBTENER EL ID DEL PRODUCTO RECIÉN CREADO */

// Ejecuta una consulta para recuperar el último identificador autoincremental generado por la base de datos
$id_result = mysqli_query($con, "SELECT LAST_INSERT_ID();");
// Extrae la fila de resultados obtenida interpretándola como un arreglo numérico
$row_id = mysqli_fetch_array($id_result, MYSQLI_NUM);
// Asigna el identificador recuperado a una variable que servirá para nombrar la imagen
$nombre_imagen = $row_id[0];

/* 8. CERRAR CONEXIÓN */

// Cierra la conexión activa con la base de datos para liberar recursos
mysqli_close($con);

/* 9. PROCESAMIENTO DE LA IMAGEN */

// Obtiene la ruta temporal del archivo de imagen subido al servidor
$ruta_temporal = $_FILES["imagen_producto"]["tmp_name"];
// Define la ruta final de almacenamiento vinculando el ID autogenerado como nombre del archivo con extensión PNG
$ruta_destino  = "../img/productos/$nombre_imagen.png";
// Traslada el archivo desde el directorio temporal hacia el directorio definitivo en el proyecto
move_uploaded_file($ruta_temporal, $ruta_destino);

/* 10. REDIRECCIÓN FINAL */

// Redirige al administrador de vuelta al catálogo de gestión de inventario
header('Location: ./modificar_productos.php');
// Finaliza la ejecución de forma limpia al terminar el redireccionamiento
exit;