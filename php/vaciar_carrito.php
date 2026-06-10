<?php
/* 1. IMPORTACIÓN DE CONFIGURACIÓN Y SESIÓN */

// Importa el archivo de configuración con las credenciales de conexión a la base de datos
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

// Extrae el identificador numérico del usuario desde la sesión actual y lo convierte a entero
$id_usuario = (int) $_SESSION['sesion_personal']['id'];

/* 3. CONEXIÓN Y PROCESAMIENTO EN BASE DE DATOS */

// Establece la conexión con el servidor MySQL mediante los parámetros configurados
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
    
// Evalúa si ocurrió algún error durante el intento de conexión a la base de datos
if (mysqli_connect_errno()) {
    // Registra internamente el error de conexión en el servidor sin exponer detalles al usuario final
    error_log("Fallo al conectar a MySQL: " . mysqli_connect_error());
    // Redirige al carrito de compras adjuntando un parámetro genérico de error en la URL
    header('Location: ./carrito.php?error=1'); 
    // Finaliza la ejecución del script debido al fallo de conexión
    exit();
}

// Construye la consulta de eliminación asegurando que solo se borren los artículos del usuario activo
$query = "DELETE FROM carrito WHERE id_usuario = ?";

// Prepara la consulta de borrado de forma segura en la base de datos
if ($stmt = mysqli_prepare($con, $query)) {
    // Enlaza el identificador numérico del usuario a la sentencia preparada
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    // Ejecuta la sentencia para remover permanentemente todos los artículos vinculados a la cuenta
    mysqli_stmt_execute($stmt);
    // Cierra la declaración preparada liberando los recursos asociados
    mysqli_stmt_close($stmt);
}

/* 4. CIERRE DE CONEXIÓN Y REDIRECCIÓN */

// Cierra la conexión activa con la base de datos
mysqli_close($con);
// Redirige al usuario de vuelta hacia la interfaz actualizada del carrito de compras (ahora vacío)
header('Location: ./carrito.php');
// Finaliza el flujo de ejecución de manera limpia tras realizar la redirección
exit();