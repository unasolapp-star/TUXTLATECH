<?php
/* 1. CONFIGURACIÓN Y SESIÓN */

// Importa el archivo de configuración con las credenciales de la base de datos
require_once("../config/config.php");
// Inicia o reanuda la sesión actual para el manejo de variables globales
session_start();

/* 2. PROCESAR FORMULARIO (Lógica superior separada del HTML) */

// Incorpora el script que procesa la validación y registro de nuevos usuarios
include "./procesar_registro_usuario.php";

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Carga modular base de componentes compartidos como meta etiquetas -->
    <?php include "./head_html.php"; ?>
    <!-- Dictamina el nombre visible en la pestaña o ventana del explorador -->
    <title>Registro</title>
    <!-- Enlace referenciando a la figura icono adjunta al título local de la pestaña -->
    <link rel="shortcut icon" href="./../img/logo.jpg">
    <!-- Estructuras de optimización asíncrona y emparejamiento predefinido entre navegadores web (Normalize) -->
    <link rel="preload" href="./../css/normalize.css" as="style">
    <link rel="stylesheet" href="./../css/normalize.css">
    <!-- Declaración anticipada e inserción de la configuración gráfica nativa del proyecto -->
    <link rel="preload" href="./../css/estilo_generico.css" as="style">
    <link rel="stylesheet" href="./../css/estilo_generico.css">
    <link rel="preload" href="./../css/styles-iniciosesion-registro.css" as="style">
    <link rel="stylesheet" href="./../css/styles-iniciosesion-registro.css">
</head>

<body class="container">
    <!-- Elemento del bloque superior abarcando y controlando todo el menú directivo del sistema -->
    <header>
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container-fluid">
                <!-- Contenedor adaptativo cabecera principal reservado a la portabilidad móvil y comercial -->
                <div class="navbar-header">
                    <!-- Botón reactivo destinado a contraer las funcionalidades secundarias en espacio reducido -->
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-tarPOST="#myNavbar">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <!-- Referencia identitaria superior a la propia denominación de la tienda -->
                    <a class="navbar-brand" href="./../index.php">TuxtlaTech</a>
                </div>

                <div class="collapse navbar-collapse" name="myNavbar">
                    <!-- Disposición en lista de viñetas alineada hacia el extremo occidental del menú -->
                    <ul class="nav navbar-nav">
                        <li><a href="./../index.php">Lista de productos</a></li>
                    </ul>
                    <!-- Envoltura aglutinante oriental con enlaces para la gestión de acceso de usuarios -->
                    <ul class="nav navbar-nav navbar-right">
                        <li class="active">
                            <a href="#"><span class="glyphicon glyphicon-user"></span>Registrarse
                            </a>
                        </li>
                        <li>
                            <a href="./iniciar_sesion.php"><span class="glyphicon glyphicon-log-in">
                                </span>Ingresar</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Contenedor matriz para dar estructura y centralizar visualmente al formulario responsivo -->
    <div class="centrar">
        <h3 style="text-align:center; margin:0">Registro de nuevo usuario</h3>
        <!-- Define el formulario que enviará los datos a esta misma página mediante el método POST -->
        <form class="form form-horizontal" method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"])?>">
            <div class="form-group">
                <!-- Etiqueta descriptiva para el campo de nombre de usuario con contenedor de errores -->
                <label for="nombre" class="control-label">Nombre de usuario: <span class="error"><?php echo $nombreErr?></span></label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                    </div>
                    <!-- Campo de texto para que el usuario ingrese su identificador o seudónimo -->
                    <input type="text" name="nombre" class="form-control" value="<?php echo $nombre?>">
                </div>
            </div>
            <div class="form-group">
                <!-- Etiqueta descriptiva para el campo de contraseña con contenedor de errores -->
                <label for="contrasena" class="control-label">Contraseña: <span class="error"><?php echo $contraErr?></span></label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span>
                    </div>
                    <!-- Campo de texto ofuscado para que el usuario ingrese su clave secreta -->
                    <input type="password" name="contrasena" id="contrasena" class="form-control" value="<?php echo $contra?>">
                    <!-- Icono interactivo que permite alternar la visibilidad de los caracteres de la contraseña -->
                    <div class="input-group-addon" style="cursor: pointer;" onclick="togglePassword('contrasena', 'toggleIcon')">
                        <span class="glyphicon glyphicon-eye-open" id="toggleIcon" aria-hidden="true"></span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <!-- Etiqueta descriptiva para el campo de fecha de nacimiento con contenedor de errores -->
                <label for="fnac" class="control-label">Fecha de nacimiento: <span class="error"><?php echo $fechanacimientoErr?></span></label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></div>
                    <!-- Campo de selección de fecha restringido a un máximo cronológico establecido -->
                    <input type="date" name="fnac" class="form-control" value="<?php echo $fechanacimiento?>" max="2004-05-03">
                </div>
            </div>
            <div class="form-group">
                <!-- Etiqueta descriptiva para el campo de correo con contenedor de errores -->
                <label for="correo" class="control-label">Correo: <span class="error"><?php echo $correoErr?></span></label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>
                    </div>
                    <!-- Campo de texto específico para que el usuario ingrese su dirección de correo -->
                    <input type="email" name="correo" class="form-control" autocomplete="email" value="<?php echo $correo?>">
                </div>
            </div>
            <div class="form-group">
                <!-- Etiqueta descriptiva para el campo del teléfono con contenedor de errores -->
                <label for="numero_telefono" class="control-label">Número de teléfono: <span class="error"><?php echo $ntelefonoErr?></span></label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="glyphicon glyphicon-phone" aria-hidden="true"></span></div>
                    <!-- Campo de texto restringido con expresión regular JavaScript a solo dígitos numéricos -->
                    <input type="text" name="numero_telefono" class="form-control" value="<?php echo $ntelefono?>" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
            </div>
            <div class="form-group">
                <!-- Etiqueta descriptiva para el campo de dirección física con contenedor de errores -->
                <label for="direccion" class="control-label">Dirección: <span class="error"><?php echo $addressErr?></span></label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="glyphicon glyphicon-home" aria-hidden="true"></span>
                    </div>
                    <!-- Campo de texto para asentar la ubicación física residencial del usuario -->
                    <input type="text" name="direccion" class="form-control" autocomplete="address-level1" value="<?php echo $address?>">
                </div>
            </div>
            <div class="form-group">
                <!-- Etiqueta descriptiva para el campo de código postal con contenedor de errores -->
                <label for="cp" class="control-label">Código Postal (CP): <span class="error"><?php echo isset($cpErr) ? $cpErr : ''?></span></label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span>
                    </div>
                    <!-- Campo de texto limitado a 5 caracteres numéricos para estandarizar el código postal -->
                    <input type="text" name="cp" class="form-control" maxlength="5" value="<?php echo isset($cp) ? $cp : ''?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
            </div>
            
            <!-- Enlace de redirección rápida para usuarios que ya poseen una cuenta -->
            <p class="no-registrado">¿Ya tienes cuenta? <a class="btn-link" href="./iniciar_sesion.php">Ingresar</a></p>
            <div class="form-group boton">
                <!-- Botón de envío que desencadena la validación y el registro de los datos capturados -->
                <input type="submit" class="btn btn-default comprar" value="Registrarse"></input>
            </div>
        </form>
        
    </div>

    <script>
    // Función JavaScript encargada de mostrar u ocultar el texto introducido en el campo de contraseña
    function togglePassword(inputId, iconId) {
        // Recupera el elemento de entrada (input) mediante su identificador
        var input = document.getElementById(inputId);
        // Recupera el elemento del ícono mediante su identificador
        var icon = document.getElementById(iconId);
        // Evalúa si el campo se encuentra en estado oculto (password)
        if (input.type === "password") {
            // Cambia el tipo de campo a texto legible
            input.type = "text";
            // Remueve la clase del ícono de ojo abierto e incrusta el ojo cerrado
            icon.classList.remove("glyphicon-eye-open");
            icon.classList.add("glyphicon-eye-close");
        } else {
            // Restaura el tipo de campo a formato ofuscado (password)
            input.type = "password";
            // Remueve la clase del ícono de ojo cerrado e incrusta el ojo abierto
            icon.classList.remove("glyphicon-eye-close");
            icon.classList.add("glyphicon-eye-open");
        }
    }
    </script>
</body>

</html>