<?php
/* 1. CONFIGURACIÓN Y SESIÓN */

// Importa el archivo de configuración con las credenciales de la base de datos
require_once("../config/config.php");
// Inicia o reanuda la sesión actual para el manejo de variables globales
session_start();
// Comprueba si la variable de sesión no existe para detectar usuarios sin autenticar
if (!isset($_SESSION['sesion_personal'])) {
    // Redirecciona al visitante hacia la pantalla de inicio de sesión
    header("Location: ./iniciar_sesion.php");
    // Finaliza la ejecución del script para evitar accesos no autorizados
    exit;
}

/* 2. CONEXIÓN ÚNICA A LA BASE DE DATOS */

// Establece la conexión con el servidor MySQL mediante los parámetros configurados
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
// Evalúa si ocurrió algún error durante la conexión
if (mysqli_connect_errno()) {
    // Detiene el proceso y muestra un mensaje de fallo de conexión
    die("Fallo al conectar a MySQL: " . mysqli_connect_error());
}

/* 3. CALCULAR BADGE DEL CARRITO */

// Inicializa en cero la variable que almacena la cantidad total de artículos en el carrito
$cantidad_carrito = 0;
// Ejecuta una consulta para sumar todas las cantidades seleccionadas por el administrador
$res_badge = mysqli_query($con, "SELECT SUM(cantidad_seleccionada) as total FROM carrito WHERE id_usuario=".$_SESSION['sesion_personal']['id']);
// Verifica que la consulta fue exitosa y extrae el resultado asociativo
if($res_badge && $row_badge = mysqli_fetch_assoc($res_badge)){
    // Asigna el valor sumado recuperado o mantiene cero si el resultado es nulo
    $cantidad_carrito = $row_badge['total'] ? $row_badge['total'] : 0;
}

/* 4. EXTRACCIÓN Y PAGINACIÓN DEL HISTORIAL */

    // Inicializa un arreglo vacío para estructurar y agrupar los datos del historial de compras
    $historial_agrupado = [];

    // Captura y sanitiza el estado del pedido proporcionado por parámetro GET, de existir
    $filtro_estado = isset($_GET['estado']) ? mysqli_real_escape_string($con, $_GET['estado']) : '';
    // Construye la cláusula condicional para filtrar las órdenes según su estado actual
    $where_estado = !empty($filtro_estado) ? "WHERE h.estado = '$filtro_estado'" : "";

    // Define el límite estricto de registros a mostrar por cada página
    $registros_por_pagina = 10;
    // Obtiene el número de página solicitado por URL o asigna 1 por defecto
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    // Restringe el valor numérico de la página para que no sea menor a 1
    if ($pagina < 1) $pagina = 1;
    // Calcula el margen de desplazamiento (offset) para el arranque de la consulta de registros
    $offset = ($pagina - 1) * $registros_por_pagina;

    // Ejecuta una consulta para contar el número total de registros en la tabla vinculada con el filtro activo
    $res_total = mysqli_query($con, "SELECT COUNT(*) as total FROM historial_compras h $where_estado");
    // Obtiene el número total de registros extraído del arreglo asociativo
    $total_registros = mysqli_fetch_assoc($res_total)['total'];
    // Calcula el número total de páginas redondeando hacia arriba la división de los registros
    $total_paginas = ceil($total_registros / $registros_por_pagina);

    // Ensambla la consulta relacional combinando el historial, los usuarios y los productos
    $query="SELECT
            h.id_producto,          
            u.nombre_usuario,       
            u.numero_telefono,      
            u.direccion,
            u.cp,
            p.nombre_producto,      
            p.precio_producto,      
            h.cantidad_comprada,    
            h.fecha_compra,          
            h.codigo_orden,
            h.estado
        FROM historial_compras AS h 
        JOIN usuario AS u ON h.id_usuario = u.id_usuario 
        JOIN producto AS p ON p.id_producto = h.id_producto 
        $where_estado
        ORDER BY h.fecha_compra DESC 
        LIMIT $offset, $registros_por_pagina;"; 
        
    // Ejecuta la consulta SQL ensamblada y almacena el conjunto de resultados
    $result = mysqli_query($con, $query);
    // Obtiene el número de filas o registros que devolvió la consulta
    $n_productos=mysqli_num_rows($result);
    
    // Itera de forma secuencial sobre cada fila del resultado obtenido
    while ($row = mysqli_fetch_array($result)):
        // Extrae el precio unitario del producto en la iteración actual
        $precio=$row['precio_producto'];
        // Extrae el volumen de piezas adquiridas de dicho producto
        $cantidad=$row['cantidad_comprada'];
        // Calcula el importe subtotal multiplicando el precio unitario por la cantidad
        $total=$precio*$cantidad;
        // Extrae la fecha en la que se concretó la adquisición
        $fecha=$row['fecha_compra'];
        // Extrae el nombre del usuario para emplearlo en la agrupación de órdenes simultáneas
        $usuario_id=$row['nombre_usuario']; 
        
        // Define una llave única priorizando el código de orden o construyendo uno temporal si es pedido antiguo
        $llave_grupo = $row['codigo_orden'] ? $row['codigo_orden'] : 'SIN_CODIGO_'.$fecha.'_'.$usuario_id;
        // Asigna el estado devuelto en el registro o asume 'Entregado' por retrocompatibilidad
        $estado = $row['estado'] ? $row['estado'] : 'Entregado';
        
        // Evalúa si la orden actual no ha sido declarada previamente en la matriz de agrupación
        if (!isset($historial_agrupado[$llave_grupo])) {
            // Estructura y asocia los metadatos generales del comprador y la orden al nuevo índice
            $historial_agrupado[$llave_grupo] = array(
                'fecha' => $fecha,
                'codigo_orden' => $row['codigo_orden'],
                'estado' => $estado,
                'nombre_usuario' => $row['nombre_usuario'],
                'numero_telefono' => $row['numero_telefono'],
                'direccion' => $row['direccion'],
                'cp' => $row['cp'],
                'total_compra' => 0,
                'productos' => array()
            );
        }
        
        // Captura el identificador único del artículo evaluado
        $id_prod = $row['id_producto'];
        // Evalúa si el artículo actual no ha sido anexado a la lista de esta orden específica
        if (!isset($historial_agrupado[$llave_grupo]['productos'][$id_prod])) {
            // Instancia y asocia los detalles elementales del artículo en el arreglo
            $historial_agrupado[$llave_grupo]['productos'][$id_prod] = array(
                "id_producto"=>$id_prod,
                "nombre_producto"=>$row['nombre_producto'],
                "precio_producto"=>$precio,
                "cantidad_comprada"=>0,
                "total"=>0,
            );
        }
        
        // Acumula la cantidad progresivamente en caso de repetición del producto dentro de la orden
        $historial_agrupado[$llave_grupo]['productos'][$id_prod]['cantidad_comprada'] += $cantidad;
        // Acumula el costo en el total específico de este artículo
        $historial_agrupado[$llave_grupo]['productos'][$id_prod]['total'] += $total;
        
        // Suma el subtotal al monto global de cobro correspondiente a toda la orden
        $historial_agrupado[$llave_grupo]['total_compra'] += $total;
    // Concluye el ciclo iterativo de resultados de la base de datos
    endwhile;
    
    /* 5. CERRAR CONEXIÓN Y MANDAR A LA VISTA HTML */
    
    // Cierra la conexión activa a la base de datos para liberar los recursos
    mysqli_close($con);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Carga modular base de componentes compartidos como meta etiquetas -->
    <?php include "head_html.php";?>
    <!-- Dictamina el nombre visible en la pestaña o ventana del explorador -->
    <title>Consultar historial - MODO ADMIN</title>
    <!-- Enlace referenciando a la figura icono adjunta al título local de la pestaña -->
    <link rel="shortcut icon" href="../img/logo.jpg">
    <!-- Estructuras de optimización asíncrona y emparejamiento predefinido entre navegadores web (Normalize) -->
    <link rel="preload" href="../css/normalize.css" as="style">
    <link rel="stylesheet" href="../css/normalize.css">
    <!-- Declaración anticipada e inserción de la configuración gráfica nativa del proyecto -->
    <link rel="preload" href="../css/estilo_generico.css" as="style">
    <link rel="stylesheet" href="../css/estilo_generico.css">
    <link rel="preload" href="../css/styles-consultar_historial.css" as="style">
    <link rel="stylesheet" href="../css/styles-consultar_historial.css">
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
    <h1 style="position: sticky; top: 50px; z-index: 100; background-color: rgba(255, 255, 255, 0.95); padding: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">Historial de ventas</h1>

    <!-- Pestañas interactivas de navegación para filtrar la consulta de órdenes por estado logístico -->
    <ul class="nav nav-tabs" style="margin-bottom: 20px;">
        <li class="<?= empty($filtro_estado) ? 'active' : '' ?>"><a href="?estado=">Todos</a></li>
        <li class="<?= $filtro_estado == 'En proceso' ? 'active' : '' ?>"><a href="?estado=En%20proceso">En proceso</a></li>
        <li class="<?= $filtro_estado == 'Confirmado' ? 'active' : '' ?>"><a href="?estado=Confirmado">Confirmados</a></li>
        <li class="<?= $filtro_estado == 'Entregado' ? 'active' : '' ?>"><a href="?estado=Entregado">Entregados</a></li>
        <li class="<?= $filtro_estado == 'Cancelado' ? 'active' : '' ?>"><a href="?estado=Cancelado">Cancelados</a></li>
    </ul>

    <!-- Evalúa si no existen registros de compras recuperados en la consulta actual -->
    <?php if ($n_productos==0) :?>
    <h3 class="text-center text-muted" style="margin-top: 50px;">No se encontraron pedidos.</h3>
    <!-- Ramificación ejecutada en caso de encontrar uno o múltiples registros de compras -->
    <?php else: ?>
    
    <!-- Bucle para iterar y desplegar visualmente cada bloque u orden agrupada -->
    <?php foreach ($historial_agrupado as $grupo): ?>
    <!-- Estructura de cabecera divisoria con información sintetizada de la compra -->
    <h3 style="background-color: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 30px; border-left: 5px solid #337ab7;">
    <b><?= $grupo['fecha'] ?></b> | <b><?= htmlspecialchars($grupo['nombre_usuario']) ?></b> | Total <span style="color: #5cb85c;">$<?= number_format(floatval($grupo['total_compra']), 2) ?></span>
    | Estado: <span class="label label-<?= $grupo['estado']=='Cancelado' ? 'danger' : ($grupo['estado']=='Entregado' ? 'success' : 'warning') ?>"><?= $grupo['estado'] ?></span>
    <div class="pull-right">
         <!-- Validadores para mostrar u ocultar la capacidad de gestión en compras antiguas sin código formal -->
         <?php if($grupo['codigo_orden'] && strpos($grupo['codigo_orden'], 'SIN_CODIGO') === false && strpos($grupo['codigo_orden'], 'OLD') === false): ?>
         <a href="estado_compra.php?orden=<?= $grupo['codigo_orden'] ?>" class="btn btn-sm btn-info"><span class="glyphicon glyphicon-edit"></span> Gestionar Estado</a>
         <a href="etiqueta_envio.php?orden=<?= $grupo['codigo_orden'] ?>" target="_blank" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-print"></span> Hoja de Envío</a>
         <?php else: ?>
         <span style="font-size: 14px; color: gray;">Pedido antiguo sin hoja</span>
         <?php endif; ?>
     </div>
    </h3>
    <!-- Contenedor responsivo tabular de los artículos pertenecientes a la orden -->
    <div class="table-responsive" style="margin-bottom: 20px;">
        <table class="table table-hover">
            <tr>
                <th>Imagen producto</th>
                <th>Nombre producto</th>
                <th>Precio unitario</th>
                <th>Cantidad comprada</th>
                <th>Subtotal</th>
            </tr>
            <!-- Estructura iterativa para desglosar la tabla interna artículo por artículo -->
            <?php foreach ($grupo['productos'] as $producto): ?>
            <tr>
                <td>
                    <img src="../img/productos/<?= $producto["id_producto"]; ?>.png" alt="producto <?= $producto["nombre_producto"]; ?>" class="imagen">
                </td>
                <td><?=$producto['nombre_producto']; ?></td>
                <td>$<?= number_format(floatval($producto['precio_producto'])); ?></td>
                <td><?=$producto['cantidad_comprada']?></td>
                <td>$<?= number_format(floatval($producto['total'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endforeach; ?>

    <!-- Piezas selectoras interactivas habilitando movimiento fragmentario sobre listados robustos (Paginación) -->
    <?php if(isset($total_paginas) && $total_paginas > 1): ?>
    <div class="text-center">
        <ul class="pagination">
            <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="<?= ($i == $pagina) ? 'active' : '' ?>"><a href="?pagina=<?= $i ?>&estado=<?= urlencode($filtro_estado) ?>"><?= $i ?></a></li>
            <?php endfor; ?>
        </ul>
    </div>
    <?php endif; ?>
    <?php endif; ?><br>
</body>

</html>