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

/* 3. CONEXIÓN ÚNICA A LA BASE DE DATOS */

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
// Ejecuta una consulta para sumar todas las cantidades seleccionadas por el administrador
$res_badge = mysqli_query($con, "SELECT SUM(cantidad_seleccionada) as total FROM carrito WHERE id_usuario=".$_SESSION['sesion_personal']['id']);
// Verifica que la consulta fue exitosa y extrae el resultado asociativo
if($res_badge && $row_badge = mysqli_fetch_assoc($res_badge)){
    // Asigna el valor sumado recuperado o mantiene cero si el resultado es nulo
    $cantidad_carrito = $row_badge['total'] ? $row_badge['total'] : 0;
}

/* 5. GESTIÓN DE DIRECTIVAS Y PARÁMETROS DEL FORMULARIO */

// Captura la directiva enviada por URL para identificar si se debe armar el formulario de modificación (1) o creación (2)
$opcion=$_GET['op']; 
// Asigna el identificador del producto si existe en la URL, de lo contrario asigna una cadena vacía
$id_producto=isset($_GET['i']) ? $_GET['i'] : "";
// Asigna el nombre del producto si fue provisto, previniendo errores por valores nulos en nuevas creaciones
$nombre_producto=isset($_GET['n']) ? $_GET['n'] : "";
// Asigna la descripción del producto mediante un operador ternario
$descripcion_producto=isset($_GET['d']) ? $_GET['d'] : "";
// Asigna la cantidad disponible en inventario o la inicializa vacía
$cantidad_disponible=isset($_GET['c']) ? $_GET['c'] : "";
// Asigna el precio monetario del producto si viene en la petición
$precio_producto=isset($_GET['p']) ? $_GET['p'] : "";
// Asigna el nombre del fabricante del artículo
$fabricante=isset($_GET['f']) ? $_GET['f'] : "";
// Asigna el lugar de origen de manufactura del producto
$origen=isset($_GET['o']) ? $_GET['o'] : "";
// Asigna la categoría correspondiente para la clasificación del producto
$categoria=isset($_GET['cat']) ? $_GET['cat'] : "";

/* 6. OBTENCIÓN DE CATEGORÍAS DINÁMICAS */

// Inicializa un arreglo para almacenar las categorías únicas existentes en el catálogo
$categorias_db = [];
// Ejecuta una consulta para extraer todas las clasificaciones distintas registradas y no nulas
$res_cat = mysqli_query($con, "SELECT DISTINCT categoria FROM producto WHERE categoria IS NOT NULL AND categoria != ''");
// Itera secuencialmente sobre los resultados de las categorías encontradas
while ($row_cat = mysqli_fetch_assoc($res_cat)) {
    // Apila cada categoría recuperada dentro del arreglo dinámico
    $categorias_db[] = $row_cat['categoria'];
}

/* 7. CERRAR CONEXIÓN */

// Cierra la conexión activa a la base de datos liberando los recursos
mysqli_close($con);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Carga modular base de componentes compartidos como meta etiquetas -->
    <?php include "head_html.php";
    // Define dinámicamente el título de la página dependiendo de la opción elegida (modificar o crear)
    $titulo=$opcion==1?"Modificar producto":"Agregar producto";?>
    <!-- Dictamina el nombre visible en la pestaña o ventana del explorador en base a la variable dinámica -->
    <title><?= $titulo?></title>
    <!-- Enlace referenciando a la figura icono adjunta al título local de la pestaña -->
    <link rel="shortcut icon" href="../img/logo.jpg">
    <!-- Estructuras de optimización asíncrona y emparejamiento predefinido entre navegadores web (Normalize) -->
    <link rel="preload" href="../css/normalize.css" as="style">
    <link rel="stylesheet" href="../css/normalize.css">
    <!-- Declaración anticipada e inserción de la configuración gráfica nativa del proyecto -->
    <link rel="preload" href="../css/styles.css" as="style">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="preload" href="../css/styles_mod_crear_prod.css" as="style">
    <link rel="stylesheet" href="../css/styles_mod_crear_prod.css">
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
                    <li>
                        <!-- Despliegue textual descriptivo que reconoce a la persona con sesión activa -->
                        <span class="navbar-text">Sesión iniciada como
                            <a href="../php/perfil.php"
                                class="navbar-link"><u><?=$_SESSION['sesion_personal']['nombre']?></u>
                            </a>
                        </span>
                    </li>
                </ul>
                <!-- Envoltura aglutinante oriental con capacidades dedicadas a la gestión personal y navegación -->
                <ul class="nav navbar-nav navbar-right">
                    <!-- Valida integralmente si el actor operativo posee facultad general como súper usuario -->
                    <?php if ($_SESSION['sesion_personal']['super']==1): ?>
                    <li class="active">
                        <!-- Enlace visual indicando la sección actual en la que se encuentra el administrador -->
                        <a href="../php/cerrar_sesion.php"><span class="glyphicon glyphicon-cog"></span> <?= $titulo?></a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                            aria-expanded="false">MODO ADMIN <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="../php/consultar_historial.php"><span class="glyphicon glyphicon-list"></span> Consultar historial</a></li>
                            <li><a href="../php/modificar_productos.php"><span class="glyphicon glyphicon-cog"></span> Modificar productos</a></li>
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
    <!-- Título de la página inyectado dinámicamente según la acción seleccionada -->
    <h1><?= $titulo?></h1>
    <!-- Evalúa y asigna el script destino que procesará los datos dependiendo si es actualización o creación -->
    <?php $directorio=$opcion==1?"hacer_modificacion.php":"hacer_registro.php";?>
    <!-- Almacena temporalmente el identificador del producto en la sesión para que el script destino lo recupere -->
    <?php $_SESSION['sesion_personal']['id_producto']=$id_producto;?>

    <!-- Formulario configurado para enviar datos de texto y archivos (multipart/form-data) hacia el script de procesamiento -->
    <form action="<?= $directorio?>" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="nombre_producto">Nombre</label>
        <!-- Campo de entrada para el nombre, prellenado si se está modificando un registro -->
        <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" value="<?= htmlspecialchars($nombre_producto)?>" required>
    </div>
    <div class="form-group">
        <label for="descripcion_producto">Descripción</label>
        <!-- Área de texto para la descripción detallada, insertando el valor previo si existe -->
        <textarea class="form-control" rows="3" id="descripcion_producto" name="descripcion_producto" required><?= htmlspecialchars($descripcion_producto)?></textarea>
    </div>
    <div class="form-group">
        <label for="cantidad_disponible">Cantidad</label>
        <!-- Campo de entrada numérica para definir el volumen de stock -->
        <input type="number" class="form-control" id="cantidad_disponible" name="cantidad_disponible" value="<?= htmlspecialchars($cantidad_disponible)?>" required>
    </div>
    <div class="form-group">
        <label for="precio_producto">Precio</label>
        <!-- Campo de entrada numérica aceptando decimales para asentar el costo del artículo -->
        <input type="number" step="any" class="form-control" id="precio_producto" name="precio_producto" value="<?= htmlspecialchars($precio_producto)?>" required>
    </div>
    <div class="form-group">
        <label for="fabricante">Fabricante</label>
        <!-- Campo de entrada de texto para nombrar a la empresa fabricante o marca -->
        <input type="text" class="form-control" id="fabricante" name="fabricante" value="<?= htmlspecialchars($fabricante)?>" required>
    </div>
    <div class="form-group">
        <label for="origen">Origen</label>
        <!-- Campo de texto para declarar la región de procedencia de la mercancía -->
        <input type="text" class="form-control" id="origen" name="origen" value="<?= htmlspecialchars($origen)?>" required>
    </div>
    <div class="form-group">
        <label for="categoria">Categoria</label>
        <!-- Campo de entrada de texto asociado a una lista de opciones dinámicas preexistentes -->
        <input list="lista_categorias" class="form-control" id="categoria" name="categoria" value="<?= htmlspecialchars($categoria)?>" placeholder="Selecciona o escribe una categoría" autocomplete="off" required>
        <!-- Estructura de lista de datos que provee sugerencias de autocompletado -->
        <datalist id="lista_categorias">
            <!-- Bucle para inyectar cada una de las categorías extraídas previamente de la base de datos -->
            <?php foreach($categorias_db as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>"></option>
            <?php endforeach; ?>
        </datalist>
    </div>
    <!-- Valida si la opción actual es específicamente la creación de un nuevo registro (opcion == 2) -->
    <?php if($opcion==2):?>
    <div class="form-group">
        <label for="imagen_producto">Imágen</label>
        <!-- Muestra el campo de subida de archivo forzando y restringiendo las extensiones a imágenes PNG o JPEG -->
        <input type="file" id="imagen_producto" name="imagen_producto" accept="image/png, image/jpeg" required>
    </div>
    <?php endif;?>
    <!-- Botón de ejecución para someter el envío de la información asumiendo el título de la directiva -->
    <button type="submit" class="btn btn-default boton"><?= $titulo?></button>
    </form>
    
</body>

</html>
