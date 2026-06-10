<?php
/* 1. INICIALIZACIÓN DE VARIABLES */

// Inicializa las variables para almacenar los posibles mensajes de error del formulario
$nombreErr = $contraErr = $fechanacimientoErr = $correoErr = $ntelefonoErr = $addressErr = $cpErr = "";
// Inicializa las variables para retener los valores ingresados por el usuario
$nombre = $contra = $correo = $ntelefono = $address = $cp = "";
// Establece una fecha por defecto en caso de que no se proporcione ninguna
$fechanacimiento = "1969-12-31";
// Inicializa la bandera lógica para controlar la presencia de errores de validación
$hay_errores = false;

/* 2. DEFINICIÓN DE FUNCIONES DE VALIDACIÓN Y LIMPIEZA */

// Verifica si la función de sanitización general no ha sido declarada previamente
if (!function_exists('test_input')) {
    // Define la función para procesar y limpiar las entradas del formulario
    function test_input($data){
        // Elimina espacios en blanco al inicio y al final de la cadena
        $data = trim($data);
        // Remueve barras invertidas para prevenir caracteres de escape no deseados
        $data = stripslashes($data);
        // Convierte caracteres especiales a entidades HTML para prevenir inyecciones XSS
        return htmlspecialchars($data);
    }
}

// Verifica si la función de validación de correo no ha sido declarada previamente
if (!function_exists('checkemail')) {
    // Define la función para comprobar que el correo pertenezca a dominios permitidos
    function checkemail($str){
        // Retorna falso si el formato no coincide con dominios de Gmail, Outlook o Hotmail, o verdadero si es correcto
        return (!preg_match("/^[a-zA-Z0-9._%+-]+@(gmail\.com|outlook\.com|hotmail\.com)$/i", $str)) ? false : true;
    }
}

/* 3. PROCESAMIENTO DEL FORMULARIO POST */

// Comprueba si la solicitud actual se realizó a través del método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Evalúa si el campo del nombre de usuario fue enviado vacío
    if (empty($_POST["nombre"])) {
        // Asigna un mensaje de error indicando que el nombre es obligatorio
        $nombreErr = "* Nombre requerido";
        // Activa la bandera de errores
        $hay_errores = true;
    } else {
        // Captura y sanitiza el nombre de usuario ingresado
        $nombre = test_input($_POST["nombre"]);
    }
    
    // Evalúa si el campo de la contraseña fue enviado vacío
    if (empty($_POST["contrasena"])) {
        // Asigna un mensaje de error indicando que la contraseña es obligatoria
        $contraErr = "* Contraseña requerida";
        // Activa la bandera de errores
        $hay_errores = true;
    } else {
        // Captura y sanitiza la contraseña ingresada
        $contra = test_input($_POST["contrasena"]);
        // Valida mediante expresión regular que la contraseña cumpla con los requisitos de seguridad y longitud
        if (!preg_match("/^(?=.*\d)[a-zA-Z0-9_$#%\/]{5,18}$/", $contra)) {
            // Asigna un mensaje indicando el formato requerido para la contraseña
            $contraErr = "* De 5 a 18 caracteres, mínimo 1 dígito y letras o _$#%/";
            // Activa la bandera de errores
            $hay_errores = true;
        }
    }
    
    // Configura la zona horaria predeterminada a la correspondiente de la región
    date_default_timezone_set("America/Mexico_City");
    // Evalúa si la fecha de nacimiento enviada es la fecha por defecto o se encuentra vacía
    if (($_POST["fnac"]) == "1969-12-31" || empty($_POST["fnac"])) { 
        // Asigna un mensaje de error indicando que la fecha es obligatoria
        $fechanacimientoErr = "* Fecha requerida";
        // Activa la bandera de errores
        $hay_errores = true;
    } else {
        // Formatea la fecha de nacimiento ingresada al estándar de base de datos (Y-m-d)
        $fechanacimiento = date("Y-m-d", strtotime($_POST["fnac"]));
    }
    
    // Evalúa si el campo de correo electrónico fue enviado vacío
    if (empty($_POST["correo"])) {
        // Asigna un mensaje de error indicando que el correo es obligatorio
        $correoErr = "* Email requerido";
        // Activa la bandera de errores
        $hay_errores = true;
    } else {
        // Captura y sanitiza el correo electrónico ingresado
        $correo = test_input($_POST["correo"]);
        // Verifica que el correo pertenezca a los dominios autorizados mediante la función previamente declarada
        if (!checkemail($correo)) {
            // Asigna un mensaje de error para dominios no permitidos
            $correoErr = "* Solo permitidos @gmail, @outlook o @hotmail";
            // Activa la bandera de errores
            $hay_errores = true;
        }
    }
    
    // Evalúa si el campo del número de teléfono fue enviado vacío
    if (empty($_POST["numero_telefono"])) {
        // Asigna un mensaje de error indicando que el teléfono es obligatorio
        $ntelefonoErr = "* Número de teléfono requerido";
        // Activa la bandera de errores
        $hay_errores = true;
    } else {
        // Captura y sanitiza el número de teléfono ingresado
        $ntelefono = test_input($_POST["numero_telefono"]);
        // Verifica mediante expresión regular que el teléfono contenga como máximo 10 dígitos numéricos
        if (!preg_match("/^\d{1,10}$/", $ntelefono)) {
            // Asigna un mensaje de error para formatos de teléfono inválidos
            $ntelefonoErr = "* Máximo 10 dígitos numéricos";
            // Activa la bandera de errores
            $hay_errores = true;
        }
    }
    
    // Evalúa si el campo de dirección física fue enviado vacío
    if (empty($_POST["direccion"])) {
        // Asigna un mensaje de error indicando que la dirección es obligatoria
        $addressErr = "* Dirección requerida";
        // Activa la bandera de errores
        $hay_errores = true;
    } else {
        // Captura y sanitiza la dirección ingresada
        $address = test_input($_POST["direccion"]);
    }
    
    // Evalúa si el campo de código postal fue enviado vacío
    if (empty($_POST["cp"])) {
        // Asigna un mensaje de error indicando que el código postal es obligatorio
        $cpErr = "* Código postal requerido";
        // Activa la bandera de errores
        $hay_errores = true;
    } else {
        // Captura y sanitiza el código postal ingresado
        $cp = test_input($_POST["cp"]);
        // Verifica mediante expresión regular que el código postal consista exactamente de 5 dígitos
        if (!preg_match("/^\d{5}$/", $cp)) {
            // Asigna un mensaje de error para longitudes incorrectas en el código postal
            $cpErr = "* Debe contener exactamente 5 números";
            // Activa la bandera de errores
            $hay_errores = true;
        }
    }

    /* 4. REGISTRO EN BASE DE DATOS Y CREACIÓN DE SESIÓN */

    // Evalúa que la bandera de errores esté desactivada antes de proceder con la base de datos
    if (!$hay_errores) { 
        // Establece la conexión con el servidor MySQL mediante los parámetros globales
        $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
        // Verifica si ocurrió algún error durante el intento de conexión a la base de datos
        if (mysqli_connect_errno()) {
            // Finaliza la ejecución del script y muestra el error de conexión
            die("Fallo al conectar a MySQL: " . mysqli_connect_error());
        }
        
        // Escapa caracteres especiales en las variables para prevenir inyecciones SQL
        $nombre_seguro = mysqli_real_escape_string($con, $nombre);
        $fecha_seguro = mysqli_real_escape_string($con, $fechanacimiento);
        $correo_seguro = mysqli_real_escape_string($con, $correo);
        $contra_seguro = mysqli_real_escape_string($con, $contra);
        $tel_seguro = mysqli_real_escape_string($con, $ntelefono);
        $dir_seguro = mysqli_real_escape_string($con, $address);
        $cp_seguro = mysqli_real_escape_string($con, $cp);
        
        // Construye la consulta SQL para insertar un nuevo registro de usuario con los datos validados
        $query_insert = "INSERT INTO usuario (nombre_usuario, fecha_nacimiento, correo, contrasena, numero_telefono, direccion, cp) VALUES ('$nombre_seguro', '$fecha_seguro', '$correo_seguro', '$contra_seguro', '$tel_seguro', '$dir_seguro', '$cp_seguro')";
        // Ejecuta la consulta de inserción sobre la base de datos
        mysqli_query($con, $query_insert);

        // Ejecuta una consulta para recuperar los datos esenciales del usuario recién creado mediante su correo
        $result = mysqli_query($con, "SELECT id_usuario, super_usuario, nombre_usuario FROM usuario WHERE correo='$correo_seguro' LIMIT 1");
        // Verifica si se obtuvo el registro y lo extrae a un arreglo
        if ($row = mysqli_fetch_array($result)) {
            // Genera el arreglo de sesión personal autenticando al usuario de forma automática
            $_SESSION['sesion_personal'] = array(
                'id' => $row['id_usuario'],
                'nombre' => $row['nombre_usuario'],
                'super' => $row['super_usuario']
            );
        }
        
        // Cierra la conexión activa a la base de datos
        mysqli_close($con);
        
        // Redirige al nuevo usuario directamente a la página principal de la tienda
        header("Location: ../index.php");
        // Finaliza la ejecución del script de manera limpia
        exit;
    }
}
