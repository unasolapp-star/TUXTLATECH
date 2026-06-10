<?php
/* 1. CONFIGURACIÓN, SESIÓN Y SEGURIDAD */

// Importa el archivo de configuración con las credenciales de la base de datos
require_once("../config/config.php");
// Inicia o reanuda la sesión actual para acceder a las variables globales del usuario
session_start();
// Verifica si la variable de sesión no existe o si el usuario no es un superusuario
if (!isset($_SESSION['sesion_personal']) || $_SESSION['sesion_personal']['super'] != 1) {
    // Redirige a la página principal si el usuario no tiene permisos de administrador
    header("Location: ../index.php");
    // Finaliza la ejecución del script para evitar procesamientos no autorizados tras la redirección
    exit;
}

// Comprueba que el parámetro identificador de la orden haya sido enviado a través de la URL
if (!isset($_GET['orden'])) {
    // Detiene la ejecución del script y muestra un mensaje de error si falta la orden
    die("Orden no especificada.");
}

// Captura y almacena el código alfanumérico de la orden enviado por el método GET
$codigo_orden = $_GET['orden'];

// Establece la conexión con el servidor MySQL mediante los parámetros configurados
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
// Evalúa si ocurrió algún error durante el intento de conexión a la base de datos
if (mysqli_connect_errno()) {
    // Interrumpe la ejecución del script y notifica el fallo de conexión
    die("Fallo al conectar a MySQL.");
}

/* 2. EXTRACCIÓN DE DATOS DEL DESTINATARIO */

// Construye la consulta relacional para obtener los datos personales y de la compra vinculados a la orden
$query = "SELECT u.nombre_usuario, u.numero_telefono, u.direccion, u.cp, h.fecha_compra 
          FROM historial_compras AS h 
          INNER JOIN usuario AS u ON h.id_usuario = u.id_usuario 
          WHERE h.codigo_orden = '$codigo_orden' LIMIT 1";
// Ejecuta la consulta estructurada sobre la base de datos
$result = mysqli_query($con, $query);

// Verifica si la consulta arrojó resultados y extrae la fila como un arreglo asociativo
if ($row = mysqli_fetch_assoc($result)) {
    // Extrae y almacena el nombre del destinatario
    $nombre = $row['nombre_usuario'];
    // Extrae y almacena el número de contacto del destinatario
    $telefono = $row['numero_telefono'];
    // Extrae y almacena la dirección de entrega
    $direccion = $row['direccion'];
    // Extrae el código postal o asigna "N/A" en caso de no estar registrado
    $cp = $row['cp'] ? $row['cp'] : "N/A";
    // Extrae y almacena la fecha en que se realizó la transacción
    $fecha = $row['fecha_compra'];
} else {
    // Ramificación en caso de no encontrar un registro coincidente con el código proporcionado
    die("No se pudo localizar la información de esta orden.");
}
// Cierra la conexión activa con la base de datos liberando los recursos
mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Define la codificación de caracteres para asegurar la correcta visualización de texto -->
    <meta charset="UTF-8">
    <!-- Dictamina el título dinámico de la pestaña incluyendo el código de la orden -->
    <title>Hoja de Envío - <?= htmlspecialchars($codigo_orden) ?></title>
    <!-- Define los estilos en cascada integrados específicamente para la hoja de impresión -->
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f0f0f0;
        }
        .etiqueta {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border: 2px dashed #000;
            padding: 30px;
            box-sizing: border-box;
        }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .datos {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .codigo-barras {
            text-align: center;
            margin-top: 30px;
        }
        /* Reglas de estilo específicas activadas únicamente durante la impresión física del documento */
        @media print {
            body { background: #fff; }
            /* Oculta los elementos interactivos que no deben imprimirse en la hoja final */
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- Contenedor superior con la botonera de acción, oculto durante la impresión -->
    <div class="text-center no-print" style="text-align: center; margin-bottom: 20px;">
        <!-- Botón interactivo que dispara el diálogo de impresión nativo del navegador -->
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Imprimir Etiqueta</button>
    </div>
    <!-- Envoltura principal de la etiqueta física con bordes predefinidos -->
    <div class="etiqueta">
        <!-- Cabecera de la etiqueta con la marca de la tienda y la fecha -->
        <div class="header">
            <!-- Título principal de la etiqueta de envío -->
            <h1>TuxtlaTech - ENVÍOS</h1>
            <!-- Imprime la fecha de la orden dinámicamente -->
            <p>Fecha de Compra: <b><?= $fecha ?></b></p>
        </div>
        <!-- Sección dedicada al desglose de los datos logísticos del destinatario -->
        <div class="datos">
            <!-- Inyección de la información personal extraída previamente de la base de datos -->
            <p><strong>Destinatario:</strong> <?= htmlspecialchars($nombre) ?></p>
            <p><strong>Dirección:</strong> <?= htmlspecialchars($direccion) ?></p>
            <p><strong>Código Postal (CP):</strong> <?= htmlspecialchars($cp) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($telefono) ?></p>
        </div>
        <!-- Sección inferior destinada a la validación mediante escáner -->
        <div class="codigo-barras">
            <p>Orden: <b><?= htmlspecialchars($codigo_orden) ?></b></p>
            <!-- Consume una API externa (Bwip-js) para renderizar el código alfanumérico en un código de barras gráfico -->
            <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text=<?= urlencode($codigo_orden) ?>&scale=2&includetext=true" alt="Código de barras">
        </div>
    </div>
</body>
</html>