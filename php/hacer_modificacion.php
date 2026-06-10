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
}

/* 3. RECOLECCIÓN Y LIMPIEZA DE VARIABLES DE SESIÓN */

// Recupera el identificador del producto almacenado temporalmente en la sesión
$id_producto=$_SESSION['sesion_personal']['id_producto'];
// Elimina la variable temporal de la sesión para liberar memoria y evitar conflictos
unset($_SESSION['sesion_personal']['id_producto']);

/* 4. RECOLECCIÓN DE DATOS DEL FORMULARIO */

// Captura el nombre del producto enviado mediante el método POST
$nombre_producto=$_POST['nombre_producto'];
// Captura la descripción detallada del producto enviada por el formulario
$descripcion_producto=$_POST['descripcion_producto'];
// Captura la cantidad de unidades en existencia del producto
$cantidad_disponible=$_POST['cantidad_disponible'];
// Captura el precio unitario asignado al producto
$precio_producto=$_POST['precio_producto'];
// Captura el nombre del fabricante o marca del producto
$fabricante=$_POST['fabricante'];
// Captura el país o lugar de origen del producto
$origen=$_POST['origen'];
// Captura la categoría a la cual pertenece el producto
$categoria=$_POST['categoria'];

/* 5. BLOQUE DE DEPURACIÓN VISUAL */

// Imprime un texto indicativo de la acción en curso para fines de prueba
echo "modificar<br>";
// Imprime en pantalla el identificador único del producto que se está modificando
echo "id: ".$id_producto;
// Imprime una etiqueta de preformato HTML para estructurar la salida
echo "<pre>";
// Despliega visualmente el arreglo completo con los datos recibidos vía POST
print_r($_POST);
// Cierra la etiqueta de preformato
echo "</pre>";
// Inserta saltos de línea para separar visualmente el bloque de depuración
echo "<br>";
echo "<br>";

/* 6. CONEXIÓN A LA BASE DE DATOS Y ACTUALIZACIÓN */

// Establece la conexión con el servidor MySQL mediante los parámetros configurados
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
// Evalúa si ocurrió algún error durante el intento de conexión
if (mysqli_connect_errno()) :
    // Imprime en pantalla el mensaje de error arrojado por el servidor
    echo "Fallo al conectar a MySQL: " . mysqli_connect_error();
else:
    // Construye la consulta de actualización con los nuevos valores para el producto correspondiente
    $query="UPDATE producto
            SET precio_producto=$precio_producto,fabricante='$fabricante',origen='$origen',nombre_producto='$nombre_producto',cantidad_disponible=$cantidad_disponible,descripcion_producto='$descripcion_producto',categoria='$categoria'
            WHERE id_producto=$id_producto
            ;";
    // Ejecuta la consulta de actualización sobre la base de datos
    mysqli_query($con, $query);

    // Redirige al administrador de vuelta al catálogo principal de modificación de productos
    header('Location: ./modificar_productos.php');
// Cierra el bloque condicional de la conexión
endif;