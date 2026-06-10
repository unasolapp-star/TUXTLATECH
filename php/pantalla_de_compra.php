<?php
/* 1. CONFIGURACIÓN Y SESIÓN */

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

/* 3. VARIABLES GLOBALES Y CONEXIÓN A BASE DE DATOS */

// Extrae el identificador numérico del usuario desde la sesión actual
$id_usuario = $_SESSION['sesion_personal']['id'];
// Evalúa si la compra proviene del carrito para determinar si debe vaciarse al finalizar
$vaciar_carrito = isset($_GET['v']) ? (int)$_GET['v'] : 0;

// Establece la conexión con el servidor MySQL mediante los parámetros configurados
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
// Evalúa si ocurrió algún error durante la conexión
if (mysqli_connect_errno()) {
    // Detiene el proceso y muestra un mensaje de fallo de conexión
    die("Fallo al conectar a MySQL: " . mysqli_connect_error());
}

/* 4. CALCULAR BADGE DEL CARRITO */

// Inicializa en cero la variable que almacena la cantidad total de artículos en el carrito
$cantidad_carrito = 0;
// Ejecuta una consulta para sumar todas las cantidades seleccionadas por el usuario actual
$res_badge = mysqli_query($con, "SELECT SUM(cantidad_seleccionada) as total FROM carrito WHERE id_usuario=" . $id_usuario);
// Verifica que la consulta fue exitosa y extrae el resultado asociativo
if ($res_badge && $row_badge = mysqli_fetch_assoc($res_badge)) {
    // Asigna el valor sumado recuperado o mantiene cero si el resultado es nulo
    $cantidad_carrito = $row_badge['total'] ? $row_badge['total'] : 0;
}

/* 5. OBTENER INFORMACIÓN DE ENVÍO DEL COMPRADOR */

// Inicializa un arreglo vacío para almacenar los datos personales y logísticos del usuario
$usuario_data = [];
// Ejecuta una consulta para extraer el correo, teléfono y dirección del usuario activo
$result = mysqli_query($con, "SELECT correo, numero_telefono, direccion, cp FROM usuario WHERE id_usuario=" . $id_usuario);
// Evalúa si la consulta devolvió un resultado válido
if ($row = mysqli_fetch_assoc($result)) {
    // Estructura y asocia los datos recuperados dentro del arreglo de información de usuario
    $usuario_data = [
        "correo" => $row['correo'],
        "n_telefono" => $row['numero_telefono'],
        "direccion" => $row['direccion'],
        "cp" => $row['cp'] ? $row['cp'] : "No registrado"
    ];
}

/* 6. PROCESAR ARREGLOS DE PRODUCTOS Y OBTENER INFO DEL INVENTARIO */

// Inicializa un arreglo general que contendrá las parejas de cantidad e identificador
$arreglo = array(); 
// Inicializa un arreglo para almacenar los detalles expandidos de cada artículo
$producto_detalles = [];
// Verifica si el parámetro de datos fue enviado y tiene un formato de arreglo válido
if (isset($_GET['datos']) && is_array($_GET['datos'])) {
    // Itera secuencialmente sobre cada elemento del arreglo de datos recibido
    foreach ($_GET['datos'] as $value) {
        // Descompone la cadena de texto separando la cantidad y el identificador usando la coma
        $subarreglo = explode(",", $value);
        // Evalúa que la descomposición haya generado exactamente dos elementos numéricos
        if (count($subarreglo) == 2) {
            // Apila la tupla dentro del arreglo general para su posterior uso en JavaScript
            array_push($arreglo, $subarreglo);
            // Convierte y asigna la cantidad de piezas solicitadas a entero
            $cantidad = (int)$subarreglo[0];
            // Convierte y asigna el identificador único del artículo a entero
            $id_producto = (int)$subarreglo[1];
            
            // Ejecuta una consulta para recuperar el nombre y el precio unitario del producto
            $res_prod = mysqli_query($con, "SELECT nombre_producto, precio_producto FROM producto WHERE id_producto=" . $id_producto);
            // Verifica si la consulta arrojó resultados y extrae la fila
            if ($row_prod = mysqli_fetch_assoc($res_prod)) {
                // Ensambla un sub-arreglo con toda la información y lo apila en el contenedor de detalles
                $producto_detalles[] = [
                    "id_producto" => $id_producto,
                    "nombre" => $row_prod['nombre_producto'],
                    "precio" => $row_prod['precio_producto'],
                    "cantidad" => $cantidad,
                    "total_individual" => $cantidad * $row_prod['precio_producto']
                ];
            }
        }
    }
}

/* 7. CERRAR CONEXIÓN */

// Cierra la conexión activa a la base de datos para liberar los recursos
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Carga modular base de componentes compartidos como meta etiquetas -->
    <?php include "head_html.php"?>
    <!-- Dictamina el nombre visible en la pestaña o ventana del explorador -->
    <title>Pantalla de compra</title>
    <!-- Enlace referenciando a la figura icono adjunta al título local de la pestaña -->
    <link rel="shortcut icon" href="../img/logo.jpg">
    <!-- Estructuras de optimización asíncrona y emparejamiento predefinido entre navegadores web (Normalize) -->
    <link rel="preload" href="../css/normalize.css" as="style">
    <link rel="stylesheet" href="../css/normalize.css">
    <!-- Declaración anticipada e inserción de la configuración gráfica nativa del proyecto -->
    <link rel="preload" href="../css/estilo_generico.css" as="style">
    <link rel="stylesheet" href="../css/estilo_generico.css">
    <link rel="preload" href="../css/styles-pantalla_compra.css" as="style">
    <link rel="stylesheet" href="../css/styles-pantalla_compra.css">
    <!-- Inclusión de la lógica interactiva de carrito y compra mediante JavaScript -->
    <script type="text/javascript" src="../js/comprar_agregarcarrito.js"></script>
</head>
<!-- Elemento del bloque superior abarcando y controlando todo el menú directivo del sistema -->
<header>
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container-fluid">
            <!-- Contenedor adaptativo cabecera principal reservado a la portabilidad móvil y comercial -->
            <div class="navbar-header">
                <!-- Botón reactivo destinado a contraer las funcionalidades secundarias en espacio reducido -->
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <!-- Referencia identitaria superior a la propia denominación de la tienda -->
                <a class="navbar-brand" href="../index.php">TuxtlaTech</a>
            </div>

            <div class="collapse navbar-collapse" id="myNavbar">
                <!-- Disposición en lista de viñetas alineada hacia el extremo occidental del menú -->
                <ul class="nav navbar-nav">
                    <li><a href="../index.php">Lista de productos</a></li>
                    <li class="active">
                        <a href="#">Comprar</a>
                    </li>
                    <!-- Despliegue textual descriptivo que reconoce a la persona con sesión activa -->
                    <li><span class="navbar-text">Sesión iniciada como <a href="../php/perfil.php"
                                class="navbar-link"><u><?=$_SESSION['sesion_personal']['nombre']?></u></a></span></li>
                </ul>
                <!-- Envoltura aglutinante oriental con capacidades dedicadas a la gestión personal y navegación -->
                <ul class="nav navbar-nav navbar-right">
                    <!-- Valida integralmente si el actor operativo posee facultad general como súper usuario -->
                    <?php if ($_SESSION['sesion_personal']['super']==1): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                            aria-expanded="false">MODO ADMIN <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="../php/consultar_historial.php"><span class="glyphicon glyphicon-list"></span>
                                    Consultar historial</a></li>
                            <li><a href="../php/modificar_productos.php"><span class="glyphicon glyphicon-cog"></span>
                                    Modificar productos</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="../php/cerrar_sesion.php"><span class="glyphicon glyphicon-log-out"></span> Cerrar
                            sesión</a>
                    </li>
                    <li>
                        <a href="../php/carrito.php"><span class="glyphicon glyphicon-shopping-cart"></span> Carrito de compras
                            <!-- Procesa la inclusión gráfica de la burbuja (badge) únicamente ante valores positivos de productos -->
                            <?php if($cantidad_carrito > 0): ?>
                            <span class="badge" style="background-color: #d9534f;"><?= $cantidad_carrito ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<body class="container">
    <!-- Título principal de la sección con estilo pegajoso para mantenerse visible -->
    <h1 style="position: sticky; top: 50px; z-index: 100; background-color: rgba(255, 255, 255, 0.95); padding: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">Pantalla de compra</h1>
    <!-- Subtítulo separador para la sección de los datos logísticos -->
    <h4>Información de facturación</h4>
    <!-- Contenedor general que engloba y formatea la dirección, teléfono y correo del usuario -->
    <div class="info-producto"><br>
    <div class="centrar-texto">
    <!-- Bloques de inyección dinámica con los datos previamente recuperados -->
    <p><b>Dirección:</b> <?= htmlspecialchars($usuario_data['direccion']);?></p>
    <p><b>Código Postal (CP):</b> <?= htmlspecialchars($usuario_data['cp']);?></p>
    <p><b>Número de teléfono:</b> <?= htmlspecialchars($usuario_data['n_telefono']);?></p>
    <p><b>Correo:</b> <?= htmlspecialchars($usuario_data['correo']);?></p>
    </div></div>
    <!-- Trazo separador estético -->
    <hr>
    <!-- Subtítulo separador para la sección de los artículos seleccionados -->
    <h4>Confirmación de compra</h4> 
    <!-- Área iterativa de vaciado visual de los datos calculados de los productos que se están por adquirir -->
    <?php foreach ($producto_detalles as $value) :?>
    <div class="info-producto">
        <div class="ancho-minimo">
            <!-- Imprime secuencialmente nombre, costo, cantidad y total individual de la iteración -->
            <p><b>Nombre:</b> <?= $value['nombre'];?></p>
            <p><b>Precio:</b> $<?= number_format(floatval($value['precio']), 2, '.', ',')?></p>
            <p><b>Cantidad:</b> <?= $value['cantidad'];?></p>
            <p><b>Total:</b>
                $<?= number_format($value['total_individual'], 2, '.', ',');?>
            </p>
        </div>
        <div>
            <!-- Anexa la imagen en miniatura respectiva del componente -->
            <img src="../img/productos/<?= $value['id_producto']?>.png" alt="<?= $value['nombre']?>">
        </div>
    </div>
    <br><br>
    <?php endforeach; ?>

    <script>
    // Transfiere la matriz de componentes PHP hacia una constante JavaScript en formato JSON
    var arreglo_de_productos = JSON.parse('<?= json_encode($arreglo); ?>');
    </script>
    <!-- Contenedor inferior para agrupar los botones de acción terminal -->
    <div class="centrar-botones">
        <!-- Botón de ejecución para llamar a la función comprar confirmando la transacción definitiva -->
        <input type="submit" value="Confirmar compra" class="btn btn-default boton"
            onclick="comprar(arreglo_de_productos,<?=(int) $vaciar_carrito?>)">
        <!-- Botón de ejecución para revertir y abortar el flujo abandonando la vista de pago -->
        <input type="submit" value="Cancelar compra" class="btn btn-default boton"
            onclick="window.location.replace('../index.php')">
    </div>
</body>

</html>