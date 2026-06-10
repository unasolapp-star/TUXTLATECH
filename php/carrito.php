<?php
/* 1. CONFIGURACIÓN Y SESIÓN */

// Importa el archivo de configuración con las credenciales de la base de datos
require_once("../config/config.php");
// Inicia o reanuda la sesión actual para el manejo de variables globales
session_start();

/* 2. SEGURIDAD: Evitar acceso no autorizado */

// Comprueba si la variable de sesión no existe para detectar usuarios sin autenticar
if (!isset($_SESSION['sesion_personal'])) {
    // Redirecciona al visitante hacia la pantalla de inicio de sesión
    header("Location: ./iniciar_sesion.php");
    // Finaliza la ejecución del script para evitar renderizados indebidos tras la redirección
    exit;
}

// Recupera el identificador único del usuario desde la variable de sesión
$id_usuario = $_SESSION['sesion_personal']['id'];

/* 3. CONEXIÓN ÚNICA A LA BASE DE DATOS */

// Establece la conexión con el servidor MySQL mediante los parámetros configurados
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
// Evalúa si ocurrió algún error durante la conexión
if (mysqli_connect_errno()) {
    // Detiene el proceso y muestra un mensaje de fallo de conexión
    die("Fallo al conectar a MySQL: " . mysqli_connect_error());
}

/* 4. OBTENER TOTAL DE ÍTEMS PARA EL ICONO "BADGE" */

// Inicializa en cero la variable que almacena la cantidad total de artículos en el carrito
$cantidad_carrito = 0;
// Ejecuta una consulta para sumar todas las cantidades seleccionadas por el usuario actual
$res_badge = mysqli_query($con, "SELECT SUM(cantidad_seleccionada) as total FROM carrito WHERE id_usuario=" . $id_usuario);
// Verifica que la consulta fue exitosa y extrae el resultado asociativo
if ($res_badge && $row_badge = mysqli_fetch_assoc($res_badge)) {
    // Asigna el valor sumado recuperado o mantiene cero si el resultado es nulo
    $cantidad_carrito = $row_badge['total'] ? $row_badge['total'] : 0;
}

/* 5. CÁLCULO DE LA PAGINACIÓN */

// Define el límite estricto de registros a mostrar por cada página
$registros_por_pagina = 10;
// Obtiene el número de página solicitado por URL o asigna 1 por defecto
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
// Restringe el valor numérico de la página para que no sea menor a 1
if ($pagina < 1) $pagina = 1;
// Calcula el margen de desplazamiento para el arranque de la consulta de registros
$offset = ($pagina - 1) * $registros_por_pagina;

// Consulta la cantidad total de artículos distintos almacenados en el carrito de este usuario
$res_total = mysqli_query($con, "SELECT COUNT(*) as total FROM carrito WHERE id_usuario=" . $id_usuario);
// Extrae el conteo total de filas devueltas
$total_registros = mysqli_fetch_assoc($res_total)['total'];
// Calcula el total de páginas redondeando hacia arriba la división de registros
$total_paginas = ceil($total_registros / $registros_por_pagina);

/* 6. OBTENER INFORMACIÓN DEL CARRITO DEL USUARIO */

// Inicializa el arreglo que contendrá la información detallada de los productos a mostrar
$arreglo_de_productos = [];
// Inicializa el arreglo con el formato específico requerido por JavaScript para procesar la compra
$arreglo_para_comprar = [];
// Inicializa la variable que acumulará el costo total monetario del carrito
$suma_total = 0;

// Estructura la consulta relacional combinando el carrito y el catálogo de productos con paginación
$query_carrito = "SELECT p.id_producto, p.nombre_producto, p.precio_producto, p.cantidad_disponible, c.cantidad_seleccionada, c.id_carrito 
                  FROM carrito as c INNER JOIN producto as p ON c.id_producto = p.id_producto 
                  WHERE c.id_usuario = $id_usuario LIMIT $offset, $registros_por_pagina";
// Ejecuta la petición sobre la base de datos para extraer los artículos
$result = mysqli_query($con, $query_carrito);
// Obtiene y guarda la cantidad de filas resultantes de la búsqueda
$n_productos = mysqli_num_rows($result);

// Verifica si el carrito contiene al menos un producto en la página actual
if ($n_productos > 0) {
    // Itera de forma secuencial sobre cada artículo encontrado en el resultado
    while ($row = mysqli_fetch_assoc($result)) {
        // Calcula el importe subtotal multiplicando precio unitario por cantidad
        $subtotal = floatval($row['precio_producto']) * (int)$row['cantidad_seleccionada'];
        // Acumula progresivamente el importe en el contador global del total a pagar
        $suma_total += $subtotal;
        
        // Estructura y anexa los datos de visualización del artículo dentro del arreglo principal
        $arreglo_de_productos[] = [
            "id_carrito" => $row['id_carrito'],
            "id" => $row['id_producto'],
            "nombre" => $row['nombre_producto'],
            "precio" => $row['precio_producto'],
            "disponibles" => $row['cantidad_disponible'],
            "cantidad" => $row['cantidad_seleccionada'],
            "subtotal" => $subtotal
        ];
        
        // Anexa la dupla de cantidad e identificador formateada como cadena de texto requerida por JavaScript
        $arreglo_para_comprar[] = $row['cantidad_seleccionada'] . "," . $row['id_producto'];
    }
}

/* 7. CERRAR CONEXIÓN Y MANDAR A LA VISTA HTML */

// Cierra la conexión activa a la base de datos liberando los recursos
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Carga modular base de componentes compartidos como meta etiquetas -->
    <?php include "head_html.php" ?>
    <!-- Dictamina el nombre visible en la pestaña o ventana del explorador -->
    <title>Carrito de compras</title>
    <!-- Enlace referenciando a la figura icono adjunta al título local de la pestaña -->
    <link rel="shortcut icon" href="../img/logo.jpg">
    <!-- Estructuras de optimización asíncrona y emparejamiento predefinido entre navegadores web (Normalize) -->
    <link rel="preload" href="../css/normalize.css" as="style">
    <link rel="stylesheet" href="../css/normalize.css">
    <!-- Declaración anticipada e inserción de la configuración gráfica nativa del proyecto -->
    <link rel="preload" href="../css/estilo_generico.css" as="style">
    <link rel="preload" href="../css/styles-carrito.css" as="style">
    <link rel="stylesheet" href="../css/estilo_generico.css">
    <link rel="stylesheet" href="../css/styles-carrito.css">
    <!-- Inclusión de la lógica interactiva de carrito y compra mediante JavaScript -->
    <script type="text/javascript" src="../js/comprar_agregarcarrito.js"></script>
</head>

<body class="container">
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
                    </ul>
                    <!-- Despliegue textual descriptivo que reconoce a la persona con sesión activa -->
                    <ul class="nav navbar-nav">
                        <p class="navbar-text">Sesión iniciada como <a href="../php/perfil.php"
                                class="navbar-link"><u><?=$_SESSION['sesion_personal']['nombre']?></u></a></p>
                    </ul>
                    <!-- Envoltura aglutinante oriental con capacidades dedicadas a la gestión personal y navegación -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Valida integralmente si el actor operativo posee facultad general como súper usuario -->
                        <?php if ($_SESSION['sesion_personal']['super']==1): ?>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                aria-haspopup="true" aria-expanded="false">MODO ADMIN <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="../php/consultar_historial.php"><span
                                            class="glyphicon glyphicon-list"></span> Consultar historial</a></li>
                                <li><a href="../php/modificar_productos.php"><span
                                            class="glyphicon glyphicon-cog"></span> Modificar productos</a></li>
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

    <!-- Verifica la existencia de notificaciones de error transmitidas mediante parámetros URL -->
    <?php if (isset($_GET['error'])): ?>
        <?php
        // Prepara contenedor de texto vacío para la evaluación condicional del mensaje a devolver
        $mensaje = '';
        if ($_GET['error'] == 'stock') {
            $mensaje = 'No puedes agregar más unidades de este producto, has alcanzado el límite disponible.';
        } else if ($_GET['error'] == 'stock_insuficiente') {
            $mensaje = 'La compra no pudo ser procesada porque uno o más productos no tienen stock suficiente. Por favor, revisa tu carrito.';
        } else if ($_GET['error'] == 'compra_fallida') {
            $mensaje = 'Hubo un error inesperado al procesar tu compra. Por favor, inténtalo de nuevo.';
        }
        ?>
        <!-- Valida la contención de un texto de error y despliega el componente de alerta SweetAlert -->
        <?php if ($mensaje): ?>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Aviso',
                        text: '<?= htmlspecialchars($mensaje) ?>',
                        confirmButtonColor: '#d9534f'
                    });
                });
            </script>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Evalúa si el inventario personal del carrito carece de artículos cargados -->
    <?php if ($n_productos==0): ?>
    <h1 class="h1" style="position: sticky; top: 50px; z-index: 100; background-color: rgba(255, 255, 255, 0.95); padding: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">TU CARRITO ESTA VACIO</h1>
    <!-- Ramificación orientada a un carrito con uno o múltiples elementos pendientes -->
    <?php else: ?>
    <h1 class="h1" style="position: sticky; top: 50px; z-index: 100; background-color: rgba(255, 255, 255, 0.95); padding: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">CARRITO DE COMPRAS</h1>
    <div class="table-responsive">
        <table class="table table-hover">
            <tr>
                <!-- Checkbox maestro para seleccionar o deseleccionar todos los elementos -->
                <th style="text-align: center; vertical-align: middle;">
                    <input type="checkbox" id="seleccionar_todos" onclick="toggleSeleccionTodos(this)" title="Seleccionar todos">
                </th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Disponibles</th>
                <th>Cantidad seleccionada</th>
                <th>Precio</th>
                <th>Total individual</th>
                <th>Eliminar</th>
            </tr>

            <!-- Estructura iterativa para desglosar la tabla artículo por artículo -->
            <?php foreach ($arreglo_de_productos as $producto): ?>
            <tr>
                <td style="text-align: center; vertical-align: middle;">
                    <!-- Checkbox individual con el valor requerido para la compra y el subtotal asociado para JS -->
                    <input type="checkbox" class="checkbox_producto" value="<?= $producto["cantidad"] . ',' . $producto["id"] ?>" data-subtotal="<?= $producto["subtotal"] ?>" onchange="verificarSeleccion()">
                </td>
                <td>
                    <img src="../img/productos/<?= $producto["id"] ?>.png" alt="producto <?= $producto["nombre"] ?>"
                        class="imagen">
                </td>
                <td>
                    <span class="texto-informativo"><?= $producto["nombre"] ?></span>
                </td>
                <td>
                    <span class="texto-informativo"><?= $producto["disponibles"] ?></span>
                </td>
                <td>
                    <div class="btn-group">
                        <!-- Botón y ancla para decrementar progresivamente la unidad en la base de datos -->
                        <a href="modificar_producto_carrito.php?signo=0&id_carrito=
                            <?=$producto['id_carrito']?>&disp=<?=$producto["disponibles"]?>&cant=
                            <?=$producto["cantidad"]?>" class="btn btn-default">-
                        </a>

                        <!-- Etiqueta que visualiza la cantidad actual atesorada del producto -->
                        <button type="submit" class="btn btn-default disabled"><?= $producto["cantidad"] ?></button>

                        <!-- Botón y ancla para incrementar la unidad adquirida dentro del resguardo -->
                        <a href="modificar_producto_carrito.php?signo=1&
                            id_carrito=<?=$producto['id_carrito']?>&disp=<?=$producto["disponibles"]?>
                            &cant=<?=$producto["cantidad"]?>" class="btn btn-default">
                            +
                        </a>
                    </div>
                </td>
                <td>
                    <span
                        class="texto-informativo">$<?= number_format(floatval($producto["precio"]), 2, '.', ',') ?></span>
                </td>
                <td>
                    <span class="texto-informativo">
                        $<?= number_format($producto["subtotal"], 2, '.', ',') ?>
                    </span>
                </td>
                <td>
                    <!-- Botón orientativo asociado a un alert para desechar el ítem del registro de manera completa -->
                    <a href="eliminar_producto_carrito.php?id_carrito=<?=$producto['id_carrito']?>" class="btn btn-danger btn-sm" onclick="confirmarAccion(event, this.href, '¿Eliminar artículo?', '¿Estás seguro de que deseas eliminar este artículo?');">
                        <span class="glyphicon glyphicon-trash"></span> Eliminar
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <th>Total</th>
                <!-- El total dinámico iniciará en 0 o se calculará al cargar la página -->
                <td id="total_dinamico">$0.00</td>
                <td></td>
            </tr>
        </table>
    </div>
    <script>
    // Pone a la disposición del bloque JS el conjunto empaquetado y analizado del carro en formato JSON
    var arreglo_de_productos = JSON.parse('<?= json_encode($arreglo_para_comprar); ?>');

    // Función para marcar o desmarcar todos los checkboxes de los productos
    function toggleSeleccionTodos(source) {
        let checkboxes = document.querySelectorAll('.checkbox_producto');
        checkboxes.forEach(function(cb) {
            cb.checked = source.checked;
        });
        // Actualizamos el total a pagar después de marcar/desmarcar todos
        actualizarTotal();
    }

    // Función para verificar si todos están marcados y actualizar el checkbox principal
    function verificarSeleccion() {
        let checkboxes = document.querySelectorAll('.checkbox_producto');
        let todosMarcados = true;
        for (let i = 0; i < checkboxes.length; i++) {
            if (!checkboxes[i].checked) {
                todosMarcados = false;
                break;
            }
        }
        document.getElementById('seleccionar_todos').checked = todosMarcados;
        // Actualizamos el total a pagar después de cada cambio individual
        actualizarTotal();
    }

    // Función para recalcular y mostrar el precio total sumando únicamente los artículos marcados
    function actualizarTotal() {
        let checkboxes = document.querySelectorAll('.checkbox_producto:checked');
        let suma = 0;
        checkboxes.forEach(function(cb) {
            suma += parseFloat(cb.getAttribute('data-subtotal'));
        });
        document.getElementById('total_dinamico').innerText = '$' + suma.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Función para recopilar y enviar solo los artículos seleccionados a la compra
    function procesarCompraSeleccionados() {
        let checkboxes = document.querySelectorAll('.checkbox_producto:checked');
        if (checkboxes.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Aviso', text: 'Por favor, selecciona al menos un producto para comprar.', confirmButtonColor: '#d9534f', backdrop: 'rgba(0,0,0,0.8)' });
            return;
        }
        let arreglo_seleccionados = [];
        checkboxes.forEach(function(cb) { arreglo_seleccionados.push(cb.value); });
        
        // Llamamos a la función existente mandando únicamente los elementos marcados
        enviarAPantallaDeCompraMuchos(arreglo_seleccionados);
    }

    // Asegurar que el total se calcule correctamente al cargar la página (por si el navegador recuerda selecciones previas)
    document.addEventListener("DOMContentLoaded", function() {
        actualizarTotal();
    });
    </script>
    
    <!-- Piezas selectoras interactivas habilitando movimiento fragmentario sobre listados robustos (Paginación) -->
    <?php if(isset($total_paginas) && $total_paginas > 1): ?>
    <div class="text-center">
        <ul class="pagination">
            <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="<?= ($i == $pagina) ? 'active' : '' ?>"><a href="?pagina=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Botonera basal que controla las acciones integrales respecto a los productos exhibidos -->
    <div class="posiciona-botones">
        <!-- Disparador hipervinculado de borrado permanente e instantáneo para este perfil -->
        <a href="vaciar_carrito.php"><input type="submit" class="btn btn-default boton" value="Vaciar carrito"></a>
        <!-- Disparador hacia la pantalla de verificación que procesa únicamente los seleccionados -->
        <input type="button" class="btn btn-default boton" value="Comprar" onclick="procesarCompraSeleccionados()">
    </div>
    <?php endif ?>
</body>

</html>