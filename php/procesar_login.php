<?php
/* 1. INICIALIZACIÓN DE VARIABLES */

// Inicializa las variables para almacenar los mensajes de error de los campos en blanco
$nombreErr = $contraErr = "";
// Inicializa las variables para retener los valores ingresados por el usuario
$nombre = $contra = "";
// Inicializa una bandera lógica para determinar si existen errores en el formulario
$hay_errores = false;

/* 2. DEFINICIÓN DE FUNCIONES DE LIMPIEZA */

// Verifica si la función de sanitización de datos no ha sido declarada previamente
if (!function_exists('test_input')) {
    // Define la función para procesar y limpiar las entradas del usuario
    function test_input($data) {
        // Elimina los espacios en blanco al principio y al final de la cadena
        $data = trim($data);
        // Remueve las barras invertidas para prevenir caracteres de escape no deseados
        $data = stripslashes($data);
        // Convierte los caracteres especiales a entidades HTML para evitar inyecciones de código (XSS)
        return htmlspecialchars($data);
    }
}

/* 3. PROCESAMIENTO DEL FORMULARIO POST */

// Comprueba si la solicitud HTTP actual se realizó mediante el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Evalúa si el campo del nombre de usuario fue enviado vacío
    if (empty($_POST["nombre"])) {
        // Asigna un mensaje de error indicando que el nombre es obligatorio
        $nombreErr = "* Nombre requerido";
        // Activa la bandera indicando que se encontró un error de validación
        $hay_errores = true;
    } else {
        // Captura el nombre ingresado y lo pasa por la función de sanitización
        $nombre = test_input($_POST["nombre"]);
    }
    
    // Evalúa si el campo de la contraseña fue enviado vacío
    if (empty($_POST["contrasena"])) {
        // Asigna un mensaje de error indicando que la contraseña es obligatoria
        $contraErr = "* Contraseña requerida";
        // Activa la bandera indicando que se encontró un error de validación
        $hay_errores = true;
    } else {
        // Captura la contraseña ingresada y la pasa por la función de sanitización
        $contra = test_input($_POST["contrasena"]);
    }

    /* 4. VERIFICACIÓN EN LA BASE DE DATOS */

    // Procede con la consulta solo si no se encontraron errores en los campos del formulario
    if (!$hay_errores) { 
        // Establece la conexión con el servidor MySQL utilizando los parámetros globales
        $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
        // Evalúa si ocurrió algún error durante el intento de conexión
        if (mysqli_connect_errno()) {
            // Detiene la ejecución del script y muestra el mensaje del fallo de conexión
            die("Fallo al conectar a MySQL: " . mysqli_connect_error());
        } 
        
        // Escapa los caracteres especiales del nombre para prevenir ataques de inyección SQL
        $nombre_seguro = mysqli_real_escape_string($con, $nombre);
        // Escapa los caracteres especiales de la contraseña para prevenir inyecciones
        $contra_seguro = mysqli_real_escape_string($con, $contra);
        
        // Construye la consulta para buscar un registro que coincida exactamente con las credenciales dadas
        $query = "SELECT id_usuario, super_usuario, nombre_usuario FROM usuario WHERE nombre_usuario='$nombre_seguro' AND contrasena='$contra_seguro' LIMIT 1";
        // Ejecuta la consulta estructurada sobre la base de datos
        $result = mysqli_query($con, $query);
        
        // Verifica si la consulta fue exitosa y si logró extraer una fila con los datos del usuario
        if ($result && $row = mysqli_fetch_array($result)) {
            // Genera el arreglo de sesión personal con los datos recuperados de la cuenta
            $_SESSION['sesion_personal']=array(
                'id' => $row['id_usuario'],
                'super' => $row['super_usuario'],
                'nombre' => $row['nombre_usuario']
            );
            // Cierra la conexión a la base de datos tras una autenticación exitosa
            mysqli_close($con);
            // Redirige al usuario logueado hacia la página principal de la tienda
            header("Location: ../index.php");
            // Finaliza la ejecución del script de manera limpia
            exit;
        } else {
            // Asigna un mensaje de error si las credenciales no coinciden con ningún registro
            $nombreErr="* Credenciales incorrectas";
        }
        // Cierra la conexión a la base de datos en caso de que la autenticación haya fallado
        mysqli_close($con);
    }
}
