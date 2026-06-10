-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-05-2022 a las 06:25:46
-- Versión del servidor: 10.4.22-MariaDB
-- Versión de PHP: 8.1.2

-- Configura el modo SQL para evitar que el valor cero genere un incremento automático
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
-- Inicia una nueva transacción para asegurar la integridad atómica de los datos a restaurar
START TRANSACTION;
-- Establece la zona horaria del servidor de base de datos al formato UTC estándar (+00:00)
SET time_zone = "+00:00";


-- Configura el set de caracteres del cliente a usar durante la importación
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
-- Configura el set de caracteres de los resultados
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
-- Configura el nivel de colación predeterminado para las conexiones
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
-- Establece la codificación general a utf8mb4 predeterminada en este volcado
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tienda_online`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--
-- Almacena los productos que los usuarios añaden a su carrito de compras de forma temporal.

-- Sentencia para inicializar la estructura de la tabla del carrito
CREATE TABLE `carrito` (
  -- Define la columna requerida para almacenar el identificador del comprador
  `id_usuario` int(11) NOT NULL,
  -- Define la columna requerida para almacenar el identificador del artículo
  `id_producto` int(11) NOT NULL,
  -- Declara la llave primaria que identificará de manera única cada fila insertada
  `id_carrito` int(11) NOT NULL,
  -- Reserva el espacio numérico para guardar cuántos de esos artículos se seleccionaron
  `cantidad_seleccionada` int(11) DEFAULT NULL
-- Asigna el motor InnoDB (que permite llaves foráneas) y la codificación utf8mb4
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_compras`
--
-- Registra las transacciones o compras ya finalizadas de forma permanente.

-- Sentencia para inicializar la estructura de los registros de compra
CREATE TABLE `historial_compras` (
  -- Declara la columna que fungirá como identificador principal único de cada venta
  `id_historial` int(11) NOT NULL,
  -- Permite enlazar opcionalmente quién fue el usuario que hizo el pedido
  `id_usuario` int(11) DEFAULT NULL,
  -- Permite enlazar opcionalmente el identificador del producto vendido
  `id_producto` int(11) DEFAULT NULL,
  -- Define un atributo numérico para indicar el total de piezas involucradas en el registro
  `cantidad_comprada` int(11) DEFAULT NULL,
  -- Define un atributo de fecha en la cual el usuario concretó la transacción
  `fecha_compra` date DEFAULT NULL,
  -- Establece un código alfanumérico común que sirve para agrupar múltiples productos de una misma orden
  `codigo_orden` varchar(50) DEFAULT NULL,
  -- Maneja el estatus físico de entrega de la compra, asignando 'En proceso' inicial
  `estado` varchar(50) DEFAULT 'En proceso'
-- Asigna el motor relacional InnoDB y codificación estándar de caracteres
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `historial_compras`
--

-- Inserción múltiple de los registros de muestra originales para poblar el historial de ventas
INSERT INTO `historial_compras` (`id_historial`, `id_usuario`, `id_producto`, `cantidad_comprada`, `fecha_compra`, `codigo_orden`, `estado`) VALUES
(57, 1, 1, 1, '2022-05-17', 'OLD-20220517-1', 'Entregado'),
(58, 1, 2, 3, '2022-05-17', 'OLD-20220517-1', 'Entregado'),
(59, 1, 3, 2, '2022-05-17', 'OLD-20220517-1', 'Entregado'),
(60, 1, 4, 1, '2022-05-17', 'OLD-20220517-1', 'Entregado'),
(61, 1, 5, 25, '2022-05-17', 'OLD-20220517-1', 'Entregado'),
(62, 3, 1, 1, '2022-05-17', 'OLD-20220517-3', 'Entregado'),
(63, 3, 2, 1, '2022-05-17', 'OLD-20220517-3', 'Entregado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--
-- Contiene el catálogo de todos los artículos disponibles en la tienda, incluyendo su stock, precio y descripción.

-- Sentencia para inicializar la estructura base del catálogo de productos
CREATE TABLE `producto` (
  -- Especifica la llave principal única para identificar cada mercancía
  `id_producto` int(11) NOT NULL,
  -- Genera un campo de texto con longitud máxima de 100 caracteres para los títulos
  `nombre_producto` varchar(100) DEFAULT NULL,
  -- Genera un campo de texto extendido para colocar información técnica detallada
  `descripcion_producto` varchar(255) DEFAULT NULL,
  -- Declara el límite de existencia o inventario disponible actual
  `cantidad_disponible` int(11) DEFAULT NULL,
  -- Utiliza punto flotante de doble precisión para manejar decimales en los precios
  `precio_producto` double DEFAULT NULL,
  -- Define de manera textual qué fabricante respalda o elaboró el producto
  `fabricante` varchar(100) DEFAULT NULL,
  -- Indica la procedencia manufacturera del ítem asumiendo 'China' como norma general
  `origen` varchar(100) DEFAULT 'China',
  -- Delimita el departamento de la tienda en el que este artículo debe listarse
  `categoria` varchar(100) DEFAULT NULL
-- Configura la capacidad transaccional vinculante de InnoDB
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `producto`
--

-- Inserción múltiple del inventario principal para propósitos de demostración en la base de datos
INSERT INTO `producto` (`id_producto`, `nombre_producto`, `descripcion_producto`, `cantidad_disponible`, `precio_producto`, `fabricante`, `origen`, `categoria`) VALUES
(1, 'Monitor gamer curvo Samsung C32R500', 'Este monitor de 32 pulgadas te dará comodidad para estudiar, trabajar o ver una película, su resolución de 1920 x 1080 te permitirá disfrutar de momentos únicos gracias a una imagen de alta fidelidad.', 4, 5999, 'Samsung', 'China', 'monitores'),
(2, 'Teclado inalámbrico Logitech K400 Plus QWERTY español', 'Color negro, con su touchpad incorporado puedes controlar el cursor de manera sencilla y mantener una cómoda navegación en cualquier interfaz.', 3, 800, 'Logitech', 'China', 'teclados'),
(3, 'Mouse de juego Glorious Model O', 'El mouse de juego te ofrecerá la posibilidad de marcar la diferencia y sacar ventajas en tus partidas. Su conectividad y sensor suave ayudará a que te desplaces rápido por la pantalla.', 34, 2300, 'Glorious', 'China', 'accesorios'),
(4, 'Asus Zenbook Pro Duo 15 Ux582', 'ASUS - ZenBook Pro Duo 15 UX582 Laptop con pantalla táctil de 15.6 - Intel Core i9 - Memoria de 32 GB - NVIDIA GeForce RTX 3060 - SSD de 1 TB - Celestial Blue', 1, 94000, 'Asus', 'China', 'ordenadores\r\n'),
(5, 'Audífonos gamer HyperX Cloud Alpha S blue', '¡Experimenta la adrenalina de sumergirte en la escena de otra manera! Tener auriculares específicos para jugar cambia completamente tu experiencia en cada partida.', 15, 2046, 'HP', 'China', 'accesorios'),
(6, 'Xtreme Pc Geforce Rtx 3060 Ryzen 5 360O 16gb Ssd 480gb 2tb', 'Gráficos NVIDIA GeForce RTX 3060 12GB GDDR6, Memory Bus 192-bit, Engine ClockBoost 1882 MHz, Memory Clock 14 Gbps lo que proporciona un rendimiento rápido, sin interrupciones y fluido en los juegos que te apasiona.', 4, 27026, 'Xtreme PC gamer', 'Mexico', 'ordenadores'),
(7, 'Silla de escritorio Seats And Stools giratoria reclinable reposa pies ergonómica', 'Con esta silla Seats And Stools, tendrás la comodidad y el bienestar que necesitas a lo largo de tu jornada. Además, puedes ubicarla en cualquier parte de tu casa u oficina ya que su diseño se adapta a múltiples entornos.', 8, 3521, 'Seats And Stools', 'China', 'accesorios'),
(8, 'Micrófono Maono AU-PM421 condensador cardioide', 'Con este producto lograrás que la reproducción obtenida sea lo más parecida a la original. Excelente para grabar voces debido a su sensibilidad y amplio rango de frecuencia.', 5, 2015, 'Maono', 'China', 'accesorios'),
(9, 'Microsoft Xbox Series X 1TB', 'Con tu consola Xbox Series tendrás entretenimiento asegurado todos los días. Su tecnología fue creada para poner nuevos retos tanto a jugadores principiantes como expertos.', 12, 20845, 'Microsoft', 'China', 'gamer'),
(10, 'Sony PlayStation 5 825GB', 'Con tu consola PlayStation 5 tendrás entretenimiento asegurado todos los días. Su tecnología fue creada para poner nuevos retos tanto a jugadores principiantes como expertos.', 48, 20895, 'Sony', 'China', 'gamer'),
(11, 'Nintendo Switch 32GB', 'Con tu consola Switch tendrás entretenimiento asegurado todos los días. Su tecnología fue creada para poner nuevos retos tanto a jugadores principiantes como expertos.', 14, 7000, 'Nintendo', 'China', 'gamer'),
(12, 'Audífonos in-ear inalámbricos Samsung Galaxy Buds Live mystic black', 'Cuenta con tecnología True Wireless, La batería dura 6 h, Modo manos libres incluido, Asistente de voz integrado: Bixby, Con cancelación de ruido.', 54, 1920, 'Samsung', 'China', 'accesorios'),
(13, 'Rog Zephyrus 14 Amd Ryzen 9-5900hs 16gb Nvidia Rtx 3060 1tb', 'ASUS - ROG Zephyrus 14 Gaming Laptop - AMD Ryzen 9 - 16GB Memory - NVIDIA GeForce RTX 3060 - 1TB SSD - Moonlight White - Moonlight White, Modelo:GA401QM-211.ZG14', 62, 45500, 'Asus', 'China', 'ordenadores'),
(14, 'Audífonos gamer Redragon Zeus black', 'Con micrófono incorporado.\r\nTipo de conector: Jack 3.5 mm/USB.\r\nSonido superior y sin límites.\r\nCómodos y prácticos.', 25, 1327, 'Redragon', 'China', 'accesorios'),
(15, 'Mouse de juego Game Factor MOG601 rosa', 'Utiliza cable. posee rueda de desplazamiento. cuenta con 7 botones para un mayor control.\r\nCon luces para mejorar la experiencia de uso.\r\nCon sensor óptico.\r\nResolución de 32000dpi.', 24, 631, 'Game Factor', 'China', 'accesorios'),
(16, 'Monitor gamer curvo Huawei Sound Edition MateView GT LCD 34 negro', 'Pantalla LCD de 34 . Curvo. Tiene una resolución de 3440px-1440px. Relación de aspecto de 21:9. Panel VA. Su brillo es de 350cd/m.', 15, 12499, 'Huawei', 'China', 'monitores'),
(17, 'T50 Full - Silla Ergonómica - Oficina - Alta Tecnología', 'La hermosa forma del respaldo diseñado con la inspiración de la estructura proporcionada del ser humano, contribuye a una mayor comodidad ergonómica y estabilidad en su espalda.', 9, 8500, 'T50', 'Corea', 'accesorios'),
(18, 'Escritorio Para Videojuegos Gamer Con Librero Para Home', 'ESCRITORIO GAMER MODERNO IDEAL PARA TU HOGAR FACIL DE ARMAR INCLUYE ENVIO GRATUITO A TODA EL PAIS MEXICO, (APLICA RESTRICCIONES)', 47, 2500, 'GNN', 'Chiapas', 'accesorios'),
(19, 'The Walking Dead Collection Xbox One Físico Sellado 5 Juegos', 'Videojuego THE WALKING DEAD COLLECTION Para Xbox One Totalmente nuevo (Sellado) ¡Listo para envío!', 10, 2000, 'Telltale Games', 'China', 'gamer'),
(20, 'Halo Infinite Físico', 'CONVIÉRTETE. La legendaria saga Halo regresa con la campaña de Master Chief más amplia hasta la fecha y una experiencia multijugador gratuita revolucionaria.', 15, 1500, 'Xbox One', 'China', 'gamer'),
(21, 'Control joystick ACCO Brands PowerA Enhanced Wired Controller for Xbox One black', 'Compatible con: Xbox One y Televisores. Incluye un control. Con sistema de vibración incorporado. Cuenta con 1 cable usb de 3 m y 1 manual.', 45, 700, 'Slang', 'China', 'gamer'),
(22, 'Xtreme Pc Amd Radeon Vega Ryzen 5 4650g 16gb Ssd 3tb Wifi', 'Gráficos AMD Radeon 7 Renoir con frecuencia de 1900MHz y 7 núcleos lo que proporciona un rendimiento rápido, sin interrupciones y fluido en los juegos que te apasionan, más potente de lo que crees.\r\n', 36, 18542, 'Xtreme Pc Gamer', 'China', 'ordenadores'),
(23, 'Mesa Gamer Balam Rush Olympus Rgb, 2*usb, portavasos, soportes', 'Estilo: Forma en Z Accesorios: Soporte para control, soporte para headset y portavasos Puertos USB: 2 * 2.0 (carga) Iluminación: RGB Dimensiones: 100 * 64 * 77 cm', 21, 5200, 'Balam Rush', 'China', 'gamer'),
(24, 'Hp Pavilion 17 Gamer Laptop Gtx 1660ti 16gb Ram 1tb', 'La laptop HP Pavilion Gaming 15-dk0005la es una solución tanto para trabajar y estudiar como para entretenerte.', 100, 34999, 'HP', 'China', 'ordenadores'),
(25, 'Tarjeta de video Nvidia GeForce\r\nRTX 30 Series RTX 3090 24GB', 'Interfaz PCI-Express 4.0.\r\nBus de memoria: 384bit.\r\nCantidad de núcleos: 10496.\r\nFrecuencia boost del núcleo de 1.7GHz y base de 1.4GHz.\r\nResolución máxima: 7680x4320.\r\nCompatible con directX y openGL.', 10, 63999, 'Nvidia', 'China', 'gamer'),
(26, 'Procesador gamer Intel Core i9- 10850K BX8070110850K de 10 núcleos y 5.2GHZ de frecuencia con gráfic', 'Ejecuta con rapidez y eficiencia cualquier tipo de programa sin afectar el funcionamiento total del dispositivo. Memoria caché de 20 MB, rápida y volátil.\r\nProcesador gráfico Intel UHD Graphics 630. Soporta memoria RAM DDR4. Su potencia es de 125 W.', 26, 11046, 'Intel', 'China', 'gamer'),
(27, 'Disco duro externo Seagate\r\nExpansion STEB1200040O 12TB\r\nnegro', 'Útil para guardar programas y documentos con su capacidad de 12 TB. Es compatible con Windows. Disco externo de escritorio. Interfaz de conexión: USB 3.0. Apto para PC y Laptop.', 14, 8389, 'Seagate', 'China', 'accesorios'),
(28, 'Monitor Gamer 23.8 Pulgadas 165hz 1080p Led Slim Curvo Xzeal', 'El monitor LED de XZEAL proporciona imágenes claras, nítidas y colores más vivos para una experiencia visual extraordinaria, además de ser una de las pocas líneas ultra slim del mercado.', 25, 4999, 'Xzeal', 'China', 'monitores'),
(29, 'Xtreme Pc Amd Radeon Renoir Ryzen 5 4650g 8gb Ssd 240gb Wifi', 'Gráficos AMD Radeon 7 Renoir con frecuencia de 1900MHz y 7 núcleos lo que proporciona un rendimiento rápido, sin interrupciones y fluido en los juegos que te apasionan, más potente de lo que crees.\r\n', 12, 8200, 'xtreme pc gamer', 'China', 'ordenadores'),
(30, 'Control joystick inalámbrico Sony PlayStation DualSense CFI-ZCT1 cosmic red', 'Cuenta con Bluetooth. Pantalla táctil. Mando inalámbrico. Compatible con: PlayStation 5. Incluye un control.', 8, 1549, 'Sony', 'China', 'gamer');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--
-- Almacena la información personal de los clientes registrados y define si son administradores (super_usuario).

-- Sentencia para inicializar el alojamiento centralizado de cuentas de clientes
CREATE TABLE `usuario` (
  -- Llave primaria obligatoria que evita repeticiones dentro de los registros de cuenta
  `id_usuario` int(11) NOT NULL,
  -- Acepta un apodo descriptivo y legible asignado al perfil
  `nombre_usuario` varchar(100) DEFAULT NULL,
  -- Campo numérico asignado para registrar cronológicamente la fecha de nacimiento
  `fecha_nacimiento` date DEFAULT NULL,
  -- Receptáculo textual para la dirección de correo como dato logístico
  `correo` varchar(100) DEFAULT NULL,
  -- Almacén de credenciales alfanuméricas cifradas de seguridad
  `contrasena` varchar(100) DEFAULT NULL,
  -- Guarda caracteres relativos al contacto de voz por red móvil
  `numero_telefono` varchar(100) DEFAULT NULL,
  -- Destina 255 caracteres para permitir descripciones residenciales físicas muy detalladas
  `direccion` varchar(255) DEFAULT NULL,
  -- Bandera lógica de 1 bit: asume 0 para clientes ordinarios y 1 como administrador
  `super_usuario` tinyint(1) DEFAULT 0,
  -- Receptáculo destinado al resguardo de las rutas postales
  `cp` varchar(10) DEFAULT NULL
-- Consolida requerimiento de almacenamiento relacional para llaves en la última tabla
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuario`
--

-- Inserción estática de tres perfiles iniciales de prueba (incluyendo perfiles de alto nivel)
INSERT INTO `usuario` (`id_usuario`, `nombre_usuario`, `fecha_nacimiento`, `correo`, `contrasena`, `numero_telefono`, `direccion`, `super_usuario`, `cp`) VALUES
(1, 'franciscousuario', '2001-06-04', 'francgutierrezlopez@gmail.com', '2931456767', '1234567890123456', 'josegiadans', 0, '95830'),
(3, 'jonuhedios', '2005-01-25', '231u0153@gmail.com', '12345', '2491029616', 'uni TEC', 1, '95830'),
(5, 'carlo', '2001-06-04', 'carlo1@gmail.com', '12345', '2941021122', 'tec ingenieria 2', 0, '95830');

--
-- Índices para tablas volcadas
-- Definición de llaves primarias (Primary Keys) y de llaves foráneas para mantener la integridad de los datos.
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id_carrito`),
  ADD KEY `carrito_FK_1` (`id_producto`),
  ADD KEY `carrito_FK` (`id_usuario`);

--
-- Indices de la tabla `historial_compras`
--
ALTER TABLE `historial_compras`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `historial_compras_FK_1` (`id_producto`),
  ADD KEY `historial_compras_FK` (`id_usuario`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
-- Configuración para que los identificadores principales (IDs) se generen secuencialmente de forma automática.
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `historial_compras`
--
ALTER TABLE `historial_compras`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Restricciones para tablas volcadas
-- Reglas de relación entre tablas (ej. ON DELETE CASCADE asegura que al borrar un usuario, se borre su carrito).
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_FK` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `carrito_FK_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `historial_compras`
--
ALTER TABLE `historial_compras`
  ADD CONSTRAINT `historial_compras_FK` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `historial_compras_FK_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
