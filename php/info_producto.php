<?php
/* 1. CONFIGURACIÓN, SESIÓN Y SEGURIDAD */

// Importa el archivo de configuración con las credenciales de la base de datos
require_once("../config/config.php");
// Inicia o reanuda la sesión actual para gestionar las variables globales del usuario
session_start();

// Verifica si la variable de sesión no existe para prevenir el acceso no autorizado
if (!isset($_SESSION['sesion_personal'])) {
    // Redirige al visitante hacia la página de inicio de sesión si carece de autenticación
    header("Location: ./iniciar_sesion.php");
    // Finaliza la ejecución del script para evitar procesamientos adicionales tras la redirección
    exit;
}

/* 2. CONEXIÓN A BASE DE DATOS Y OBTENCIÓN DE DATOS */

// Establece la conexión con el servidor MySQL mediante los parámetros configurados
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
    
// Evalúa si ocurrió algún error durante el intento de conexión a la base de datos
if (mysqli_connect_errno()) {
    // Interrumpe la ejecución del script y muestra un mensaje de fallo de conexión
    die("Fallo al conectar a MySQL: " . mysqli_connect_error());
}

// Inicializa en cero la variable que almacena la cantidad total de artículos en el carrito
$cantidad_carrito = 0;
// Ejecuta una consulta para sumar todas las cantidades seleccionadas por el usuario activo
$res_badge = mysqli_query($con, "SELECT SUM(cantidad_seleccionada) as total FROM carrito WHERE id_usuario=".intval($_SESSION['sesion_personal']['id']));
// Verifica que la consulta fue exitosa y extrae el resultado asociativo
if($res_badge && $row_badge = mysqli_fetch_assoc($res_badge)){
    // Asigna el valor sumado recuperado o mantiene cero si el resultado es nulo
    $cantidad_carrito = $row_badge['total'] ? $row_badge['total'] : 0;
}

// Obtiene y sanitiza el parámetro de identificación del producto desde la URL convirtiéndolo a entero
$id_producto = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

    // Inicializa un arreglo vacío para almacenar los detalles específicos del producto solicitado
    $info_del_producto=[];
    // Ejecuta una consulta para extraer toda la información del producto correspondiente al identificador
    $result = mysqli_query($con, "SELECT * FROM producto WHERE id_producto=".$id_producto.";");
// Verifica si la consulta arrojó al menos un resultado válido coincidente en el catálogo
if ($result && mysqli_num_rows($result) > 0) {
    // Itera sobre la fila de resultados obtenida desde la base de datos
    while ($row = mysqli_fetch_array($result)) {
        // Inserta un arreglo asociativo con los datos extraídos dentro de la lista principal
        array_push($info_del_producto, array(
            "id"=>$row['id_producto'],
            "nombre"=>$row['nombre_producto'],
            "descripcion"=>$row['descripcion_producto'],
            "disponibles"=>$row['cantidad_disponible'],
            "precio"=>$row['precio_producto'],
            "fabricante"=>$row['fabricante'],
            "origen"=>$row['origen'],
            "categoria"=>$row['categoria'],
        ));
    }
} else {
    // Ramificación ejecutada si el identificador proporcionado no corresponde a ningún producto existente
    // Cierra la conexión activa a la base de datos de manera anticipada
    mysqli_close($con);
    // Interrumpe la carga de la página y muestra un mensaje de error con un enlace para volver al inicio
    die("<h2>El producto solicitado no existe.</h2><a href='../index.php'>Volver al inicio</a>");
}

    /* 3. CERRAR CONEXIÓN */
    
    // Cierra la conexión a la base de datos liberando los recursos de memoria
    mysqli_close($con);
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Carga modular base de componentes compartidos como meta etiquetas -->
    <?php include "head_html.php"?>
    <!-- Dictamina el nombre visible en la pestaña o ventana del explorador -->
    <title>Información del producto</title>
    <!-- Enlace referenciando a la figura icono adjunta al título local de la pestaña -->
    <link rel="shortcut icon" href="../img/logo.jpg">
    <!-- Estructuras de optimización asíncrona y emparejamiento predefinido entre navegadores web (Normalize) -->
    <link rel="preload" href="../css/normalize.css" as="style">
    <link rel="stylesheet" href="../css/normalize.css">
    <!-- Declaración anticipada e inserción de la configuración gráfica nativa del proyecto -->
    <link rel="preload" href="../css/estilo_generico.css" as="style">
    <link rel="stylesheet" href="../css/estilo_generico.css">
    <link rel="preload" href="../css/styles-info-product.css" as="style">
    <link rel="stylesheet" href="../css/styles-info-product.css">
    <!-- Inclusión de la lógica interactiva del carrito y compras a través de JavaScript -->
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
                        <a href="#">Información del producto</a>
                    </li>
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
    <!-- Verifica si la lista contenedora del producto posee información válida antes de renderizar la interfaz -->
    <?php if(!empty($info_del_producto)): ?>
    <script>
    // Pone a disposición de los scripts locales de JS el identificador único del artículo visualizado
    let id_del_producto = <?=$id_producto?>;
    </script>
    <!-- Envoltura principal para estructurar la visualización general en pantalla dividida -->
    <div class="grande">
        <!-- Bloque correspondiente a la visualización de la fotografía del producto -->
        <div class="imagen">
            <span><img src="../img/productos/<?= $info_del_producto[0]["id"] ?>.png" alt=""></span>
        </div>
        <!-- Bloque correspondiente a las métricas vitales, de precio y de manipulación de acciones de compra -->
        <div class="info-importante">
            <span><b>Nombre: </b><br><?= $info_del_producto[0]["nombre"] ?></span>
            <span><b>Precio: </b><br>$ <?= number_format(floatval($info_del_producto[0]["precio"])) ?></span>
            <span><b>Disponibles: </b><br><?= $info_del_producto[0]["disponibles"] ?></span>
            <span><b>Seleccionar cantidad: </b>
                <!-- Lista desplegable autogenerada evaluando las existencias remanentes en catálogo -->
                <select class="form-control" id="cantidad_seleccionada">
                    <!-- Bucle numérico secuencial dictando las opciones factibles con base a la disponibilidad topada -->
                    <?php for ($i=1; $i <= $info_del_producto[0]["disponibles"]; $i++): ?>
                    <option value="<?=$i?>"><?=$i?></option>
                    <?php endfor ?>
                </select>
            </span>
            <span>
                <!-- Botón de accionar directo transfiriendo flujo simple a la compra final inmediata -->
                <input type="button" onclick="enviarAPantallaDeCompraUno(id_del_producto)"
                    class="btn btn-default comprar" value="Comprar">
                <!-- Botón de accionar para guardar a la reserva (Carrito) a través de una función de JavaScript encolada -->
                <input type="button" onclick="agregarAlCarrito(id_del_producto)" class="btn btn-default comprar"
                    value="Agregar al carrito">
            </span>
        </div>
    </div>
    <!-- Cabecera separadora para la ficha técnica inferior -->
    <h3>Descripción detallada</h3>
    <!-- Contenedor general en bloque de los detalles de texto en bruto extraídos de la ficha del catálogo -->
    <div class="info-secundaria">
        <span><b>Descripción: </b><?= $info_del_producto[0]["descripcion"] ?></span>
        <span><b>Fabricante: </b><?= $info_del_producto[0]["fabricante"] ?></span>
        <span><b>Origen: </b><?= $info_del_producto[0]["origen"] ?></span>
        <span><b>Categoría: </b><?= $info_del_producto[0]["categoria"] ?></span>
    </div>
    <?php endif; ?>
</body>

</html>