<?php
/* 1. CONFIGURACIÓN Y SESIÓN */

// Inicia o reanuda la sesión del usuario para mantener la persistencia y el estado de la aplicación
session_start();
// Incluye el archivo de configuración que almacena las credenciales de la base de datos
include_once("./config/config.php");

/* 2. CONEXIÓN ÚNICA A LA BASE DE DATOS */

// Establece la conexión a la base de datos MySQL utilizando las credenciales importadas
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
// Verifica de manera temprana si ocurrió algún error durante el intento de vinculación
if (mysqli_connect_errno()) {
    // Interrumpe la ejecución de todo el script y notifica la falla
    die("Failed to connect to MySQL: " . mysqli_connect_error());
}

/* 3. CALCULAR BADGE DEL CARRITO */

// Inicializa en cero la variable destinada a contabilizar el volumen del carrito virtual
$cantidad_carrito = 0;
// Comprueba si existe una sesión personal activa por un usuario logueado
if(isset($_SESSION['sesion_personal'])){
    // Ejecuta una consulta para calcular la suma total de las cantidades anexadas al carrito de ese ID en específico
    $res_badge = mysqli_query($con, "SELECT SUM(cantidad_seleccionada) as total FROM carrito WHERE id_usuario=".$_SESSION['sesion_personal']['id']);
    // Confirma la correcta recepción de datos y asocia la respuesta al arreglo contenedor
    if($res_badge && $row_badge = mysqli_fetch_assoc($res_badge)){
        // Asigna el valor resultante recuperado o mantiene 0 si el valor devuelto es nulo
        $cantidad_carrito = $row_badge['total'] ? $row_badge['total'] : 0;
    }
}

/* 4. FILTROS DE BÚSQUEDA Y CATEGORÍAS */

// Captura y aplica limpieza contra inyecciones SQL al parámetro textual de búsqueda (si existe)
$buscar = isset($_GET['buscar']) ? mysqli_real_escape_string($con, $_GET['buscar']) : '';
// Captura y sanitiza la selección de categoría elegida desde el filtro
$categoria = isset($_GET['categoria']) ? mysqli_real_escape_string($con, $_GET['categoria']) : '';

// Construye la cláusula predeterminada para restringir la muestra y ocultar el inventario agotado
$where_clause = "WHERE cantidad_disponible > 0";
// Concatena un filtro adicional de coincidencia a la cláusula si el cliente envió texto por barra de búsqueda
if (!empty($buscar)) { $where_clause .= " AND (nombre_producto LIKE '%$buscar%' OR descripcion_producto LIKE '%$buscar%')"; }
// Concatena un filtro restrictivo de departamento a la cláusula general en caso de solicitar una categoría en específico
if (!empty($categoria)) { $where_clause .= " AND categoria = '$categoria'"; }

// Inicializa el arreglo dinámico que alimentará a la caja de lista de opciones de categorías
$categorias_list = [];
// Procede a consultar en la tabla todas las variaciones únicas de categorías configuradas en la tienda
$res_categorias = mysqli_query($con, "SELECT DISTINCT categoria FROM producto WHERE categoria IS NOT NULL AND categoria != ''");
// Itera secuencialmente los resultados agrupados y apila los valores finales al arreglo dinámico local
while($cat = mysqli_fetch_assoc($res_categorias)){ $categorias_list[] = $cat['categoria']; }

/* 5. PAGINACIÓN Y LISTADO DE PRODUCTOS */

// Define estrictamente el límite de objetos individuales exhibidos por pantalla
$registros_por_pagina = 15;
// Recupera numéricamente la página solicitada o en su defecto asienta a la primera inicial
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
// Impide el desplazamiento manual hacia un rango inferior inválido garantizando como mínimo el 1
if ($pagina < 1) $pagina = 1;
// Calcula algebraicamente el espaciado (offset) necesario para iniciar la consulta dependiendo la ventana del índice
$offset = ($pagina - 1) * $registros_por_pagina;

// Lanza consulta para contar el acumulado íntegro de mercancía emparejado con los filtros definidos
$res_total = mysqli_query($con, "SELECT COUNT(*) as total FROM producto $where_clause;");
// Extrae directamente la cifra devuelta hacia la variable totalizadora
$total_registros = mysqli_fetch_assoc($res_total)['total'];
// Redondea hacia arriba la división entre registros totales y capacidad por hoja calculando cuantas vistas se necesitan
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Instancia un arreglo recolector para guardar los objetos arrojados listos para imprimirse en la vista de cuadrícula
$productos_list = [];
// Lanza la consulta selectiva del lote particular integrando cláusulas condicionales con los topes de paginación
$result = mysqli_query($con, "SELECT * FROM producto $where_clause LIMIT $offset, $registros_por_pagina;");
// Valida contención exitosa y desglosa los componentes internos guardándolos dentro del arreglo iterador
if ($result) { while ($row = mysqli_fetch_assoc($result)) { $productos_list[] = $row; } }

/* 6. CERRAR CONEXIÓN (El HTML inferior ya no usa comandos SQL activos) */

// Libera el recurso de comunicación cerrando de manera segura el hilo con la base de datos
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Carga modular base de componentes compartidos como meta etiquetas y llamadas primarias a CDN -->
    <?php include("./php/head_html.php"); ?>
    <!-- Dictamina el nombre visible en la pestaña o ventana del explorador -->
    <title>Página de inicio</title>
    <!-- Enlace referenciando a la figura icono adjunta al título local de la pestaña -->
    <link rel="shortcut icon" href="./img/logo.jpg">
    <!-- Estructuras de optimización asíncrona y emparejamiento predefinido entre navegadores web (Normalize) -->
    <link rel="preload" href="./css/normalize.css" as="style">
    <link rel="stylesheet" href="./css/normalize.css">
    <!-- Declaración anticipada e inserción de la configuración gráfica nativa del proyecto -->
    <link rel="preload" href="./css/styles.css" as="style">
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="preload" href="./css/estilo_generico.css" as="style">
    <link rel="stylesheet" href="./css/estilo_generico.css">
    
    <style>
        /* Modificación responsiva anidada forzando la diagramación a cuadrícula si se detectan pantallas amplias */
        @media (min-width: 1024px) {
            .principal {
                /* Subordina la exhibición forzándola hacia modelo dinámico tipo Grid */
                display: grid !important;
                /* Crea una regla obligatoria dictando el despliegue a una longitud total de cinco porciones equitativas */
                grid-template-columns: repeat(5, 1fr) !important;
                /* Estipula huecos separadores simétricos con longitud estática a 15 píxeles */
                gap: 15px;
                /* Protege contra una distensión colosal imponiendo margen máximo al contenedor */
                max-width: 1250px;
                /* Consolida alineamiento céntrico en su disposición horizontal */
                margin: 0 auto;
            }
            .principal .card {
                /* Permite a la tarjeta adueñarse por completo del ancho otorgado por su celda constructiva */
                width: 100% !important;
                /* Destruye el excedente marginal colateral originario */
                margin: 0 !important;
            }
        }
    </style>
</head>

<body>
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
                    <a class="navbar-brand" href="#">TuxtlaTech</a>
                </div>
                <!-- Contenedor que agrupa componentes enlazables escondidos al colapsar resolución inferior -->
                <div class="collapse navbar-collapse" id="myNavbar">
                    <!-- Disposición en lista de viñetas alineada hacia el extremo occidental del menú -->
                    <ul class="nav navbar-nav">
                        <li class="active"><a href="#">Lista de productos</a></li>
                    </ul>
                    <!-- Segmento bifurcado condicionado estrictamente al estatus del logueo -->
                    <?php if (!isset($_SESSION['sesion_personal'])):?>
                    <!-- Envoltura aglutinante enfocada a cuentas sin registro orientada hacia el extremo oriental -->
                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <a href="./php/registro.php"><span class="glyphicon glyphicon-user"></span>Registrarse
                            </a>
                        </li>
                        <li>
                            <a href="./php/iniciar_sesion.php"><span class="glyphicon glyphicon-log-in">
                                </span> Ingresar</a>
                        </li>
                    </ul>
                    <!-- Escenario bifurcado enfocado hacia el reconocimiento si una persona validó sesión de manera exitosa -->
                    <?php else: ?>
                    <ul class="nav navbar-nav">
                        <!-- Receptáculo pasivo a nombre de quien gestiona la interfaz actual del sistema -->
                        <li class="navbar-text quita_margen">
                            <a href="./php/perfil.php" class="navbar-link">
                                Sesión iniciada como 
                                <!-- Interpolación dinámica del seudónimo asignado a la identificación conectada -->
                                <u><?=$_SESSION['sesion_personal']['nombre']?></u>
                            </a>
                        </li>
                    </ul>
                    <!-- Envoltura aglutinante oriental con capacidades dedicadas a la gestión personal de cuentas vinculadas -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Valida integralmente si el actor operativo posee facultad general como súper usuario -->
                        <?php if($_SESSION['sesion_personal']['super']==1): ?>
                            <!-- Implementación particular de despliegue extra con tareas directivas de la tienda -->
                            <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                            aria-expanded="false">MODO ADMIN <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="./php/consultar_historial.php"><span class="glyphicon glyphicon-list"></span> Consultar historial de ventas</a></li>
                            <li><a href="./php/modificar_productos.php"><span class="glyphicon glyphicon-cog"></span> Modificar productos</a></li>
                        </ul>
                    </li>
                        <!-- Finaliza estructura exclusiva dedicada al modo administrable -->
                        <?php endif; ?>
                        <li>
                            <a href="./php/cerrar_sesion.php"><span class="glyphicon glyphicon-log-out"></span> Cerrar sesión</a>
                        </li>
                        <li>
                            <a href="./php/carrito.php"><span class="glyphicon glyphicon-shopping-cart"></span> Carrito de compras
                                <!-- Procesa la inclusión gráfica de la burbuja (badge) únicamente ante valores positivos de productos -->
                                <?php if($cantidad_carrito > 0): ?>
                                <!-- Despliega un fondo rojizo que recubre e imprime la cifra indicativa acumulada -->
                                <span class="badge" style="background-color: #d9534f;"><?= $cantidad_carrito ?></span>
                                <!-- Finaliza evaluación de indicador estético en menú de carrito -->
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                    <!-- Finaliza evaluación en la etapa general del navbar según inicio de sesión validado -->
                    <?php endif ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Segmento que alberga al bloque dinámico promocional visual conocido como carrusel de fotografías -->
    <div class="container-fluid carrusel" style="padding: 0;">
        <div id="myCarousel" class="carousel slide" data-ride="carousel">
            <!-- Estructuración de los componentes señaladores punteados posicionados debajo -->
            <ol class="carousel-indicators">
                <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                <li data-target="#myCarousel" data-slide-to="1"></li>
                <li data-target="#myCarousel" data-slide-to="2"></li>
                <li data-target="#myCarousel" data-slide-to="3"></li>
            </ol>
            <!-- Contenedor matriz para encapsular todos los afiches y capturas transitivas del carrusel -->
            <div class="carousel-inner">
                <div class="item active">
                    <img src="./img/carrusel/b.jpg" alt="setup1">
                    <div class="carousel-caption">
                        <h3>Monitores</h3>
                        <p>y accesorios</p>
                    </div>
                </div>
                <div class="item">
                    <img src="./img/carrusel/a.jpg" alt="setup2">
                    <div class="carousel-caption">
                        <h3>Comodidad</h3>
                        <p>y confiabilidad</p>
                    </div>
                </div>
                <div class="item">
                    <img src="./img/carrusel/c.jpg" alt="setup3">
                    <div class="carousel-caption">
                        <h3>Al mejor precio</h3>
                        <p>ofertas todos los dias</p>
                    </div>
                </div>
                <div class="item">
                    <img src="./img/carrusel/d.jpg" alt="setup4">
                    <div class="carousel-caption">
                        <h3>Bienvenido</h3>
                        <p>a una tienda como tú</p>
                    </div>
                </div>
            </div>
            <!-- Botones anclados en laterales implementados sobre un ancla para navegar el carrusel de forma manual -->
            <a class="left carousel-control" href="#myCarousel" data-slide="prev">
                <span class="glyphicon glyphicon-chevron-left"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="right carousel-control" href="#myCarousel" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </div>

    <!-- Subcabecera semántica separadora con un esquema fijable pegajoso ante su deslizamiento -->
    <h3 class="container text-center" style="margin-bottom: .6em; margin-top: .5em; position: sticky; top: 50px; z-index: 100; background-color: rgba(255, 255, 255, 0.95); padding: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">Lista de articulos</h3>

    <!-- Envoltura designada para alojar comandos formales de restricción al catálogo (Buscador y Filtrado Categórico) -->
    <div class="container text-center" style="margin-bottom: 20px;">
        <!-- Configuración bajo el método GET logrando la incrustación remota en barra de los resultados definidos por el internauta -->
        <form action="index.php" method="GET" class="form-inline">
            <div class="form-group" style="display:inline-block; margin-right:10px;">
                <!-- Componente textual para redactar sintaxis de interés interpolado al parámetro existente predeterminado -->
                <input type="text" name="buscar" class="form-control" placeholder="Buscar productos..." value="<?= htmlspecialchars(isset($_GET['buscar']) ? $_GET['buscar'] : '') ?>">
            </div>
            <div class="form-group" style="display:inline-block; margin-right:10px;">
                <!-- Menú desplegable para orientar los resultados únicamente a un rubro del inventario -->
                <select name="categoria" class="form-control">
                    <option value="">Todas las categorías</option>
                    <!-- Se cicla e inyecta la lista dinámica compilada previamente por el motor de base de datos -->
                    <?php foreach($categorias_list as $cat): ?>
                        <!-- Se genera elemento opcionable, evaluando a su vez si amerita retener su estado selectivo -->
                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($categoria === $cat) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($cat)) ?>
                        </option>
                    <!-- Concluye paso iterativo con opciones categóricas -->
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Pulsador de ejecución sobre la validación integral y sometimiento del filtrado -->
            <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Buscar</button>
            <!-- Botón orientativo reenvíador a modo de limpieza absoluta retirando los parámetros de búsqueda -->
            <a href="index.php" class="btn btn-default">Limpiar</a>
        </form>
    </div>

    <!-- Contenedor fundamental que exhibirá de forma enlazada el inventario de acuerdo al modelado en grilla de tarjetas -->
    <main class="principal">
        <!-- Proceso iterativo y programático para incrustar datos base a manera automatizada sobre la página -->
        <?php
            // Comienza comprobación cerciorándose si el conjunto receptor atesora mercancías a ser mapeadas
            if (count($productos_list) > 0):
                // Recorre uno por uno al total arrojado extraído según la configuración general dictaminada
                foreach ($productos_list as $row): 
                ?>
            <!-- Célula estructural de Bootstrap moldeando información básica resumida por cada artículo -->
            <div class="card text-center">
                <!-- Adjunta dinámicamente imagen referencial a partir del número de clave ID del ente -->
                <img class="card-img-top" src="./img/productos/<?= $row['id_producto'] ?>.png" alt="Card image cap">
                <!-- Resguardo de la identidad textual y valuaciones del producto -->
                <div class="card-body">
                    <!-- Trazado separativo elemental decorativo -->
                    <hr class="solid">
                    
                    <!-- Marco dimensionalmente estable impidiendo rupturas entre tarjetas por longitud de subtítulos desfasados -->
                    <div id="altura_caja">
                        <p class="card-text">
                            <!-- Imprime visiblemente en la celda el denominativo del objeto procesado -->
                            <?= $row['nombre_producto'] ?>
                        </p>
                    </div>

                    <!-- Trazado separativo elemental decorativo previo al importe económico -->
                    <hr class="solid">
                    <!-- Imprime cantidad formateada agregando la notación decimal regularizada con dos cifras -->
                    <p class="card-text">$
                        <?= number_format(floatval($row['precio_producto']), 2, '.', ',') ?>
                    </p>
                </div>
                <!-- Evaluación restrictiva acerca de la conexión oficial de un usuario -->
                <?php if (isset($_SESSION['sesion_personal'])):?>
                    <!-- Canaliza orden directa de transaccionabilidad enviando a vista individual con información suplementaria -->
                    <a href="./php/info_producto.php?id=<?= $row['id_producto'] ?>" class="btn btn-sm comprar">Comprar</a>
                    <!-- Condicionante reactivo si la carencia de registro domina en el caso concreto de este internauta -->
                    <?php else: ?>
                        <!-- Coacciona la interacción invitándole a iniciar primero la autenticación del registro -->
                        <a href="./php/iniciar_sesion.php" class="btn btn-sm comprar">Comprar</a>
                        <!-- Concluye estructura de verificación selectiva a las tarjetas -->
                        <?php endif ?>
                    </div>
                    <!-- Finaliza despliegue celular y continúa operando de quedar más datos en stock sin trazar -->
                    <?php
                endforeach;
            // Obtención del resto sobrante asumiendo agrupaciones divisorias en grupos de cinco celdas
            $n_relleno=(count($productos_list))%5;
            // Realiza confirmación validando de carecer asimetrías o huecos en un último grupo arrojado
            if($n_relleno != 0):
                // Estipula e instaura a ciclar la cantidad deficiente que compensará los recuadros estirados indeseados
                for ($x=0; $x < 5-$n_relleno; $x++):?>
                <!-- Dibuja un cajón ficticio espectral inobservable forzando ocupación volumétrica equilibrada a la pantalla -->
                <div class="card" style="border: solid 1px transparent; background: transparent; box-shadow: none;">
                </div>
                <!-- Concluye bucle de completamiento simulado -->
                <?php
                endfor;
            // Concluye paso procesal a favor de asimetrías
            endif;
            // En caso que el contador de inventario asocie a nulo o cero objetos con dicho rubro (Fallo en filtro o búsqueda errónea)
            else:
                // Despliega alerta ilustrativa notificando sobre un estante estéril de oportunidades según peticiones enviadas
                echo "<div class='container text-center' style='grid-column: 1 / -1; margin-top: 20px; margin-bottom: 20px;'><h4>No se encontraron productos que coincidan con tu búsqueda.</h4></div>";
            // Termina la fase generatriz en base al catálogo global
            endif;
        ?>
    </main>
    
    <!-- Piezas selectoras interactivas habilitando movimiento fragmentario sobre listados robustos (Paginación) -->
    <?php if(isset($total_paginas) && $total_paginas > 1): ?>
    <div class="text-center" style="margin-bottom: 20px;">
        <ul class="pagination">
            <?php 
            // Inicializa sintaxis para propagar las limitaciones en las ventanas paginables siguientes
            $query_params = "";
            // Inyecta el texto a buscar evaluado a manera de eslabón hacia la secuencia direccional actual
            if(!empty($_GET['buscar'])) $query_params .= "&buscar=".urlencode($_GET['buscar']);
            // Inyecta el texto categorizado a manera de eslabón preservándolo al transicionar entre fojas
            if(!empty($_GET['categoria'])) $query_params .= "&categoria=".urlencode($_GET['categoria']);
            
            // Ejecuta el acomodo visual de los pulsadores partiendo por una cuenta natural desde uno hacia el máximo factible
            for($i = 1; $i <= $total_paginas; $i++): ?>
                <!-- Modela recuadro de lista indicando activación cromática en el ítem concordante de turno -->
                <li class="<?= ($i == $pagina) ? 'active' : '' ?>"><a href="?pagina=<?= $i ?><?= $query_params ?>"><?= $i ?></a></li>
            <!-- Culmina iteración del ensamblado paginador -->
            <?php endfor; ?>
        </ul>
    </div>
    <!-- Clausura elemento condicionado en visualización de subpáginas -->
    <?php endif; ?>

    <!-- Pie de página terminal (Footer) para la contención a la autoría o enlaces mercantiles externos fijos -->
    <div class="footer">
        <a href="SHONOE">
            COPYRIGHT © 2026<br>
            DISEÑADO POR: AJFA
        </a>
    </div>
</body>

</html>