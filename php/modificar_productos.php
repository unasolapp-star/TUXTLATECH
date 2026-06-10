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

/* 5. PAGINACIÓN Y EXTRACCIÓN DE PRODUCTOS */

// Define el límite estricto de registros a mostrar por cada página
$registros_por_pagina = 10;
// Obtiene el número de página solicitado por URL o asigna 1 por defecto
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
// Restringe el valor numérico de la página para que no sea menor a 1
if ($pagina < 1) $pagina = 1;
// Calcula el margen de desplazamiento (offset) para el arranque de la consulta de registros
$offset = ($pagina - 1) * $registros_por_pagina;

// Ejecuta una consulta para contar el número total de productos en el catálogo
$res_total = mysqli_query($con, "SELECT COUNT(*) as total FROM producto;");
// Obtiene el número total de registros extraído del arreglo asociativo
$total_registros = mysqli_fetch_assoc($res_total)['total'];
// Calcula el número total de páginas redondeando hacia arriba la división de los registros
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Inicializa un arreglo vacío para almacenar los datos de los productos
$productos = [];
// Ejecuta una consulta para extraer los productos ordenados de forma descendente aplicando la paginación
$result = mysqli_query($con, "SELECT * FROM producto ORDER BY id_producto DESC LIMIT $offset, $registros_por_pagina;");
// Evalúa si la consulta devolvió resultados válidos
if ($result) {
    // Itera de forma secuencial sobre cada fila del resultado obtenido
    while ($row = mysqli_fetch_assoc($result)) {
        // Inserta la fila actual con los detalles del producto dentro del arreglo principal
        $productos[] = $row;
    }
}

/* 6. CERRAR CONEXIÓN */

// Cierra la conexión activa a la base de datos para liberar los recursos
mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Carga modular base de componentes compartidos como meta etiquetas -->
    <?php include "head_html.php";?>
    <!-- Dictamina el nombre visible en la pestaña o ventana del explorador -->
    <title>Modificar productos - MODO ADMIN</title>
    <!-- Enlace referenciando a la figura icono adjunta al título local de la pestaña -->
    <link rel="shortcut icon" href="../img/logo.jpg">
    <!-- Estructuras de optimización asíncrona y emparejamiento predefinido entre navegadores web (Normalize) -->
    <link rel="preload" href="../css/normalize.css" as="style">
    <link rel="stylesheet" href="../css/normalize.css">
    <!-- Declaración anticipada e inserción de la configuración gráfica nativa del proyecto -->
    <link rel="preload" href="../css/estilo_generico.css" as="style">
    <link rel="stylesheet" href="../css/estilo_generico.css">
    <link rel="preload" href="../css/styles-modificar_productos.css" as="style">
    <link rel="stylesheet" href="../css/styles-modificar_productos.css">
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

                    <li class="dropdown active">
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
    <!-- Contenedor alineado horizontalmente con estilo pegajoso para la cabecera y el botón de creación -->
    <div class="mismo-nivel" style="position: sticky; top: 50px; z-index: 100; background-color: rgba(255, 255, 255, 0.95); padding: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h1>Modificar productos</h1>
        <!-- Botón que redirige al administrador a la vista de registro indicando directiva de creación (op=2) -->
        <a href="modificar_crear_producto.php?op=2"><input type="submit" value="Agregar Producto" class="btn btn-default boton"></a>
    </div>
    <!-- Envoltorio contenedor maestro de la lista de productos catalogados a disposición del administrador -->
    <main>
        <?php
        // Evalúa si el arreglo de productos contiene al menos un elemento para mostrar
        if(count($productos) > 0): ?>
                <!-- Contenedor responsivo tabular para la estructura de la lista de inventario -->
                <div class="table-responsive">
                <table  class="table table-hover table-bordered">
                <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Fabricante</th>
                    <th>Origen</th>
                    <th>Categoria</th>
                    <th></th>
                    <th></th>
                </tr>
                <?php
            // Estructura iterativa para desglosar la tabla artículo por artículo
            foreach ($productos as $row): ?>
                    <tr>
                    <td><img class="imagen" src="../img/productos/<?= $row['id_producto'] ?>.png" alt="<?= htmlspecialchars($row['nombre_producto'])?>"></td>
                    <td><?= htmlspecialchars($row['nombre_producto'])?></td>
                    <td><?= htmlspecialchars($row['descripcion_producto'])?></td>
                        <td><?= $row['cantidad_disponible']?></td>
                        <td><?= $row['precio_producto']?></td>
                        <td><?= $row['fabricante']?></td>
                        <td><?= $row['origen']?></td>
                        <td><?= $row['categoria']?></td>
                        <td>
                            <!-- Botón y ancla direccionando al formulario de edición con los datos incrustados en la URL -->
                            <a href="modificar_crear_producto.php?op=1&i=<?= urlencode($row['id_producto']) ?>&n=<?= urlencode($row['nombre_producto'])?>&d=<?= urlencode($row['descripcion_producto'])?>&c=<?= urlencode($row['cantidad_disponible'])?>&p=<?= urlencode($row['precio_producto'])?>&f=<?= urlencode($row['fabricante'])?>&o=<?= urlencode($row['origen'])?>&cat=<?= urlencode($row['categoria'])?>">
                                <input type="submit" value="Modificar" class="btn btn-default btn-sm">
                            </a>
                        </td>
                        <td>
                            <!-- Botón orientativo asociado a un alert para desechar el ítem del catálogo de manera completa -->
                            <a href="eliminar_producto.php?id=<?= $row['id_producto'] ?>" onclick="confirmarAccion(event, this.href, '¿Eliminar producto?', '¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.');">
                                <input type="button" value="Eliminar" class="btn btn-danger btn-sm">
                            </a>
                        </td>
                    </tr>
                    <?php
                endforeach;?>
                
                </table>
                </div>
                
                <!-- Piezas selectoras interactivas habilitando movimiento fragmentario sobre listados robustos (Paginación) -->
                <?php if($total_paginas > 1): ?>
                <div class="text-center">
                    <ul class="pagination">
                        <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="<?= ($i == $pagina) ? 'active' : '' ?>"><a href="?pagina=<?= $i ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php
            else:?>
                <!-- Notificación exhibida en caso de que el catálogo de productos se encuentre completamente vacío -->
                <h1>NO HAY PRODUTOS EXISTENTES</h1>
            <?php
            endif;
        ?>
    </main>

</body>

</html>