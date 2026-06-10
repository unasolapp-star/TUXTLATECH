<?php
/* 1. CONFIGURACIÓN Y SESIÓN */

// Importa el archivo de configuración con las credenciales de la base de datos
require_once("../config/config.php");
// Inicia o reanuda la sesión actual para el manejo de variables globales
session_start();

/* 2. SEGURIDAD: Prevenir acceso no autorizado */

// Verifica si la variable de sesión no existe para detectar usuarios sin autenticar
if(!isset($_SESSION['sesion_personal'])){
    // Redirige al visitante hacia la página de inicio de sesión
    header("Location: ./iniciar_sesion.php");
    // Finaliza la ejecución del script para evitar accesos indebidos
    exit;
}

/* 3. CONEXIÓN ÚNICA A BASE DE DATOS Y VARIABLES GLOBALES */

// Extrae el identificador numérico del usuario activo desde la sesión
$id_usuario=$_SESSION['sesion_personal']['id'];
// Extrae el nombre del usuario activo desde la sesión
$nombre_usuario=$_SESSION['sesion_personal']['nombre'];
// Inicializa la variable para almacenar mensajes de retroalimentación
$mensaje = "";
// Inicializa la bandera lógica para controlar la presencia de errores
$error = false;
// Inicializa el arreglo que contendrá los datos del usuario extraídos de la base de datos
$usuario = [];
// Inicializa en cero el contador de artículos del carrito
$cantidad_carrito = 0;

// Establece la conexión con el servidor MySQL mediante los parámetros configurados
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
// Evalúa si ocurrió algún error durante la conexión
if (mysqli_connect_errno()) {
    // Detiene el proceso y muestra un mensaje de fallo de conexión
    die("Fallo al conectar a MySQL: " . mysqli_connect_error());
}

/* 4. LÓGICA DE ACTUALIZACIÓN DEL PERFIL */

// Verifica si la función de sanitización no ha sido declarada previamente
if (!function_exists('test_input')) {
    // Define la función para limpiar y procesar las entradas del formulario
    function test_input($data) {
        // Elimina espacios en blanco al inicio y al final de la cadena
        $data = trim($data);
        // Elimina las barras invertidas para prevenir escapes no deseados
        $data = stripslashes($data);
        // Convierte caracteres especiales en entidades HTML para evitar inyecciones XSS
        $data = htmlspecialchars($data);
        // Devuelve la cadena de texto sanitizada
        return $data;
    }
}

// Verifica si el formulario fue enviado a través del método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Captura, limpia y escapa el nuevo nombre ingresado para evitar inyecciones SQL
        $upd_nombre = mysqli_real_escape_string($con, test_input($_POST['nombre']));
        // Captura, limpia y escapa la nueva dirección de correo
        $upd_correo = mysqli_real_escape_string($con, test_input($_POST['correo']));
        // Captura, limpia y escapa el nuevo número de teléfono
        $upd_telefono = mysqli_real_escape_string($con, test_input($_POST['telefono']));
        // Captura, limpia y escapa la nueva dirección física
        $upd_direccion = mysqli_real_escape_string($con, test_input($_POST['direccion']));
        // Captura, limpia y escapa el nuevo código postal
        $upd_cp = mysqli_real_escape_string($con, test_input($_POST['cp']));
        
        // Captura y limpia la contraseña actual ingresada para propósitos de verificación
        $upd_pass_vieja = test_input($_POST['pass_vieja']);
        // Captura y limpia la nueva contraseña deseada
        $upd_pass_nueva = test_input($_POST['pass_nueva']);
        // Captura y limpia la confirmación de la nueva contraseña
        $upd_pass_conf = test_input($_POST['pass_conf']);

        // Ejecuta la consulta para recuperar la contraseña cifrada actual almacenada en la base de datos
        $res_pass = mysqli_query($con, "SELECT contrasena FROM usuario WHERE id_usuario=".$id_usuario);
        // Extrae el resultado de la consulta a un arreglo asociativo
        $row_pass = mysqli_fetch_assoc($res_pass);
        // Asigna la contraseña almacenada a una variable local
        $pass_actual = $row_pass['contrasena'];

        // Inicializa la bandera para determinar si se procederá con un cambio de contraseña
        $cambiar_pass = false;
        
        // Evalúa si el usuario intentó llenar al menos un campo de la sección de cambio de contraseña
        if (!empty($upd_pass_vieja) || !empty($upd_pass_nueva) || !empty($upd_pass_conf)) {
            // Comprueba si la contraseña actual ingresada difiere de la registrada en el sistema
            if ($upd_pass_vieja !== $pass_actual) {
                // Activa la bandera de error
                $error = true;
                // Asigna el mensaje notificando el fallo de autenticación
                $mensaje = "La contraseña actual no es correcta.";
            // Comprueba si la nueva contraseña y su confirmación no coinciden entre sí
            } elseif ($upd_pass_nueva !== $upd_pass_conf) {
                // Activa la bandera de error
                $error = true;
                // Asigna el mensaje notificando la falta de coincidencia
                $mensaje = "Las contraseñas nuevas no coinciden.";
            // Comprueba si la nueva contraseña no cumple con los requisitos mínimos de seguridad usando una expresión regular
            } elseif (!preg_match("/^(?=.*\d)[a-zA-Z0-9_$#%\/]{5,18}$/", $upd_pass_nueva)) {
                // Activa la bandera de error
                $error = true;
                // Asigna el mensaje indicando las reglas de formato para la clave
                $mensaje = "La nueva contraseña debe tener de 5 a 18 caracteres, mínimo 1 dígito y letras o _$#%/.";
            } else {
                // Habilita la autorización para procesar el cambio de clave si se superaron todas las validaciones
                $cambiar_pass = true;
            }
        }

        // Verifica si no se han registrado errores hasta el momento para validar el resto de los campos
        if (!$error) {
            // Comprueba mediante una expresión regular que el teléfono contenga exclusivamente hasta 10 dígitos numéricos
            if (!preg_match("/^\d{1,10}$/", $upd_telefono)) {
                // Activa la bandera de error
                $error = true;
                // Asigna el mensaje notificando el formato incorrecto del teléfono
                $mensaje = "El número de teléfono debe tener máximo 10 dígitos numéricos.";
            // Comprueba mediante una expresión regular que el código postal tenga exactamente 5 números, si no está vacío
            } elseif (!empty($upd_cp) && !preg_match("/^\d{5}$/", $upd_cp)) {
                // Activa la bandera de error
                $error = true;
                // Asigna el mensaje notificando la longitud inválida del código postal
                $mensaje = "El código postal debe tener exactamente 5 números.";
            }
        }

        // Ejecuta la fase de actualización en la base de datos si no existen errores de validación
        if (!$error) {
            // Determina si la actualización incluye también un cambio de clave
            if ($cambiar_pass) {
                // Construye la instrucción SQL para actualizar todos los campos de perfil junto con la nueva contraseña
                $query = "UPDATE usuario SET nombre_usuario='$upd_nombre', correo='$upd_correo', numero_telefono='$upd_telefono', direccion='$upd_direccion', cp='$upd_cp', contrasena='$upd_pass_nueva' WHERE id_usuario=$id_usuario";
            } else {
                // Construye la instrucción SQL para actualizar únicamente la información general excluyendo la contraseña
                $query = "UPDATE usuario SET nombre_usuario='$upd_nombre', correo='$upd_correo', numero_telefono='$upd_telefono', direccion='$upd_direccion', cp='$upd_cp' WHERE id_usuario=$id_usuario";
            }
            
            // Evalúa si la ejecución de la actualización en la base de datos fue exitosa
            if (mysqli_query($con, $query)) {
                // Asigna un mensaje de éxito para notificar al usuario de los cambios guardados
                $mensaje = "Perfil actualizado con éxito.";
                // Actualiza el nombre del usuario en la variable de sesión activa
                $_SESSION['sesion_personal']['nombre'] = $upd_nombre;
                // Sincroniza la variable local del nombre para su visualización inmediata en la página
                $nombre_usuario = $upd_nombre;
            } else {
                // Activa la bandera de error por problemas en la operación SQL
                $error = true;
                // Asigna un mensaje general indicando el fracaso de la transacción
                $mensaje = "Error al actualizar el perfil en la base de datos.";
            }
        }
}

/* 5. CONSULTAR DATOS DEL USUARIO Y DEL CARRITO PARA LA VISTA */

// Ejecuta una consulta para calcular la cantidad de productos que el usuario tiene pendientes en su carrito
$res_badge = mysqli_query($con, "SELECT SUM(cantidad_seleccionada) as total FROM carrito WHERE id_usuario=".$id_usuario);
// Verifica la respuesta y extrae la fila resultante
if($res_badge && $row_badge = mysqli_fetch_assoc($res_badge)){
    // Asigna el valor sumado recuperado o mantiene cero si el resultado es nulo
    $cantidad_carrito = $row_badge['total'] ? $row_badge['total'] : 0;
}

// Ejecuta la consulta para extraer íntegramente los datos de la cuenta de usuario para rellenar los campos de la interfaz
$result = mysqli_query($con, "SELECT * FROM usuario WHERE id_usuario=".$id_usuario.";");
// Extrae el arreglo numérico/asociativo de los resultados arrojados
if ($row = mysqli_fetch_array($result)) {
    // Inserta la información catalogada y estandarizada dentro de la matriz local del usuario
    $usuario[] = array(
        "correo"=>$row['correo'],
        "n_telefono"=>$row['numero_telefono'],
        "direccion"=>$row['direccion'],
        "fechanac"=>$row['fecha_nacimiento'],
        "cp"=>$row['cp'] ? $row['cp'] : ""
    );
}

/* 6. CERRAR CONEXIÓN */

// Cierra la conexión activa a la base de datos liberando los recursos de memoria
mysqli_close($con);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Carga modular base de componentes compartidos como meta etiquetas -->
    <?php include "./head_html.php"; ?>
    <!-- Dictamina el nombre visible en la pestaña o ventana del explorador -->
    <title>Historial de compra</title>
    <!-- Enlace referenciando a la figura icono adjunta al título local de la pestaña -->
    <link rel="shortcut icon" href="../img/logo.jpg">
    <!-- Estructuras de optimización asíncrona y emparejamiento predefinido entre navegadores web (Normalize) -->
    <link rel="preload" href="../css/normalize.css" as="style">
    <link rel="stylesheet" href="../css/normalize.css">
    <!-- Declaración anticipada e inserción de la configuración gráfica nativa del proyecto -->
    <link rel="preload" href="../css/estilo_generico.css" as="style">
    <link rel="stylesheet" href="../css/estilo_generico.css">
    <link rel="preload" href="../css/styles-perfil.css" as="style">
    <link rel="stylesheet" href="../css/styles-perfil.css">
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
                    <li class="navbar-text active">
                        <a href="#" class="navbar-link">
                            <!-- Despliegue textual descriptivo que reconoce a la persona con sesión activa -->
                            Sesión iniciada como
                            <u><?=$_SESSION['sesion_personal']['nombre']?></u>
                        </a>
                    </li>
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
    <!-- Título principal de la sección -->
    <h1>Perfil de usuario</h1>

    <!-- Evalúa si existe un mensaje de retroalimentación para mostrar al usuario -->
    <?php if ($mensaje): ?>
    <!-- Despliega un panel de alerta cuyo color depende de la existencia de errores (danger o success) -->
    <div class="alert alert-<?= $error ? 'danger' : 'success' ?> alert-dismissible">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <?= $mensaje ?>
    </div>
    <?php endif; ?>

    <!-- Contenedor estructurado en formato de panel para agrupar el formulario de datos -->
    <div class="panel panel-default">
        <div class="panel-heading"><b>Mis Datos y Configuración</b></div>
        <div class="panel-body">
            <!-- Define el formulario que enviará los datos de actualización a esta misma página por POST -->
            <form class="form-horizontal" method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="row">
                    <!-- Columna izquierda para agrupar la información personal general -->
                    <div class="col-md-6">
                        <h4 style="margin-top: 0;">Información Personal</h4>
                        <hr>
                        <div class="form-group">
                            <label class="control-label col-sm-4" for="nombre">Nombre de usuario:</label>
                            <div class="col-sm-8">
                                <!-- Campo de entrada inhabilitado por defecto para el nombre de usuario -->
                                <input type="text" class="form-control form-editable" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre_usuario) ?>" required disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-4">Fecha nacimiento:</label>
                            <div class="col-sm-8">
                                <!-- Campo de entrada que bloquea la modificación de la fecha de nacimiento -->
                                <input type="text" class="form-control" value="<?= htmlspecialchars($usuario[0]['fechanac']) ?>" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-4" for="correo">Correo:</label>
                            <div class="col-sm-8">
                                <!-- Campo de entrada inhabilitado por defecto para el correo electrónico -->
                                <input type="email" class="form-control form-editable" id="correo" name="correo" value="<?= htmlspecialchars($usuario[0]['correo']) ?>" required disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-4" for="telefono">Teléfono:</label>
                            <div class="col-sm-8">
                                <!-- Campo inhabilitado por defecto para el teléfono, aplicando restricciones numéricas -->
                                <input type="text" class="form-control form-editable" id="telefono" name="telefono" value="<?= htmlspecialchars($usuario[0]['n_telefono']) ?>" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-4" for="direccion">Dirección:</label>
                            <div class="col-sm-8">
                                <!-- Campo de entrada inhabilitado por defecto para la dirección de residencia -->
                                <input type="text" class="form-control form-editable" id="direccion" name="direccion" value="<?= htmlspecialchars($usuario[0]['direccion']) ?>" required disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-4" for="cp">Código Postal (CP):</label>
                            <div class="col-sm-8">
                                <!-- Campo inhabilitado por defecto para el código postal, aplicando un filtro de longitud -->
                                <input type="text" class="form-control form-editable" id="cp" name="cp" value="<?= htmlspecialchars($usuario[0]['cp']) ?>" maxlength="5" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required disabled>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha para agrupar los campos relativos al control de contraseñas -->
                    <div class="col-md-6">
                        <h4 style="margin-top: 0;">Cambiar Contraseña</h4>
                        <p class="text-muted small">Deja estos campos en blanco si no deseas cambiar tu contraseña.</p>
                        <hr>
                        <div class="form-group">
                            <label class="control-label col-sm-4" for="pass_vieja">Contraseña actual:</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <!-- Campo para ingresar la contraseña vigente bloqueado de inicio -->
                                    <input type="password" class="form-control form-editable" name="pass_vieja" disabled>
                                    <!-- Botón interactivo para alternar la visualización del texto cifrado -->
                                    <span class="input-group-addon" style="cursor: pointer;" onclick="togglePassword(this)"><i class="glyphicon glyphicon-eye-open"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-4" for="pass_nueva">Nueva contraseña:</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <!-- Campo para ingresar la clave deseada bloqueado de inicio -->
                                    <input type="password" class="form-control form-editable" name="pass_nueva" disabled>
                                    <span class="input-group-addon" style="cursor: pointer;" onclick="togglePassword(this)"><i class="glyphicon glyphicon-eye-open"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-4" for="pass_conf">Confirmar nueva:</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <!-- Campo confirmatorio de la nueva clave de acceso -->
                                    <input type="password" class="form-control form-editable" name="pass_conf" disabled>
                                    <span class="input-group-addon" style="cursor: pointer;" onclick="togglePassword(this)"><i class="glyphicon glyphicon-eye-open"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contenedor centrado para los botones de control y envío del formulario -->
                <div class="text-center" style="margin-top: 20px;">
                    <!-- Botón para alternar el estado de deshabilitado de los campos del formulario permitiendo su edición -->
                    <button type="button" id="btnEditar" class="btn btn-default" onclick="habilitarEdicion()"><span class="glyphicon glyphicon-pencil"></span> Habilitar Edición</button>
                    <!-- Botón para ejecutar el procesamiento POST de la actualización de la base de datos (Inicia desactivado) -->
                    <button type="submit" id="btnGuardar" class="btn btn-primary" disabled><span class="glyphicon glyphicon-floppy-disk"></span> Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Contenedor inferior para los enlaces de acceso general y procesos destructivos de la cuenta -->
    <div style="margin-top: 20px; margin-bottom: 50px;">
        <a href="historial_individual.php" class="btn btn-default"><span class="glyphicon glyphicon-list-alt"></span> Historial de compras</a>
        <!-- Disparador hipervinculado configurado con la alerta prevenida de seguridad hacia el script borrador -->
        <a href="eliminar_cuenta.php" class="btn btn-danger pull-right" onclick="confirmarAccion(event, this.href, '¿Eliminar cuenta?', '¿Estás seguro de que deseas eliminar tu cuenta permanentemente? Esta acción no se puede deshacer.');"><span class="glyphicon glyphicon-trash"></span> Eliminar cuenta</a>
    </div>

    <script>
    // Función responsable de remover el atributo disabled de los campos editables del formulario
    function habilitarEdicion() {
        // Recolecta todos los nodos tipo input que cuenten con la clase indicativa en el panel
        let campos = document.querySelectorAll('.form-editable');
        // Selecciona el botón de enviar actualizaciones
        let btnGuardar = document.getElementById('btnGuardar');
        // Selecciona el botón disparador del habilitado
        let btnEditar = document.getElementById('btnEditar');
        
        // Verifica el estatus actual de bloqueo del primer nodo encontrado
        let estanDeshabilitados = campos[0].disabled;
        
        // Ejecuta un bucle invirtiendo el valor actual a cada uno de los inputs encontrados
        campos.forEach(campo => {
            campo.disabled = !estanDeshabilitados;
        });
        
        // Desbloquea o bloquea el botón submit principal
        btnGuardar.disabled = !estanDeshabilitados;
        
        // Controla visualmente el estado del botón entre edición y bloqueo de escritura
        if (estanDeshabilitados) {
            btnEditar.innerHTML = '<span class="glyphicon glyphicon-lock"></span> Bloquear Edición';
        } else {
            btnEditar.innerHTML = '<span class="glyphicon glyphicon-pencil"></span> Habilitar Edición';
        }
    }

    // Función interactiva encargada de alterar el tipo del input anterior al icono para revelar la clave
    function togglePassword(button) {
        // Recupera el elemento hermano (input tag) para su tratamiento
        const input = button.previousElementSibling;
        // Recupera el componente gráfico del glifo
        const icon = button.querySelector('.glyphicon');
        // Evalúa el cambio e inversión de propiedades
        if (input.type === "password") {
            // Vuelve transparente los dígitos a simple vista
            input.type = "text";
            icon.classList.remove("glyphicon-eye-open");
            icon.classList.add("glyphicon-eye-close");
        } else {
            // Regresa la ofuscación en la cadena de seguridad
            input.type = "password";
            icon.classList.remove("glyphicon-eye-close");
            icon.classList.add("glyphicon-eye-open");
        }
    }
    </script>
</body>

</html>