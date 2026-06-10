# TuxtlaTech - Plataforma E-commerce

TuxtlaTech es una plataforma de comercio electrónico desarrollada de manera estructurada utilizando tecnologías web nativas (PHP, MySQL, HTML, CSS y JavaScript). El sistema permite la gestión completa del ciclo de compra, desde la visualización y filtrado del catálogo hasta la confirmación segura del pedido, incluyendo un panel de administración para inventarios y logística.

## Características Principales

### Para el Cliente
* **Catálogo Dinámico:** Búsqueda de productos en tiempo real, filtrado por categorías y sistema de paginación.
* **Carrito de Compras:** Almacenamiento temporal en la base de datos vinculado a la sesión del usuario.
* **Gestión de Perfil:** Modificación de datos logísticos, cambio seguro de contraseña y eliminación de cuenta.
* **Historial de Pedidos:** Seguimiento detallado del estado logístico de cada compra realizada.

### Para el Administrador (Modo Admin)
* **Control de Inventario (CRUD):** Creación, lectura, actualización y eliminación de productos (incluyendo subida y asociación de imágenes).
* **Gestión de Ventas:** Panel centralizado para revisar y administrar todas las transacciones globales de la tienda.
* **Logística:** Modificación de estados de entrega (En proceso, Confirmado, Entregado, Cancelado).
* **Etiquetas de Envío:** Generación automática de hojas de envío para impresión con códigos de barras integrados.

## Tecnologías Utilizadas

* **Backend:** PHP (Nativo) con manejo de sesiones seguras.
* **Base de Datos:** MySQL / MariaDB (Sentencias preparadas para prevenir inyección SQL y transacciones atómicas).
* **Frontend:** HTML5, CSS3, JavaScript (Vanilla).
* **Librerías/Frameworks UI:** Bootstrap 3, SweetAlert2, FontAwesome.
* **APIs Externas:** Bwip-js (Renderizado de códigos de barras logísticos).

## Instalación y Configuración

1. **Entorno de Servidor:** Asegúrate de tener instalado XAMPP o un entorno equivalente que incluya Apache, MySQL/MariaDB y PHP.
2. **Clonar Repositorio:** Extrae este repositorio dentro del directorio raíz de tu servidor local (por ejemplo, en Windows suele ser `C:\xampp\htdocs\TIENDA_SAN`).
3. **Base de Datos:**
   * Abre el gestor phpMyAdmin (generalmente accesible en `localhost/phpmyadmin`).
   * Crea una nueva base de datos vacía llamada `tienda_online`.
   * Importa el archivo de volcado SQL que se encuentra en `DB/tienda_online.sql`.
4. **Configuración de Conexión:**
   * Abre el archivo de variables `config/config.php` y verifica que las credenciales coincidan con tu entorno local:
     ```php
     $db_hostname="localhost";
     $db_username="root";
     $db_password="";
     $db_name="tienda_online";
     ```
5. **Ejecución:** Abre tu navegador web y dirígete a `http://localhost/TIENDA_SAN/`.

## Estructura de la Base de Datos

El sistema está diseñado sobre un modelo relacional que garantiza la integridad de los datos. Se utilizan restricciones de llaves foráneas (`ON DELETE CASCADE`) para eliminar registros dependientes automáticamente, y transacciones para asegurar que las ventas resten inventario de forma segura.

!Diagrama E-R

## Cuentas de Prueba

El volcado SQL ya incluye cuentas preconfiguradas para probar los diferentes niveles de acceso:

* **Cuenta Administrador:**
  * Usuario: `jonuhedios`
  * Contraseña: `12345`
* **Cuenta Cliente:**
  * Usuario: `carlo`
  * Contraseña: `12345`
