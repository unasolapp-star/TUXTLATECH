<?php
/* 1. CONFIGURACIÓN, SESIÓN Y SEGURIDAD */

// Importa el archivo de configuración con las credenciales de la base de datos
require_once("../config/config.php");
// Inicia o reanuda la sesión actual para gestionar las variables globales del usuario
session_start();
// Verifica si la variable de sesión no existe para detectar usuarios sin autenticar
if (!isset($_SESSION['sesion_personal'])) {
    // Redirige al visitante hacia la página de inicio de sesión si carece de credenciales
    header("Location: ./iniciar_sesion.php");
    // Finaliza la ejecución del script para evitar procesamientos no autorizados tras la redirección
    exit;
}

// Verifica que el parámetro identificador de la orden haya sido proporcionado en la URL
if (!isset($_GET['orden'])) {
    // Detiene la ejecución del script y muestra un mensaje de error si no hay orden especificada
    die("Orden no especificada.");
}

// Captura el código alfanumérico de la orden enviado mediante el método GET
$codigo_orden = $_GET['orden'];
// Evalúa y almacena un valor booleano indicando si el usuario activo tiene privilegios de administrador
$es_admin = $_SESSION['sesion_personal']['super'] == 1;

// Establece la conexión con el servidor MySQL mediante los parámetros configurados
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
// Evalúa si ocurrió algún error durante el intento de conexión a la base de datos
if (mysqli_connect_errno()) {
    // Interrumpe la ejecución del script y notifica el fallo de conexión
    die("Fallo al conectar a MySQL: " . mysqli_connect_error());
}

/* 2. PROCESAR ACTUALIZACIÓN DE ESTADO (SOLO ADMINISTRADORES) */

// Verifica si la petición es POST, si el usuario es administrador y si se envió un nuevo estado
if ($_SERVER["REQUEST_METHOD"] == "POST" && $es_admin && isset($_POST['nuevo_estado'])) {
    // Sanitiza el valor del nuevo estado ingresado para prevenir inyecciones SQL
    $nuevo_estado = mysqli_real_escape_string($con, $_POST['nuevo_estado']);
    // Prepara la consulta de actualización para modificar el estado del pedido en la base de datos
    $stmt = mysqli_prepare($con, "UPDATE historial_compras SET estado = ? WHERE codigo_orden = ?");
    // Enlaza los parámetros de texto correspondientes al estado y al código de orden en la sentencia preparada
    mysqli_stmt_bind_param($stmt, "ss", $nuevo_estado, $codigo_orden);
    // Ejecuta la sentencia para aplicar los cambios sobre los registros coincidentes
    mysqli_stmt_execute($stmt);
    // Cierra la declaración preparada liberando los recursos de la memoria
    mysqli_stmt_close($stmt);
    // Redirige a la misma página actualizando la URL para incluir un mensaje de confirmación de éxito
    header("Location: estado_compra.php?orden=" . $codigo_orden . "&msg=exito");
    // Finaliza la ejecución del bloque de actualización
    exit;
}

/* 3. CONSULTAR ESTADO ACTUAL DEL PEDIDO */

// Ejecuta una consulta para recuperar el estado y la fecha de compra de la orden especificada
$result = mysqli_query($con, "SELECT estado, fecha_compra FROM historial_compras WHERE codigo_orden = '$codigo_orden' LIMIT 1");
// Verifica si la consulta devolvió resultados y extrae la fila como un arreglo asociativo
if ($row = mysqli_fetch_assoc($result)) {
    // Extrae y almacena el estado logístico actual de la compra
    $estado_actual = $row['estado'];
    // Extrae y almacena la fecha en que se realizó la transacción
    $fecha_compra = $row['fecha_compra'];
} else {
    // Ramificación ejecutada en caso de no encontrar ninguna orden con el código proporcionado
    die("Orden no encontrada.");
}
// Cierra la conexión activa con la base de datos
mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de la Compra</title>
    <!-- Carga de hojas de estilo básicas para la normalización y estructura genérica -->
    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/estilo_generico.css">
    <!-- Inclusión de Bootstrap a través de un CDN para utilizar componentes de panel rápido -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
</head>
<body class="container" style="margin-top: 50px;">
    <!-- Contenedor principal estilizado como un panel informativo de Bootstrap -->
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">Detalles de la Orden: <b><?= htmlspecialchars($codigo_orden) ?></b></h3>
        </div>
        <div class="panel-body text-center">
            <h4>Fecha de compra: <?= $fecha_compra ?></h4>
            <hr>
            <h3>Estado Actual del Pedido: 
                <!-- Despliega una etiqueta visual dinámica cuyo color depende del estado de la entrega -->
                <span class="label label-<?= $estado_actual=='Cancelado' ? 'danger' : ($estado_actual=='Entregado' ? 'success' : 'warning') ?>" style="font-size: 24px; padding: 10px;">
                    <?= htmlspecialchars($estado_actual) ?>
                </span>
            </h3>
            <br>
            
            <!-- Evalúa si el usuario en sesión es administrador para mostrar el formulario de modificación -->
            <?php if ($es_admin): ?>
            <div class="well" style="max-width: 400px; margin: 0 auto;">
                <!-- Formulario dirigido a este mismo archivo mediante el método POST para actualizar el estado -->
                <form action="estado_compra.php?orden=<?= urlencode($codigo_orden) ?>" method="POST">
                    <div class="form-group">
                        <label for="nuevo_estado">Cambiar estado del paquete:</label>
                        <!-- Desplegable preseleccionando automáticamente el valor logístico actual en la base de datos -->
                        <select name="nuevo_estado" class="form-control" required>
                            <option value="En proceso" <?= $estado_actual == 'En proceso' ? 'selected' : '' ?>>En proceso</option>
                            <option value="Confirmado" <?= $estado_actual == 'Confirmado' ? 'selected' : '' ?>>Confirmado</option>
                            <option value="Entregado" <?= $estado_actual == 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                            <option value="Cancelado" <?= $estado_actual == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    <!-- Botón para someter la solicitud de cambio de estado -->
                    <button type="submit" class="btn btn-primary">Actualizar Estado</button>
                </form>
                <!-- Comprueba si existe el parámetro de éxito en la URL y despliega una notificación de confirmación -->
                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'exito'): ?>
                    <p class="text-success" style="margin-top: 10px;">¡Estado actualizado con éxito!</p>
                <?php endif; ?>
            </div>
            <?php else: ?>
                <!-- Mensaje alternativo mostrado a usuarios estándar sin permisos de edición -->
                <p class="text-muted">Si tienes problemas con tu pedido, contacta con atención al cliente.</p>
            <?php endif; ?>
            
            <!-- Botón de regreso contextualizado dinámicamente según el nivel de privilegios del usuario -->
            <br><a href="<?= $es_admin ? 'consultar_historial.php' : 'historial_individual.php' ?>" class="btn btn-default">Regresar al Historial</a>
        </div>
    </div>
</body>
</html>